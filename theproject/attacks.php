<?php

require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/server.php';
require_once dirname(__FILE__).'/include/invisible_header.php';

if(!$LOGIN_DATA['user_id']) die('Please log in.');
if(!$server) $server = new server($_REQUEST['server_id']);

?>
	<head>
		<title>The Project - <?php echo $server->getName(); ?> Attack History</title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
		<link rel="shortcut icon" href="/favicon.ico" />
	</head>
	
	<body>
	<?php

		require dirname(__FILE__).'/include/visible_header.php';
	
		echo '<h3>Recent Attacks</h3><ol style="list-style-type:none;padding:0px;">';
		$attacks = mysql_query("SELECT destination,user_id,results,time FROM server_attack_results WHERE server_id=".$server->getID()." ORDER BY time DESC LIMIT 50",$db) or die(mysql_error());

		while($attack = mysql_fetch_assoc($attacks)) {
			$user = new user($attack['user_id'],$server);
			$dest = new city($attack['destination']);
			$class = ($attack['results'] < 0) ? 'won' : 'lost';
			echo '<li class="'.$class.'">';
			echo ceil((time()-$attack['time'])/60).' minutes ago: ';
			echo '<a href="/server/'.$server->getID().'/user/'.$user->getValue('userid').'">'.($user->getValue('nickname')?htmlentities($user->getValue('nickname')):'User #'.$user->getValue('userid')).'</a>';
			echo ' attacked ';
			echo $dest->getValue('name').' (Location: '.$dest->getValue('id').')';
			echo ' and ';
			echo $class;
			echo ' by '.abs($attack['results']);
			echo '</li>';
		}//end while attacks

		echo '</ol>';
	?>
	</body>
</html>
