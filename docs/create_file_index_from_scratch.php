<?php
/*  
 *  FileTrader - Web based file sharing platform
 *  Copyright (C) 2010 François Kooman <fkooman@tuxed.net>
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

	include_once('config.php');
	include_once('utils.php');

	date_default_timezone_set($config['time_zone']);

	include_once('ext/sag/src/Sag.php');

	/* this script recreates the index for files available in 
	   the data/files directory by linking them to users, analyzing
	   their type, extracting some meta-data and other useful 
	   information. 

	   WARNING: custom added metadata and sharing properties will be 
	   overwritten! 
	*/

	$datadir = "data/files";

	/* each user has their own data directory, easy to link files
	   to users in case the index breaks, so at least the files 
	   themselves are not lost, only the metadata added by the user and
	   sharing groups.
	 */

	$dbName = $config['db_name'];

	$s = new Sag();
	$dbs = $s->getAllDatabases()->body;
	/* if db already exists, delete it */
	if(in_array($dbName, $dbs))
	        $s->deleteDatabase($dbName);
	$s->createDatabase($dbName);
	$s->setDatabase($dbName);

	$view = array( "_id" => "_design/files",
		       "type" => "view",
		       "language" => "javascript",
		       "views" => array ( 
				"all" => array ("map" => "function(doc) { emit(null, doc)}"),
				"by_date" => array ("map" => "function(doc) { emit(doc.fileDate, doc)}"),
				
"tag_count" => array (
"map" => "
function(doc) {
  if (doc.type == 'file' && doc.fileTags) {
    doc.fileTags.forEach(function(tag) {
      emit(tag, 1);
    });
  }
}", 
"reduce" => "
function(keys, values) {
  return sum(values);
}"),

"by_tag" => array (
"map" => "
function(doc) {
  if (doc.type == 'file' && doc.fileTags) {
    doc.fileTags.forEach(function(tag) {
      emit(tag, doc);
    });
  }
}
"),

	),
);
	// Add the view


	$s->post($view);

	foreach( glob($datadir."/*") as $userDir) {
		$userName = trim(base64_decode(basename($userDir)));
		echo "**** $userName\n";
		foreach(glob($userDir."/*") as $userFile) {
                        echo "[$userName] Analyzing: $userFile\n";
			$metadata = analyzeFile($userFile);
			$metadata['fileOwner'] = $userName;
			$metadata['fileTags'] = array ( 'Label1', 'Tag1', 'Taggertje' );
			$s->post($metadata);
			echo "[$userName] Imported:  $userFile\n";
		}
	}
?>
