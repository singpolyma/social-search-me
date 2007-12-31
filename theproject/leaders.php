<?php

require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/server.php';
require_once dirname(__FILE__).'/include/invisible_header.php';

if(!$LOGIN_DATA['user_id']) die('Please log in.');
if(!$server) $server = new server($_REQUEST['server_id']);
$current_user = new user($LOGIN_DATA['user_id'],$server);

?>
	<head>
		<title>The Project - <?php echo $server->getName(); ?> Leaders</title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="/styles/login.css" type="text/css" media="screen" />
		<link rel="shortcut icon" href="/favicon.ico" />
	</head>
	
	<body>
	<?php

		require dirname(__FILE__).'/include/visible_header.php';
	
		echo '<h3>Round Leaders</h3><ol style="list-style-type:none;padding:0px;">';
		$close_players = mysql_query("SELECT user_id FROM server_data WHERE server_id=".$server->getID()." AND `key`='gold' ORDER BY value DESC LIMIT 50",$db) or die(mysql_error());
		$leaders = array();
		while($player = mysql_fetch_assoc($close_players)) {
			$player_data = new user($player['user_id'], $server);
			$leaders[$player_data->calculateScore()] = $player_data;
		}//end while close_players
		krsort($leaders);
		foreach($leaders as $score => $leader) {
			echo '<li>';
			echo $leader->online_icon().' ';
			$nickname = $leader->getValue('nickname') ? $leader->getValue('nickname') : 'User #'.$leader->getValue('userid');
			echo '<a href="/server/'.$server->getID().'/user/'.$leader->getValue('userid').'">'.htmlentities($nickname).'</a>';
			echo ' (Score: '.$score.')';
			echo '</li>';
		}//end foreach
		echo '</ol>';
	?>
	</body>
</html>
