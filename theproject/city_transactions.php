<?php

require_once dirname(__FILE__).'/include/city.php';
require_once dirname(__FILE__).'/include/processCookie.php';
require_once dirname(__FILE__).'/include/cron.php';
if(!$LOGIN_DATA['user_id']) die('<head><title>Please Log in</title></head><body><h2>Please log in.</h2></body></html>');
if(!$this_city) $this_city = new city($_REQUEST['city_id']);

$is_owner = ($LOGIN_DATA['user_id'] == $this_city->getValue('user')->getValue('userid'));
$can_edit = $this_city->getValue('user_'.$LOGIN_DATA['user_id'].'_edit');
$can_edit = ($can_edit === true) || (intval($can_edit) > time());
if($can_edit !== true && intval($can_edit) > time()) $can_edit_time = ceil(($can_edit - time())/60);
$can_access = $this_city->getValue('user_'.$LOGIN_DATA['user_id'].'_access');
if($can_access !== true && intval($can_access) > time()) $can_access_time = ceil(($can_access - time())/60);
$can_access = ($can_access === true) || (intval($can_access) > time());
if(!$can_access) die('<head><title>Cannot View City</title></head><body><h2>You cannot view this city.</h2></body></html>');


if(!$server) $server = new server($_REQUEST['server_id']);

      $units = mysql_query("SELECT unit_id,name,cost FROM units WHERE server_id=".$server->getID(),$db) or die(mysql_query());
      while($unit = mysql_fetch_assoc($units)) {
         $transactions = mysql_query("SELECT unit_count,eta FROM server_unit_transaction WHERE server_id=".$server->getID()." AND unit_id=".$unit['unit_id']." AND user_id=".$this_city->getValue('user')->getValue('userid')." AND destination=".$this_city->getValue('id')." ORDER BY eta DESC",$db) or die(mysql_query());
         while($transaction = mysql_fetch_assoc($transactions)) {
            $time_left = round(($transaction['eta']-time())/60,2);
            if($time_left < 0) $time_left = 0;
            $unit_transaction_list .= "\t\t\t".'<li>'.htmlentities($unit['name']).' ('.$transaction['unit_count'].') in '.$time_left.' minutes</li>'."\n";
         }//end while unit
      }//end while unit

      $buildings = mysql_query("SELECT building_id,name,cost FROM buildings WHERE server_id=".$server->getID(),$db) or die(mysql_query());
      while($building = mysql_fetch_assoc($buildings)) {
         $transactions = mysql_query("SELECT eta FROM server_building_transaction WHERE server_id=".$server->getID()." AND building_id=".$building['building_id']." AND user_id=".$this_city->getValue('user')->getValue('userid')." AND city_id=".$this_city->getValue('id')." ORDER BY eta DESC",$db) or die(mysql_query());
         while($transaction = mysql_fetch_assoc($transactions)) {
            $time_left = round(($transaction['eta']-time())/60,2);
            if($time_left < 0) $time_left = 0;
            $building_transaction_list .= "\t\t\t".'<li>'.htmlentities($building['name']).' in '.$time_left.' minutes</li>'."\n";
         }//end while unit
      }//end while building
?>
      <h4>Inbound Units</h4>
      <ol>
      <?php echo $unit_transaction_list; ?>
      </ol>
      <h4>Buildings in Progress</h4>
      <ol>
      <?php echo $building_transaction_list; ?>
      </ol>
