<?php

header('Content-type: text/plain');

function bank_td($province,$city) {

	$rtrn = array();//return data in this

	//get crap from bank
	$ch = curl_init('https://tdbank.infonow.net/bin/findNow?CLIENT_ID=TD_BRANCH_CAN');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
	$result = curl_exec($ch);
	curl_close($ch);

	preg_match('/<FORM NAME="form1" action="(.*?)"/', $result, $url);
	$url = $url[1];
	preg_match('/LIST_LIST_KEY=(.*?)&/', $url, $sessid);
	$sessid = $sessid[1];
	$url = 'https://tdbank.infonow.net'.$url;
	$result = '';

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_REFERER, 'https://tdbank.infonow.net/bin/findNow?CLIENT_ID=TD_BRANCH_CAN');
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "CITY31=$city&TYPE=ABM&SEARCH_TYPE=CITY_ONLY&PAGE=ResultTemp.html&MAP_MODE=MAP&NEW_SEARCH=FALSE&BROWSER=&BROWSER_VER=&HTTP_UA_STRING=&LIST_LIST_KEY=$sessid&STREET11=&CITY11=&STATE_PROV11=&STREET21=&STREET22=&CITY21=&STATE_PROV21=&FTR0_POSTAL_CODE=&TRANSIT=");
	$result .= curl_exec($ch);
	curl_close($ch);

	$tmpresult = $result;
	while(true) {
		preg_match('/<A HREF="(.*?)" class=pageutility>Next<\/A>/', $tmpresult, $nextlink);
		$nextlink = $nextlink[1];
		if(!$nextlink) break;
		$nextlink = 'https://tdbank.infonow.net'.$nextlink;
		$ch = curl_init($nextlink);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$tmpresult = curl_exec($ch);
		curl_close($ch);
		$result .= $tmpresult;
	}//end while
	
	preg_match_all('/<TD width="100%".*?>([^\f]*?)<\/TD>/', $result, $fragments);
	$fragments = $fragments[1];

	foreach($fragments as $fragment) {
		$newABM = array();
		$tmp = explode(' at ', trim($fragment));
		$tmp2 = explode('<BR>', $tmp[1]);
		$tmp2[1] = explode(', ', $tmp2[1]);
		$newABM['name'] = $tmp[0];
		$newABM['address'] = ucwords(strtolower($tmp2[0].$tmp2[1][0])).', '.$tmp2[1][1];
		$rtrn[] = $newABM;
	}//foreach fragments

	return $rtrn;//return the data

}//bank_td

var_dump(bank_td('BC', 'Richmond'));

?>
