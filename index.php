<?php
	//Define that this script is meant to be run from the web interface
	define('WEBACCESS', true);
	
	include_once('util.php');
	
	function page_contents() {
		page_render_index();
	}
	
	page_display();