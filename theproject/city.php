<?php
require_once dirname(__FILE__).'/include/city.php';
if(isset($_REQUEST['ajax']))
	require_once dirname(__FILE__).'/include/processCookie.php';
else
	require_once dirname(__FILE__).'/include/invisible_header.php';

if(!$LOGIN_DATA['user_id']) die('<head><title>Please Log in</title></head><body><h2>Please log in.</h2></body></html>');

$this_city = new city($_REQUEST['city_id']);
$is_owner = ($LOGIN_DATA['user_id'] == $this_city->getValue('user')->getValue('userid'));
$can_edit = $this_city->getValue('user_'.$LOGIN_DATA['user_id'].'_edit');
$can_edit = ($can_edit === true) || (intval($can_edit) > time());
if($can_edit !== true && intval($can_edit) > time()) $can_edit_time = ceil(($can_edit - time())/60);
$can_access = $this_city->getValue('user_'.$LOGIN_DATA['user_id'].'_access');
if($can_access !== true && intval($can_access) > time()) $can_access_time = ceil(($can_access - time())/60);
$can_access = ($can_access === true) || (intval($can_access) > time());
//if(!$can_access) die('<head><title>Cannot View City</title></head><body><h2>You cannot view this city.</h2></body></html>');

$server = new server($_REQUEST['server_id']);

if($can_edit && $_POST['building_id']) {
	$message = $this_city->build(intval($_POST['building_id']));
	if($message) $message = '<b style="padding:3px;border:1px solid red;display:block;"><img src="/images/error.png" alt="" /> '.htmlentities($message).'</b>';
		else $message = '<div style="padding:3px;border:1px solid #ccc;display:block;"><img src="/images/information.png" alt="" /> Building started.</div>';
	if(isset($_REQUEST['ajax'])) exit($message);
}//end if POST building_id

if($can_edit && $_POST['unit_id']) {
	$message = $this_city->create_units(intval($_POST['unit_id']), intval($_POST['unit_count']));
	if($message) $message = '<b style="padding:3px;border:1px solid red;display:block;"><img src="/images/error.png" alt="" /> '.htmlentities($message).'</b>';
		else $message = '<div style="padding:3px;border:1px solid #ccc;display:block;"><img src="/images/information.png" alt="" /> Units started.</div>';
	if(isset($_REQUEST['ajax'])) exit($message);
}//end if POST unit_id

?>
	<head>
		<title>The Project - City @ <?php echo htmlentities($_REQUEST['city_id']); ?></title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
      <script type="text/javascript" src="/include/prototype.js"></script>
      <script type="text/javascript" src="/include/ajax.js"></script>
      <script type="text/javascript">
      //<![CDATA[
         var city_transaction_id = '';
         function update_city_transactions() {
            new Ajax.Updater('city-transactions', '/city_transactions.php?city_id=<?php echo $this_city->getValue('id'); ?>&server_id=<?php echo $server->getID(); ?>');
            new Ajax.Updater('units', '/city_units.php?city_id=<?php echo $this_city->getValue('id'); ?>&server_id=<?php echo $server->getID(); ?>');
            new Ajax.Updater('buildings', '/city_buildings.php?city_id=<?php echo $this_city->getValue('id'); ?>&server_id=<?php echo $server->getID(); ?>');
            new Ajax.Updater('stats', '/city_stats.php?city_id=<?php echo $this_city->getValue('id'); ?>&server_id=<?php echo $server->getID(); ?>');
            city_transaction_id = setTimeout("update_city_transactions()", 1000*10);
         }//end function update_day_left
         city_transaction_id = setTimeout("update_city_transactions()", 1000*10)
      //]]>
      </script>
	</head>
	
	<body>
	<?php require_once dirname(__FILE__).'/include/visible_header.php';
		echo '<div id="ajax-response" style="text-align:center;font-size:1.3em;">';
		if($message) echo $message; 
		echo '</div>';
	?>

	<div id="stats">
		<?php require_once dirname(__FILE__).'/city_stats.php'; ?>
	</div>

	<div id="city-transactions" style="float:right;">
		<?php require dirname(__FILE__).'/city_transactions.php'; ?>
	</div>

	<div id="units">
		<?php require_once dirname(__FILE__).'/city_units.php'; ?>
	</div>
	<?php if($can_edit) : ?>
	<form method="post" action="" onsubmit="dofrm(this,'&lt;div style=&quot;padding:3px;border:1px solid #ccc;display:block;&quot;&gt;&lt;img src=&quot;/images/information.png&quot; alt=&quot;&quot; /&gt; Training...&lt;/div&gt;'); return false;">
		<select id="unit_id" name="unit_id">
			<?php echo $unit_options ?>
		</select>
		<input type="text" name="unit_count" value="Number to train" onclick="this.value=''" />
		<input type="submit" value="Train" />
	</form>
	<?php endif; ?>

	<a href="/server/<?php echo $server->getID(); ?>/attack/<?php if($can_access) echo '+'; ?><?php echo $this_city->getValue('id'); ?>">attack/move</a>
	
	<div id="buildings">
		<?php require_once dirname(__FILE__).'/city_buildings.php';  ?>
	</div>
	<?php if($can_edit) : ?>
	<form method="post" action="" onsubmit="dofrm(this,'&lt;div style=&quot;padding:3px;border:1px solid #ccc;display:block;&quot;&gt;&lt;img src=&quot;/images/information.png&quot; alt=&quot;&quot; /&gt; Building...&lt;/div&gt;'); return false;">
		<select id="building_id" name="building_id">
			<?php echo $building_options ?>
		</select>
		<input type="submit" value="Bulid" />
	</form>
	<?php endif; ?>
	 
	</body>
</html>
