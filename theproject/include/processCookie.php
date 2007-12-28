<?php

session_start();

if($_COOKIE['user_openid'] && !$_SESSION['user_openid']) {
   $at = $_SERVER['SCRIPT_URI'];
   header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/login/try_auth.php?openid_identifier='.urlencode($_COOKIE['user_openid']).'&return_to='.urlencode($at),true,303);//login
   exit;
}//end if user_openid

$LOGIN_DATA = $_SESSION;//in case we ever change data model

?>
