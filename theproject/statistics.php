<?php
      require_once dirname(__FILE__).'/include/processCookie.php';
      require_once dirname(__FILE__).'/include/server.php';
      if($LOGIN_DATA['user_id']) {
         if($_REQUEST['server_id']) {
            $server = new server($_REQUEST['server_id']);
            require_once dirname(__FILE__).'/include/user.php';
            $current_user = new user($LOGIN_DATA['user_id'],$server);
            echo round($current_user->calculateScore(),2)." Overall Score";
            echo ' | '.round($current_user->calculateDailyGold(),2).' <img src="/images/coins_add.png" alt="Income" title="Income" /> ';
            echo ' | '.$current_user->getValue('gold').' <img src="/images/coins.png" alt="Gold" title="Gold" /> ';
            echo ' | <a href="/server/'.$_REQUEST['server_id'].'">'.$current_user->getValue('city_count').' <img src="/images/building.png" alt="Cities" title="Cities" /></a>';
         }//end if server_id
		}//end if user_id
?>
