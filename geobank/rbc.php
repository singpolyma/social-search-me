<?php

/*
    rbc.php - RBC web scraper for ABM Locator

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

function bank_rbc($province,$city) {

	$rtrn = array();

	$ch = curl_init('http://maps.rbc.com/index.en.asp');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "province=$province&city=$city&location_type=ABM");
	$result = curl_exec($ch);
	curl_close($ch);

	preg_match_all('/var point = new GLatLng\(parseFloat\((.*?)\),parseFloat\((.*?)\)\);/',$result,$coords);
	preg_match_all('/map.addOverlay\(createMarker\(point, \'(.*?)\',\'.*?\'\)\);/',$result,$names);
	$ewcoords = $coords[1]; array_shift($ewcoords);
	$nscoords = $coords[2]; array_shift($nscoords);
	$names = $names[1];

	foreach($names as $idx => $name) {
		$tmp = array();
		$tmp['ewcoords'] = $ewcoords[$idx];
		$tmp['nscoords'] = $nscoords[$idx];
		$name = explode('<br>',$names[$idx]);
		$tmp['name'] = ucwords(strtolower($name[0]));
		$name2 = explode(',',$name[2]);
		$tmp['address'] = ucwords(strtolower($name[1])).', '.ucwords(strtolower($name2[0])).', '.str_replace('   ',' ',$name2[1]);
		$rtrn[] = $tmp;
	}

	return $rtrn;

}//bank_rbc

?>
