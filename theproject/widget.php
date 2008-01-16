<?php

require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/server.php';

	$user = new user($_REQUEST['user_id'], new server(1));
	$fbml = '';
	$userid = $user->getValue('userid');

	$nickname = $user->getValue('nickname') ? htmlentities($user->getValue('nickname')) : 'User #'.$userid;
	$fbml .= '<div class="vcard">';
	$fbml .= 'I am <a class="fn nickname url" rel="me" href="http://t.heproject.com/user/'.$userid.'">'.$nickname.'</a> on The Project<br />';

	require_once dirname(__FILE__).'/include/connectDB.php';
	$servers = mysql_query('SELECT server_id,server_name FROM servers', $db) or die(mysql_error());
	while($server = mysql_fetch_assoc($servers)) {
		$user = new user($userid, new server($server['server_id']));
		$fbml .= '<p>';
		$fbml .= ' <b>On '.htmlentities($server['server_name']).'</b>';
		$fbml .= ($user->getValue('rank') ? ' Ranked #'.$user->getValue('rank') : ' Not Ranked');
		$fbml .= ' <br />&nbsp; &nbsp; &nbsp; Score: '.ceil($user->calculateScore());
		$fbml .= ' <br />&nbsp; &nbsp; &nbsp; Gold: '.ceil($user->getValue('gold'));
		$fbml .= '</p>';
	}//end while servers

	$fbml .= '</div>';

if(isset($_REQUEST['js'])) {
	header('Content-type: text/javascript');
	echo 'document.writeln(\'';
	echo $fbml;
	echo '\');';
} else
	echo $fbml;

?>
