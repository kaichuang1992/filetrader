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

include_once ('config.php');
include_once ('utils.php');

if (!isset ($config) || !is_array($config))
	die("broken or missing configuration file?");

date_default_timezone_set(getConfig($config, 'time_zone', FALSE, 'Europe/Amsterdam'));

include_once ('ext/sag/src/Sag.php');

/* this script recreates the index for files available in 
   the data/files directory by linking them to users, analyzing
   their type, extracting some meta-data and other useful 
   information. 

   WARNING: custom added metadata and sharing properties will be 
   overwritten! 
*/

$fileStorageDir = getConfig($config, 'file_storage_dir', TRUE);
$cachePath = getConfig($config, 'cache_dir', TRUE);

/* each user has their own data directory, easy to link files
   to users in case the index breaks, so at least the files 
   themselves are not lost, only the metadata added by the user and
   sharing groups.
 */

$dbName = getConfig($config, 'db_name', TRUE);

$s = new Sag();
$dbs = $s->getAllDatabases()->body;
/* if db already exists, delete it */
if (in_array($dbName, $dbs))
	$s->deleteDatabase($dbName);
$s->createDatabase($dbName);
$s->setDatabase($dbName);

/* load all the map/reduce js functions from mapReduce directory */
$views = array ();
foreach (glob("docs/mapReduce/*") as $mrFiles) {
	list ($name, $type) = explode(".", basename($mrFiles));
	$views[$name][$type] = file_get_contents($mrFiles);
}
$view = array (
	"_id" => "_design/files",
	"type" => "view",
	"language" => "javascript",
	"views" => $views,
	
);

// Add the view
$s->post($view);

// Delete all cache entries as to not accumulate too many
foreach (glob($cachePath . "/*") as $cacheEntry) {
	unlink($cacheEntry);
}

// Import all new entries
foreach (glob($fileStorageDir . "/*") as $userDir) {
	$userName = trim(base64_decode(basename($userDir)));
	foreach (glob($userDir . "/*") as $userFile) {
		if (!is_file($userFile))
			continue;
		echo "[$userName] Analyzing: " . basename($userFile) . "\n";

		$metaData = new stdClass();
		$metaData->fileName = basename($userFile);
		analyzeFile($metaData, dirname($userFile), $cachePath);
		$metaData->fileOwner = $userName;
		$metaData->fileTags = array (
			'Demo Tag',
			"Length" . strlen(basename($userFile))
		);
		$metaData->fileDescription = 'Imported on ' . strftime("%c", time());
		$metaData->fileGroups = array ();
		$metaData->fileTokens = array ();
		$metaData->fileLicense = 'none';
		$metaData->fileTags = array ();
		$s->post($metaData);
	}
}
?>
