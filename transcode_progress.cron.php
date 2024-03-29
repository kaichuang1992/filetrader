<?php

/*
 *  FileTrader - Web based file sharing platform
 *  Copyright (C) 2011 François Kooman <fkooman@tuxed.net>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once ('config.php');
require_once ('utils.php');

$videoHeight = 360;

if (!isset ($config) || !is_array($config))
	die("broken or missing configuration file?");

date_default_timezone_set(getConfig($config, 'time_zone', FALSE, 'Europe/Amsterdam'));

$dbName = getConfig($config, 'db_name', TRUE);

require_once ("ext/sag/src/Sag.php");

$storage = new Sag();
$storage->setDatabase($dbName);

do {
	$transcodingNow = $storage->get("_design/files/_view/get_media_status?limit=1&startkey=[\"PROGRESS\"]&endkey=[\"PROGRESS\",{}]")->body->rows;

	if (!empty ($transcodingNow)) {
		$t = $transcodingNow[0];
		$id = $t->id;
		$info = $storage->get($id)->body;

                if(isset($info->video)) {
			$progress = determineProgress($info->video->transcode->$videoHeight->file . ".log", $info->video->duration);
		} elseif(isset($info->audio)) {
                        $progress = determineProgress($info->audio->transcode->file . ".log", $info->audio->duration);
		}

		$info->transcodeProgress = $progress;
		$storage->put($id, $info);
	}
	sleep(10);
} while (TRUE);

function determineProgress($logFile, $mediaDuration) {
	/* FFmpeg transcoding output when transcoding a video and audio file:

	   video: 
		frame=  514 fps= 13 q=0.0 size=    1807kB time=20.56 bitrate= 720.1kbits/s 
	   audio: 
		size=     982kB time=105.57 bitrate=  76.2kbits/s    
	 */
	$fp = fopen("data/$logFile", 'r');

	$pos = -1;
	$line = '';
	$c = '';
	do {
		$line = $c . $line;
		fseek($fp, $pos--, SEEK_END);
		$c = fgetc($fp);
	} while (strpos($line, 'time') === FALSE);

	preg_match("/time=([0-9]+\.[0-9]+)/s", $line, $matches);

	$elapsedTime = (int) $matches[1];
	$progress = (int) ($elapsedTime / $mediaDuration * 100);
	fclose($fp);
	return $progress;
}
?>
