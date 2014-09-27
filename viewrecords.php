<?php
	//Define that this script is meant to be run from the web interface
	define('WEBACCESS', true);
	
	include_once('demo_handling.php');
	include_once('util.php');
	
	// Get map
	$map = false;
	if (isset($_GET['map']) && preg_match('/[0-9a-fA-F]{32}/i', $_GET['map']))
		$map = strtoupper($_GET['map']);
	
	// Get category
	$category = 0;
	define('CAT_SCORE', 1);
	define('CAT_TIME', 2);
	define('CAT_RINGS', 4);
	if (isset($_GET['category'])) {
		if (strcasecmp($_GET['category'], 'score') == 0)
			$category = CAT_SCORE;
		else if (strcasecmp($_GET['category'], 'time') == 0)
			$category = CAT_TIME;
		else if (strcasecmp($_GET['category'], 'rings') == 0)
			$category = CAT_RINGS;
	}
	
	// Get character
	$character = false;
	if (isset($_GET['character']) && preg_match('/(.){1,16}/i', $_GET['character'])) // TODO only use characters allowed in character names
		$character = $_GET['character'];
	
	// Figure out what type of page to display
	define('RT_LISTING', 0);
	define('RT_MAP', 1);
	define('RT_MAPCATEGORY', 2);
	define('RT_MAPCATEGORYCHARACTER', 3);
	$pagetype = RT_LISTING;
	if ($map) {
		$pagetype = RT_MAP;
		if ($category) {
			$pagetype = RT_MAPCATEGORY;
			if ($character)
				$pagetype = RT_MAPCATEGORYCHARACTER;
		}
	}
	
	// Separate processing functions per page
	$list = false;
	function process_listing() {
		global $list, $character, $category;
		
		define('MAPSPERPAGE', 12);
		
		$first = 0;
		$character = 1;
		if (isset($_GET['page']) && intval($_GET['page'])) {
			$first = (intval($_GET['page'])*MAPSPERPAGE)-MAPSPERPAGE;
			$character = intval($_GET['page']); // Use $character as a dummy page var
		}
		
		$db = db_connect();
		$list = $db->prepare('SELECT SQL_CALC_FOUND_ROWS * FROM maps WHERE banned=0 ORDER BY LOWER(name) ASC LIMIT :first,:last');
		$list->bindValue(':first', $first, PDO::PARAM_INT);
		$list->bindValue(':last', MAPSPERPAGE, PDO::PARAM_INT);
		$list->execute();
		
		// Use $category as a dummy "number of pages" var
		$category = $db->query('SELECT FOUND_ROWS()');
		$category = $category->fetchColumn();
		$category = (int)(($category+MAPSPERPAGE-1)/MAPSPERPAGE);
	}
	
	function process_map() {
		global $list, $map;
		
		$db = db_connect();
		$list = array();
		
		foreach (array(
			CAT_SCORE => 'score DESC, time ASC',
			CAT_TIME => 'time ASC',
			CAT_RINGS => 'rings DESC, time ASC',
		) as $category => $sort) {
			$li = $db->prepare('SELECT * FROM records WHERE map=:map AND (playerbest & :category) > 0'
								.' ORDER BY '.$sort); // Can't limit here because repeating characters can take multiple slots
			
			$li->bindValue(':map', $map, PDO::PARAM_STR);
			$li->bindValue(':category', $category, PDO::PARAM_INT);
			
			$li->execute();
			
			$list[$category] = $li;
		}
		
		$map = map_get($map);
	}
	
	function process_mapcategory() {
		global $list, 
				$map, $category;
		$sort;
		if ($category == CAT_TIME)
			$sort = 'time ASC';
		else if ($category == CAT_SCORE)
			$sort = 'score DESC, time ASC';
		else
			$sort = 'rings DESC, time ASC';
	
		$db = db_connect();
		$list = $db->prepare('SELECT * FROM records WHERE map=:map AND (playerbest & :category) > 0'
							.' ORDER BY '.$sort);
		
		$list->bindValue(':map', $map, PDO::PARAM_STR);
		$list->bindValue(':category', $category, PDO::PARAM_INT);
		
		$list->execute();
		
		$map = map_get($map);
	}
	
	function process_mapcategorycharacter() {
		global $list, 
				$map, $category, $character;
		$sort;
		if ($category == CAT_TIME)
			$sort = 'time ASC';
		else if ($category == CAT_SCORE)
			$sort = 'score DESC, time ASC';
		else
			$sort = 'rings DESC, time ASC';
	
		$db = db_connect();
		$list = $db->prepare('SELECT * FROM records WHERE map=:map AND `character`=:character AND (playerbest & :category) > 0'
							.' ORDER BY '.$sort);
		
		$list->bindValue(':map', $map, PDO::PARAM_STR);
		$list->bindValue(':character', $character, PDO::PARAM_STR);
		$list->bindValue(':category', $category, PDO::PARAM_INT);
		
		$list->execute();
		
		$map = map_get($map);
		$character = character_get($character);
		// TODO same as above, but for character
	}
	
	// Pick one of the above based on the page type
	switch($pagetype) {
		case RT_LISTING:
			process_listing();
			break;
		
		case RT_MAP:
			process_map();
			break;
			
		case RT_MAPCATEGORY:
			process_mapcategory();
			break;
			
		case RT_MAPCATEGORYCHARACTER:
			process_mapcategorycharacter();
			break;
	}
	
	function page_title() {
		global $map, $category, $character;
		if ($map) {
			echo $map['name'];
			if ($category) {
				$cat;
				switch($category) {
					case CAT_SCORE: $cat = 'Score'; break;
					case CAT_TIME:  $cat = 'Time'; break;
					case CAT_RINGS: $cat = 'Rings'; break;
				}
				
				echo ' ['.$cat;
				
				if ($character)
					echo ' as '.$character['name'];
				
				echo ']';
			}
		} else {
			echo 'Map listing';
			if ($character > 1)
				echo " (Page $character)";
		}
	}
	
	function page_bodyattrs() {
		global $pagetype;
		switch($pagetype) {
			case RT_LISTING: ?>id="maplisting"<?php break;
			
			case RT_MAP: ?>id="map"<?php break;
				
			case RT_MAPCATEGORY: ?>id="mapcategory"<?php break;
				
			case RT_MAPCATEGORYCHARACTER: ?>id="mapcategorycharacter"<?php break;
		}
	}
	
	// Page contents
	function page_contents() {
		global $list, $pagetype,
				$map, $category, $character;
		
		switch($pagetype) {
			case RT_LISTING:
				page_render_map_listing($character, $category, function($renderfunc) {
					global $list;
					while ($map = $list->fetch(PDO::FETCH_ASSOC))
						$renderfunc($map);
				});
				break;
		
			case RT_MAP:
				page_render_map_scoreboard($map, function($listfunc, $lastfunc) { // Score
					global $list, $map;
				
					// Store characters already shown - we only want the top time for each!
					$character = array();
					$count = 0;
				
					while ($rec = $list[CAT_SCORE]->fetch(PDO::FETCH_ASSOC)) {
						if (isset($character[$rec['character']]))
							continue;
							
						$character[$rec['character']] = true;
						$rec['character'] = character_get($rec['character']);
						
						$rec['user'] = acct_get($rec['user']);
						$rec['time'] = demo_parsetime($rec['time']);
						$listfunc($rec);
						
						$count++;
						
						if ($count == 5) {
							$lastfunc($map);
							return;
						}
					}
					
				}, function($listfunc, $lastfunc) { // Time
					global $list, $map;
				
					// Store characters already shown - we only want the top time for each!
					$character = array();
					$count = 0;
				
					while ($rec = $list[CAT_TIME]->fetch(PDO::FETCH_ASSOC)) {
						if (isset($character[$rec['character']]))
							continue;
							
						$character[$rec['character']] = true;
						$rec['character'] = character_get($rec['character']);
						
						$rec['user'] = acct_get($rec['user']);
						$rec['time'] = demo_parsetime($rec['time']);
						$listfunc($rec);
						
						$count++;
						
						if ($count == 5) {
							$lastfunc($map);
							return;
						}
					}
					
				}, function($listfunc, $lastfunc) { // Rings
					global $list, $map;
				
					// Store characters already shown - we only want the top time for each!
					$character = array();
					$count = 0;
				
					while ($rec = $list[CAT_RINGS]->fetch(PDO::FETCH_ASSOC)) {
						if (isset($character[$rec['character']]))
							continue;
							
						$character[$rec['character']] = true;
						$rec['character'] = character_get($rec['character']);
						
						$rec['user'] = acct_get($rec['user']);
						$rec['time'] = demo_parsetime($rec['time']);
						$listfunc($rec);
						
						$count++;
						
						if ($count == 5) {
							$lastfunc($map);
							return;
						}
					}
					
				});
				break;
				
			case RT_MAPCATEGORY:
				$cat;
				switch($category) {
					case CAT_SCORE: $cat = 'Score'; break;
					case CAT_TIME:  $cat = 'Time'; break;
					case CAT_RINGS: $cat = 'Rings'; break;
				}
				
				page_render_mapcategory_scoreboard($map, $cat, function($renderfunc) {
					global $list;
				
					// Store characters already shown - we only want the top time for each!
					$character = array();
				
					while ($rec = $list->fetch(PDO::FETCH_ASSOC)) {
						if (isset($character[$rec['character']]))
							continue;
							
						$character[$rec['character']] = true;
						$rec['character'] = character_get($rec['character']);
						
						$rec['user'] = acct_get($rec['user']);
						$rec['time'] = demo_parsetime($rec['time']);
						$renderfunc($rec);
					}
				});
				break;
		
			case RT_MAPCATEGORYCHARACTER:
				$cat;
				switch($category) {
					case CAT_SCORE: $cat = 'Score'; break;
					case CAT_TIME:  $cat = 'Time'; break;
					case CAT_RINGS: $cat = 'Rings'; break;
				}
				
				page_render_mapcharcategory_scoreboard($map, $character, $cat, function($renderfunc) {
					global $list;
					while ($rec = $list->fetch(PDO::FETCH_ASSOC)) {
						$rec['user'] = acct_get($rec['user']);
						$rec['time'] = demo_parsetime($rec['time']);
						$renderfunc($rec);
					}
				});
				break;
		}
	}
	
	page_display();