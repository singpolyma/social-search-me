<?php

	require_once dirname(__FILE__).'/server.php';
	require_once dirname(__FILE__).'/city.php';

	class user {
		protected $userid;
		protected $nickname;
		protected $email;
		protected $photo;
		protected $gold;
		protected $city_count;
		protected $cities = array();
		protected $server;

		function __construct($userid,$server='main') {
			global $db;
			if(is_string($server)) $server = new server($server);
			$this->server = $server;
			if(!$userid) die('Need to pass user constructor a valid userid in user.php');
			$this->userid = $userid;
			require_once dirname(__FILE__).'/connectDB.php';
			$data = mysql_query("SELECT nickname, email, photo FROM users WHERE user_id=$userid LIMIT 1") or die(mysql_error());
			if(!$data || !count($data)) die('Need to pass user constructor a valid userid in user.php');
			$data = mysql_fetch_assoc($data);
			$this->nickname = $data['nickname'];
			$this->email = $data['email'];
			$this->photo = $data['photo'];
			$data = mysql_query("SELECT `key`, value FROM server_data WHERE server_id=".$this->server->getID()." AND user_id=$userid") or die(mysql_error());
			while($record = mysql_fetch_assoc($data)) {//loop over all returned records
				if($record['key'] == 'gold') $this->gold = $record['value'];
				if($record['key'] == 'city_count') $this->city_count = $record['value'];
			}//end while record
			if($this->gold === NULL || $this->city_count === NULL) $this->setupNewUser();
			$data = mysql_query("SELECT city_id FROM server_cities WHERE server_id=".$this->server->getID()." AND user_id=$this->userid") or die(mysql_error());
			while($city = mysql_fetch_assoc($data)) {
				$this->cities[] = new city($city['city_id'],$this->server,$this);
			}//end while city 
		}//end constructor
		
		function setupNewUser() {
			global $db;
			require_once dirname(__FILE__).'/connectDB.php';
			mysql_query("INSERT INTO server_data (server_id,user_id,`key`,value) VALUES (".$this->server->getID().",$this->userid,'gold',".$this->server->getInitialGold().")",$db) or die(mysql_error());
			$this->gold = $this->server->getInitialGold();
			mysql_query("INSERT INTO server_data (server_id,user_id,`key`,value) VALUES (".$this->server->getID().",$this->userid,'city_count',".$this->server->getInitialCityCount().")",$db) or die(mysql_error());
			$this->city_count = $this->server->getInitialCityCount();
			for($i = 0; $i < $this->server->getInitialCityCount(); $i++) {
				city::build_city($this, $this->server, false);
			}//end for $i < $this->server->getInitialCities()
		}//end funciton setupNewUser
		
		function getValue($key) {return $this->$key;}
		
		function setValue($key,$value) {
			global $db;
			require_once dirname(__FILE__).'/connectDB.php';
			mysql_query("UPDATE server_data SET value='".mysql_real_escape_string($value,$db)."' WHERE server_id=".$this->server->getID()." AND `key`='".mysql_real_escape_string($key,$db)."' AND user_id=$this->userid") or die(mysql_error());
			$this->$key = $value;
		}//end function setValue
		
		function calculateDailyGold() {
			$cities = intval($this->getValue('city_count'));
			$dailyGold = 10;
			foreach($this->getValue('cities') as $city)
				$dailyGold += ($city->getValue('population')/10)*5;
			return $dailyGold; 
		}//end function calculateDailyGold
		
		function dailyGold() {
			$gold = intval($this->getValue('gold'));
			$new_gold = $gold + $this->calculateDailyGold();
			$this->setValue('gold',$new_gold);
		}//end function dailyGold

		function calculateScore() {
			$score = 0;
			$score += $this->gold/2;//two gold, one point
			foreach($this->cities as $city) {
				$score += $city->getValue('population')/5;//5 population, one point
				foreach($city->getKeys() as $key) {//10 units, one point
					$key2 = explode('_',$key);
					if($key2[0] != 'unit' || !is_numeric($key2[1])) continue;
					$score += $city->getValue($key)/10;
				}//end foreach keys
			}//end foreach cities
			return $score;
		}//end function calculateScore

	}//end class user
?>
