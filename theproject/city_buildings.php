<?php

require_once dirname(__FILE__).'/include/city.php';
require_once dirname(__FILE__).'/include/processCookie.php';
require_once dirname(__FILE__).'/include/connectDB.php';
if(!$LOGIN_DATA['user_id']) die('Please log in.');

if(!$this_city) $this_city = new city($_REQUEST['city_id']);
if(!$can_access) {
	$can_access = $this_city->getValue('user_'.$LOGIN_DATA['user_id'].'_access');
	if($can_access !== true && intval($can_access) > time()) $can_access_time = ceil(($can_access - time())/60);
	$can_access = ($can_access === true) || (intval($can_access) > time());
}//end if ! can_access
if(!$can_access) die('You cannot view this city.');

if(!$server) $server = new server($_REQUEST['server_id']);

      $buildings = mysql_query("SELECT building_id,name,description,cost FROM buildings WHERE server_id=".$server->getID(),$db) or die(mysql_query());
      while($building = mysql_fetch_assoc($buildings)) {
         $building_list .= "\t\t\t".'<li>';
         $building_list .= htmlentities($building['name']);
         $building_list .= ' ('.intval($this_city->getValue($building['building_id'])).')';
         if($building['description'])
            $building_list .= ' - '.htmlentities($building['description']);
         $building_list .= '</li>'."\n";
         $building_options .= "\t\t\t".'<option value="'.$building['building_id'].'">'.htmlentities($building['name']).' ('.$building['cost'].' Gold)</option>'."\n";
      }//end while building

echo '<h3>Buildings</h3>';
echo '<ul>';
echo $building_list;
echo '</ul>';

?>
