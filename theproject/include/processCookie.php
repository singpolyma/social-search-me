<?php
/*
$sess_save_path = dirname(dirname(__FILE__)).'/session_data';

function open($save_path, $session_name)
{
  global $sess_save_path;

  $sess_save_path = $save_path;
  return(true);
}

function close()
{
  return(true);
}

function read($id)
{
  global $sess_save_path;

  $sess_file = "$sess_save_path/sess_$id";
  return (string) @file_get_contents($sess_file);
}

function write($id, $sess_data)
{
  global $sess_save_path;

  $sess_file = "$sess_save_path/sess_$id";
  if ($fp = @fopen($sess_file, "w")) {
    $return = fwrite($fp, $sess_data);
    fclose($fp);
    return $return;
  } else {
    return(false);
  }

}

function destroy($id)
{
  global $sess_save_path;

  $sess_file = "$sess_save_path/sess_$id";
  return(@unlink($sess_file));
}

function gc($maxlifetime)
{
  global $sess_save_path;

  foreach (glob("$sess_save_path/sess_*") as $filename) {
    if (filemtime($filename) + $maxlifetime < time()) {
      @unlink($filename);
    }
  }
  return true;
}

session_set_save_handler("open", "close", "read", "write", "destroy", "gc");

session_start();

if($_COOKIE['user_openid'] && !$_SESSION['user_openid']) {
   $at = $_SERVER['SCRIPT_URI'];
   header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/login/try_auth.php?openid_identifier='.urlencode($_COOKIE['user_openid']).'&return_to='.urlencode($at),true,303);//login
   exit;
}//end if user_openid

$LOGIN_DATA = $_SESSION;//in case we ever change data model

*/

if($_COOKIE['user_openid'] && !$_COOKIE['the_project_session']) {
   $at = $_SERVER['SCRIPT_URI'];
   header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/login/try_auth.php?openid_identifier='.urlencode($_COOKIE['user_openid']).'&return_to='.urlencode($at),true,303);//login
   exit;
}//end if user_openid

if($_COOKIE['the_project_session']) {
	require_once dirname(__FILE__).'/connectDB.php';
	$LOGIN_DATA = mysql_query("SELECT user_id FROM users WHERE session_id='".$_COOKIE['the_project_session']."' LIMIT 1",$db) or die(mysql_error());
	$LOGIN_DATA = mysql_fetch_assoc($LOGIN_DATA);//in case we ever change data model
}

?>
