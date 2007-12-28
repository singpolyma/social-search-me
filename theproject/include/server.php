<?php
	class server {
		protected $name;
		protected $id;
		protected $previous_day;
		protected $day_length;
		protected $city_cost;
		protected $initial_gold;
		protected $initial_city_count;
		protected $initial_city_population;

		function __construct($name) {
			global $db;
			require_once dirname(__FILE__).'/connectDB.php';
			if(!$name) die('Need to pass server constructor a valid server name in server.php');
			if(!is_numeric($name)) {
				$data = mysql_query("SELECT server_id,server_name,initial_gold,initial_city_count,initial_city_population,city_cost FROM servers WHERE server_name='".mysql_real_escape_string($name,$db)."' LIMIT 1",$db) or die(mysql_error());
			} else {
				$data = mysql_query("SELECT server_id,server_name,previous_day,day_length,initial_gold,initial_city_count,initial_city_population,city_cost FROM servers WHERE server_id=".mysql_real_escape_string($name,$db)." LIMIT 1",$db) or die(mysql_error());
			}//end if-else is_int name
			$data = mysql_fetch_assoc($data);
			if(!$data || !$data['server_id']) die('Need to pass server constructor a valid server name in server.php');
			$this->id = $data['server_id'];
			$this->name = $data['server_name'];
			$this->day_length = $data['day_length'];
			$this->previous_day = $data['previous_day'];
			$this->city_cost = $data['city_cost'];
			$this->initial_gold = $data['initial_gold'];
			$this->initial_city_count = $data['initial_city_count'];
			$this->initial_city_population = $data['initial_city_population'];
		}//end consturctor

		function getName() {return $this->name;}
		function getID() {return $this->id;}
		function getDayLength() {return $this->day_length;}
		function getPreviousDay() {return $this->previous_day;}
		function getCityCost() {return $this->city_cost;}
		function getInitialGold() {return $this->initial_gold;}
		function getInitialCityCount() {return $this->initial_city_count;}
		function getInitialCityPopulation() {return $this->initial_city_population;}

		function reset() {
			global $db;
			require_once dirname(__FILE__).'/connectDB.php';
			mysql_query("UPDATE servers SET previous_week=".time().", previous_day=".time()."  WHERE server_id=$this->id",$db) or die(mysql_error());
			mysql_query("DELETE FROM server_data WHERE server_id=$this->id",$db) or die(mysql_error());
			mysql_query("DELETE FROM server_cities WHERE server_id=$this->id",$db) or die(mysql_error());
			mysql_query("DELETE FROM server_cities_data WHERE server_id=$this->id",$db) or die(mysql_error());
			mysql_query("DELETE FROM server_unit_transaction WHERE server_id=$this->id",$db) or die(mysql_error());
			mysql_query("DELETE FROM server_building_transaction WHERE server_id=$this->id",$db) or die(mysql_error());
		}//end function reset

	}//end class user
?>
