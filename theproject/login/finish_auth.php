<?php

require_once "common.php";
session_start();

$return_to = $_SESSION['return_to']; unset($_SESSION['return_to']);
$action = $_SESSION['action']; unset($_SESSION['action']);

// Complete the authentication process using the server's response.
$response = $consumer->complete($_GET);

if($action == 'add')
	require_once dirname(dirname(__FILE__)).'/include/processCookie.php';

if ($response->status == Auth_OpenID_CANCEL) {
    // This means the authentication was cancelled.
    if($action == 'add')
	    header('Location: http://'.$_SERVER['HTTP_HOST'].dirname(dirname($_SERVER['PHP_SELF'])),true,303);//redirect to home
    else
	    header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/out.php',true,303);//redirect to home
} else if ($response->status == Auth_OpenID_FAILURE) {
    $msg = "OpenID authentication failed: " . $response->message;
    include 'index.php';
} else if ($response->status == Auth_OpenID_SUCCESS) {

   setcookie("user_openid",$response->identity_url,time()+(3600*1000),'/');//set cookie
   $_SESSION['user_openid'] = $response->identity_url;
   $sreg = $response->extensionResponse('sreg');
   if (@$sreg['email']) $_SESSION['user_email'] = $sreg['email'];
   if (@$sreg['nickname']) $_SESSION['user_nickname'] = $sreg['nickname'];

   require(dirname(__FILE__).'/../include/connectDB.php');//connect to database
   $user = mysql_query("SELECT user_id FROM openids WHERE openid='".mysql_real_escape_string($response->identity_url,$db)."' LIMIT 1", $db) or die(mysql_error());//get user_id
   $user = mysql_fetch_assoc($user);
   if($user && $action == 'add') {
   	$msg = 'That OpenID is already in the system!';
   	include 'index.php';
   	die;
   }//end if user && add
   if(!$user) {//non-existant user, create
   	if($action != 'add') {
	      mysql_query("INSERT INTO users (nickname,email) VALUES ('".mysql_real_escape_string(@$sreg['nickname'],$db)."','".mysql_real_escape_string(@$sreg['email'],$db)."')", $db) or die(mysql_error());//insert new user
   	   $userid = mysql_insert_id();
			require_once dirname(__FILE__).'/../include/hcard-import.php';
			hcard_import($userid, $response->identity_url);
   	} else
   		$userid = $LOGIN_DATA['user_id'];
      mysql_query("INSERT INTO openids (user_id,openid) VALUES ($userid,'".mysql_real_escape_string($response->identity_url,$db)."')", $db) or die(mysql_error());//insert user's OpenID
      $_SESSION['user_id'] = $userid;//deprecated, store custom sessid in database
		$session_id = sha1('the'.$userid.microtime(true).rand(-999999,999999).'project');
		mysql_query("UPDATE users SET session_id='$session_id' AND session_timeout=".(time()+60*60*25)." WHERE user_id=".$_SESSION['user_id'],$db) or die(mysql_error());
   	setcookie("the_project_session",$session_id,0,'/');//set cookie
      if($action == 'add')
	      header('Location: '.'http://'.$_SERVER['HTTP_HOST'].dirname(dirname($_SERVER['PHP_SELF'])),true,303);//redirect
      else
	      header('Location: '.'http://'.$_SERVER['HTTP_HOST'].dirname(dirname($_SERVER['PHP_SELF'])),true,303);//redirect
      exit;
   }//end if user
   $_SESSION['user_id'] = $user['user_id'];
	$session_id = sha1('the'.$userid.microtime(true).rand(-999999,999999).'project');
	mysql_query("UPDATE users SET session_id='$session_id', session_timeout=".(time()+60*60*25)." WHERE user_id=".$_SESSION['user_id'],$db) or die(mysql_error());
   setcookie("the_project_session",$session_id,0,'/');//set cookie
   @mysql_close($db);
   if(!$return_to) $return_to = 'http://'.$_SERVER['HTTP_HOST'].dirname(dirname($_SERVER['PHP_SELF']));
   header('Location: '.$return_to,true,303);//redirect
   exit;

}//end if-elses OpenID status

?>
