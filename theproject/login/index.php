<?php

require_once dirname(__FILE__).'/../include/invisible_header.php';

?>
	<head>
		<title>The Project - Login</title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="/styles/login.css" type="text/css" media="screen" />
		<style type="text/css">
			.alert {
				border: 1px solid #e7dc2b;
				background: #fff888;
			}
			.success {
				border: 1px solid #669966;
				background: #88ff88;
			}
			.error {
				border: 1px solid #ff0000;
				background: #ffaaaa;
			}
		</style>
	</head>
	
	<body>

	 <?php if (isset($msg)) { print "<div class=\"alert\">$msg</div>"; } ?>
	 <?php if (isset($error)) { print "<div class=\"error\">$error</div>"; } ?>
	 <?php if (isset($success)) { print "<div class=\"success\">$success</div>"; } ?>

	<?php require dirname(__FILE__).'/../include/visible_header.php'; ?>
		 <?php require(dirname(__FILE__).'/form.php'); ?>
	 
	</body>
</html>
