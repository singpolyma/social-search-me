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

      $units = mysql_query("SELECT unit_id,name,description,cost FROM units WHERE server_id=".$server->getID(),$db) or die(mysql_query());
      while($unit = mysql_fetch_assoc($units)) {
         $unit_options .= "\t\t\t".'<option value="'.$unit['unit_id'].'">'.htmlentities($unit['name']).' ('.$unit['cost'].' Gold)</option>'."\n";

         $unit_list .= "\t\t\t<li>";
         $unit_list .= htmlentities($unit['name']);
         $unit_list .= ' ('.intval($this_city->getValue('unit_'.$unit['unit_id'])).')';
         if($unit['description'])
            $unit_list .= ' - '.htmlentities($unit['description']);
         $unit_list .= "</li>\n";
      }//end while unit

echo '<h3>Units ('.$this_city->unit_count().')</h3>';
echo '<ul>';
echo $unit_list;
echo '</ul>';

?>
