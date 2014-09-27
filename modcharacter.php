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
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['charskin'])) {
		// Handle update
		$map = character_get(strtolower($_POST['charskin']));
		$listing = false;
		if (isset($_POST['charname']))
			$map['name'] = $_POST['charname'];
		if (isset($_POST['chardesc']))
			$map['description'] = $_POST['chardesc'];
		$map['banned'] = (isset($_POST['mapbanned']) ? 1 : 0);
		
		// Handle level pic uploading
		if (isset($_FILES['charpic']) && $_FILES['charpic']['error'] == UPLOAD_ERR_OK) {
			$size = @getimagesize($_FILES['charpic']['tmp_name']);
			if ($size) {
				if ($map['icon'] != 'unknown.png')
					unlink(sprintf(url_get(URL_CHARPICFILE, true), $map['icon']));
				$map['icon'] = $map['skin'].'-'.time().'.'.end((explode('.', $_FILES['charpic']['name'])));
				move_uploaded_file($_FILES['charpic']['tmp_name'], sprintf(url_get(URL_CHARPICFILE, true), $map['icon']));
			}
		}
		
		// Now either create or update this record!
		$db = db_connect();
		$qu;
		if ($map['id'] == 0) // Create!
			$qu = $db->prepare('INSERT INTO characters(`skin`, `name`, `description`, `icon`, `banned`) VALUES (:skin, :name, :description, :icon, :banned)');
		else // Update!
			$qu = $db->prepare('UPDATE characters SET `name`=:name, `description`=:description, `icon`=:icon, `banned`=:banned WHERE `skin`=:skin');
		
		foreach (array('skin' => PDO::PARAM_STR, 'name' => PDO::PARAM_STR, 'description' => PDO::PARAM_STR, 'icon' => PDO::PARAM_STR, 
				'banned' => PDO::PARAM_INT) as $field => $type)
			$qu->bindValue(':'.$field, $map[$field], $type);
		$qu->execute();
		
		$map['message'] = ($map['id'] == 0 ? 'Character added to database.' : 'Character entry updated in database.');
	} else if (isset($_GET['character']) && preg_match('/(.){1,16}/i', $_GET['character'])) {
		$map = character_get(strtolower($_GET['character']));
		$listing = false;
	} else {
		$db = db_connect();
		$map = $db->prepare('SELECT * FROM characters ORDER BY name ASC');
		$map->execute();
	}
	
	function page_title() {
		global $listing;
		echo ($listing ? 'Skin listing - Mod panel' : 'Edit skin - Mod panel');
	}
	
	function page_contents() {
		global $map, $listing;
		if (!$listing)
			page_render_modcharacteredit($map);
		else
			page_render_modcharacterlisting(function($renderfunc) {
				global $map;
				
				while($m = $map->fetch(PDO::FETCH_ASSOC))
					$renderfunc($m);
			});
	}
	
	page_display();