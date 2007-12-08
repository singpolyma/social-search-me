<?php

header('Content-type: text/plain');

function bank_bmo($province,$city) {

	$rtrn = array();//return data in this

	//get crap from bank
	$ch = curl_init('http://www4.bmo.com/bmo/tools/ABMLocator/step2');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "Province1=$province&CityTown=$city&SearchFor=1&SearchBy=2&start=1&end=50&startPG=1&endPG=0&pageNAV=0&Order=1");
	$result = curl_exec($ch);
	curl_close($ch);

	//regex out the data fragments
	preg_match('/<!-- First Record -->([^\f]*?)<!-- Column widths for record set -->/', $result, $blob);
	$fragments = explode('<!-- First Record -->',$blob[0]); array_shift($fragments);

	foreach($fragments as $fragment) {
		$newABM = array();
		preg_match('/<B>(.*?)<\/B>/', $fragment, $newABM['name']);
		$newABM['name'] = ucwords(strtolower($newABM['name'][1]));
		preg_match_all('/<td valign=top >([^\f]*?)<\/td>/', $fragment, $tmp);
		$newABM['address'] = explode(', ',str_replace('<BR>',' ',$tmp[1][1]));
		$newABM['address'] = ucwords(strtolower($newABM['address'][0])).', '.$newABM['address'][1];
		$newABM['telephone'] = explode('  <BR>Branch Transit # ',trim($tmp[1][2]));
		$newABM['transit_no'] = $newABM['telephone'][1];
		$newABM['telephone'] = $newABM['transit_no'] ? str_replace('Tel:','',$newABM['telephone'][0]) : NULL;
		$rtrn[] = $newABM;
	}//foreach fragments

	return $rtrn;//return the data

}//bank_bmo

var_dump(bank_bmo('BC', 'Richmond'));

?>
