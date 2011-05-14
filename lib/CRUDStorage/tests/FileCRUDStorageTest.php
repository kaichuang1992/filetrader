<?php
	include_once("../FileCRUDStorage.class.php");

	$s = new FileCRUDStorage('data.php');
	$a =  $s->createEntry('test', array('title' => 'TestTitle',
				'description' => 'TestDescription'));
	$b =  $s->createEntry('test', array('title' => 'Another Test Title',
				'description' => 'Another Test Description'));
	if(!$s->deleteEntry('test', $a))
		die("unable to delete\n");

	if(!$s->updateEntry('test', $b, array('title' => 'ModifiedTestTitle',
                                'description' => 'ModifiedTestDescription')))
		die('unable to update\n');

	var_dump($s->searchEntry('test', 'modi'));

	$entry = $s->readEntry('test', $b);
	var_dump($entry);

?>
