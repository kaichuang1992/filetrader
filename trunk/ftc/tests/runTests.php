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
$fileName = generateToken(4);
$dirName = generateToken(4);

/* act as this user, also use a random name */
$userName = generateToken(4);

/* we use the first configured storage provider as defined in the config file,
 * make sure it is valid! */
$spKeys = array_keys(getConfig($config, 'storage_providers'));

$sc = new StorageClient($config, $spKeys[0]);
$sc->performDecode(TRUE);

$response = $sc->call("pingServer");
handleResponse("ping", $response);

/* create a directory */
$response = $sc
		->call("createDirectory",
				array('userName' => $userName, 'dirName' => $dirName), "POST");
handleResponse("dir create", $response);

/* delete it again */
$response = $sc
		->call("deleteDirectory",
				array('userName' => $userName, 'dirName' => $dirName), "POST");
handleResponse("dir delete", $response);

/* upload a file, use random name, but actually send COPYING as it is 
 * there anyway... */
$response = $sc
		->call("getUploadToken",
				array('userName' => $userName, 'fileName' => $fileName,
						'fileSize' => filesize("COPYING")), "POST");
handleResponse("get upload token", $response);

$response = uploadFile($response->uploadLocation, "COPYING", 1024);
handleResponse("upload file", $response);

/* download the file */
$response = $sc
		->call("getDownloadToken",
				array('userName' => $userName, 'fileName' => $fileName),
				"POST");
handleResponse("get download token", $response);

$response = downloadFile($response->downloadLocation, "COPYING");
handleResponse("download file", $response);

/* delete the file */
$response = $sc
		->call("deleteFile",
				array('userName' => $userName, 'fileName' => $fileName),
				"POST");
handleResponse("delete file", $response);

/* show directory */
$response = $sc->call("getFileList", array('userName' => $userName), "GET");
handleResponse("get file list", $response);

?>
