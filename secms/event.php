<?php

class Event {

	protected $properties;
	protected $db;

	static function get_events() {

		$events = array();

		require dirname(__FILE__).'/settings.php';
		$db = mysql_connect($db_host, $db_user, $db_password);//connect to database
		mysql_select_db($db_name, $db);

		$result = mysql_query("SELECT * FROM events WHERE dtstart > ".time()." OR rrule > 0 ORDER BY dtstart ASC, event_id DESC",$db) or die(mysql_error());
		while($event = mysql_fetch_assoc($result)) {
			$obj = new Event($event);
			if($obj->repeats()) $obj->save();
			$events[] = $obj;
		}//end while event

		mysql_close($db);

		return $events;

	}//end function get_events

	function __construct($array) {
		require dirname(__FILE__).'/settings.php';
		$this->properties = $array;
		if(!is_numeric($this->properties['dtstart']))
			$this->properties['dtstart'] = strtotime($this->properties['dtstart'].' UTC') + $this->properties['timezone']*60*60*-1;
		if($this->properties['rrule'] && !is_numeric($this->properties['rrule'])) {
			switch($this->properties['rrule']) {
				case 'daily':
					$this->properties['rrule'] = 60*60*24; break;
				case 'weekly':
					$this->properties['rrule'] = 60*60*24*7; break;
			}//end switch
		}//end if rrule
		if($this->properties['dtstart'] < time() && $this->properties['rrule'] && is_numeric($this->properties['rrule'])) {
			$this->properties['dtstart'] += $this->properties['rrule'];
		}//end if rrule go
		if($this->properties['duration'] && !is_numeric($this->properties['duration'])) {
			$this->properties['duration'] = strtotime($this->properties['duration'])-time();
			$this->properties['dtend'] = $this->properties['dtstart'] + $this->properties['duration'];
		}//end if duration
		unset($this->properties['timezone']);
		$this->db = mysql_connect($db_host, $db_user, $db_password);//connect to database
		mysql_select_db($db_name, $this->db);
		register_shutdown_function('mysql_close', $this->db);
	}//end constructor

	function repeats() {
		return $this->properties['rrule'] && is_numeric($this->properties['rrule']);
	}//end function repeats

	function save() {
		if(!$this->properties['event_id']) {
			$query = 'INSERT INTO events (';
			foreach(array_keys($this->properties) as $k)
				$query .= mysql_real_escape_string(stripslashes($k), $this->db).', ';
			$query = substr($query, 0, strlen($query)-2);
			$query .= ') VALUES (';
			foreach($this->properties as $v)
				$query .= (is_numeric($v) ? '' : '\'').mysql_real_escape_string(stripslashes($v), $this->db).(is_numeric($v) ? '' : '\'').', ';
			$query = substr($query, 0, strlen($query)-2);
			$query .= ')';
			mysql_query($query,$this->db) or die(mysql_error());
			$this->properties['event_id'] = mysql_insert_id();
		} else {
			$query = 'UPDATE events SET ';
			foreach($this->properties as $k => $v) {
				$query .= mysql_real_escape_string(stripslashes($k), $this->db).'='.
					(is_numeric($v) ? '' : '\'').mysql_real_escape_string(stripslashes($v), $this->db).(is_numeric($v) ? '' : '\'').', ';
			}//end foreach
			$query = substr($query, 0, strlen($query)-2);
			$query .= ' WHERE event_id='.$this->properties['event_id'];
			mysql_query($query,$this->db) or die(mysql_error());
		}//end if if-else ! event_id
	}//end function save

	function __toString() {
		$rtrn = '';
		$rtrn .= '<h3 class="summary entry-title">';
		if($this->properties['url']) $rtrn .= '<a class="url" rel="bookmark" href="'.htmlspecialchars($this->properties['url']).'">';
		$rtrn .= htmlspecialchars($this->properties['summary']);
		if($this->properties['url']) $rtrn .= '</a>';
		$rtrn .= '</h3>';
		$rtrn .= '<div>Starts: <abbr class="dtstart" title="'.date('c',$this->properties['dtstart']).'">'.date('Y-m-d H:i T [U]',$this->properties['dtstart']).'</abbr>';
		if($this->properties['duration']) $rtrn .= ' / Lasts: <abbr class="dtend duration" title="'.date('c',$this->properties['dtend']).'">'.($this->properties['duration']/(60*60)).' hours</abbr>';
		if($this->properties['rrule']) $rtrn .= ' / Happens every: <span class="rrule">'.($this->properties['rrule']/(60*60*24)).' days</span>';
		$rtrn .= '</div>';
		if($this->properties['location']) $rtrn .= '<div>Location: <span class="location">'.htmlspecialchars($this->properties['location']).'</span></div>';
		if($this->properties['description']) {
			$description = $this->properties['description'];
			if(strip_tags($description) == $description)
				$description = htmlspecialchars($description);
			$rtrn .= '<p class="entry-content description">'.$description.'</p>';
		}//end if description
		return $rtrn;
	}//end function __toString

}//end class Event

?>
