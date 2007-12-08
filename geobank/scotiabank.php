<?php

/*
    scotiabank.php - Scotiabank web scraper for ABM Locator

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

function bank_scotiabank($province,$city) {

	$rtrn = array();//return data in this

	$ch = curl_init('http://locator.scotiabank.com/ScotiaExt/Container.asp');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "SearchType=submitLocation&rSearhType=ABM&cmbdealer=2&city=$city&cmbstate=$province");
	$result = curl_exec($ch);
	curl_close($ch);

var_dump($result);

	preg_match_all('/size="-2" (COLOR|color)="(BLACK|black)">(.*? '.strtoupper($city).' .*?)<\/font>.*?<\/td>/', $result, $address);
//	$address = $address[1];
	var_dump($address);

	return $rtrn;//return the data

}//bank_scotiabank

?>
