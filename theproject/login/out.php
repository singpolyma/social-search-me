<?php

// Initialize the session.
// If you are using session_name("something"), don't forget it now!
session_start();

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (isset($_COOKIE[session_name()])) {
   setcookie(session_name(), '', time()-42000, '/');
}

// Finally, destroy the session.
session_destroy();

//unset cookie
setcookie("user_openid",'',time()-3600,'/');
setcookie("the_project_session",'',time()-3600,'/');

header('Location: http://'.$_SERVER['HTTP_HOST'].dirname(dirname($_SERVER['PHP_SELF'])),true,303);//redirect to home

?>
