<?php

require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/server.php';
if(isset($_REQUEST['ajax']))
	require_once dirname(__FILE__).'/include/processCookie.php';
else
	require_once dirname(__FILE__).'/include/invisible_header.php';
require_once dirname(__FILE__).'/include/connectDB.php';

if(!$LOGIN_DATA['user_id']) die('<head><title>Please log in</title></head><body><h2>Please log in.</h2></body></html>');
if(!$server) $server = new server($_REQUEST['server_id']);
$current_user = new user($LOGIN_DATA['user_id'],$server);

if($_POST['build_city']) {
	if($_REQUEST['city_name'] == 'City Name (optional)') $_REQUEST['city_name'] = '';
	$message = city::build_city($current_user, $server, true, $_REQUEST['city_name']);
	if(isset($_REQUEST['ajax'])) {
		if($message) die($message);
			else exit('City built.');
	}//end if ajax
}//end if build city

?>
	<head>
		<title>The Project - <?php echo $server->getName(); ?></title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="/styles/login.css" type="text/css" media="screen" />
		<link rel="shortcut icon" href="/favicon.ico" />
		<script type="text/javascript" src="/include/prototype.js"></script>
		<script type="text/javascript" src="/include/ajax.js"></script>
		<script type="text/javascript">
		//<![CDATA[
			var troop_movement_id = '';
			function update_troop_movements() {
				get_user_cities();
				update_recent_attacks();
				new Ajax.Updater('troop-movements', '/troop_movements.php?server_id=<?php echo $server->getID(); ?>');
				troop_movement_id = setTimeout("update_troop_movements()", 1000*10);
			}//end function update_day_left
			troop_movement_id = setTimeout("update_troop_movements()", 1000*10)

			function update_recent_attacks() {
				new Ajax.Updater('recent-attacks', '/user_attacks.php?server_id=<?php echo $server->getID(); ?>');
			}//end update_recent_attacks

			function get_user_cities() {
				new Ajax.Updater('user-cities', '/user_cities.php?server_id=<?php echo $server->getID(); ?>');
			}//end get_user_cities
		//]]>
		</script>
	</head>
	
	<body>
	<?php

		require dirname(__FILE__).'/include/visible_header.php';

		if($message) echo '<b>'.$message.'</b>';

		/* CLOSE COMPETITORS AND FRINEDS */
		echo '<div style="position:absolute;top:6em;right:2em;background-color:black;padding-left:1em;">';
		echo '<h3>Close Competitors</h3><ol style="list-style-type:none;padding:0px;">';
		$gold_upper_bound = $current_user->getValue('gold') + 300;
		$close_competitors = mysql_query("SELECT user_id,value+0 AS value FROM server_data WHERE server_id=".$server->getID()." AND `key`='gold' AND value < $gold_upper_bound ORDER BY value DESC LIMIT 10",$db) or die(mysql_error());
		while($player = mysql_fetch_assoc($close_competitors)) {
			if($player['user_id'] == $current_user->getValue('userid')) continue;
			$player = new user($player['user_id'], $server);
			echo '<li>';
			echo '<a href="/server/'.$server->getID().'/user/'.$player->getValue('userid').'">';
			echo '<img style="width:40px;" src="'.htmlentities($player->getValue('photo')).'" alt="" /> ';
			echo $player->getValue('nickname') ? $player->getValue('nickname') : 'User #'.$player->getValue('userid');
			echo '</a>';
			echo ' ('.$player->getValue('gold').' Gold)';
			echo $player->online_icon();
			echo '</li>';
		}//end while close_competitors
		echo '</ol>';

		echo '<a href="/server/'.$server->getID().'/leaders">View the leaders &raquo;</a>';

		$friends = mysql_query("SELECT friend_id FROM friends WHERE user_id=".$current_user->getValue('userid'),$db) or die(mysql_error());
		if(mysql_fetch_assoc($friends)) {
			$friends = mysql_query("SELECT friend_id FROM friends WHERE user_id=".$current_user->getValue('userid'),$db) or die(mysql_error());
			echo '<h3>Friends</h3><ol style="list-style-type:none;padding:0px;">';
			while($player = mysql_fetch_assoc($friends)) {
				$player = new user($player['friend_id'], $server);
				echo '<li>';
				echo '<a href="/server/'.$server->getID().'/user/'.$player->getValue('userid').'">';
				echo '<img style="width:40px;" src="'.htmlentities($player->getValue('photo')).'" alt="" /> ';
				echo $player->getValue('nickname') ? $player->getValue('nickname') : 'User #'.$player->getValue('userid');
				echo '</a>';
				echo ' ('.$player->getValue('gold').' Gold)';
				echo $player->online_icon();
				echo '</li>';
			}//end while close_competitors
			echo '</ol>';
		}//end if
		echo '</div>';


		/* CITIES */
		echo '<div id="user-cities">';
		require dirname(__FILE__).'/user_cities.php';
		echo '</div>';
		echo '<form method="post" action="" onsubmit="dofrm(this, \'Building...\', false, get_user_cities); return false;">';
		echo '<input type="text" name="city_name" value="City Name (optional)" onclick="this.value=\'\'" /> ';
		echo '<input type="submit" name="build_city" value="Build City ('.$server->getCityCost().' Gold)" />';
		echo '</form>';
		echo '<div id="ajax-response"></div>';
		
		/* RECENT ATTACKS */
		echo '<div id="recent-attacks">';
		require dirname(__FILE__).'/user_attacks.php';
		echo '</div>';

		/* TROOP MOVEMENTS */
		echo '<div id="troop-movements">';
		require dirname(__FILE__).'/troop_movements.php';
		echo '</div>';

	?>
	
	</body>
</html>
