<?php
	//Define that this script is meant to be run from the web interface
	define('WEBACCESS', true);
	
	include_once('util.php');
	
	// TODO error out if logged in
	if (acct_loggedin()) {
		page_error('Already logged in.');
		end_page();
	}
	
	$acct = false;
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['pconfirm'])) {
		$user = trim($_POST['username']);
		$pass = $_POST['password'];
		$pass2 = $_POST['pconfirm'];
		$remember = isset($_POST['remember']);
		
		if ($pass != $pass2)
			$acct = array('error' => 'The passwords don\'t match!');
		else if (strlen($user) < 3)
			$acct = array('error' => 'Usernames must be 3-64 characters long.');
		else if (strlen($user) > 64)
			$acct = array('error' => 'Usernames must be 3-64 characters long.');
		else
			$acct = acct_register($user, $pass, $remember);
	}
	
	function page_contents() {
		global $acct;
		page_render_register($acct);
	}
	
	page_display();