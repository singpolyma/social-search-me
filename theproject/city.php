<?php
require_once dirname(__FILE__).'/include/city.php';
require_once dirname(__FILE__).'/include/invisible_header.php';

$this_city = new city($_REQUEST['city_id']);
if(!$LOGIN_DATA['user_id'] || $LOGIN_DATA['user_id'] != $this_city->getValue('user')->getValue('userid')) die('Please log in to the correct account.');

$server = new server($_REQUEST['server_id']);

if($_POST['building_id']) {
	$message = $this_city->build(intval($_POST['building_id']));
}//end if POST building_id

if($_POST['unit_id']) {
	$message = $this_city->create_units(intval($_POST['unit_id']), intval($_POST['unit_count']));
}//end if POST unit_id

?>
	<head>
		<title>The Project - Manage City @ <?php echo htmlentities($_REQUEST['city_id']); ?></title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
	</head>
	
	<body>
	<?php require_once dirname(__FILE__).'/include/visible_header.php';

		if($message) echo '<b style="padding:3px;border:1px solid red;display:block;">'.htmlentities($message).'</b>';

		$units = mysql_query("SELECT unit_id,name,cost FROM units WHERE server_id=".$server->getID(),$db) or die(mysql_query());
		while($unit = mysql_fetch_assoc($units)) {
			$unit_options .= "\t\t\t".'<option value="'.$unit['unit_id'].'">'.htmlentities($unit['name']).' ('.$unit['cost'].' Gold)</option>'."\n";

			$unit_list .= "\t\t\t<li>";
			$unit_list .= htmlentities($unit['name']);
			$unit_list .= ' ('.intval($this_city->getValue('unit_'.$unit['unit_id'])).')';
			$unit_list .= "</li>\n";
		
			$transactions = mysql_query("SELECT unit_count,eta FROM server_unit_transaction WHERE server_id=".$server->getID()." AND unit_id=".$unit['unit_id']." AND user_id=".$this_city->getValue('user')->getValue('userid')." AND destination=".$this_city->getValue('id')." ORDER BY eta DESC",$db) or die(mysql_query());
			while($transaction = mysql_fetch_assoc($transactions)) {
				$unit_transaction_list .= "\t\t\t".'<li>'.htmlentities($unit['name']).' ('.$transaction['unit_count'].') in '.ceil(($transaction['eta']-time())/60).' minutes</li>'."\n";
			}//end while unit
		}//end while unit

		$buildings = mysql_query("SELECT building_id,name,cost FROM buildings WHERE server_id=".$server->getID(),$db) or die(mysql_query());
		while($building = mysql_fetch_assoc($buildings)) {
			$building_list .= "\t\t\t".'<li>';
			$building_list .= htmlentities($building['name']);
			$building_list .= ' ('.intval($this_city->getValue($building['building_id'])).')';
			$building_list .= '</li>'."\n";
			$building_options .= "\t\t\t".'<option value="'.$building['building_id'].'">'.htmlentities($building['name']).' ('.$building['cost'].' Gold)</option>'."\n";
			$transactions = mysql_query("SELECT eta FROM server_building_transaction WHERE server_id=".$server->getID()." AND building_id=".$building['building_id']." AND user_id=".$this_city->getValue('user')->getValue('userid')." AND city_id=".$this_city->getValue('id')." ORDER BY eta DESC",$db) or die(mysql_query());
			while($transaction = mysql_fetch_assoc($transactions)) {
				$building_transaction_list .= "\t\t\t".'<li>'.htmlentities($building['name']).' in '.ceil(($transaction['eta']-time())/60).' minutes</li>'."\n";
			}//end while unit
		}//end while building

		echo '<h3>Location: '.$this_city->getValue('id').'</h3>';
		foreach($this_city->getKeys() as $key) {
			$key2 = explode('_',$key);
			if($key == 'id' || $key == 'server' || $key == 'user' || is_numeric($key) || ($key2[0] == 'unit' && is_numeric($key2[1]))) continue;
			$label = ucwords(str_replace('_',' ',$key));
			echo '<div>'.$label.': '.$this_city->getValue($key).'</div>';
		}//end foreach key

	?>
	<div style="float:right;">
		<h4>Inbound Units</h4>
		<ol>
		<?php echo $unit_transaction_list; ?>
		</ol>
		<h4>Buildings in Progress</h4>
		<ol>
		<?php echo $building_transaction_list; ?>
		</ol>
	</div>
	<h3>Units</h3>
	<ul>
		<?php echo $unit_list; ?>
	</ul>
	<form method="post" action="">
		<select id="unit_id" name="unit_id">
			<?php echo $unit_options ?>
		</select>
		<input type="text" name="unit_count" value="Number to train" />
		<input type="submit" value="Train" />
	</form>
	<a href="/server/<?php echo $server->getID(); ?>/attack/+<?php echo $this_city->getValue('id'); ?>">attack/move</a>
	
	<h3>Buildings</h3>
	<ul>
		<?php echo $building_list;  ?>
	</ul>
	<form method="post" action="">
		<select id="building_id" name="building_id">
			<?php echo $building_options ?>
		</select>
		<input type="submit" value="Bulid" />
	</form>
	 
	</body>
</html>
