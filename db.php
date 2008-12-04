<?php
	$db_settings = preg_split('/\s+/',file_get_contents('/home/singpolyma/buddydb'));
	$db = mysql_connect($db_settings[0], $db_settings[1], $db_settings[2]);//connect to database
	mysql_select_db($db_settings[3], $db);
	mysql_query("SET NAMES 'UTF8'");
?>
