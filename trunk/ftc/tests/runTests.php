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
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'testUtils.php');

if (!isset($config) || !is_array($config)) {
	die("broken or missing configuration file?");
}

/* generate some random names in order to be reasonably sure they don't exist yet */
$fileName = 'demoFileName.txt';
$otherFileName = 'otherDemoFileName.txt';
$dirName = 'demoDirectory';
$otherDirName = 'otherDemoDirectory';

// $dbg = TRUE;
$dbg = FALSE;

/* we use the first configured storage provider as defined in the config file,
 * make sure it is valid! */
$storageProviders = getConfig($config, 'storage_providers');

$sc = new StorageClient($storageProviders[0]);
$sc->performDecode(TRUE);

handleResponse("ping " . $storageProviders[0]['apiEndPoint'], $dbg,
		$sc->call("pingServer"));
handleResponse("mkdir $dirName", $dbg,
		$sc
				->call("createDirectory", array('relativePath' => $dirName),
						"POST"));
handleResponse("mkdir $dirName/$otherDirName", $dbg,
		$sc
				->call("createDirectory",
						array(
								'relativePath' => $dirName
										. DIRECTORY_SEPARATOR . $otherDirName),
						"POST"));
/* upload a file, use random name, but actually send COPYING as it is 
 * there anyway... */
$r = handleResponse("utoken $fileName", $dbg,
		$sc
				->call("getUploadToken",
						array('relativePath' => $fileName,
								'fileSize' => filesize("COPYING")), "POST"));

handleResponse("ufile $fileName", $dbg,
		uploadFile($r->uploadLocation, "COPYING", 1024));
$r = handleResponse("dtoken $fileName", $dbg,
		$sc
				->call("getDownloadToken", array('relativePath' => $fileName),
						"POST"));

handleResponse("dfile $fileName", $dbg,
		downloadFile($r->downloadLocation, "COPYING"));
handleResponse("ls .", $dbg,
		$sc->call("getFileList", array('relativePath' => '.'), "GET"));

handleResponse("ls $dirName", $dbg,
		$sc->call("getFileList", array('relativePath' => $dirName), "GET"));

handleNegativeResponse("rmdir $dirName", $dbg,
		$sc
				->call("deleteDirectory", array('relativePath' => $dirName),
						"POST"));
handleResponse("rmdir $dirName/$otherDirName", $dbg,
		$sc
				->call("deleteDirectory",
						array(
								'relativePath' => $dirName
										. DIRECTORY_SEPARATOR . $otherDirName),
						"POST"));
handleResponse("rmdir $dirName", $dbg,
		$sc
				->call("deleteDirectory", array('relativePath' => $dirName),
						"POST"));
handleResponse("rm $fileName", $dbg,
		$sc->call("deleteFile", array('relativePath' => $fileName), "POST"));

?>
