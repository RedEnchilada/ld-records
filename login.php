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
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
		$user = $_POST['username'];
		$pass = $_POST['password'];
		$remember = isset($_POST['remember']);
		$acct = acct_login($user, $pass, $remember);
	}
	
	function page_contents() {
		global $acct;
		page_render_login($acct);
	}
	
	page_display();