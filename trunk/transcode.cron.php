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
	$toTranscode = $storage->get("_design/files/_view/get_media_status?limit=1&startkey=[\"WAITING\"]&endkey=[\"WAITING\",{}]")->body->rows;

	if (!empty ($toTranscode)) {
		$t = $toTranscode[0];
		$id = $t->id;

		$info = $storage->get($id)->body;
		$info->transcodeStatus = 'PROGRESS';
		$storage->put($id, $info);

		$fileOwner = $info->fileOwner;
		$fileName = getConfig($config, 'file_storage_dir', TRUE) . DIRECTORY_SEPARATOR . base64_encode($fileOwner) . DIRECTORY_SEPARATOR . $info->fileName;
		if(isset($info->video)) {
			$transcodeFileName = getConfig($config, 'cache_dir', TRUE) . DIRECTORY_SEPARATOR . $info->video->transcode->$videoHeight->file;
			$newSize = $info->video->transcode-> $videoHeight->width . "x" . $info->video->transcode-> $videoHeight->height;
			// -vf transpose=1   (for rotating clockwise 90 degrees)
			$cmd = "ffmpeg -i \"$fileName\" -threads 2 -f webm -acodec libvorbis -vcodec libvpx -s $newSize -b 524288 -y $transcodeFileName";
		}elseif(isset($info->audio)) {
                        $transcodeFileName = getConfig($config, 'cache_dir', TRUE) . DIRECTORY_SEPARATOR . $info->audio->transcode->file;
                        $cmd = "ffmpeg -i \"$fileName\" -threads 2 -f ogg -acodec libvorbis -ab 96000 -y $transcodeFileName";
		}

		$returnValue = execCommand($cmd, 'data' . DIRECTORY_SEPARATOR . basename($transcodeFileName) . ".log", "Transcoding $fileName");

		$info = $storage->get($id)->body;
		if($returnValue != 0)
			$info->transcodeStatus = 'FAILED';
		else		
			$info->transcodeStatus = 'DONE';
		$storage->put($id, $info);
	}
	sleep(10);
} while (TRUE);
?>
