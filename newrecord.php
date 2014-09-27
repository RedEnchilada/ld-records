<?php
	//Define that this script is meant to be run from the web interface
	define('WEBACCESS', true);
	
	include_once('util.php');
	
	include_once('demo_handling.php');
	
	// New record added, if added (return either the SQL object or a string with the error)
	$newrecord = false;
	
	function page_title() { ?>Submit new record<?php }
	
	function page_contents() {
		global $newrecord;
		page_render_newrecord($newrecord);
	}
	
	// TODO error out if not logged in
	if (!acct_loggedin()) {
		page_error('Not logged in.');
		end_page();
	}
	
	// Constants
	define('DEMOBEST_SCORE', 1);
	define('DEMOBEST_TIME', 2);
	define('DEMOBEST_RINGS', 4);
	
	// Parse replay if given
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['replay']) && $_FILES['replay']['error'] == UPLOAD_ERR_OK) {
		$replay = demo_parse($_FILES['replay']['tmp_name']);
		//var_dump($replay);
		if(isset($replay['error']))
			$newrecord = $replay['error'];
		else {
			// Replay was successfully parsed! Now check if it's a new record
			$db = db_connect();
			
			$replay['mapcheck'] = demo_safehash($replay['mapcheck']);
			
			// Check and make sure this map isn't banned
			$mapcheck = map_get($replay['mapcheck']);
			if ($mapcheck['banned'] == 1)
				$newrecord = 'This replay is for a banned map.';
			else {
				
				// Build query
				$oldrecs = $db->prepare('SELECT * FROM records WHERE user=:user AND map=:map AND `character`=:character');
				
				$acct = acct_current();
				$oldrecs->bindValue(':user', $acct['id'], PDO::PARAM_INT);
				$oldrecs->bindValue(':map', $replay['mapcheck'], PDO::PARAM_STR);
				$oldrecs->bindValue(':character', $replay['skin'], PDO::PARAM_STR);
				
				// Exec query
				$oldrecs->execute();
				
				// Check each record to see what this one beats, and remove old ones that no longer have a player best
				$newbests = DEMOBEST_SCORE|DEMOBEST_TIME;
				if (($replay['demoflags'] & 2) == 2)
					$newbests |= DEMOBEST_RINGS;
				while ($oldrec = $oldrecs->fetch(PDO::FETCH_ASSOC)) {
					// Check if this record is still a best in... something.
					$oldbests = $oldrec['playerbest'];
					
					if ($oldbests & DEMOBEST_SCORE) {
						if ($replay['score'] > $oldrec['score'])
							$oldbests &= ~DEMOBEST_SCORE;
						else if ($replay['score'] == $oldrec['score'] && $replay['time'] < $oldrec['time'])
							$oldbests &= ~DEMOBEST_SCORE;
						else
							$newbests &= ~DEMOBEST_SCORE;
					}
					
					if ($oldbests & DEMOBEST_TIME) {
						if ($replay['time'] < $oldrec['time'])
							$oldbests &= ~DEMOBEST_TIME;
						else
							$newbests &= ~DEMOBEST_TIME;
					}
					
					if ($oldbests & DEMOBEST_RINGS) {
						if ($replay['rings'] > $oldrec['rings'])
							$oldbests &= ~DEMOBEST_RINGS;
						else if ($replay['rings'] == $oldrec['rings'] && $replay['time'] < $oldrec['time'])
							$oldbests &= ~DEMOBEST_RINGS;
						else
							$newbests &= ~DEMOBEST_RINGS;
					}
					
					if (!$oldbests) {
						// Old record is now obsolete! Delete it :(
						unlink(sprintf(url_get(URL_REPLAYFILE, true), $oldrec['id']));
						$recupdate = $db->prepare('DELETE FROM records WHERE id=:id');
						$recupdate->bindValue(':id', $oldrec['id'], PDO::PARAM_INT);
						$recupdate->execute();
					} else if ($oldbests != $oldrec['playerbest']) {
						// Old record got beaten in one category, but not all of them. Update its best field!
						$recupdate = $db->prepare('UPDATE records SET playerbest=:pb WHERE id=:id');
						$recupdate->bindValue(':id', $oldrec['id'], PDO::PARAM_INT);
						$recupdate->bindValue(':pb', $oldbests, PDO::PARAM_INT);
						$recupdate->execute();
					}
				}
				
				if ($newbests) { // Make a new record!
					$recinsert = $db->prepare('INSERT INTO records(user,map,`character`,playerbest,score,time,rings,submitted)'
											.' VALUES(:user,:map,:character,:playerbest,:score,:time,:rings,:submitted)');
					
					$acct = acct_current();
					$recinsert->bindValue(':user', $acct['id'], PDO::PARAM_INT);
					
					$recinsert->bindValue(':map', $replay['mapcheck'], PDO::PARAM_STR);
					$recinsert->bindValue(':character', $replay['skin'], PDO::PARAM_STR);
					
					$recinsert->bindValue(':playerbest', $newbests, PDO::PARAM_INT);
					$recinsert->bindValue(':score', $replay['score'], PDO::PARAM_INT);
					$recinsert->bindValue(':time', $replay['time'], PDO::PARAM_INT);
					$recinsert->bindValue(':rings', $replay['rings'], PDO::PARAM_INT);
					
					$recinsert->bindValue(':submitted', time(), PDO::PARAM_INT);
					
					$recinsert->execute();
					
					// Fetch new record back
					$newrecord = $db->prepare('SELECT * FROM records WHERE map=:map ORDER BY id DESC LIMIT 1');
					$newrecord->bindValue(':map', $replay['mapcheck'], PDO::PARAM_STR);
					$newrecord->execute();
					$newrecord = $newrecord->fetch(PDO::FETCH_ASSOC); // TODO isn't actually working... look into fixing this
					//$newrecord['time'] = demo_parsetime($newrecord['time']);
					
					// Put replay in the web-accessible folder
					move_uploaded_file($_FILES['replay']['tmp_name'], sprintf(url_get(URL_REPLAYFILE, true), $newrecord['id']));
					//var_dump($newrecord);
				} else { // No new record... T_T
					$newrecord = 'This replay doesn\'t beat any of your old records.';
				}
			}
		}
	}
	
	page_display();