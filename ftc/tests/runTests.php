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
require_once('utils.php');
require_once('lib/StorageClient/StorageClient.class.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'testUtils.php');

if (!isset($config) || !is_array($config)) {
	die("broken or missing configuration file?");
}

$storageProvider = getConfig($config, 'storage_providers', TRUE);
$sP = $storageProvider[0];

$consumerKey = $sP['consumerKey'];
$consumerSecret = $sP['consumerSecret'];
$apiEndpoint = $sP['apiEndpoint'];

$sc = new StorageClient($config);
$sc->performDecode(TRUE);

/* create a directory */
$response = $sc->createDirectory('demoUser', 'test');
var_export($response);

/* upload a file */
$response = $sc
		->getUploadFileLocation('demoUser', 'COPYING', filesize("COPYING"));
var_export($response);

$response = uploadFile(
		$apiEndpoint . "/?action=uploadFile&token=" . $response['uploadToken']);
var_export($response);

/* download a file */
$response = $sc->getDownloadFileLocation('demoUser', 'COPYING');
var_export($response);

$response = downloadFile(
		$apiEndpoint . "/?action=downloadFile&token="
				. $response['downloadToken'], 'COPYING');
var_export($response);

/* show directory */
$response = $sc->getFileList('demoUser');
var_export($response);

?>
