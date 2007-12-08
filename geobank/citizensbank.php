<?php

/*
    citizensbank.php - Citizens Bank web scraper for ABM Locator

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

function bank_citizensbank($province,$city) {

	$rtrn = array();//return data in this

	$ch = curl_init('https://www.citizensbank.ca/Personal/Products/BankAccounts/HowtoBankwithUs/ATMs/ATMLocations/');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "province=$province&city=$city");
	$result = curl_exec($ch);
	curl_close($ch);

	//regex out the data fragments
	preg_match_all('/<li class="item[^\f]*?<\/li>[^\f]*?<\/li>/', $result, $fragments);
	$fragments = $fragments[0];

	//expat each fragment
	foreach($fragments as $fragment) {
		$newABM = array();//temporary variable for records
		$theParser = xml_parser_create();//actual expat lines
		xml_parse_into_struct($theParser,$fragment,$vals);
		xml_parser_free($theParser);
		$nextvalue = '';//name of the field for the next class=value data
		foreach($vals as $el) {//loop through parsed XML values
			if($el['tag'] == 'H5') $newABM['name'] = $el['value'];//<h5>NAME</h5>
			if(in_array('address',explode(' ',$el['attributes']['CLASS']))) $nextvalue = 'address';//these classes mean the next time we see class=value the data goes in this field
			if(in_array('hours',explode(' ',$el['attributes']['CLASS']))) $nextvalue = 'hours';
			if(in_array('features',explode(' ',$el['attributes']['CLASS']))) $nextvalue = 'features';
			if(in_array('languages',explode(' ',$el['attributes']['CLASS']))) $nextvalue = 'languages';
			if(in_array('value',explode(' ',$el['attributes']['CLASS']))) $newABM[$nextvalue] = $el['value'];//put class=value data into the field name we found before
		}//end foreach vals
		$rtrn[] = $newABM;//insert record into return value array
	}//end foreach fragments

	return $rtrn;//return the data

/*	OLD NICE LESS PORTABLE CODE
	$tidy = new tidy;
	$tidy->parseString($result, array('output-xml' => true, 'doctype' => 'loose', 'add-xml-decl' => true),'utf8');
	$tidy->cleanRepair();

	$doc = new DOMDocument();
	$doc->preserveWhiteSpace = false;
	$doc->loadHTML($tidy->value);

	$xpath = new DOMXPath($doc);
	$data = $xpath->query("//*[contains(@class,'address')]/*[contains(@class,'value')]");

	foreach($data as $node)
		$rtrn[] = array('address' => str_replace("\n",' ',$node->nodeValue));
*/

}//bank_citizensbank

?>
