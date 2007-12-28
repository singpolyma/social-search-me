<?php

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
