<?php

	require_once dirname(__FILE__).'/server.php';
	require_once dirname(__FILE__).'/user.php';

	class city {
		protected $server;
		protected $user;
		protected $id;

		function __construct($cityid,$server='',$user='') {
			global $db;
			require_once dirname(__FILE__).'/connectDB.php';
			if(!$cityid) die('Need to pass city constructor a valid city id in city.php');
			$this->id = $cityid;
			$data = mysql_query("SELECT server_id,user_id FROM server_cities WHERE city_id=$cityid LIMIT 1") or die(mysql_error());
			$data = mysql_fetch_assoc($data);
			if(!$data) die('Need to pass city constructor a valid city id in city.php');
			$this->server = $server ? $server : new server($data['server_id']);
			$this->user = $user ? $user : new user($data['user_id'], $this->server);
			$data = mysql_query("SELECT `key`,value FROM server_cities_data WHERE city_id=$cityid") or die(mysql_error());
			while($pair = mysql_fetch_assoc($data)) {//get all key=>value pairs for this type of building
				$this->$pair['key'] = $pair['value'];
			}//end while pair
		}//end constructor

		function getValue($key) {return $this->$key;}
		function getKeys() {return array_keys(get_object_vars($this));}

		function setValue($key,$value,$additive=false) {
			global $db;
			require_once dirname(__FILE__).'/connectDB.php';
			$exists = mysql_query("SELECT value FROM server_cities_data WHERE server_id=".$this->server->getID()." AND city_id=$this->id AND `key`='$key' ",$db) or die(mysql_error());
			$exists = mysql_fetch_assoc($exists);
			if($exists['value'] && $additive) $value += $exists['value'];
			if($exists['value'])
				mysql_query("UPDATE server_cities_data SET value='".mysql_real_escape_string($value,$db)."' WHERE server_id=".$this->server->getID()." AND `key`='".mysql_real_escape_string($key,$db)."' AND city_id=$this->id",$db) or die(mysql_error());
			else
				mysql_query("INSERT INTO server_cities_data (server_id, city_id, `key`, value) VALUES (".$this->server->getID().", $this->id, '".mysql_real_escape_string($key,$db)."', '".mysql_real_escape_string($value,$db)."')",$db) or die(mysql_error());
			$this->$key = $value;
			return $value;
		}//end function setValue

		function initiate_transaction($unitid, $count, $destination) {
			global $db;
			require_once dirname(__FILE__).'/connectDB.php';
			if($this->getValue('unit_'.$unitid)-$count < 0) return 'Tried to move more units than were present.';
			$this->setValue('unit_'.$unitid, -1*$count, true);
			$unit = mysql_query("SELECT value FROM units_data WHERE unit_id=$unitid AND `key`='speed' LIMIT 1") or die(mysql_error());
			$unit = mysql_fetch_assoc($unit);
			$speed = $unit['value'] ? $unit['value'] : 1;
			$eta = time() + (int)(($unit['cost']*130)/$speed) - $this->population + abs($this->id - $destination)/999;
			mysql_query("INSERT INTO server_unit_transaction (server_id, unit_id, destination, user_id, unit_count, eta) VALUES (".$this->server->getID().", $unitid, $destination, ".$this->user->getValue('userid').",$count,$eta)",$db) or die(mysql_error());
			return false;
		}//end function initiate_transaction

		function create_units($unitid, $count) {
			global $db;
			if($count > $this->getValue('population')) return 'Cannot train more units than you have population!';
			require_once dirname(__FILE__).'/connectDB.php';
			$unit = mysql_query("SELECT cost FROM units WHERE unit_id=$unitid AND server_id=".$this->server->getID()." LIMIT 1") or die(mysql_error());
			$unit = mysql_fetch_assoc($unit);
			$cost_of_units = $unit['cost']*$count;
			if($this->user->getValue('gold')-$cost_of_units < 0) return 'Not enough gold.';
			$this->user->setValue('gold', $this->user->getValue('gold')-$cost_of_units);
			if(!$this->unit_production) $this->unit_production = 1;
			$eta = time() + (int)(($unit['cost']*130)/$this->unit_production) + $this->population;
			mysql_query("INSERT INTO server_unit_transaction (server_id, unit_id, destination, user_id, unit_count, eta) VALUES (".$this->server->getID().", $unitid, $this->id, ".$this->user->getValue('userid').",$count,$eta)",$db) or die(mysql_error());
			return false;
		}//end function create_units

		function build($buildingid) {
			global $db;
			require_once dirname(__FILE__).'/connectDB.php';
			$building = mysql_query("SELECT cost FROM buildings WHERE building_id=$buildingid AND server_id=".$this->server->getID()." LIMIT 1") or die(mysql_error());
			$building = mysql_fetch_assoc($building);
			if($this->user->getValue('gold')-$building['cost'] < 0) return 'Not enough gold.';
			$this->user->setValue('gold', $this->user->getValue('gold')-$building['cost']);
			$eta = time() + (int)($building['cost']*130) - $this->population;
			mysql_query("INSERT INTO server_building_transaction (city_id, building_id, server_id, user_id, eta) VALUES ($this->id,$buildingid,".$this->server->getID().",".$this->user->getValue('userid').",$eta)") or die(mysql_error());
			return false;//return message on error, false on success
		}//end function build

		function finish_build($buildingid) {
			global $db;
			require_once dirname(__FILE__).'/connectDB.php';
			$this->setValue($buildingid, 1, true);//add building to city
			$data = mysql_query("SELECT `key`,value FROM buildings_data WHERE building_id=$buildingid",$db) or die(mysql_error());
			while($pair = mysql_fetch_assoc($data)) {//get all key=>value pairs for this type of building
				$this->setValue($pair['key'], $pair['value'], true);
			}//end while pair
		}//end function finish_build

		static function build_city($user,$server,$dogold=true) {
			global $db;
			require_once dirname(__FILE__).'/connectDB.php';
			$exists = array(1);
         while($exists && count($exists)) {
            $cityid = rand(1,999999);
            $exists = mysql_query("SELECT city_id FROM server_cities WHERE city_id=".$cityid." LIMIT 1",$db) or die(mysql_error());
            $exists = mysql_fetch_assoc($exists);
         }//end generate random id
			if($dogold) {
				if($user->getValue('gold')-$server->getCityCost() < 0) return 'Not enough gold.';
				$user->setValue('gold', $user->getValue('gold')-$server->getCityCost());
			}//end if dogold
         mysql_query("INSERT INTO server_cities (city_id,server_id,user_id) VALUES ($cityid,".$server->getID().",".$user->getValue('userid').")",$db) or die(mysql_error());
         mysql_query("INSERT INTO server_cities_data (server_id,city_id,`key`,value) VALUES (".$server->getID().",$cityid,'population','".$server->getInitialCityPopulation()."')",$db) or die(mysql_error());
			return false;
		}//end function build_city

	}//end class city

?>
