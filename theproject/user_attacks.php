<?php

require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/server.php';
require_once dirname(__FILE__).'/include/processCookie.php';

if(!$LOGIN_DATA['user_id']) die('Please log in.');
if(!$server) $server = new server($_REQUEST['server_id']);

?>
	<?php

		echo '<h3>Recent Attacks</h3><ul>';
		$attacks = mysql_query("SELECT destination,results,time FROM server_attack_results WHERE user_id=".$LOGIN_DATA['user_id']." AND server_id=".$server->getID()." ORDER BY time DESC LIMIT 5",$db) or die(mysql_error());

		while($attack = mysql_fetch_assoc($attacks)) {
			$dest = new city($attack['destination']);
			$class = ($attack['results'] < 0) ? 'won' : 'lost';
			echo '<li class="'.$class.'">';
			echo ceil((time()-$attack['time'])/60).' minutes ago: ';
			echo ' You attacked ';
			if($attack['results'] < 0) echo '<a href="/server/'.$_REQUEST['server_id'].'/city/'.$dest->getValue('id').'">';
			echo $dest->getValue('name').' (Location: '.$dest->getValue('id').')';
			if($attack['results'] < 0) echo '</a>';
			echo ' and ';
			echo $class;
			echo ' by '.abs($attack['results']);
			echo '</li>';
		}//end while attacks

		echo '</ul>';

		echo '<a href="/server/'.$server->getID().'/attacks">View all recent attacks »</a>';

	?>
