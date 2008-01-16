<?php

@include(dirname(__FILE__).'/include/xhtmlSite.php');
$xhtmlSite = new xhtmlSite();
$xhtmlSite->startDocument(true);

?>
	<head>
		<title>The Project - Discuss</title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
	</head>
	
	<body>
	
<div style="height:400px;" id="__pibb_thread"></div><script src="https://pibb.com/widget/thread/186B"></script>
	 
	</body>
</html>
