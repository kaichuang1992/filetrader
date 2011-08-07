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
require_once('tests/testUtils.php');

if (!isset($config) || !is_array($config)) {
    die("broken or missing configuration file?");
}

date_default_timezone_set(getConfig($config, 'time_zone', FALSE, 'Europe/Amsterdam'));

set_include_path(get_include_path() . PATH_SEPARATOR . getConfig($config, "oauth_lib_dir", TRUE));

require_once('lib/StorageClient/StorageClient.class.php');

/* generate some random names in order to be reasonably sure they don't exist yet */
$fileName = 'demoFileName.txt';
$otherFileName = 'otherDemoFileName.txt';
$dirName = 'demoDirectory';
$otherDirName = 'otherDemoDirectory';

$dbg = FALSE; 
// $dbg = TRUE;

$storageProvider = array('displayName' => 'Test Server', 'apiUrl' => 'http://localhost/fts', 'consumerKey' => 'demo', 'consumerSecret' => 'a1bf8348016c52f81498cd576d55a932');

$sc = new StorageClient($storageProvider);
$sc->performDecode(TRUE);

handleResponse("ping " . $storageProvider['apiUrl'], $dbg, $sc->call("pingServer"));
handleNegativeResponse("mkdir $dirName/$otherDirName", $dbg, $sc->call("createDirectory", array('relativePath' => "$dirName/$otherDirName"), "POST"));
handleResponse("mkdir $dirName", $dbg, $sc->call("createDirectory", array('relativePath' => $dirName), "POST"));
handleResponse("mkdir $dirName/$otherDirName", $dbg, $sc->call("createDirectory", array('relativePath' => "$dirName/$otherDirName"), "POST"));

handleNegativeResponse("mkdir $dirName/$otherDirName/.test", $dbg, $sc->call("createDirectory", array('relativePath' => "$dirName/$otherDirName/.test"), "POST"));

handleNegativeResponse("mkdir ../test", $dbg, $sc->call("createDirectory", array('relativePath' => "../test"), "POST"));

handleNegativeResponse("setdesc $fileName", $dbg, $sc->call('setDescription', array('relativePath' => $fileName, 'fileDescription' => 'Hello World'), "POST"));
handleNegativeResponse("getdesc $fileName", $dbg, $sc->call('getDescription', array('relativePath' => $fileName), "POST"));

// upload a file, use random name, but actually send COPYING as it is
// there anyway...
$r = handleResponse("utoken $fileName", $dbg, $sc->call("getUploadToken", array('relativePath' => $fileName, 'fileSize' => filesize("COPYING")), "POST"));

handleResponse("ufile $fileName", $dbg, uploadFile($r->data->uploadLocation, "COPYING", 1024));

handleResponse("setdesc $fileName", $dbg, $sc->call('setDescription', array('relativePath' => $fileName, 'fileDescription' => "'Hello World'"), "POST"));

$d = handleResponse("getdesc $fileName", $dbg, $sc->call('getDescription', array('relativePath' => $fileName), "POST"));
if ($d->data->fileDescription !== "'Hello World'") {
    die("FAIL");
}

$r = handleResponse("dtoken $fileName", $dbg, $sc->call("getDownloadToken", array('relativePath' => $fileName), "POST"));

handleResponse("dfile $fileName", $dbg, downloadFile($r->data->downloadLocation, "COPYING"));
handleResponse("ls .", $dbg, $sc->call("getDirList", array('relativePath' => '.'), "GET"));

handleResponse("ls $dirName", $dbg, $sc->call("getDirList", array('relativePath' => $dirName), "GET"));

handleNegativeResponse("rmdir $dirName", $dbg, $sc->call("deleteDirectory", array('relativePath' => $dirName), "POST"));
handleResponse("rmdir $dirName/$otherDirName", $dbg, $sc->call("deleteDirectory", array('relativePath' => "$dirName/$otherDirName"), "POST"));
handleResponse("rmdir $dirName", $dbg, $sc->call("deleteDirectory", array('relativePath' => $dirName), "POST"));
handleResponse("rm $fileName", $dbg, $sc->call("deleteFile", array('relativePath' => $fileName), "POST"));

handleNegativeResponse("setdesc $fileName", $dbg, $sc->call('setDescription', array('relativePath' => $fileName, 'fileDescription' => 'Hello World'), "POST"));

?>
