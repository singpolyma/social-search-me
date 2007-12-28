<?php

@include(dirname(__FILE__).'/../include/xhtmlSite.php');
$xhtmlSite = new xhtmlSite();
$xhtmlSite->startDocument(true);

?>
	<head>
		<title>The Project - FAQ</title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
	</head>
	
	<body>
	<div style="float:right;">
		<a href="https://pibb.com/go/theproject">Discuss</a>
	</div>
	<h1>The Project - FAQ</h1>
	
	<div style="height:400px;" id="__pibb_thread"></div>
	<script type="text/javascript" src="https://pibb.com/widget/thread/186C"></script>
	 
	</body>
</html>
