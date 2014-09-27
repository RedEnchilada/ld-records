<?php
	//Define that this script is meant to be run from the web interface
	define('WEBACCESS', true);
	
	include_once('util.php');
	
	// Mods only!
	$acct = acct_current();
	if ($acct['role'] != ROLE_MOD) {
		page_error('I\'m afraid I can\'t let you do that.');
		exit;
	}
	
	$map = false;
	$listing = true;
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maphash'])) {
		// Handle update
		$map = map_get(strtoupper($_POST['maphash']));
		$listing = false;
		if (isset($_POST['mapname']))
			$map['name'] = $_POST['mapname'];
		if (isset($_POST['mapdesc']))
			$map['description'] = $_POST['mapdesc'];
		$map['type'] = (isset($_POST['mapnights']) ? 1 : 0);
		$map['banned'] = (isset($_POST['mapbanned']) ? 1 : 0);
		
		// Handle level pic uploading
		if (isset($_FILES['mappic']) && $_FILES['mappic']['error'] == UPLOAD_ERR_OK) {
			$size = @getimagesize($_FILES['mappic']['tmp_name']);
			if ($size) {
				if ($map['levelpic'] != 'unknown.png')
					unlink(sprintf(url_get(URL_LEVELPICFILE, true), $map['levelpic']));
				$map['levelpic'] = $map['hash'].'-'.time().'.'.end((explode('.', $_FILES['mappic']['name'])));
				move_uploaded_file($_FILES['mappic']['tmp_name'], sprintf(url_get(URL_LEVELPICFILE, true), $map['levelpic']));
			}
		}
		
		// Now either create or update this record!
		$db = db_connect();
		$qu;
		if ($map['id'] == 0) // Create!
			$qu = $db->prepare('INSERT INTO maps(`hash`, `name`, `description`, `type`, `levelpic`, `banned`) VALUES (:hash, :name, :description, :type, :levelpic, :banned)');
		else // Update!
			$qu = $db->prepare('UPDATE maps SET `name`=:name, `description`=:description, `type`=:type, `levelpic`=:levelpic, `banned`=:banned WHERE `hash`=:hash');
		
		foreach (array('hash' => PDO::PARAM_STR, 'name' => PDO::PARAM_STR, 'description' => PDO::PARAM_STR, 'type' => PDO::PARAM_INT, 'levelpic' => PDO::PARAM_STR, 
				'banned' => PDO::PARAM_INT) as $field => $type)
			$qu->bindValue(':'.$field, $map[$field], $type);
		$qu->execute();
		
		$map['message'] = ($map['id'] == 0 ? 'Map added to database.' : 'Map entry updated in database.');
	} else if (isset($_GET['map']) && preg_match('/[0-9a-fA-F]{32}/i', $_GET['map'])) {
		$map = map_get(strtoupper($_GET['map']));
		$listing = false;
	} else {
		$db = db_connect();
		$map = $db->prepare('SELECT * FROM maps ORDER BY name ASC');
		$map->execute();
	}
	
	function page_title() {
		global $listing;
		echo ($listing ? 'Map listing - Mod panel' : 'Edit map - Mod panel');
	}
	
	function page_contents() {
		global $map, $listing;
		if (!$listing)
			page_render_modmapedit($map);
		else
			page_render_modmaplisting(function($renderfunc) {
				global $map;
				
				while($m = $map->fetch(PDO::FETCH_ASSOC))
					$renderfunc($m);
			});
	}
	
	page_display();