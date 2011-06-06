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

require_once('config.php');
require_once('../fts/utils.php'); /* steal fts utils ;) */
require_once('lib/StorageClient/StorageClient.class.php');
require_once('tests/testUtils.php');

if (!isset($config) || !is_array($config)) {
	die("broken or missing configuration file?");
}

// $dbg = TRUE;
$dbg = FALSE;

/* we use the first configured storage provider as defined in the config file,
 * make sure it is valid! */
$storageProviders = getConfig($config, 'storage_providers');

$sc = new StorageClient($storageProviders[0]);
$sc->performDecode(TRUE);

handleResponse("ping " . $storageProviders[0]['apiEndPoint'], $dbg,
		$sc->call("pingServer"));

if(!isset($argv) || empty($argv[1])) {
	die("Specify path to upload\n");
}

$pth = realpath($argv[1]);
if($pth === FALSE) {
	die("Path does not exist\n");
}

$rootDir = dirname($pth);
$relativePath = basename($pth);

echo "Recursivly uploading '$pth'...\n";

uploadPath($sc, $dbg, $rootDir, $relativePath);

function uploadPath($sc, $dbg, $rootDir, $relativePath) {
        handleResponse("mkdir $relativePath", $dbg, $sc->call("createDirectory", array('relativePath' => $relativePath),"POST"));
	$fullDir = $rootDir . DIRECTORY_SEPARATOR . $relativePath;

	foreach(glob($fullDir . DIRECTORY_SEPARATOR . "*") as $fileName) {
		echo $fileName . "\n";
		if(is_file($fileName)) {
			$r = handleResponse("utoken $fileName", $dbg, $sc->call("getUploadToken", array('relativePath' => $relativePath . DIRECTORY_SEPARATOR . basename($fileName), 'fileSize' => filesize($fileName)), "POST"));
			handleResponse("ufile $fileName", $dbg, uploadFile($r->uploadLocation, $fileName, 1024*1024));
		}else if(is_dir($fileName)) {
			uploadPath($sc, $dbg, $rootDir, $relativePath . DIRECTORY_SEPARATOR . basename($fileName));
		}
	}
}
?>
