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

if (!isset ($config) || !is_array($config))
        die("broken or missing configuration file?");

date_default_timezone_set(getConfig($config, 'time_zone', FALSE, 'Europe/Amsterdam'));

$dbName = getConfig($config, 'db_name', TRUE);

require_once ("ext/sag/src/Sag.php");
//require_once ("lib/Files/Files.class.php");	// FIXME needed?

$storage = new Sag();
$storage->setDatabase($dbName);

$toTranscode = $storage->get("_design/files/_view/to_transcode")->body->rows;

//var_dump($toTranscode);
foreach($toTranscode as $t) {
	$id = $t->id;

 	$info = $storage->get($id)->body;
	$info->video->transcodeStatus = 'PROGRESS';
        $storage->put($id, $info);

	$fileOwner = $info->fileOwner;
	$fileName = getConfig($config, 'file_storage_dir', TRUE) . DIRECTORY_SEPARATOR . base64_encode($fileOwner) . DIRECTORY_SEPARATOR . $info->fileName;
	$width = $info->video->width;
        $height = $info->video->height;
	$transcodeFileName = getConfig($config, 'cache_dir', TRUE) . DIRECTORY_SEPARATOR . $info->video->transcode->{360};
	$resize = scaleVideo(array($width, $height));
	$newSize = $resize['width'] . "x" . $resize['height'];
	$cmd = "ffmpeg -i \"$fileName\" -threads 8 -f webm -acodec libvorbis -vcodec libvpx -s $newSize -b 1000000 $transcodeFileName";

#	echo "$cmd\n";
        execCommand($cmd, 'data/transcoder.log', "Transcoding $fileName");

        $info = $storage->get($id)->body;
	$info->video->transcodeStatus = 'DONE';
        $storage->put($id, $info);
}
?>
