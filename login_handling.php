<?php
	if(!defined('WEBACCESS'))
		exit(0); //Prevent script from being run from the web interface
	
	// All of the stuff in this file needs actually done.
	
	// Make sure we can connect to the database, for logins
	include_once('util.php');
	
	// Constants
	define('ROLE_USER', 0);
	define('ROLE_BANNED', 1);
	define('ROLE_MOD', 2);
	
	// Get currently logged-in user in the database (returns 0 if logged out)
	function acct_current() {
		global $_SESSION;
		if (isset($_SESSION['user']))
			return acct_get($_SESSION['user']);
		else
			return acct_get(0);
	}
	
	// Check if user is logged in
	function acct_loggedin() {
		global $_SESSION;
		return (isset($_SESSION['user']) && $_SESSION['user'] > 0);
	}
	
	// Attempt to log in with a certain username and password
	function acct_login($user, $pass, $remember=false) {
		global $_SESSION;
		// TODO store this info in the session
		$acct = acct_getname($user);
		if (!passwd_compare($pass, $acct['password']))
			return array('error' => 'Incorrect username or password.');
		
		// TODO store account login in session, pass/create rememberme cookie, and store IP used to login
		$_SESSION['user'] = $acct['id'];
		
		return $acct;
	}
	
	// Log out if logged in
	function acct_logout() {
		global $_SESSION;
		$_SESSION['user'] = 0;
		
		// TODO clear remember session?
		return acct_get(0);
	}
	
	// Remember an account
	function acct_remember() {
		global $_SESSION;
		if (isset($_SESSION['user']))
			return;
		// TODO
	}
	
	// Register an account with the specified username and password (checks for username occupation automatically)
	function acct_register($user, $pass, $remember=false) {
		// Is this username already taken?
		$utest = acct_getname($user);
		if ($utest['id'] > 0)
			return array('error' => 'This username is already taken!');
		
		// It isn't! Let's register!
		$db = db_connect();
		$reg = $db->prepare('INSERT INTO users (username, password, lastloginip, remembercookie, role) VALUES (:username, :password, :lastloginip, :remembercookie, :role)');
		$reg->bindValue(':username', $user, PDO::PARAM_STR);
		$reg->bindValue(':password', passwd_hash($pass), PDO::PARAM_STR);
		$reg->bindValue(':lastloginip', user_ip(), PDO::PARAM_STR);
		$reg->bindValue(':remembercookie', 'blapck', PDO::PARAM_STR); // It should never be possible to do a remembered login with this user ID
		$reg->bindValue(':role', ROLE_USER, PDO::PARAM_INT);
		$reg->execute();
		
		return acct_login($user, $pass, $remember);
	}
	
	// Get user by ID, caching results to avoid unnecessary lookups
	$acct_cache = array();
	function acct_get($id) {
		global $acct_cache;
		if (isset($acct_cache[$id]))
			return $acct_cache[$id];
		
		$user = false;
		
		if ($id > 0) {
			$db = db_connect();
			$qu = $db->prepare('SELECT * FROM users WHERE id=:id');
			$qu->bindValue(':id', $id, PDO::PARAM_INT);
			$qu->execute();
			
			$user = $qu->fetch(PDO::FETCH_ASSOC);
		}
		
		if (!$user)
			$user = array(
				'id' => 0,
				'username' => 'Who even is this user??',
				'password' => 'HAHA THIS WILL NEVER RESULT IN A MATCH ANYWAY',
				'lastloginip' => 'mystic.is.a.roboass',
				'remembercookie' => 'blapck',
				'role' => -1
			); // Default for non-logged-in users
		
		$acct_cache[$user['id']] = $user;
		$acct_cache[$user['username']] = $user;
		return $user;
	}
	
	// See above, but by name
	function acct_getname($id) {
		global $acct_cache;
		if (isset($acct_cache[$id]))
			return $acct_cache[$id];
		
		$user = false;
		
		$db = db_connect();
		$qu = $db->prepare('SELECT * FROM users WHERE username=:id');
		$qu->bindValue(':id', $id, PDO::PARAM_STR);
		$qu->execute();
		
		$user = $qu->fetch(PDO::FETCH_ASSOC);
		
		if (!$user)
			$user = array(
				'id' => 0,
				'username' => 'Who even is this user??',
				'password' => 'HAHA THIS WILL NEVER RESULT IN A MATCH ANYWAY',
				'lastloginip' => 'mystic.is.a.roboass',
				'remembercookie' => 'blapck',
				'role' => -1,
			); // Default for non-logged-in users
		
		$acct_cache[$user['id']] = $user;
		$acct_cache[$user['username']] = $user;
		return $user;
	}
	
	// Hash a password
	function passwd_hash($password) {
		global $db_config;
		$cost = $db_config['passcost'];
		$salt=sprintf('$2a$%02d$',$cost);
		
		$chars='./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		mt_srand();
		for($i=0;$i<22;$i++)
			$salt .= $chars[mt_rand(0,63)];
			
		return crypt($db_config['passpepper'].$password, $salt);
	}
	
	// Does this password match the hash given?
	function passwd_compare($password, $hash) {
		global $db_config;
		return crypt($db_config['passpepper'].$password, $hash) == $hash;
	}