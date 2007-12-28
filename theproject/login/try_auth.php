<?php

require_once "common.php";
session_start();

if($_GET['openid_identifier']) $_GET['openid_url'] = $_GET['openid_identifier'];
switch($_GET['type']) {
   case 'aol':
      $_GET['openid_url'] = 'http://openid.aol.com/'.$_GET['openid_url'];
      break;
   case 'livejournal':
      $_GET['openid_url'] .= '.livejournal.com';
      break;
   case 'technorati':
      $_GET['openid_url'] = 'http://technorati.com/people/technorati/'.$_GET['openid_url'];
      break;
   case 'wordpress':
      $_GET['openid_url'] .= '.wordpress.com';
      break;
   case 'openid':
   default:
      if(!strstr($_GET['openid_url'],'.')) $_GET['openid_url'] .= '.myopenid.com';
}//end switch

if($_GET['return_to']) $_SESSION['return_to'] = $_GET['return_to'];
if($_GET['action']) $_SESSION['action'] = $_GET['action'];

// Render a default page if we got a submission without an openid
// value.
if (empty($_GET['openid_url'])) {
    $error = "Expected an OpenID URL.";
    include 'index.php';
    exit(0);
}

$scheme = 'http';
if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
    $scheme .= 's';
}

$openid = $_GET['openid_url'];
$process_url = sprintf("$scheme://%s%s/finish_auth.php",
                       $_SERVER['SERVER_NAME'],
                       dirname($_SERVER['PHP_SELF']));

$trust_root = sprintf("$scheme://%s%s",
                      $_SERVER['SERVER_NAME'],
                      dirname(dirname($_SERVER['PHP_SELF'])));

// Begin the OpenID authentication process.
$auth_request = $consumer->begin($openid);

// Handle failure status return values.
if (!$auth_request) {
    $error = "Authentication error.";
    include 'index.php';
    exit(0);
}

$auth_request->addExtensionArg('sreg', 'optional', 'email,nickname');

// Redirect the user to the OpenID server for authentication.  Store
// the token for this authentication so we can verify the response.

$redirect_url = $auth_request->redirectURL($trust_root,
                                           $process_url);

header("Location: ".$redirect_url);

?>
