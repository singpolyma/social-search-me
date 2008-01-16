<?php

   require_once dirname(__FILE__).'/include/processCookie.php';
   require_once dirname(__FILE__).'/include/connectDB.php';

	$server_message = mysql_query("SELECT message FROM messages WHERE server_id=".mysql_real_escape_string($_REQUEST['server_id'],$db).' AND user_id='.$LOGIN_DATA['user_id']." ORDER BY time DESC LIMIT 1",$db) or die(mysql_error());
	$server_message = mysql_fetch_assoc($server_message);
	echo $server_message['message'];

?>
