<?php

		require_once dirname(__FILE__).'/include/processCookie.php';
		require_once dirname(__FILE__).'/include/user.php';
		require_once dirname(__FILE__).'/include/server.php';
		require_once dirname(__FILE__).'/include/cron.php';
		if(!$server) $server = new server($_REQUEST['server_id']);
		if(!$current_user) $current_user = new user($LOGIN_DATA['user_id'],$server);

      echo "<h3>Cities</h3><ul>";
      foreach($current_user->getValue('cities') as $city) {
         echo '<li>';
         echo '<a href="/server/'.$server->getID().'/city/'.$city->getValue('id').'">';
			if($city->getValue('name'))
				echo htmlentities($city->getValue('name')).' / ';
         echo 'Location: '.str_pad($city->getValue('id'), 6, '0', STR_PAD_LEFT);
         echo '</a>';
         echo ' /  Population: '.$city->getValue('population');
			echo ' /  Defense: '.(intval($city->getValue('defense'))+1);
			echo ' /  Units: '.intval($city->unit_count());
         $attack = mysql_query("SELECT user_id,unit_count,eta FROM server_unit_transaction WHERE server_id=".$server->getID()." AND destination=".$city->getValue('id')." AND user_id!=".$current_user->getValue('userid')." AND eta < ".(time()+60*5)." ORDER BY eta DESC LIMIT 1",$db) or die(mysql_error());
         $attack = mysql_fetch_assoc($attack);
         if($attack) {
				$attack_user = new user($attack['user_id'],$server);
				$nickname = $attack_user->getValue('nickname') ? $attack_user->getValue('nickname') : 'User #'.$attack_user->getValue('userid');
            echo ' <br /><i>'.$attack['unit_count'].' units attacking from <a href="/server/'.$server->getID().'/user/'.$attack_user->getValue('userid').'">'.$nickname.'</a> in '.round(($attack['eta']-time())/60,2)." minutes.</i>";
			}//end if attack
         echo "</li>";
      }//end foreach cities
      echo "</ul>";

?>
