<?php

require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/server.php';
require_once dirname(__FILE__).'/include/connectDB.php';
require_once dirname(__FILE__).'/facebook/do_facebook.php';
$servers = mysql_query("SELECT day_length,week_length,previous_day,server_id,previous_week FROM servers") or die(mysql_error());
while($server = mysql_fetch_assoc($servers)) {

	if($server['previous_day'] < time()-$server['day_length']) {
		mysql_query("UPDATE servers SET previous_day=".time()." WHERE server_id=".$server['server_id']) or die(mysql_error());
		$num_of_days = floor((time()-$server['previous_day'])/$server['day_length']);
		$serverobj = new server($server['server_id']);

         $close_players = mysql_query("SELECT user_id FROM server_data WHERE server_id=".$server['server_id']." AND `key`='gold' ORDER BY value",$db) or die(mysql_error());
         $leaders = array();
         while($player = mysql_fetch_assoc($close_players)) {
            $player_data = new user($player['user_id'], $serverobj);
				for($i = 0; $i < $num_of_days; $i++)
					$player_data->dailyGold();
            $leaders[$player_data->calculateScore()] = $player_data;
         }//end while close_players
         krsort($leaders);
         $c = 0;
         foreach($leaders as $leader) {
            $c++;
            $leader->setValue('rank',$c);
				do_facebook($leader);
         }//end foreach

	}//end if new day

	if($server['week_length'] && $server['previous_week'] < time()-$server['week_length']) {
		echo 'RESET';
		$serverobj = new server($server['server_id']);
		$serverobj->reset();//TODO: highscores, etc	
	}//end if new week

}//end while servers

mysql_query("UPDATE users SET session_id='' WHERE session_timeout < ".time(),$db) or die(mysql_error());

?>DONE
