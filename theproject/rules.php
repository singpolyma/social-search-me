<?php

require_once dirname(__FILE__).'/include/server.php';
require_once dirname(__FILE__).'/include/invisible_header.php';

if(!$server) $server = new server($_REQUEST['server_id']);

?>
	<head>
		<title>The Project - <?php echo $server->getName(); ?> Rules</title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
		<link rel="shortcut icon" href="/favicon.ico" />
		<style type="text/css">
			dt {float: left;}
			dd {text-align:right; width: 15em;}
		</style>
	</head>
	
	<body>
	<?php

		require dirname(__FILE__).'/include/visible_header.php';
	
		echo '<h3>'.$server->getName().' Rules</h3>';

		echo '<dl>';
		echo '	<dt>Gold players start with</dt>';
		echo '		<dd>'.$server->getInitialGold().'</dd>';
		echo '	<dt>Cities players start with</dt>';
		echo '		<dd>'.$server->getInitialCityCount().'</dd>';
		echo '	<dt>Cities start with population</dt>';
		echo '		<dd>'.$server->getInitialCityPopulation().'</dd>';
		echo '	<dt>Gold needed to build a city</dt>';
		echo '		<dd>'.$server->getCityCost().'</dd>';
		echo '	<dt>Maximum city population</dt>';
		echo '		<dd>'.$server->getCityPopulationMax().'</dd>';
		echo '	<dt>Hours in game day</dt>';
		echo '		<dd>'.($server->getDayLength()/(60*60)).'</dd>';
		echo '	<dt>Game days per round</dt>';
		echo '		<dd>'.$server->getWeekLength()/$server->getDayLength().'</dd>';
		echo '</dl>';

	?>
	</body>
</html>
