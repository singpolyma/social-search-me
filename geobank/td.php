<?php

/*
    td.php - TD Canada Trust web scraper for ABM Locator

    Copyright (C) 2007  Stephen Paul Weber

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function bank_td($province,$city) {

	$rtrn = array();//return data in this

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

?>
