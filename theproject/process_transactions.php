<?php

require_once dirname(__FILE__).'/include/connectDB.php';
require_once dirname(__FILE__).'/include/city.php';
require_once dirname(__FILE__).'/include/user.php';

$time = time();
$transactions = mysql_query("SELECT destination,unit_id,unit_count,user_id FROM server_unit_transaction WHERE eta < $time") or die(mysql_error());
while($transaction = mysql_fetch_assoc($transactions)) {
	$city = new city($transaction['destination']);
	if($city->getValue('user')->getValue('userid') == $transaction['user_id']) {//if this is a friendly transfer
		$city->setValue('unit_'.$transaction['unit_id'],$transaction['unit_count'],true);
	} else {//battle
		$ranged = 0;
		$melee = 0;
		$defense = $city->getValue('defense') + 1;
		foreach($city->getKeys() as $key) {
			$key2 = explode('_',$key);
			if($key2[0] != 'unit' || !is_numeric($key2[1])) continue;

			$uranged = mysql_query("SELECT value FROM units_data WHERE unit_id=".$key2[1]." AND `key`='ranged_hit'") or die(mysql_error());
			$uranged = mysql_fetch_assoc($uranged);
			$ranged += $uranged['value']*$city->getValue($key);

			$umelee = mysql_query("SELECT value FROM units_data WHERE unit_id=".$key2[1]." AND `key`='melee_hit'") or die(mysql_error());
			$umelee = mysql_fetch_assoc($umelee);
			$melee += $umelee['value']*$city->getValue($key);
			
			$udefense = mysql_query("SELECT value FROM units_data WHERE unit_id=".$key2[1]." AND `key`='defense'") or die(mysql_error());
			$udefense = mysql_fetch_assoc($udefense);
			$defense += $udefense['value']*$city->getValue($key);

		}//end foreach keys

		$random_bound = ($ranged + $melee + $defense)/rand(1,6);
		$randomness = rand($random_bound*-1,$random_bound);
		
		$uranged = mysql_query("SELECT value FROM units_data WHERE unit_id=".$transaction['unit_id']." AND `key`='ranged_hit'") or die(mysql_error());
		$uranged = mysql_fetch_assoc($uranged);
		$uranged = $uranged['value']*$transaction['unit_count'];

		$umelee = mysql_query("SELECT value FROM units_data WHERE unit_id=".$transaction['unit_id']." AND `key`='melee_hit'") or die(mysql_error());
		$umelee = mysql_fetch_assoc($umelee);
		$umelee = $umelee['value']*$transaction['unit_count'];
			
		$udefense = mysql_query("SELECT value FROM units_data WHERE unit_id=".$transaction['unit_id']." AND `key`='defense'") or die(mysql_error());
		$udefense = mysql_fetch_assoc($udefense);
		$udefense = $udefense['value']*$transaction['unit_count'];

		$ranged_attack = $ranged + $randomness - $uranged;
		$melee_attack = $melee + $randomness - $umelee;
		$total_defense = $defense + $randomness - $udefense;
		$attack_results = ($ranged_attack + $melee_attack) / $defense;

		if($attack_results < 0) {//we only have to do something if the attacker made an impact
			foreach($city->getKeys() as $key) {
				$key2 = explode('_',$key);
				if($key2[0] != 'unit' || !is_numeric($key2[1])) continue;
				$attack_results += $city->getValue($key);
				if($attack_results > 0) {//we have destroyed all the units we are going to
					$city->setValue($key, $attack_results);
					break;
				}//end if attack_results > 0
				$city->setValue($key, 0);
			}//end foreach keys
		}//end if attack_results < 0

		if($attack_results < 0) {//after destroying units the attacker still prevails, take city
			mysql_query("UPDATE server_cities SET user_id=".$transaction['user_id']." WHERE city_id=".$transaction['destination'],$db) or die(mysql_error());
			$city->setValue('unit_'.$transaction['unit_id'], $transaction['unit_count']/2);
			$user = new user($transaction['user_id']);
			$user->setValue('city_count', intval($user->getValue('city_count'))+1);
			$city->getValue('user')->setValue('city_count', intval($city->getValue('user')->getValue('city_count'))-1);
		}//end if attack_results < 0

	}//end if-else user == user
}//end while transaction
mysql_query("DELETE FROM server_unit_transaction WHERE eta < $time") or die(mysql_error());

$transactions = mysql_query("SELECT city_id,building_id FROM server_building_transaction WHERE eta < $time") or die(mysql_error());
while($transaction = mysql_fetch_assoc($transactions)) {
	$city = new city($transaction['city_id']);
	$city->finish_build($transaction['building_id']);
}//end while transaction
mysql_query("DELETE FROM server_building_transaction WHERE eta < $time") or die(mysql_error());

?>
