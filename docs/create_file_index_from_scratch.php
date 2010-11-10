<?php
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

	foreach( glob($datadir."/*") as $userDir) {
		$userName = trim(base64_decode(basename($userDir)));
		echo "**** $userName\n";
		foreach(glob($userDir."/*") as $userFile) {
			$metadata = analyzeFile($userFile);
			$metadata['fileOwner'] = $userName;
			$s->post($metadata);
			echo "[$userName] $userFile\n";
		}
	}
?>
