<?php
// Copyright 2007 Facebook Corp.  All Rights Reserved. 
// 
// Application: The Project
// File: 'index.php' 
//   This is a sample skeleton for your application. 
// 

require_once dirname(__FILE__).'/../include/invisible_header.php';

?>
	<head>
		<title>The Project - Facebook App</title>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="/styles/login.css" type="text/css" media="screen" />
		<link rel="shortcut icon" href="/favicon.ico" />
		<style type="text/css">
			#login, h1 {
				position: absolute;
				left: 230px;
				top: 120px;
				background-color: black;
				padding-bottom: 20px;
				padding-left: 30px;
				padding-right: 30px;
				z-index: 100;
			}
		</style>
	</head>

	<body>
	<img style="position:absolute;top:0px;left:90px;" src="/images/theprojectlogotext.jpg" alt="The Project" />
<?php

if(!$LOGIN_DATA['user_id']) {
?>
<?php
	$_SESSION['return_to'] = 'http://theproject.singpolyma.net/facebook/';
	require_once dirname(__FILE__).'/../login/form.php';
	echo '</body></html>';
	exit;
}//end if user_id

require_once 'facebook.php';

$appapikey = '5502d60d6c4172a6f84ff7269aa8da80';
$appsecret = 'a603ff4995447f220eb65785b7f916fb';
$facebook = new Facebook($appapikey, $appsecret);
$fbid = $facebook->require_login();

require_once dirname(__FILE__).'/../include/connectDB.php';
mysql_query("UPDATE users SET facebook_id=$fbid WHERE user_id=".$LOGIN_DATA['user_id'],$db) or die(mysql_error());

//import friends
$friends = $facebook->api_client->friends_get();
foreach ($friends as $friend) {
	$local = mysql_query("SELECT user_id FROM users WHERE facebook_id=$friend LIMIT 1",$db) or die(mysql_error());
	$local = mysql_fetch_assoc($local);
	if($local) {
		$exists = mysql_query("SELECT user_id FROM friends WHERE user_id=".$LOGIN_DATA['user_id']." AND friend_id=".$local['user_id'],$db) or die(mysql_error()); 
		if(!mysql_fetch_assoc($exists))
			mysql_query("INSERT INTO friends (user_id,friend_id) VALUES (".$LOGIN_DATA['user_id'].",".$local['user_id'].")",$db) or die(mysql_error());
	}//end if local
}//end foreach friends

?>
	<h1>Setup Complete.</h1>
	</body>
</html>
