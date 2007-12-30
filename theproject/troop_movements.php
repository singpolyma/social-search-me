<?php
			require_once dirname(__FILE__).'/include/processCookie.php';
			require_once dirname(__FILE__).'/include/cron.php';
			require_once dirname(__FILE__).'/include/connectDB.php';
			require_once dirname(__FILE__).'/include/user.php';
			require_once dirname(__FILE__).'/include/server.php';
			require_once dirname(__FILE__).'/include/city.php';
			if(!$server) $server = new server($_REQUEST['server_id']);
			if(!$current_user) $current_user = new user($LOGIN_DATA['user_id'],$server);
?>
      <h3>Troop Movements</h3>
      <ul>
      <?php
         $transactions = mysql_query("SELECT unit_count,eta,destination,user_id FROM server_unit_transaction WHERE server_id=".$server->getID()." AND user_id=".$current_user->getValue('userid')." ORDER BY eta ASC",$db) or die(mysql_query());
         while($transaction = mysql_fetch_assoc($transactions)) {
				$city = new city($transaction['destination']);
				$dest = $city->getValue('name') ? htmlentities($city->getValue('name')) : 'City at '.str_pad($city->getValue('id'),6,'0',STR_PAD_LEFT);
				$is_attack = ($city->getValue('user')->getValue('userid') != $transaction['user_id']);
				$time_left = round(($transaction['eta']-time())/60,2);
				if($time_left > 0)
	            echo "\t\t\t".'<li>'.($is_attack?'Attacking ':'Moving to ').$dest.' with '.$transaction['unit_count'].' troops in '.$time_left.' minutes </li>'."\n";
         }//end while unit
      ?>
      </ul>

