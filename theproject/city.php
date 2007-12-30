<?php
require_once dirname(__FILE__).'/include/city.php';
require_once dirname(__FILE__).'/include/invisible_header.php';

$this_city = new city($_REQUEST['city_id']);
if(!$LOGIN_DATA['user_id'] || $LOGIN_DATA['user_id'] != $this_city->getValue('user')->getValue('userid')) die('<head><title>Lost City</title></head><body><h2>The city is no longer yours.</h2></body></html>');

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
      <script type="text/javascript" src="/include/prototype.js"></script>
      <script type="text/javascript" src="/include/ajax.js"></script>
      <script type="text/javascript">
      //<![CDATA[
         var city_transaction_id = '';
         function update_city_transactions() {
            new Ajax.Updater('city-transactions', '/city_transactions.php?city_id=<?php echo $this_city->getValue('id'); ?>&server_id=<?php echo $server->getID(); ?>');
            city_transaction_id = setTimeout("update_city_transactions()", 1000*10);
         }//end function update_day_left
         city_transaction_id = setTimeout("update_city_transactions()", 1000*10)
      //]]>
      </script>
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
		}//end while unit

		$buildings = mysql_query("SELECT building_id,name,cost FROM buildings WHERE server_id=".$server->getID(),$db) or die(mysql_query());
		while($building = mysql_fetch_assoc($buildings)) {
			$building_list .= "\t\t\t".'<li>';
			$building_list .= htmlentities($building['name']);
			$building_list .= ' ('.intval($this_city->getValue($building['building_id'])).')';
			$building_list .= '</li>'."\n";
			$building_options .= "\t\t\t".'<option value="'.$building['building_id'].'">'.htmlentities($building['name']).' ('.$building['cost'].' Gold)</option>'."\n";
		}//end while building

		echo '<h3>';
		if($this_city->getValue('name'))
			echo htmlentities($this_city->getValue('name')).' ';
		echo '(Location: '.$this_city->getValue('id').')</h3>';
		foreach($this_city->getKeys() as $key) {
			$key2 = explode('_',$key);
			if($key == 'id' || $key == 'name' || $key == 'server' || $key == 'user' || is_numeric($key) || ($key2[0] == 'unit' && is_numeric($key2[1]))) continue;
			$label = ucwords(str_replace('_',' ',$key));
			echo '<div>'.$label.': '.$this_city->getValue($key).'</div>';
		}//end foreach key

	?>
	<div id="city-transactions" style="float:right;">
		<?php require dirname(__FILE__).'/city_transactions.php'; ?>
	</div>
	<h3>Units</h3>
	<ul>
		<?php echo $unit_list; ?>
	</ul>
	<form method="post" action="">
		<select id="unit_id" name="unit_id">
			<?php echo $unit_options ?>
		</select>
		<input type="text" name="unit_count" value="Number to train" onclick="this.value=''" />
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
