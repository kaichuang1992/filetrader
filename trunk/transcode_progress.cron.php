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
	$transcodingNow = $storage->get("_design/files/_view/get_video_status?limit=1&startkey=[\"PROGRESS\"]&endkey=[\"PROGRESS\",{}]")->body->rows;

	if (!empty ($transcodingNow)) {
		$t = $transcodingNow[0];
		$id = $t->id;

		$info = $storage->get($id)->body;

		// read log file
		echo "Progress: $info->fileName\n";
		$progress = determineProgress($info->video->transcode-> $videoHeight->file . ".log", $info->video->duration);

		// set progress indicator

		$info->video->transcodeProgress = $progress;
		$storage->put($id, $info);
	}
	sleep(10);
} while (TRUE);

function determineProgress($logFile, $videoDuration) {
	$fp = fopen("data/$logFile", 'r');

	$pos = -1;
	$line = '';
	$c = '';
	do {
		$line = $c . $line;
		fseek($fp, $pos--, SEEK_END);
		$c = fgetc($fp);
	} while (strpos($line, 'frame') === FALSE);

	//$line = substr($line, 0, $eol);
	preg_match("/time=([0-9]+\.[0-9]+)/s", $line, $matches);

	$elapsedTime = (int) $matches[1];

	//var_dump($matches);
	//echo $elapsedTime . "\n";

	$progress = (int) ($elapsedTime / $videoDuration * 100);

	//echo trim($line);
	echo $progress . "\n";

	fclose($fp);
	//return $line;

	return $progress;
}
?>
