<?php
			require_once dirname(__FILE__).'/include/processCookie.php';
			require_once dirname(__FILE__).'/include/cron.php';
			require_once dirname(__FILE__).'/include/connectDB.php';
			require_once dirname(__FILE__).'/include/user.php';
			require_once dirname(__FILE__).'/include/server.php';
			if(!$server) $server = new server($_REQUEST['server_id']);
			if(!$current_user) $current_user = new user($LOGIN_DATA['user_id'],$server);
?>
      <h3>Troop Movements</h3>
      <ul>
      <?php
         $transactions = mysql_query("SELECT unit_count,eta FROM server_unit_transaction WHERE server_id=".$server->getID()." AND user_id=".$current_user->getValue('userid')." ORDER BY eta DESC",$db) or die(mysql_query());
         while($transaction = mysql_fetch_assoc($transactions)) {
				$time_left = round(($transaction['eta']-time())/60,2);
				if($time_left > 0)
	            echo "\t\t\t".'<li>Troop Movement ('.$transaction['unit_count'].') in '.$time_left.' minutes</li>'."\n";
         }//end while unit
      ?>
      </ul>

