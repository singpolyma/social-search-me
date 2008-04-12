<?php

require dirname(__FILE__).'/settings.php';

session_start();

if($_POST['password'])
	$_SESSION['password'] = $_POST['password'];

if($_SESSION['password'] != $password) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Authenticate - Simple Event CMS</title>
	</head>
	<body>
		<h1>Simple Event CMS</h1>
		<form method="post" action=""><div>
			<h2>Password Please</h2>
			<input type="password" name="password" value="password plz" />
			<input type="submit" value="Login &raquo;" />
		</div></form>
	</body>
</html>
<?php
	die;
}//end if ! password

?>
