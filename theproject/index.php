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
if($pathparts[0] == 'leaders') {
	$_REQUEST['server_id'] = $pathparts[1];
	require dirname(__FILE__).'/leaders.php';
	exit;
}//end if server
if($pathparts[0] == 'rules') {
	$_REQUEST['server_id'] = $pathparts[1];
	require dirname(__FILE__).'/rules.php';
	exit;
}//end if server
if($pathparts[0] == 'attacks') {
	$_REQUEST['server_id'] = $pathparts[1];
	require dirname(__FILE__).'/attacks.php';
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

<div style="text-align:center;">
	<p>
		The Project is a community-driven online strategy game.  One of the most unique features is the modular game engine, which enables admins to create servers that each have drastically different gameplay.  The Project is currently under heavy development.  Please report and bugs you find or request any features on <a href="http://pibb.com/go/theproject">our forums</a>.
	</p>
	<object style="border-width:0px;width:95%;height:450px;" data="/discuss.php" type="text/html"></object>
</div>
	 
	</body>
</html>
