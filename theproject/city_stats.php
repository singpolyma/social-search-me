<?php

require_once dirname(__FILE__).'/include/city.php';
require_once dirname(__FILE__).'/include/processCookie.php';
require_once dirname(__FILE__).'/include/connectDB.php';
if(!$LOGIN_DATA['user_id']) die('Please log in.');

if(!$this_city) $this_city = new city($_REQUEST['city_id']);
if(!$can_access) {
	$can_access = $this_city->getValue('user_'.$LOGIN_DATA['user_id'].'_access');
	if($can_access !== true && intval($can_access) > time()) $can_access_time = ceil(($can_access - time())/60);
	$can_access = ($can_access === true) || (intval($can_access) > time());
}//end if ! can_access
if(!$can_edit) {
	$can_edit = $this_city->getValue('user_'.$LOGIN_DATA['user_id'].'_edit');
	$can_edit = ($can_edit === true) || (intval($can_edit) > time());
	if($can_edit !== true && intval($can_edit) > time()) $can_edit_time = ceil(($can_edit - time())/60);
}//if ! can edit

if(!$server) $server = new server($_REQUEST['server_id']);

      echo '<h3>';
      if($this_city->getValue('name'))
         echo htmlentities($this_city->getValue('name')).' ';
      echo '(Location: '.$this_city->getValue('id').')</h3>';
      if(!$can_access) echo '<p class="vcard">Owned by: <a href="/server/'.$this_city->getValue('server')->getID().'/user/'.$this_city->getValue('user')->getValue('userid').'" class="fn url nickname">'.$this_city->getValue('user')->getValue('nickname').'</a></p>';
      if($can_access_time > 0) echo '<b>Can access this page for '.$can_access_time.' more minutes.</b>';
      if($can_edit_time > 0) echo '<b>Can edit this page for '.$can_edit_time.' more minutes.</b>';
      if($can_access) {
	      foreach($this_city->getKeys() as $key) {
		 $key2 = explode('_',$key);
		 if($key == 'id' || $key == 'name' || $key == 'server' || $key == 'user' || is_numeric($key) || $key2[0] == 'user' || ($key2[0] == 'unit' && is_numeric($key2[1]))) continue;
		 $label = ucwords(str_replace('_',' ',$key));
		 echo '<div>'.$label.': '.$this_city->getValue($key).'</div>';
	      }//end foreach key
	}//end if can_access

?>
