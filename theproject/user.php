<?php

require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/server.php';
require_once dirname(__FILE__).'/include/invisible_header.php';

if($_REQUEST['server_id']) $server = new server($_REQUEST['server_id']);

if($_REQUEST['user_id'] == $LOGIN_DATA['user_id'] && isset($_REQUEST['nickname'])) {//if this is the logged in user
	mysql_query("UPDATE users SET 
nickname='".mysql_real_escape_string($_REQUEST['nickname'],$db)."',
photo='".mysql_real_escape_string($_REQUEST['photo'],$db)."',
email='".mysql_real_escape_string($_REQUEST['email'],$db)."',
twitter='".mysql_real_escape_string($_REQUEST['twitter'],$db)."'
WHERE user_id=".$LOGIN_DATA['user_id']
,$db) or die(mysql_error());
   $ch = curl_init('http://twitter.com/friendships/create/'.urlencode($_REQUEST['twitter']).'.xml');
   curl_setopt($ch, CURLOPT_USERPWD, file_get_contents('include/twitter.txt'));
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
   $response = curl_exec($ch);
   curl_close($ch);
}//end if user is loggid in and form submitted

$this_user = new user(intval($_REQUEST['user_id']), $server?$server:new server(1), true);

?>
	<head>
		<title>The Project - User <?php echo htmlentities($this_user->getValue('nickname')); ?></title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
	</head>
	
	<body>
	<?php	require_once dirname(__FILE__).'/include/visible_header.php'; ?>
	<div style="float:right;">
		<?php $openids=$this_user->getValue('openids'); $openid = urlencode($openids[0]);  ?>
		<a href="https://pibb.com/me/<?php echo $openid; ?>">
			<img src="https://pibb.com/images/pibb_me_medium.png" style="border-width:0px;" title="Chat at Pibb.com" alt="Chat at Pibb.com" />
		</a>
	</div>
	<address class="vcard">
		<img src="<?php echo htmlentities($this_user->getValue('photo')); ?>" alt="" class="photo" />
		<span class="fn nickname"><?php echo htmlentities($this_user->getValue('nickname')); ?></span>
	</address>
	<?php
		if($_REQUEST['user_id'] == $LOGIN_DATA['user_id']) {//if this is the logged in user
			echo '<form method="post" action=""><div style="float:right;"><h2>Widgets</h2>';
			echo '<a href="http://www.facebook.com/apps/application.php?id=21604790816">Add on Facebook</a>';
			echo '<p>Embed in your website/Myspace:</p>';
			echo "\n".'<input type="text" onclick="this.select();" value="'.htmlentities('<script type="text/javascript" src="http://theproject.singpolyma.net/widget.php?js&amp;user_id='.$this_user->getValue('userid').'"></script>').'" />';
			echo '<p>Embed a PHP script:</p>';
			echo '<input type="text" onclick="this.select();" value="'.htmlentities('<?php include("http://theproject.singpolyma.net/widget.php?user_id='.$this_user->getValue('userid').'"); ?>').'" />';
			echo '</div></form>';
			
			echo '<h2>Edit User Data</h2>';
			echo '<form method="post" action=""><div style="margin-bottom:1em;">';
			echo '<label style="display:block;float:left;width:150px;" for="nickname">Nickname:</label>';
			echo ' <input type="text" name="nickname" id="nickname" value="'.htmlentities($this_user->getValue('nickname')).'" /><br />';
			echo '<label style="display:block;float:left;width:150px;" for="photo">Photo URL:</label>';
			echo ' <input type="text" name="photo" id="photo" value="'.htmlentities($this_user->getValue('photo')).'" /><br />';
			echo '<label style="display:block;float:left;width:150px;" for="email">Email:</label>';
			echo ' <input type="text" name="email" id="email" value="'.htmlentities($this_user->getValue('email')).'" /><br />';
			echo '<label style="display:block;float:left;width:150px;" for="twitter">Twitter Username:</label>';
			echo ' <input type="text" name="twitter" id="twitter" value="'.htmlentities($this_user->getValue('twitter')).'" /><br />';
			echo ' <input type="submit" value="Save" />';
			echo '</div></form>';
		}//end if user_id
	?>
	<?php if($server) : ?>
	<div><?php echo $this_user->getValue('gold'); ?> Gold</div>
	<div><?php echo $this_user->getValue('city_count'); ?> Cities</div>
	<h2 style="clear:both;">Cities</h2>
	<ul>
	<?php
			foreach($this_user->getValue('cities') as $city) {
				echo '<li>';
				$can_access = $city->getValue('user_'.$LOGIN_DATA['user_id'].'_access');
				$can_access = ($can_access === true) || (intval($can_access) > time());
				echo '<a href="/server/'.$server->getID().'/city/'.$city->getValue('id').'">';
				if($city->getValue('name'))
					echo htmlentities($city->getValue('name')).' / ';
				echo ' Location: '.str_pad($city->getValue('id'),6,'0',STR_PAD_LEFT);
				echo '</a>';
				echo ' / Population: '.$city->getValue('population');
				echo ' / Defense: '.(intval($city->getValue('defense'))+1);
				echo ' - <a href="/server/'.$server->getID().'/attack/'.$city->getValue('id').'">attack/move</a>';
				echo '</li>';
			}//end foreach city
	?>
	</ul>
	<?php endif; ?>
	</body>
</html>
