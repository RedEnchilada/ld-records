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
	
	function page_contents() {
		page_render_mod();
	}
	
	page_display();