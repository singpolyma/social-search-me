<?php

/*
    cibc-pc.php - CIBC and PC Financial web scraper for ABM Locator

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

function bank_cibc_pc($province,$city,$bank) {

	$infonow_urls = array();
	$infonow_urls['cibc'] = array();
	$infonow_urls['cibc']['form'] = 'http://cibc.via.infonow.net/locator/inter/?LOC=en_CA';
	$infonow_urls['cibc']['domain'] = 'http://cibc.via.infonow.net';
	$infonow_urls['cibc']['refer'] = 'http://cibc.via.infonow.net/locator/inter/AddressResultsDisplayAction.do;';
	$infonow_urls['pc'] = array();
	$infonow_urls['pc']['form'] = 'http://amicus.via.infonow.net/locator/abm/?LOC=en_CA';
	$infonow_urls['pc']['domain'] = 'http://amicus.via.infonow.net';
	$infonow_urls['pc']['refer'] = 'http://amicus.via.infonow.net/locator/abm/ResultsDisplayAction.do;';

	$rtrn = array();//return data in this

	$ch = curl_init($infonow_urls[$bank]['form']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
	$result = curl_exec($ch);
	curl_close($ch);

	preg_match('/<form name="searchForm" method="GET" action="(.*?)"/', $result, $url);
	$url = $url[1];
	preg_match('/jsessionid=.*/', $url, $sessid);
	$sessid = $sessid[0];
	$url = $infonow_urls[$bank]['domain'].$url."?stateProvince=$province&city=$city&country=CAN";
	$result = '';

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	//curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	$result .= curl_exec($ch);
	curl_close($ch);
	
	for($i = 1; $i < 10; $i++) {
		$refer = $infonow_urls[$bank]['refer'].$sessid;
		$ch = curl_init($refer.'?startIndex='.(($i*5)+1));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_REFERER, $refer);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		$result .= curl_exec($ch);
		curl_close($ch);
	}//end for

	preg_match_all('/<A HREF=".*?>(.*?)<\/a><br>/', $result, $names);
	$names = $names[1];
	preg_match_all('/<td valign="top">([^\f]*?)<p><A HREF="/', $result, $meta);
	$meta = $meta[1];
	foreach($meta as $idx => $block) {
		$newABM = array();
		preg_match_all('/<p>([^\f]*?)<\/p>/', $block, $tmp);
		$tmp = $tmp[1]; array_shift($tmp); array_shift($tmp);
		$newABM['name'] = $names[$idx];
		$newABM['address'] = preg_replace('/<br>\s*/', ' ', $tmp[0]);
		if($tmp[1]) {
			$phone = explode('<br>', $tmp[1]);
			$newABM['phone'] = str_replace('Phone: ','',trim($phone[0]));
			$newABM['fax'] = str_replace('Fax: ','',trim($phone[1]));
			$newABM['phone2'] = str_replace('Toll-Free: ','',trim($phone[2]));
		}//end if tmp
		if($tmp[2]) $newABM['transit_no'] = trim(str_replace('<span class="emphasizedText">Transit Number:</span><br>','',$tmp[2]));
		$rtrn[] = $newABM;
	}//end foreach meta

	return $rtrn;//return the data

}//bank_cibc_pc

?>
