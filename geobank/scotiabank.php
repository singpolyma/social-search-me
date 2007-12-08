<?php

header('Content-type: text/plain');

function bank_scotiabank($province,$city) {

	$rtrn = array();//return data in this

	//get crap from bank
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

var_dump(bank_scotiabank('BC', 'Richmond'));

?>
