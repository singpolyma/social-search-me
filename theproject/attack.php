<?php

require_once dirname(__FILE__).'/include/city.php';
require_once dirname(__FILE__).'/include/server.php';
require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/invisible_header.php';

if(!$LOGIN_DATA['user_id']) die('Please log in.');
$current_user = new user($LOGIN_DATA['user_id']);

$server = new server($_REQUEST['server_id']);
$cities = preg_split('/[\s\+]+/',$_REQUEST['attack_id']);
if($cities[0]) $tocity = new city($cities[0], $server);
if($cities[1]) $fromcity = new city($cities[1], $server);

if($_POST['attack_id']) {
   $message = $fromcity->initiate_transaction(intval($_POST['attack_id']), intval($_POST['attack_count']), intval($_POST['attack_destination']));
	header('Location: '.dirname(dirname($_SERVER['SCRIPT_URI'])),true,303);
	exit;
}//end if POST attack_id

?>
	<head>
		<title>The Project - Attack/Move</title>
		<?php $xhtmlSite->metaType(); ?>
		<link rel="stylesheet" href="/styles/main.css" type="text/css" media="screen" />
	</head>
	
	<body>
		<?php
			require_once dirname(__FILE__).'/include/visible_header.php';

			if($message) echo '<b>'.$message.'</b>';
				elseif($_POST['attack_id']) echo '<b>Troop movements started!</b>';

			if(!$tocity) {
				echo '<h2>Select a City to Attack/Move to</h2><ul>';
				if($fromcity) {
					$close_upper_bound = intval($fromcity->getValue('id'))+25000;
					$dbcities = mysql_query("SELECT user_id,city_id FROM server_cities WHERE server_id=".$server->getID()." AND city_id < $close_upper_bound ORDER BY city_id DESC  LIMIT 50",$db) or die(mysql_error());
				} else {
					$dbcities = mysql_query("SELECT user_id,city_id FROM server_cities WHERE server_id=".$server->getID()." ORDER BY user_id LIMIT 50",$db) or die(mysql_error());
				}//end if-else fromcity
				while($city = mysql_fetch_assoc($dbcities)) {
					$user = new user($city['user_id']);
					$city = new city($city['city_id']);
					echo '<li>';
					echo '<a href="/server/'.$server->getID().'/attack/'.$city->getValue('id').'+'.$cities[1].'">Location: '.$city->getValue('id').'</a> Population: '.$city->getValue('population').' Defense: '.(intval($city->getValue('defense'))+1).' User: <a href="/server/'.$server->getID().'/user/'.$user->getValue('userid').'">'.htmlentities($user->getValue('nickname')).'</a>';
					echo '</li>';
				}//end while cities
				echo '</ul>';
			}//end if ! tocity

			if(!$fromcity) {
				echo '<h2>Select a City to Attack/Move From</h2><ul>';
				foreach($current_user->getValue('cities') as $city) {
					echo '<li>';
					echo '<a href="/server/'.$server->getID().'/attack/'.$cities[0].'+'.$city->getValue('id').'">Location: '.$city->getValue('id').'</a> Population: '.$city->getValue('population').' Defense: '.(intval($city->getValue('defense'))+1);
					echo '</li>';
				}//end foreach cities
				echo '</ul>';
			}//end if ! fromcity

			if($fromcity && $tocity) {
		     $units = mysql_query("SELECT unit_id,name,cost FROM units WHERE server_id=1",$db) or die(mysql_query());
   		   while($unit = mysql_fetch_assoc($units)) {
		         $unit_list .= "\t\t\t<li>";
   		      $unit_list .= htmlentities($unit['name']);
      		   $unit_list .= ' ('.intval($fromcity->getValue('unit_'.$unit['unit_id'])).')';
         		$unit_list .= '<form method="post" action="">';
	         	$unit_list .= '   <input type="hidden" name="attack_id" value="'.$unit['unit_id'].'" />';
	   	      $unit_list .= '   <input type="text" name="attack_count" value="Number to attack with" />';
   	   	   $unit_list .= '   <input type="hidden" name="attack_destination" value="'.$tocity->getValue('id').'" />';
      	   	$unit_list .= '   <input type="submit" value="Attack/Move" />';
	      	   $unit_list .= '</form>';
   	      	$unit_list .= "</li>\n";
        		}//end while unit
				echo '<h2>Units Available for Attack</h2><ul>'.$unit_list.'</ul>';
		}//end if fromcity && tocity

		?>
	</body>
</html>
