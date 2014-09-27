<?php
	if(!defined('WEBACCESS'))
		exit(0); //Prevent script from being run from the web interface
	
	$db_config = array(
		'host' => '',
		'user' => '',
		'pass' => '',
		'passpepper' => '', // 8 chars only please
		'passcost' => 0,
		'database' => '',
		'root_page' => '/srbrecords/',
		'fancy_urls' => false,
		'cookie_session' => 'LDRecord',
		'cookie_remember' => 'LDRemember',
	);