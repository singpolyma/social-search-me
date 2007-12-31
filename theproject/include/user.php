<?php

	require_once dirname(__FILE__).'/server.php';
	require_once dirname(__FILE__).'/city.php';

	class user {
		protected $userid;
		protected $nickname;
		protected $email;
		protected $openids = array();
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
				$this->$record['key'] = $record['value'];
			}//end while record
			if($this->gold === NULL && $this->city_count === NULL) $this->setupNewUser();
			$data = mysql_query("SELECT city_id FROM server_cities WHERE server_id=".$this->server->getID()." AND user_id=$this->userid ORDER BY city_id DESC") or die(mysql_error());
			while($city = mysql_fetch_assoc($data)) {
				$this->cities[] = new city($city['city_id'],$this->server,$this);
			}//end while city 
			$data = mysql_query("SELECT openid FROM openids WHERE user_id=$this->userid") or die(mysql_error());
			while($openid = mysql_fetch_assoc($data)) {
				$this->openids[] = $openid['openid'];
			}//end while openid
		}//end constructor
		
		function setupNewUser() {
			global $db;
			require_once dirname(__FILE__).'/connectDB.php';
			mysql_query("INSERT INTO server_data (server_id,user_id,`key`,value) VALUES (".$this->server->getID().",$this->userid,'gold',".$this->server->getInitialGold().")",$db) or die(mysql_error());
			$this->gold = $this->server->getInitialGold();
			mysql_query("INSERT INTO server_data (server_id,user_id,`key`,value) VALUES (".$this->server->getID().",$this->userid,'city_count',0)",$db) or die(mysql_error());
			mysql_query("INSERT INTO server_data (server_id,user_id,`key`,value) VALUES (".$this->server->getID().",$this->userid,'last_online',0)",$db) or die(mysql_error());
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
			$dailyGold = 10;
			foreach($this->getValue('cities') as $city)
				$dailyGold += ($city->getValue('population')/10)*7;
			return $dailyGold; 
		}//end function calculateDailyGold
		
		function dailyGold() {
			$gold = intval($this->getValue('gold'));
			$new_gold = $gold + $this->calculateDailyGold();
			$this->setValue('gold',$new_gold);
		}//end function dailyGold

		function calculateScore() {
			$score = 0;
			$score += ($this->gold + $this->calculateDailyGold())/17;//17 gold or income, one point
			foreach($this->cities as $city) {
				$score += $city->getValue('population');//1 population, one point
				foreach($city->getKeys() as $key) {//10 units, one point
					$key2 = explode('_',$key);
					if($key2[0] != 'unit' || !is_numeric($key2[1])) continue;
					$score += $city->getValue($key)/10;
				}//end foreach keys
			}//end foreach cities
			return $score;
		}//end function calculateScore

		function online_icon() {
         if(time()-$this->getValue('last_online') < 60*2)
            echo ' <img src="/images/status_online.png" alt="[online]" />';
         elseif(time()-$this->getValue('last_online') < 60*10)
            echo ' <img src="/images/status_away.png" alt="[online]" />';
         else
            echo ' <img src="/images/status_offline.png" alt="[online]" />';
		}//end function online_icon

	}//end class user
?>
