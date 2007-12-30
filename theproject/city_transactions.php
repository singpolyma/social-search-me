<?php

require_once dirname(__FILE__).'/include/city.php';
require_once dirname(__FILE__).'/include/processCookie.php';
require_once dirname(__FILE__).'/include/cron.php';
if(!$this_city) $this_city = new city($_REQUEST['city_id']);
if(!$LOGIN_DATA['user_id'] || $LOGIN_DATA['user_id'] != $this_city->getValue('user')->getValue('userid')) die('Please log in to the correct account.');

if(!$server) $server = new server($_REQUEST['server_id']);

      $units = mysql_query("SELECT unit_id,name,cost FROM units WHERE server_id=".$server->getID(),$db) or die(mysql_query());
      while($unit = mysql_fetch_assoc($units)) {
         $transactions = mysql_query("SELECT unit_count,eta FROM server_unit_transaction WHERE server_id=".$server->getID()." AND unit_id=".$unit['unit_id']." AND user_id=".$this_city->getValue('user')->getValue('userid')." AND destination=".$this_city->getValue('id')." ORDER BY eta DESC",$db) or die(mysql_query());
         while($transaction = mysql_fetch_assoc($transactions)) {
            $time_left = round(($transaction['eta']-time())/60,2);
            if($tie_left < 0) $time_left = 0;
            $unit_transaction_list .= "\t\t\t".'<li>'.htmlentities($unit['name']).' ('.$transaction['unit_count'].') in '.$time_left.' minutes</li>'."\n";
         }//end while unit
      }//end while unit

      $buildings = mysql_query("SELECT building_id,name,cost FROM buildings WHERE server_id=".$server->getID(),$db) or die(mysql_query());
      while($building = mysql_fetch_assoc($buildings)) {
         $transactions = mysql_query("SELECT eta FROM server_building_transaction WHERE server_id=".$server->getID()." AND building_id=".$building['building_id']." AND user_id=".$this_city->getValue('user')->getValue('userid')." AND city_id=".$this_city->getValue('id')." ORDER BY eta DESC",$db) or die(mysql_query());
         while($transaction = mysql_fetch_assoc($transactions)) {
            $time_left = round(($transaction['eta']-time())/60,2);
            if($tie_left < 0) $time_left = 0;
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
