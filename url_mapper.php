<?php
	if(!defined('WEBACCESS'))
		exit(0); //Prevent script from being run from the web interface
	
	// Array of URLs mapped
	$url_map = array();
	$url_map_count = 0;
	
	// Helper function to add URLs (automatically creates a constant)
	function url_add($identifier, $fancy, $unfancy) {
		global $url_map, $url_map_count;
		if (!preg_match('/[A-Z]/', $identifier))
			throw new Exception('Invalid identifier!');
		
		define('URL_'.$identifier, $url_map_count);
		$url_map[$url_map_count] = array(
			'fancy' => $fancy,
			'unfancy' => $unfancy
		);
		
		$url_map_count++;
	}
	
	// Get a URL (pass an identifier!)
	function url_get($identifier, $filesystem_loc = false) {
		global $url_map, $db_config;
		if ($filesystem_loc)
			return $url_map[$identifier]['unfancy'];
		else if (fancy_urls())
			return $db_config['root_page'].$url_map[$identifier]['fancy'];
		else
			return $db_config['root_page'].$url_map[$identifier]['unfancy'];
	}
	
	// Now list all the URLs!
	url_add('INDEX', '', 'index.php');
	
	// Login-based pages
	url_add('LOGIN', 'login', 'login.php');
	url_add('LOGOUT', 'logout', 'logout.php');
	url_add('REGISTER', 'register', 'register.php');
	
	// Record- and listing-related URLs
	url_add('NEWRECORD', 'record/new', 'newrecord.php');
	url_add('RECORDLISTING', 'record', 'viewrecords.php');
	url_add('RECORDLISTING_PAGE', 'record?page=%d', 'viewrecords.php?page=%d');
	url_add('RECORDMAP', 'record/%s', 'viewrecords.php?map=%s');
	url_add('RECORDMAPCATEGORY', 'record/%s/%s', 'viewrecords.php?map=%s&category=%s');
	url_add('RECORDMAPCATEGORYCHARACTER', 'record/%s/%s/%s', 'viewrecords.php?map=%s&category=%s&character=%s');
	// map = /[0-9a-fA-F]{32}/i, character = /.{1,16}/, category = /(score|time|rings)/
	
	// Show user/character
	url_add('SHOWUSER', 'user/%s', 'user.php?user=%s');
	url_add('SHOWCHARACTER', 'character/%s', 'character.php?character=%s');
	url_add('CHARACTERLISTING', 'character', 'character.php');
	// user = /[\d]+/
	
	// Mod functions
	url_add('MODPANEL', 'mod', 'mod.php');
	url_add('MODPANEL_MAPLISTING', 'mod/map', 'modmap.php');
	url_add('MODPANEL_EDITMAP', 'mod/map/%s', 'modmap.php?map=%s');
	url_add('MODPANEL_CHARACTERLISTING', 'mod/character', 'modcharacter.php');
	url_add('MODPANEL_EDITCHARACTER', 'mod/character/%s', 'modcharacter.php?character=%s');
	
	// Filesystem naming (just in case it needs changed later!)
	url_add('REPLAYFILE', 'replays/replay-%08d.lmp', 'replays/replay-%08d.lmp');
	url_add('LEVELPICFILE', 'levelpics/%s', 'levelpics/%s');
	url_add('CHARPICFILE', 'charpics/%s', 'charpics/%s');