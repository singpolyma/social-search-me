<?php
//Licensed under the GPL

function get_url($url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$page = curl_exec($ch);
	curl_close($ch);
	return $page;
}//end funnction get_url

if( !function_exists( 'normalize_uri' ) ) {
  function normalize_uri ($url) {
        $url = trim( $url );
        
        $parts = parse_url( $url );
        $scheme = isset( $parts['scheme'] ) ? $parts['scheme'] : null;   
   
        if( !$scheme )
        {
            $url = 'http://' . $url;
            $parts = parse_url( $url );
        }

        $path = isset( $parts['path'] ) ? $parts['path'] : null;

        if( !$path )
            $url .= '/';

        return $url;
  }
}//end if

function xfn2db($userid,$url) {
	global $db;
	$xfn = array();

	$page = get_url($url);
	@$xml = simplexml_load_string($page);
	if($xml) {
		$xfn += $xml->xpath("//*[contains(concat(' ',normalize-space(@rel),' '),' friend ')]");
		$xfn += $xml->xpath("//*[contains(concat(' ',normalize-space(@rel),' '),' contact ')]");
		$xfn += $xml->xpath("//*[contains(concat(' ',normalize-space(@rel),' '),' kin ')]");
		$relme = $xml->xpath("//*[contains(concat(' ',normalize-space(@rel),' '),' me ')]");
	}//end if xml
	if($relme) {
		foreach($relme as $tag) {
			if(substr($tag['href'],0,4) != 'http') {
				$domain = explode('/',$url);
				$domain = $domain[2];
				if(substr($tag['href'],0,1) == '/')
					$tag['href'] = 'http://'.$domain.$tag['href'];
				else
					$tag['href'] = dirname($url).'/'.$tag['href'];
			}//end if not http
			if($tag['href'] != $url) {
				$apage = get_url($tag['href']);
				@$xml = simplexml_load_string($apage);
				if($xml) {
					$xfn += $xml->xpath("//*[contains(concat(' ',normalize-space(@rel),' '),' friend ')]");
					$xfn += $xml->xpath("//*[contains(concat(' ',normalize-space(@rel),' '),' contact ')]");
					$xfn += $xml->xpath("//*[contains(concat(' ',normalize-space(@rel),' '),' kin ')]");
				}//end if xml
			}//end if !=
		}//end foreach relme
	}//end if relme
	require_once dirname(__FILE__).'/connectDB.php';
	foreach($xfn as $tag) {
		if(substr($tag['href'],0,4) != 'http') {
			$domain = explode('/',$url);
			$domain = $domain[2];
			if(substr($tag['href'],0,1) == '/')
				$tag['href'] = 'http://'.$domain.$tag['href'];
			else
				$tag['href'] = dirname($url).'/'.$tag['href'];
		}//end if not http
		$dbuser = mysql_query("SELECT user_id FROM openids WHERE openid='".mysql_real_escape_string(normalize_uri($tag['href']),$db)."' LIMIT 1",$db) or die(mysql_error());
		$dbuser = mysql_fetch_assoc($dbuser);
		if($dbuser['user_id']) {
			$exists = mysql_query("SELECT friend_id FROM friends WHERE user_id=$userid AND friend_id=".$dbuser['user_id'],$db) or die(mysql_error());
			if(!mysql_fetch_assoc($exists))
				mysql_query("INSERT INTO friends (user_id,friend_id) VALUES ($userid,".$dbuser['user_id'].")",$db) or die(mysql_error());
		}//end if dbuser
	}//end foreach xfn

}//end function get_xfn

?>
