<?php
	if(!defined('WEBACCESS'))
		exit(0); //Prevent script from being run from the web interface
		
	// When given the file path of a replay, dissect it and return its values
	function demo_parse($filepath) {
		$file = fopen($filepath, "rb");
		
		$filesize = 12+1+1+2+16+4+2+16+1;
		$fileformat = // First bits to read
			  'a12demoheader'	// Demo header ("\xF0" "SRB2Replay" "\x0F")
			.'/Cversion'		// Version
			.'/Csubversion'		// Subversion
			.'/vdemoversion'	// Demo version
			.'/a16checksum'		// Demo checksum (unused for now)
			.'/a4demotype'		// Demo type ("PLAY")
			.'/vgamemap'		// Game map
			.'/a16mapcheck'		// Map check(sum)
			.'/Cdemoflags'		// Demo flags
//			.'/'		// 
		;
		
		$binarystring = fread($file, $filesize); // Read the first bit of the demo header
		
		// Check that we have enough file for this first bit
		if (strlen($binarystring) < $filesize) {
			fclose($file);
			return array('error' => 'File parsing error! (Probably not an SRB2 replay.)');
		}
		
		$headerone = unpack($fileformat, $binarystring); // Read the first bits, now let's do some CHECKING!
		
		//var_dump($headerone);
		
		$demoheader = chr(0xF0) . "SRB2Replay" . chr(0x0F);
		//echo ($demoheader);
		
		// Error checking on this first bit of the file
		if ($headerone['demoheader'] != $demoheader) {
			fclose($file);
			return array('error' => 'Not an SRB2 replay!');
		}
		if (
			($headerone['version']) < 201
			||($headerone['subversion']) < 8
			||($headerone['demoversion']) != 9
		) {
			fclose($file);
			return array('error' => 'This replay is too old! (Or maybe we haven\'t updated to read it yet?)');
		}
		if ($headerone['demotype'] != "PLAY" || ($headerone['demoflags'] & 6) == 0) {
			fclose($file);
			return array('error' => 'This is not a Record Attack or NiGHTS Mode replay!');
		}
		
		// The first bit checks out, so now let's figure out how we read the rest of the file!
		
		$filesize = 4+4+($headerone['demoflags'] & 2)+4+16+16;
		$fileformat = // First bits to read
			  'Vtime'			// Demo time
			.'/Vscore'			// Demo score
			.(($headerone['demoflags'] & 6) == 2 ? '/vrings' : '')		// Demo rings (not in NiGHTs)
			.'/Vrngseed'		// RNG seed (dunno why you'd need it for this, but it's in the file so let's read it)
			.'/a16playername'	// Player name (we use the account's name instead, but why not keep this?)
			.'/a16skin'			// Skin used to record the demo
//			.'/'		// 
		;
		
		$binarystring = fread($file, $filesize); // Read the first bit of the demo header
		
		// Check that we have enough file for this first bit
		if (strlen($binarystring) < $filesize) {
			fclose($file);
			return array('error' => 'File parsing error! (But it is an SRB2 replay.)');
		}
		
		$headertwo = unpack($fileformat, $binarystring); // Read the remainder of the useful info
		
		$demoinfo = array_merge($headerone, $headertwo);
		//var_dump($demoinfo);
		fclose($file);
		return $demoinfo;
	}
	
	// Turns a time, in tics, into an array with minutes, seconds, and 1/100ths of a second (as well as a preformatted string!)
	function demo_parsetime($time) {
		$r = array(
			'min' => (int)($time/35/60),
			'sec' => (int)(($time/35)%60),
			'centisec' => (int)(($time%35)*100/35),
			'tic' => $time
		);
		$r['str'] = sprintf('%d:%02d.%02d', $r['min'], $r['sec'], $r['centisec']);
		return $r;
	}
	
	// Turns a binary string hash (kind of hard to support if you ask me) into a hex-printed string safe for database storage.
	// Not sure if it's exactly what an MD5 would look like, but it's consistent for our use and that's what matters!
	function demo_safehash($binarystring) {
		$temp = unpack('v8h', $binarystring);
		$result = '';
		$i;
		for($i=1;$i<=8;$i++) {
			$result .= sprintf("%04X", $temp["h$i"]);
		}
		return $result;
	}