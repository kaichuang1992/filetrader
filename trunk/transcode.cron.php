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
	$toTranscode = $storage->get("_design/files/_view/all_waiting_for_transcode?limit=1")->body->rows;

	if(!empty($toTranscode)) {
		$t = $toTranscode[0];
		$id = $t->id;

	 	$info = $storage->get($id)->body;
		$info->video->transcodeStatus = 'PROGRESS';
	        $storage->put($id, $info);

		$fileOwner = $info->fileOwner;
		$fileName = getConfig($config, 'file_storage_dir', TRUE) . DIRECTORY_SEPARATOR . base64_encode($fileOwner) . DIRECTORY_SEPARATOR . $info->fileName;
		$transcodeFileName = getConfig($config, 'cache_dir', TRUE) . DIRECTORY_SEPARATOR . $info->video->transcode->$videoHeight->file;
		$newSize = $info->video->transcode->$videoHeight->width . "x" . $videoHeight;
		// -vf transpose=1   (for rotating clockwise 90 degrees)
		$cmd = "ffmpeg -i \"$fileName\" -threads 8 -f webm -acodec libvorbis -vcodec libvpx -s $newSize -b 1000000 $transcodeFileName";
	
	        execCommand($cmd, 'data/transcoder.log', "Transcoding $fileName");
	
	        $info = $storage->get($id)->body;
		$info->video->transcodeStatus = 'DONE';
	        $storage->put($id, $info);
	}
}while(!empty($toTranscode));
?>
