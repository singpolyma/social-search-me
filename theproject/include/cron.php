<?php

if($LOGIN_DATA['user_id'] && $_REQUEST['server_id']) {
	require_once dirname(__FILE__).'/user.php';
	require_once dirname(__FILE__).'/server.php';
	$server = new server($_REQUEST['server_id']);
	$current_user = new user($LOGIN_DATA['user_id'], $server);
	$current_user->setValue('last_online',time());
	mysql_query("UPDATE users SET session_timeout=".(time()+(60*60*25)),$db) or die(mysql_error());
}//end if user

$argyle = @ fsockopen( $_SERVER['HTTP_HOST'], 80, $errno, $errstr, 0.01 );

if ( $argyle )
	fputs( $argyle,
   	"GET /process_transactions.php HTTP/1.0\r\n"
	. "Host: {$_SERVER['HTTP_HOST']}\r\n\r\n"
);

$argyle = @ fsockopen( $_SERVER['HTTP_HOST'], 80, $errno, $errstr, 0.01 );

if ( $argyle )
	fputs( $argyle,
   	"GET /process_day.php HTTP/1.0\r\n"
	. "Host: {$_SERVER['HTTP_HOST']}\r\n\r\n"
);

$argyle = @ fsockopen( $_SERVER['HTTP_HOST'], 80, $errno, $errstr, 0.01 );

if ( $argyle )
	fputs( $argyle,
   	"GET /process_xfn.php HTTP/1.0\r\n"
	. "Host: {$_SERVER['HTTP_HOST']}\r\n\r\n"
);

?>
