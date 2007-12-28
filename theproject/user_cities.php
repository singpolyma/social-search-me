<?php

		require_once dirname(__FILE__).'/include/processCookie.php';
		require_once dirname(__FILE__).'/include/user.php';
		require_once dirname(__FILE__).'/include/server.php';
		if(!$server) $server = new server($_REQUEST['server_id']);
		if(!$current_user) $current_user = new user($LOGIN_DATA['user_id'],$server);

      echo "<h3>Cities</h3><ul>";
      foreach($current_user->getValue('cities') as $city) {
         echo '<li>';
         echo '<a href="/server/'.$server->getID().'/city/'.$city->getValue('id').'">';
         echo 'Location: '.$city->getValue('id');
         echo '</a>';
         echo ' Population: '.$city->getValue('population');
         $attack = mysql_query("SELECT unit_count,eta FROM server_unit_transaction WHERE server_id=".$server->getID()." AND destination=".$city->getValue('id')." AND user_id!=".$current_user->getValue('userid')." AND eta < ".(time()+60*5)." ORDER BY eta DESC LIMIT 1",$db) or die(mysql_error());
         $attack = mysql_fetch_assoc($attack);
         if($attack)
            echo ' '.$attack['unit_count']." units attacking in ".ceil(($attack['eta']-time())/60)." minutes.";
         echo "</li>";
      }//end foreach cities
      echo "</ul>";

?>
