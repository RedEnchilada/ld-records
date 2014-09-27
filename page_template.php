<?php
	if(!defined('WEBACCESS'))
		exit(0); //Prevent script from being run from the web interface
		
	// Error message to be set by page_error
	$page_errormessage = false;
	
	// Rendering helpers (shouldn't need modified)
	function pageR_title() {
		global $page_errormessage;
		if ($page_errormessage) {
			?>Error<?php
			return true;
		}
		
		if (function_exists("page_title")) {
			page_title();
			return true;
		}
		return false;
	}
	
	function pageR_bodyattrs() {
		global $page_errormessage;
		if ($page_errormessage) {
			?>id="error"<?php
			return;
		}
		
		if (function_exists("page_bodyattrs")) {
			page_bodyattrs();
		}
	}
	
	function pageR_contents() {
		global $page_errormessage;
		if ($page_errormessage) {
			page_render_error($page_errormessage);
			return;
		}
		
		if (function_exists("page_contents")) {
			page_contents();
		}
	}
	
	function pageR_ago($t) {
		$t = time()-$t;
		if ($t < 60)
			return 'Just now';
		
		$t = (int)($t/60);
		if ($t < 2)
			return '1 minute ago';
		if ($t < 60)
			return sprintf('%d minutes ago', $t);
		
		$t = (int)($t/60);
		if ($t < 2)
			return '1 hour ago';
		if ($t < 24)
			return sprintf('%d hours ago', $t);
		
		$t = (int)($t/24);
		if ($t < 2)
			return '1 day ago';
		return sprintf('%d days ago', $t);
	}
	
	// Base page template
	function page_render() { ?>
 <!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?php if(pageR_title()) echo ' - '; ?>LightDash Record Attack (alpha)</title>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<?php if(false ? !isset($_GET['less']) : isset($_GET['css'])) { ?>
		<link href="/srbrecords/style/less.css" rel="stylesheet" type="text/css" />
		<?php } else { ?>
		<link href="/srbrecords/style/less.less" rel="stylesheet" type="text/less" />
		<script type="text/javascript">
		less = {
			env: "development", // or "production"
			poll: 15000
		};
		</script>
		<script src="/srbrecords/style/less-1.5.0.min.js" type="text/javascript"></script>
		<script type="text/javascript">less.watch();</script>
		<?php } ?>
		<link rel="shortcut icon" href="favicon.png" />
	</head>
	<body <?php pageR_bodyattrs(); ?>>
		<div id="header">
			<h1 id="title">LightDash Records</h1>
			<div id="title_nav_container">
				<ul id="site_nav">
					<li id="site_nav_index"><a href="<?php echo url_get(URL_INDEX); ?>">Home</a></li>
					<li id="site_nav_recordlisting"><a href="<?php echo url_get(URL_RECORDLISTING); ?>">Browse by map</a></li>
					<li id="site_nav_characterlisting"><a href="<?php echo url_get(URL_CHARACTERLISTING); ?>">Browse by character</a></li>
				</ul>
				<?php if (acct_loggedin()) {
					$acct = acct_current();
					?>
					<ul id="user_nav" class="logged_in">
						<li id="user_nav_showuser"><a href="<?php echo sprintf(url_get(URL_SHOWUSER), $acct['id']); ?>"><?php echo htmlspecialchars($acct['username']) ?></a></li>
						<?php if ($acct['role'] == ROLE_MOD) { ?>
							<li id="user_nav_modpanel"><a href="<?php echo url_get(URL_MODPANEL); ?>">Mod panel</a></li>
						<?php } ?>
						<li id="user_nav_newrecord"><a href="<?php echo url_get(URL_NEWRECORD); ?>">Submit replay</a></li>
						<li id="user_nav_logout"><a href="<?php echo url_get(URL_LOGOUT); ?>">Logout</a></li>
					</ul>
				<?php } else { ?>
					<ul id="user_nav" class="logged_out">
						<li id="user_nav_login"><a href="<?php echo url_get(URL_LOGIN); ?>">Login</a></li>
						<li id="user_nav_register"><a href="<?php echo url_get(URL_REGISTER); ?>">Register</a></li>
					</ul>
				<?php } ?>
			</div>
		</div>
		<div id="content">
			<?php pageR_contents(); ?>
		</div>
		<div id="footer">
		</div>
	</body>
	<!-- <?php echo (int)(exec_time()*1000); ?>ms -->
</html>
	<?php }
	
	// Rendering code for different pages
	function page_render_index() { ?>
			TODO: index page
			<div class="section left" id="questions">
				<h2>Questions</h2>
				<dl>
					<dt>What is this site?</dt>
					<dd>
						<p>LightDash Records is a place to upload records for various Sonic Robo Blast 2 level and character 
							mods, to provide a competitive aspect to the various mods available for the game. Anyone 
							interested in competing can register and upload replays, which will be automatically parsed and 
							placed on the appropriate map and character leaderboards.</p>
						
						<p>This site is not associated with Sonic Team Jr. or SRB2 itself. (Well, aside from the fact that 
							the guy who coded this site is now part of STJr, but that's beside the point :p)</p>
					</dd>
					
					<dt>What are the rules for submitting replays?</dt>
					<dd>
						<ul>
							<li>Records should be performed using the most recent version of all files involved, including 
								the most recent SRB2 version and the most recent versions of the level and/or character WAD 
								used. Replays on old versions will be grandfathered into newer versions assuming the replays 
								are still mostly synced on newer versions. (This will be determined on a case-by-case basis.)</li>
							<li>Files used must be publically available. No development versions!</li>
							<li>Replays must play back properly on a game that has load only the character WAD used, the 
								level WAD used, and any Record Attack-enabling SOCs used, in that order. (Non-applicable 
								files omitted.) Other files may be used in the recording process as long as they do not 
								affect replay sync.</li>
							<li>Replays, obviously, must not be hacked or cheated in any way.</li>
							<li>Replays must be performed by the user submitting them.</li>
							<li>Custom rules for particular maps/characters may be put in place if needed. These must be 
								followed when submitting replays for the maps in question. (<em>TODO: provide confirmation 
								screen detailing custom rules on submission?</em>)</li>
						</ul>
						<p>These rules may be updated at any time. (last updated Sept 27, 2014)</p>
					</dd>
					
					<dt>Doesn't this compete with <a href="http://records.srb2.org/" rel="external nofollow">records.srb2.org?</a></dt>
					<dd>
						<p>The official records site is designed exclusively for records on the unmodified game. While it's 
							possible to upload such records here, this site is geared toward records involving custom 
							characters and/or levels instead. We highly encourage those uploading vanilla records to use the 
							official records site instead of, or at the very least, in addition to this site.</p>
					</dd>
					
					<dt>What do I do if the level/character I'm uploading a replay for isn't in the system?</dt>
					<dd>
						<p>Upload it anyway! If the site doesn't have an entry for the map and/or character in the replay, a 
							form will pop up allowing you to describe the addon to site moderation, allowing them to quickly 
							add it to the database. Once it's in the system, your record will be automatically added to the 
							relevant leaderboards.</p>
						
						<p><em>TODO: the form doesn't exist yet &ndash; contact a mod manually in the meantime</em></p>
					</dd>
					
					<dt>How do I record replays with non-vanilla characters?</dt>
					<dd>
						<p>For custom levels, simply add the character WAD before the level WAD and it should be possible to 
							use Record Attack with them. For vanilla levels, add the character WAD, then this SOC (TODO: not 
							yet made) to enable Record Attack. (Replays will be saved to replay/ldrecords)</p>
					</dd>
					
					<dt>The level I want to compete in doesn't support Record Attack!</dt>
					<dd>
						<p>Some levels on the site may have an additional SOC you can add to enable Record Attack for the 
							level in question if not supported by the addon itself. Otherwise, you're free to make one of 
							your own and use it to create records for the level. (When you submit the replays, please 
							provide a link to the SOC used. Note that the SOC must do nothing other than enabling Record 
							Attack for the desired mod.)</p>
					</dd>
					
					<dt>Why can't I submit certain replays?</dt>
					<dd>
						<p>(<em>TODO: uncoded functionality</em>) Certain maps, characters, or combinations thereof may be 
							banned from submission for various reasons. The site will automatically reject replays in this 
							situation with a reason provided. If you feel a map, character, or combination thereof is 
							unreasonably banned, or should be banned, contact a moderator about it. (Note that character 
							bans require extenuating circumstances; overpowered characters are generally just marked as such 
							and are still allowed to be submitted.)</p>
						
						<p>Alternatively, you might just be banned for being a rulebreaker. :v</p>
					</dd>
					
					<dt>Are full-game record attacks or other special categories (all level emblems, etc.) supported?</dt>
					<dd>
						<p>No. At some point in the future, time attacks for full level packs may be supported through manual 
							submission (using an addon Lua script to calculate in-game time), but other "gimmicky" categories 
							are highly unlikely to ever be supported.</p>
					</dd>
					
					<dt>Can I submit my TASes to the site?</dt>
					<dd>
						<p>No, but please link them to Red. He'd be ecstatic to find out that someone else does SRB2 TASes.</p>
					</dd>
				</dl>
			</div>
			
			<div class="section right" id="sitecredits">
				<h2>Site credits</h2>
				<p>The site was programmed, designed, and styled by RedEnchilada.</p>
				<p>Game files designed to assist replay recording were created by {INSERT PEOPLE HERE}.</p>
				<p>Inspiration (and an icon or two, currently) were taken from records.srb2.org.</p>
				<p>Additional feedback, input, and testing was provided by the Terminal crew, including Wolfy, Steel 
					Titanium and others who I can't presently recall.</p>
				<p>High-resolution level icons are provided by various community members.</p>
				<p>Character icons are used from the WADs in question, created by their respective authors.</p>
			</div>
	<?php }
	
	function page_render_login($acct) {
			if ($acct !== false) {
				if (!isset($acct['error'])) { ?>
					<div id="notice_msg">Successfully logged in.</div>
				<?php } else { ?>
					<div id="error_msg"><strong>Error:</strong> <?php echo $acct['error'] ?></div>
				<?php }
			}
			?>
			<form method="post" action="<?php echo url_get(URL_LOGIN); ?>">
				<fieldset>
					<legend>Login</legend>
					
					<div>
						<label for="username">Username</label>
						<input id="username" type="text" name="username"></input>
					</div>
					
					<div>
						<label for="password">Password</label>
						<input id="password" type="password" name="password"></input>
					</div>
					
					<div>
						<input id="remember" class="checkbox" type="checkbox" value="true" name="remember"></input>
						<label class="checkbox" for="remember">Stay logged in</label>
					</div>
					
					<div>
						<input id="login" class="submit" type="submit" value="Login" name="login"></input>
					</div>
				</fieldset>
			</form>
	<?php }
	
	function page_render_register($acct) {
			if ($acct !== false) {
				if (!isset($acct['error'])) { ?>
					<div id="notice_msg">Thank you for registering!</div>
				<?php
					return;
				} else { ?>
					<div id="error_msg"><strong>Error:</strong> <?php echo $acct['error'] ?></div>
				<?php }
			}
			?>
			<form method="post" action="<?php echo url_get(URL_REGISTER); ?>">
				<fieldset>
					<legend>Register</legend>
					
					<div>
						<label for="username">Username</label>
						<input id="username" type="text" name="username"></input>
					</div>
					
					<div>
						<label for="password">Password</label>
						<input id="password" type="password" name="password"></input>
						<p>Must be 8-64 characters long.</p>
					</div>
					
					<div>
						<label for="pconfirm">Confirm password</label>
						<input id="pconfirm" type="password" name="pconfirm"></input>
					</div>
					
					<div>
						<input id="remember" class="checkbox" type="checkbox" value="true" name="remember"></input>
						<label class="checkbox" for="remember">Stay logged in</label>
					</div>
					
					<div>
						<input id="login" class="submit" type="submit" value="Register" name="register"></input>
					</div>
				</fieldset>
			</form>
	<?php }
	
	function page_render_newrecord($newrecord) {
			if ($newrecord !== false) {
				if (is_array($newrecord)) { // TODO: show this properly
					echo '<div style="white-space:pre-wrap">';
					var_dump($newrecord);
					echo '</div>';
					?><div id="new_record">
						<a href="<?php echo htmlspecialchars(sprintf(url_get(URL_RECORDMAP), $newrecord['map'])) ?>">New record submitted!</a>
					</div><?php // TODO: get map and character and render out some info here
				} else { ?>
					<div id="error_msg"><strong>Error:</strong> <?php echo $newrecord ?></div>
				<?php }
			}
			?>
			<form method="post" action="<?php echo url_get(URL_NEWRECORD); ?>" enctype="multipart/form-data">
				<fieldset>
					<legend>New Record</legend>
					
					<div>
						<input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
						<label for="replay">Upload replay file</label>
						<input id="replay" type="file" name="replay"></input>
					</div>
					
					<div>
						<input id="upload" class="submit" type="submit" value="Upload" name="upload"></input>
					</div>
				</fieldset>
			</form>
	<?php }
	
	function page_render_error($msg) { ?>
			<div id="error_popup"><?php echo $msg; ?></div>
	<?php }
	
	function page_render_map_listing($page, $pagecount, $mapfunc) {
		function nav($page, $pagecount) { ?>
			<div class="page_ticker">
				<?php if ($page > 1) { ?> <a class="previous_page" href="<?php echo sprintf(url_get(URL_RECORDLISTING_PAGE), $page-1) ?>">Previous</a> <?php } ?>
				<!-- TODO: page display -->
				<span class="current_page">Page <?php echo $page.'/'.$pagecount ?></span>
				<?php if ($page < $pagecount) { ?> <a class="next_page" href="<?php echo sprintf(url_get(URL_RECORDLISTING_PAGE), $page+1) ?>">Next</a> <?php } ?>
			</div>
		<?php }
		
		?>
			<h2>Map listing</h2>
			<?php nav($page, $pagecount); ?>
			<div id="map_container">
				<?php $mapfunc(function($map) { ?>
					<div class="map_listing">
						<a href="<?php echo htmlspecialchars(sprintf(url_get(URL_RECORDMAP), $map['hash'])) ?>">
							<img alt="" src="<?php echo htmlspecialchars(sprintf(url_get(URL_LEVELPICFILE), $map['levelpic'])) ?>" />
							<span><?php echo htmlspecialchars($map['name']) ?></span>
						</a>
					</div>
				<?php }); ?>
			</div>
			<?php nav($page, $pagecount); ?>
	<?php }
	
	function page_render_map_scoreboard($map, $scorefunc, $timefunc, $ringsfunc) {
		global $url_preformat, $pos;
		$url_preformat = sprintf(url_get(URL_RECORDMAPCATEGORYCHARACTER), $map['hash'], '%s', '%s');
		?>
			<img alt="" src="<?php echo htmlspecialchars(sprintf(url_get(URL_LEVELPICFILE), $map['levelpic'])) ?>" class="map_thumbnail" />
			<h2><?php echo $map['name']; ?></h2>
			<p class="map_description"><?php echo $map['description']; ?></p>
			<?php
				$acct = acct_current();
				if ($acct['role'] == ROLE_MOD) { ?>
					<a class="mod_editlink" href="<?php echo sprintf(url_get(URL_MODPANEL_EDITMAP), $map['hash']); ?>">Edit map listing</a>
				<?php }
			?>
			<table class="record_list charicon">
				<tr>
					<th class="scoreboard_ranking">#</th>
					<th class="scoreboard_character">As</th>
					<th class="scoreboard_username">Username</th>
					<th class="scoreboard_record">Score</th>
					<th class="scoreboard_submitted">Submitted</th>
					<th class="scoreboard_download">DL</th>
				</tr>
				<?php

				$pos = 0;
					$scorefunc(function($record) { // Rankings for score
					global $pos, $category, $url_preformat;
					$pos++;
				?>
					<tr>
						<td class="scoreboard_ranking"><?php echo $pos; ?></td>
						<td class="scoreboard_character"><a href="<?php echo sprintf($url_preformat, 'score', $record['character']['skin']) ?>"><img src="<?php
								echo htmlspecialchars(sprintf(url_get(URL_CHARPICFILE), $record['character']['icon'])) ?>" alt="<?php echo htmlspecialchars($record['character']['name'])
								?>" title="<?php echo htmlspecialchars($record['character']['name']) ?>" /></a></td>
						<td class="scoreboard_username"><?php echo htmlspecialchars($record['user']['username']) ?></td>
						<td class="scoreboard_record"><abbr title='Accomplished in <?php echo $record['time']['str'] ?>'><?php echo $record['score'] ?></abbr></td>
						<td class="scoreboard_submitted"><?php echo pageR_ago($record['submitted']) ?></td>
						<td class="scoreboard_download"><a href="<?php echo sprintf(url_get(URL_REPLAYFILE), $record['id']) ?>">Download</a></td>
					</tr>
				<?php }, function($map) { // "View all" button ?>
					<tr>
						<td rowspan="6" class="scoreboard_viewall"><a href="<?php echo sprint(url_get(URL_RECORDMAPCATEGORY), $map['hash'], 'score'); ?>">View all</a></td>
					</tr>
				<?php }); ?>
				<tr>
					<th class="scoreboard_ranking">#</th>
					<th class="scoreboard_character">As</th>
					<th class="scoreboard_username">Username</th>
					<th class="scoreboard_record">Time</th>
					<th class="scoreboard_submitted">Submitted</th>
					<th class="scoreboard_download">DL</th>
				</tr>
				<?php
				$pos = 0;
					$timefunc(function($record) { // Rankings for time
					global $pos, $category, $url_preformat;
					$pos++;
				?>
					<tr>
						<td class="scoreboard_ranking"><?php echo $pos; ?></td>
						<td class="scoreboard_character"><a href="<?php echo sprintf($url_preformat, 'time', $record['character']['skin']) ?>"><img src="<?php
								echo htmlspecialchars(sprintf(url_get(URL_CHARPICFILE), $record['character']['icon'])) ?>" alt="<?php echo htmlspecialchars($record['character']['name'])
								?>" title="<?php echo htmlspecialchars($record['character']['name']) ?>" /></a></td>
						<td class="scoreboard_username"><?php echo htmlspecialchars($record['user']['username']) ?></td>
						<td class="scoreboard_record"><?php echo $record['time']['str'] ?></td>
						<td class="scoreboard_submitted"><?php echo pageR_ago($record['submitted']) ?></td>
						<td class="scoreboard_download"><a href="<?php echo sprintf(url_get(URL_REPLAYFILE), $record['id']) ?>">Download</a></td>
					</tr>
				<?php }, function($map) { // "View all" button ?>
					<tr>
						<td rowspan="6" class="scoreboard_viewall"><a href="<?php echo sprint(url_get(URL_RECORDMAPCATEGORY), $map['hash'], 'time'); ?>">View all</a></td>
					</tr>
				<?php }); if ($map['type'] != 1) { ?>
				<tr>
					<th class="scoreboard_ranking">#</th>
					<th class="scoreboard_character">As</th>
					<th class="scoreboard_username">Username</th>
					<th class="scoreboard_record">Rings</th>
					<th class="scoreboard_submitted">Submitted</th>
					<th class="scoreboard_download">DL</th>
				</tr>
				<?php
				$pos = 0;
					$ringsfunc(function($record) { // Rankings for rings
					global $pos, $category, $url_preformat;
					$pos++;
				?>
					<tr>
						<td class="scoreboard_ranking"><?php echo $pos; ?></td>
						<td class="scoreboard_character"><a href="<?php echo sprintf($url_preformat, 'rings', $record['character']['skin']) ?>"><img src="<?php
								echo htmlspecialchars(sprintf(url_get(URL_CHARPICFILE), $record['character']['icon'])) ?>" alt="<?php echo htmlspecialchars($record['character']['name'])
								?>" title="<?php echo htmlspecialchars($record['character']['name']) ?>" /></a></td>
						<td class="scoreboard_username"><?php echo htmlspecialchars($record['user']['username']) ?></td>
						<td class="scoreboard_record"><abbr title='Accomplished in <?php echo $record['time']['str'] ?>'><?php echo $record['rings'] ?></abbr></td>
						<td class="scoreboard_submitted"><?php echo pageR_ago($record['submitted']) ?></td>
						<td class="scoreboard_download"><a href="<?php echo sprintf(url_get(URL_REPLAYFILE), $record['id']) ?>">Download</a></td>
					</tr>
				<?php }, function($map) { // "View all" button ?>
					<tr>
						<td rowspan="6" class="scoreboard_viewall"><a href="<?php echo sprint(url_get(URL_RECORDMAPCATEGORY), $map['hash'], 'rings'); ?>">View all</a></td>
					</tr>
				<?php }); } ?>
			</table>
	<?php }
	
	function page_render_mapcategory_scoreboard($map, $category, $loopfunc) { 
		global $url_preformat;
		$pos = 0;
		$url_preformat = sprintf(url_get(URL_RECORDMAPCATEGORYCHARACTER), $map['hash'], strtolower($category), '%s');
		?>
			<h2><?php echo $map['name']; ?></h2>
			<p class="underheading"><?php echo sprintf('%s &ndash; Top rankings per character', $category); ?></p>
			<table class="record_list charicon">
				<tr>
					<th class="scoreboard_ranking">#</th>
					<th class="scoreboard_character">As</th>
					<th class="scoreboard_username">Username</th>
					<th class="scoreboard_record"><?php echo $category; ?></th>
					<th class="scoreboard_submitted">Submitted</th>
					<th class="scoreboard_download">DL</th>
				</tr>
				<?php $loopfunc(function($record) {
					global $pos, $category, $url_preformat;
					$pos++;
				?>
					<tr>
						<td class="scoreboard_ranking"><?php echo $pos; ?></td>
						<td class="scoreboard_character"><a href="<?php echo sprintf($url_preformat, $record['character']['skin']) ?>"><img src="<?php
								echo htmlspecialchars(sprintf(url_get(URL_CHARPICFILE), $record['character']['icon'])) ?>" alt="<?php echo htmlspecialchars($record['character']['name'])
								?>" title="<?php echo htmlspecialchars($record['character']['name']) ?>" /></a></td>
						<td class="scoreboard_username"><?php echo htmlspecialchars($record['user']['username']) ?></td>
						<?php if ($category == CAT_SCORE) { ?>
							<td class="scoreboard_record"><abbr title='Accomplished in <?php echo $record['time']['str'] ?>'><?php echo $record['score'] ?></abbr></td>
						<?php } else if ($category == CAT_TIME) { ?>
							<td class="scoreboard_record"><?php echo $record['time']['str'] ?></td>
						<?php } else { ?>
							<td class="scoreboard_record"><abbr title='Accomplished in <?php echo $record['time']['str'] ?>'><?php echo $record['rings'] ?></abbr></td>
						<?php } ?>
						<td class="scoreboard_submitted"><?php echo pageR_ago($record['submitted']) ?></td>
						<td class="scoreboard_download"><a href="<?php echo sprintf(url_get(URL_REPLAYFILE), $record['id']) ?>">Download</a></td>
					</tr>
				<?php }); ?>
			</table>
	<?php }
	
	function page_render_mapcharcategory_scoreboard($map, $char, $category, $loopfunc) { 
		$pos = 0;
		?>
			<h2><?php echo $map['name']; ?></h2>
			<p class="underheading"><?php echo sprintf('%s &ndash; %s', $category, htmlspecialchars($char['name'])); ?></p>
			<table class="record_list no_charicon">
				<tr>
					<th class="scoreboard_ranking">#</th>
					<th class="scoreboard_username">Username</th>
					<th class="scoreboard_record"><?php echo $category; ?></th>
					<th class="scoreboard_submitted">Submitted</th>
					<th class="scoreboard_download">DL</th>
				</tr>
				<?php $loopfunc(function($record) {
					global $pos, $category;
					$pos++;
				?>
					<tr>
						<td class="scoreboard_ranking"><?php echo $pos; ?></td>
						<td class="scoreboard_username"><?php echo htmlspecialchars($record['user']['username']) ?></td>
						<?php if ($category == CAT_SCORE) { ?>
							<td class="scoreboard_record"><abbr title='Accomplished in <?php echo $record['time']['str'] ?>'><?php echo $record['score'] ?></abbr></td>
						<?php } else if ($category == CAT_TIME) { ?>
							<td class="scoreboard_record"><?php echo $record['time']['str'] ?></td>
						<?php } else { ?>
							<td class="scoreboard_record"><abbr title='Accomplished in <?php echo $record['time']['str'] ?>'><?php echo $record['rings'] ?></abbr></td>
						<?php } ?>
						<td class="scoreboard_submitted"><?php echo pageR_ago($record['submitted']) ?></td>
						<td class="scoreboard_download"><a href="<?php echo sprintf(url_get(URL_REPLAYFILE), $record['id']) ?>">Download</a></td>
					</tr>
				<?php }); ?>
			</table>
	<?php }
	
	function page_render_mod() { ?>
			TODO: mod panel page
	<?php }
	
	function page_render_modmaplisting($mapfunc) { ?>
			<h2>Map listing by name</h2>
			<?php $mapfunc(function($map) { ?>
				<p><a href="<?php echo htmlspecialchars(sprintf(url_get(URL_MODPANEL_EDITMAP), $map['hash'])) ?>"><?php echo htmlspecialchars($map['name']) ?></a></p>
			<?php }); ?>
	<?php }
	
	function page_render_modmapedit($map) { ?>
			<form method="post" action="<?php echo sprintf(url_get(URL_MODPANEL_EDITMAP), $map['hash']); ?>" enctype="multipart/form-data">
				<?php if (isset($map['message'])) { ?>
					<div id="notice_msg"><?php echo $map['message'] ?></div>
				<?php }	?>
				<fieldset>
					<legend>Edit map</legend>
					
					<input id="maphash" type="hidden" name="maphash" value="<?php echo htmlspecialchars($map['hash']) ?>"></input>

					<div>
						<label for="mapname">Name</label>
						<input id="mapname" type="text" name="mapname" value="<?php echo htmlspecialchars($map['name']) ?>"></input>
					</div>
					
					<div>
						<label for="mapdesc">Description</label>
						<textarea id="mapdesc" name="mapdesc"><?php echo htmlspecialchars($map['description']) ?></textarea>
					</div>
					
					<img class="levelpic" src="<?php echo htmlspecialchars(sprintf(url_get(URL_LEVELPICFILE), $map['levelpic'])) ?>" />
					<div>
						<input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
						<label for="mappic">Picture</label>
						<input id="mappic" type="file" name="mappic"></input>
					</div>
					
					<div>
						<input id="mapnights" class="checkbox" type="checkbox" value="<?php echo ($map['type'] == 1 ? 'true" checked="checked' : 'false') ?>" name="mapnights"></input>
						<label class="checkbox" for="mapnights">NiGHTS map</label>
					</div>
					
					<div>
						<input id="mapbanned" class="checkbox" type="checkbox" value="<?php echo ($map['banned'] == 1 ? 'true" checked="checked' : 'false') ?>" name="mapbanned"></input>
						<label class="checkbox" for="mapbanned">Ban this map</label>
					</div>
					
					<div>
						<input id="login" class="submit" type="submit" value="Submit" name="login"></input>
					</div>
				</fieldset>
			</form>
	<?php }
	
	function page_render_modcharacterlisting($mapfunc) { ?>
			<h2>Character listing by name</h2>
			<?php $mapfunc(function($map) { ?>
				<p><a href="<?php echo htmlspecialchars(sprintf(url_get(URL_MODPANEL_EDITCHARACTER), $map['skin'])) ?>"><?php echo htmlspecialchars($map['name']) ?></a></p>
			<?php }); ?>
	<?php }
	
	function page_render_modcharacteredit($map) { ?>
			<form method="post" action="<?php echo sprintf(url_get(URL_MODPANEL_EDITCHARACTER), $map['skin']); ?>" enctype="multipart/form-data">
				<?php if (isset($map['message'])) { ?>
					<div id="notice_msg"><?php echo $map['message'] ?></div>
				<?php }	?>
				<fieldset>
					<legend>Edit map</legend>
					
					<input id="charskin" type="hidden" name="charskin" value="<?php echo htmlspecialchars($map['skin']) ?>"></input>

					<div>
						<label for="charname">Name</label>
						<input id="charname" type="text" name="charname" value="<?php echo htmlspecialchars($map['name']) ?>"></input>
					</div>
					
					<div>
						<label for="chardesc">Description</label>
						<textarea id="chardesc" name="chardesc"><?php echo htmlspecialchars($map['description']) ?></textarea>
					</div>
					
					<img class="charicon" src="<?php echo htmlspecialchars(sprintf(url_get(URL_CHARPICFILE), $map['icon'])) ?>" />
					<div>
						<input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
						<label for="charpic">Picture</label>
						<input id="charpic" type="file" name="charpic"></input>
					</div>
					
					<div>
						<input id="charbanned" class="checkbox" type="checkbox" value="<?php echo ($map['banned'] == 1 ? 'true" checked="checked' : 'false') ?>" name="charbanned"></input>
						<label class="checkbox" for="charbanned">Ban this skin</label>
					</div>
					
					<div>
						<input id="submit" class="submit" type="submit" value="Submit" name="submit"></input>
					</div>
				</fieldset>
			</form>
	<?php }
	