<?php

$time = time();
if(file_get_contents('transaction_lock') > $time-30) die('LOCK');

file_put_contents('transaction_lock',time());

require_once dirname(__FILE__).'/include/connectDB.php';
require_once dirname(__FILE__).'/include/city.php';
require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/unit_functions.php';

$transactions = mysql_query("SELECT server_id,destination,unit_id,unit_count,user_id FROM server_unit_transaction WHERE eta < $time") or die(mysql_error());
while($transaction = mysql_fetch_assoc($transactions)) {
	$city = new city($transaction['destination']);
	if($city->getValue('user')->getValue('userid') == $transaction['user_id']) {//if this is a friendly transfer
		$city->setValue('unit_'.$transaction['unit_id'],$transaction['unit_count'],true);
	} else {//battle
		$unit_data = mysql_query("SELECT function FROM units WHERE unit_id=".$transaction['unit_id'],$db) or die(mysql_error());	
		$unit_data = mysql_fetch_assoc($unit_data);
		unit_functions::$unit_data['function']($transaction,$city,$db);
	}//end if-else user == user
}//end while transaction
mysql_query("DELETE FROM server_unit_transaction WHERE eta < $time") or die(mysql_error());

$transactions = mysql_query("SELECT city_id,building_id FROM server_building_transaction WHERE eta < $time") or die(mysql_error());
while($transaction = mysql_fetch_assoc($transactions)) {
	$city = new city($transaction['city_id']);
	$city->finish_build($transaction['building_id']);
}//end while transaction
mysql_query("DELETE FROM server_building_transaction WHERE eta < $time") or die(mysql_error());

mysql_query("DELETE FROM messages WHERE time < ".ceil($time-60*3)) or die(mysql_error());//delete all messages more than 3 minutes old

file_put_contents('transaction_lock',0);

?>DONE
