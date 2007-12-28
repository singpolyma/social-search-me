<?php
//Licensed under the GPL

require_once dirname(__FILE__).'/hkit.class.php';
require_once dirname(__FILE__).'/connectDB.php';

function hcard_import($userid, $url) {
	global $db;

	//GET HCARD
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$page = curl_exec($ch);
	curl_close($ch);

	if(function_exists('tidy_clean_repair'))
		$page = tidy_clear_repair($page);
	$page = str_replace('&nbsp;','&#160;',$page);
	$h = new hKit;
	@$hcard = $h->getByString('hcard', $page);
	if(count($hcard['preferred'])) {
		$phcard = $hcard['preferred'][0];
	} else {
		if($hcard['all']) {
			foreach($hcard['all'] as $card) {
				if($card['uid'] == $userdata->user_url) { $phcard = $card; break; }
				if(!is_array($card['url']) && $card['url'] == $url) { $phcard = $card; break; }
				if(is_array($card['url']) && in_array($url,$card['url'])) { $phcard = $card; break; }
			}//end foreach all
			if(!$phcard) $phcard = $hcard['all'][0];
		}//end if hcard all
	}//end if-else preferred

	$domain = explode('/',$url);
	$domain = $domain[2];
	if(substr($phcard['photo'],0,3) == '://') {
		$photo = explode('/',$phcard['photo']);
		array_shift($photo);
		array_shift($photo);
		array_shift($photo);
		$phcard['photo'] = 'http://'.$domain.'/'.implode('/',$photo);
	}//end busted photo url

	//IMPORT INTO PROFILE
	if($phcard['nickname']) mysql_query("UPDATE users SET nickname='".mysql_real_escape_string($phcard['nickname'],$db)."' WHERE user_id=$userid") or die(mysql_error());
	if($phcard['email']) mysql_query("UPDATE users SET email='".mysql_real_escape_string($phcard['email'],$db)."' WHERE user_id=$userid") or die(mysql_error());
	if($phcard['photo']) mysql_query("UPDATE users SET photo='".mysql_real_escape_string($phcard['photo'],$db)."' WHERE user_id=$userid") or die(mysql_error());
}//end function hcard_import

?>
