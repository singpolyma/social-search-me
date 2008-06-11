<?php

$time = time();
if(file_get_contents('transaction_lock') > $time-30) die('LOCK');

file_put_contents('transaction_lock',time());

require_once dirname(__FILE__).'/include/connectDB.php';
require_once dirname(__FILE__).'/include/city.php';
require_once dirname(__FILE__).'/include/user.php';
require_once dirname(__FILE__).'/include/unit_functions.php';
require_once dirname(__FILE__).'/include/twitter.php';

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

/* TWITTER */
	$xml = get_tweets();
	$first = true;
	foreach($xml->xpath('//direct_message') as $message) {
//		if($first) file_put_contents('last_twitter', $message->id.'');
		$first = false;
		$user = mysql_query("SELECT user_id, last_server FROM users WHERE twitter='$message->sender_screen_name' LIMIT 1",$db) or die(mysql_error());
		$user = mysql_fetch_assoc($user);
		if(!$user || !$user['user_id']) continue;
		preg_match('/([\s]+on[\s+])?server[\s]+(.*)$/i', $message->text, $server);
		$message->text = preg_replace('/([\s]+on[\s+])?server[\s]+.*$/i', '', $message->text);
		if($server[2]) {
			$server = new server($server[2]);
			mysql_query("UPDATE users SET last_server={$server->getID()} WHERE user_id={$user['user_id']} LIMIT 1",$db) or die(mysql_error());
		} else
			$server = new server($user['last_server']);
		$user = new user($user['user_id'], $server);
		preg_match('/^([^\s]+)[\s]*(.*)$/', $message->text, $data);
		$verb = $data[1];
		$data = $data[2];
		$reply = '';
		switch($verb) {
			case 'list':
			case 'show':
			case 'cities':
				$data = 'cities';
			case 'near':
				$mod = 'near';
			case 'city':
			case 'ls':
					$data = trim(preg_replace('/^city[\s]+/i', '', $data));
					$data = trim(preg_replace('/^near[\s]+/i', '', $data));
					if(!$data || $data == 'cities') {
						$reply = 'On '.$server->getName().' your cities : ';
						foreach($user->getValue('cities') as $city) {
							$reply .= $city->getValue('name').'@'.$city->getValue('id').' ';
						}
					} else if($mod == 'near') {
						foreach($user->getValue('cities') as $city) {
							if($city->getValue('id') == $data || @preg_match('/^'.$data.'$/i',$city->getValue('name'))) {
								$close_upper_bound = intval($city->getValue('id'))+30000;
		               	$dbcities = mysql_query("SELECT city_id FROM server_cities WHERE server_id=".$server->getID()." AND city_id < $close_upper_bound ORDER BY city_id DESC LIMIT 10",$db) or die(mysql_error());
								$reply = 'On '.$server->getName().' near '.$city->getValue('name').'@'.$city->getValue('id').' : ';
								while($icity = mysql_fetch_assoc($dbcities)) {
									$cityo = new city($icity['city_id']);
									$reply .= $cityo->getValue('name').'@'.$cityo->getValue('id').' ';
								}
								break;
							}
						}

					} else {
						foreach($user->getValue('cities') as $city) {
							if($city->getValue('id') == $data || @preg_match('/^'.$data.'$/i',$city->getValue('name'))) {
								$reply = $city->getValue('name').'@'.$city->getValue('id').' Population: '.$city->getValue('population').' / Defense: '.($city->getValue('defense')+1).' / Units: '.$city->unit_count();
								break;
							}
						}//end foreach
					}
				break;
			case 'attack':
			case 'move':
				preg_match('/^([^\s]+)[\s]+from[\s]+([^\s]+)[\s]+with[\s]+([\d]+)[\s]+([^\s]+)$/', trim($data), $data);
var_dump($data);
				if(!count($data)) $reply = 'I do not understand your troop movement request.';
				else {
	            foreach($user->getValue('cities') as $city) {
   	            if($city->getValue('id') == $data || @preg_match('/^'.$data[2].'$/i',$city->getValue('name'))) {
//							$message = $city->initiate_transaction(UNITID, intval($data[3]), intval(DESTID));
							break;
						}
					}//end foreach
				}
				break;
			default:
				$reply = 'I do not understand "'.$verb.' '.$data.'"';
		}//end switch
		if($reply) {
			echo send_tweet($message->sender_id, $reply);
		}//end if reply
	}//end

/* /TWITTER */

file_put_contents('transaction_lock',0);

?>DONE
