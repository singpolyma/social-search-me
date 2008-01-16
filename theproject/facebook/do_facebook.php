<?php

require_once dirname(__FILE__).'/../include/user.php';
require_once dirname(__FILE__).'/../include/server.php';
require_once dirname(__FILE__).'/facebook.php';

function do_facebook($user) {
	if(!$user->getValue('facebook_id')) return;
	global $db;
	$fbml = '';
	$userid = $user->getValue('userid');
	$appapikey = '5502d60d6c4172a6f84ff7269aa8da80';
	$appsecret = 'a603ff4995447f220eb65785b7f916fb';
	$facebook = new Facebook($appapikey, $appsecret);

	$nickname = $user->getValue('nickname') ? htmlentities($user->getValue('nickname')) : 'User #'.$userid;
	$fbml .= '<div class="vcard">';
	$fbml .= 'I am <a class="fn nickname url" rel="me" href="http://theproject.singpolyma.net/user/'.$userid.'">'.$nickname.'</a> on The Project<br />';

	require_once dirname(__FILE__).'/../include/connectDB.php';
	$servers = mysql_query('SELECT server_id,server_name FROM servers', $db) or die(mysql_error());
	while($server = mysql_fetch_assoc($servers)) {
		$user = new user($userid, new server($server['server_id']));
		$fbml .= '<p>';
		$fbml .= ' <b>On '.htmlentities($server['server_name']).'</b>';
		$fbml .= $user->getValue('rank') ? ' Ranked #'.$user->getValue('rank') : ' Not Ranked';
		$fbml .= ' <br />&nbsp; &nbsp; &nbsp; Score: '.ceil($user->calculateScore());
		$fbml .= ' <br />&nbsp; &nbsp; &nbsp; Gold: '.ceil($user->getValue('gold'));
		$fbml .= '</p>';
	}//end while servers

	$fbml .= '</div>';

	$friends = $facebook->api_client->profile_setFBML($fbml,$user->getValue('facebook_id'));
	echo "DONE FACEBOOK FOR USER#$userid\n";
}//end do_facebook

?>
