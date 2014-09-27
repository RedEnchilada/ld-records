<?php
	//Define that this script is meant to be run from the web interface
	define('WEBACCESS', true);
	
	include_once('demo_handling.php');
	include_once('util.php');
	
	$test = demo_parse('testreplay.lmp');
	var_dump($test);
	var_dump(demo_parsetime($test['time']));
	echo(demo_safehash($test['mapcheck']));