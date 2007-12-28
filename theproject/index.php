<?php

$pathparts = array_reverse(explode('/',$_SERVER['SCRIPT_URI']));
if(!$pathparts[0]) array_shift($pathparts);
if($pathparts[3] == 'server') $_REQUEST['server_id'] = $pathparts[2];
if($pathparts[1] == 'city') {
	$_REQUEST['city_id'] = $pathparts[0];
	require dirname(__FILE__).'/city.php';
	exit;
}//end if city
if($pathparts[1] == 'user') {
	$_REQUEST['user_id'] = $pathparts[0];
	require dirname(__FILE__).'/user.php';
	exit;
}//end if user
if($pathparts[1] == 'attack') {
	$_REQUEST['attack_id'] = $pathparts[0];
	require dirname(__FILE__).'/attack.php';
	exit;
}//end if attack
if($pathparts[1] == 'server') {
	$_REQUEST['server_id'] = $pathparts[0];
	require dirname(__FILE__).'/server.php';
	exit;
}//end if server


require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/server.php';
require_once dirname(__FILE__).'/include/invisible_header.php';

?>
	<head>
		<title>The Project</title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="/styles/login.css" type="text/css" media="screen" />
		<link rel="shortcut icon" href="/favicon.ico" />
	</head>
	
	<body>
	<?php

	require dirname(__FILE__).'/include/visible_header.php';
	
	if($LOGIN_DATA['user_id']) { ?>
	<h2>Active Servers</h2>
	<ul>
	<?php
		require_once dirname(__FILE__).'/include/connectDB.php';
		$servers = mysql_query('SELECT server_id,server_name FROM servers', $db) or die(mysql_error());
		while($server = mysql_fetch_assoc($servers)) {
			echo '<li>';
			echo '<a href="/server/'.$server['server_id'].'">'.htmlentities($server['server_name']).'</a>';
			echo '</li>';
		}//end while servers
		echo '</ul>';
	 } else { ?>
		 <?php require(dirname(__FILE__).'/login/form.php'); ?>
	<?php } ?>
	 
	</body>
</html>
