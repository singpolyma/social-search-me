<?php

header('Content-type: text/plain');

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

var_dump(bank_rbc('ON', 'Waterloo'));

?>
