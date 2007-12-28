<?php

$last_day = intval(file_get_contents('xfn_day'));
if($last_day < time()-(60*60*24)) {
	//XFN
	require_once dirname(__FILE__).'/include/connectDB.php';
	require_once dirname(__FILE__).'/include/xfn.php';
	mysql_query("TRUNCATE TABLE friends",$db) or die(mysql_error());
	$urls = mysql_query("SELECT user_id, openid FROM openids",$db) or die(mysql_error());
	while($url = mysql_fetch_assoc($urls)) {
		xfn2db($url['user_id'], $url['openid']);
	}//end for while url
	file_put_contents('xfn_day',time());
	echo 'Did XFN<br />';
}//end if is next day

?>
