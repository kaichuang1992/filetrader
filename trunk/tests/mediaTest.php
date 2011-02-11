<?php
	include("../utils.php");
	if(empty($argv[1]) || !is_file($argv[1]))
		die("specify full file path for file to analyze\n");
	$metaData = array();
	$metaData['fileName'] = basename($argv[1]);
	analyzeFile($metaData, dirname($argv[1]), '/tmp');
	var_dump($metaData);
?>

