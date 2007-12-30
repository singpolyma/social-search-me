<?php
      require_once dirname(__FILE__).'/include/processCookie.php';
      require_once dirname(__FILE__).'/include/server.php';
      if($LOGIN_DATA['user_id']) {
         if($_REQUEST['server_id']) {
            $server = new server($_REQUEST['server_id']);
            require_once dirname(__FILE__).'/include/user.php';
            $current_user = new user($LOGIN_DATA['user_id'],$server);
            echo ' | '.round($current_user->calculateScore(),2)." Overall Score";
            echo ' | '.round($current_user->calculateDailyGold(),2)." Income";
            echo ' | '.$current_user->getValue('gold')." Gold";
            echo ' | <a href="/server/'.$_REQUEST['server_id'].'">'.$current_user->getValue('city_count')." Cities</a>";
         }//end if server_id
		}//end if user_id
?>
