<?php

require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/server.php';
require_once dirname(__FILE__).'/include/invisible_header.php';

if($_REQUEST['server_id']) $server = new server($_REQUEST['server_id']);
$this_user = new user(intval($_REQUEST['user_id']), $server?$server:'main');

if($_REQUEST['user_id'] == $LOGIN_DATA['user_id'] && isset($_REQUEST['nickname'])) {//if this is the logged in user
	mysql_query("UPDATE users SET 
nickname='".mysql_real_escape_string($_REQUEST['nickname'],$db)."',
photo='".mysql_real_escape_string($_REQUEST['photo'],$db)."',
email='".mysql_real_escape_string($_REQUEST['email'],$db)."'
WHERE user_id=".$this_user->getValue('userid')
,$db) or die(mysql_error());
}//end if user is loggid in and form submitted

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
			<img src="https://pibb.com/images/pibb_me_medium.png" style="border-width:0px;" title="Chat at Pibb.com" />
		</a>
	</div>
	<address class="vcard">
		<img src="<?php echo htmlentities($this_user->getValue('photo')); ?>" alt="" class="photo" />
		<span class="fn nickname"><?php echo htmlentities($this_user->getValue('nickname')); ?></span>
	</address>
	<?php
		if($_REQUEST['user_id'] == $LOGIN_DATA['user_id']) {//if this is the logged in user
			echo '<h2>Edit User Data</h2>';
			echo '<form method="post" action=""><div>';
			echo '<label style="display:block;float:left;width:100px;" for="nickname">Nickname:</label>';
			echo ' <input type="text" name="nickname" id="nickname" value="'.htmlentities($this_user->getValue('nickname')).'" /><br />';
			echo '<label style="display:block;float:left;width:100px;" for="photo">Photo URL:</label>';
			echo ' <input type="text" name="photo" id="photo" value="'.htmlentities($this_user->getValue('photo')).'" /><br />';
			echo '<label style="display:block;float:left;width:100px;" for="email">Email:</label>';
			echo ' <input type="text" name="email" id="email" value="'.htmlentities($this_user->getValue('email')).'" /><br />';
			echo ' <input type="submit" value="Save" />';
			echo '</div></form><br />';
		}//end if user_id
	?>
	<?php if($server) : ?>
	<?php echo $this_user->getValue('gold'); ?> Gold<br />
	<?php echo $this_user->getValue('city_count'); ?> Cities<br />
	<h2>Cities</h2>
	<ul>
	<?php
			foreach($this_user->getValue('cities') as $city) {
				echo '<li>';
				if($city->getValue('name'))
					echo htmlentities($city->getValue('name')).' / ';
				echo ' Location: '.str_pad($city->getValue('id'),6,'0',STR_PAD_LEFT);
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
