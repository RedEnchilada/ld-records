<?php
	if(!defined('WEBACCESS'))
		exit(0); //Prevent script from being run from the web interface
	
	// Time logging
	$tstam = microtime(true);
	function exec_time() {
		global $tstam;
		return microtime(true)-$tstam;
	}
		
	// Sub-files with other common utilities
	include_once('login_handling.php');
	include_once('url_mapper.php');
	
	// Page template
	include_once('page_template.php');
	
	// Connect to database, returning the PDO holding the connection
	include_once('db_config.php'); // $db_config['host', 'user', 'pass', 'database']
	$db_conn = false;
	function db_connect() {
		global $db_conn, $db_config;
		if ($db_conn !== false)
			return $db_conn;
		try {
			$db_conn = new PDO("mysql:host=${db_config['host']};dbname=${db_config['database']};charset=utf8",
							$db_config['user'], $db_config['pass'], array(PDO::ATTR_EMULATE_PREPARES => false, 
																	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			return $db_conn;
		} catch (PDOException $e) {
			$db_conn = false;
			return false;
		}
	}
	
	// Start session
	session_name($db_config['cookie_remember']);
	session_start();
	
	// Render page without extra whitespace
	function page_smallrender() {
		ob_start();
		page_render();
		$o = ob_get_contents();
		ob_end_clean();
		$o = preg_replace("/[\s]+/", " ", $o);
		$o = preg_replace("/> </", "><", $o);
		$o = preg_replace("/ (\/?>)/", "$1", $o);
		echo $o;
	}
	
	// Helper function to render the page with/without whitespace trimming to be easily settable (call this to render the page!)
	function page_display() {
		if(isset($_GET['small']))
			page_smallrender();
		else if(isset($_GET['large']))
			page_render();
		else
			page_render();
			//page_smallrender();
	}
	
	// Render an error page
	function page_error($errormsg) {
		global $page_errormessage;
		$page_errormessage = $errormsg;
		page_display();
	}
	
	// Finish rendering the page
	function end_page() {
		exit;
	}
	
	// Are we using fancy URLS?
	function fancy_urls() {
		global $db_config;
		return $db_config['fancy_urls'];
	}
	
	// Get IP of current user
	function user_ip() {
		// TODO
		return 'q.w.o.p';
	}
	
	// Get map by hash, caching as appropriate
	$map_cache = array();
	function map_get($hash) {
		global $map_cache;
		
		if (isset($map_cache[$hash]))
			return $map_cache[$hash];
		
		$db = db_connect();
		$qu = $db->prepare('SELECT * FROM maps WHERE hash=:hash LIMIT 1');
		$qu->bindValue(':hash', $hash, PDO::PARAM_STR);
		$qu->execute();
		
		$map = $qu->fetch(PDO::FETCH_ASSOC);
		
		if (!$map)
			$map = array(
				'id' => 0,
				'hash' => $hash,
				'name' => 'Unknown map #'.$hash,
				'description' => '',
				'type' => 0, // NOTE: have failsafes so undefined NiGHTS maps don't error
				'levelpic' => 'unknown.png',
				'banned' => 0,
			); // Default map listing
		
		$map_cache[$hash] = $map;
		return $map;
	}
	
	// Get character by skin name, caching as appropriate
	$skin_cache = array();
	function character_get($skin) {
		global $skin_cache;
		
		if (isset($skin_cache[$skin]))
			return $skin_cache[$skin];

		$db = db_connect();
		$qu = $db->prepare('SELECT * FROM characters WHERE skin=:skin LIMIT 1');
		$qu->bindValue(':skin', $skin, PDO::PARAM_STR);
		$qu->execute();
		
		$char = $qu->fetch(PDO::FETCH_ASSOC);
		
		if (!$char)
			$char = array(
				'id' => 0,
				'skin' => $skin,
				'name' => 'Unknown \''.$skin.'\'',
				'icon' => 'unknown.png',
				'description' => '',
				'banned' => false,
			); // Default map listing
		
		$skin_cache[$skin] = $char;
		return $char;
	}