<?php

header('Content-Type: text/javascript;charset=utf-8');
require('db.php');

if($_GET['callback'])
	echo $_GET['callback'].'(';

	echo '{ ';

		function print_results($people, $db, $title) {
			static $done;
			if(!$done) $done = array();
			if($matches = mysql_num_rows($people)) {
				echo '"'.addslashes(preg_replace('/\s/','_',strtolower($title))).'": ';
				echo '[';

				while($person = mysql_fetch_assoc($people)) {
					if(in_array($person['person_id'],$done)) continue;
					$done[] = $person['person_id'];

					echo '{';
					echo '"fn": "'.addslashes($person['fn']).'", ';
					echo '"id": "'.addslashes($person['person_id']).'"';
					echo '}, ';

				}//end while person = fetch people

				echo "], ";
			}//end if-else num_rows people
			return $matches;
		}//end function print_results

		$n = explode(' ',$_GET['q']);
		$nickname = mysql_real_escape_string($_GET['q'],$db);

		$given_name = mysql_real_escape_string(array_shift($n),$db);
		$family_name = mysql_real_escape_string(array_pop($n),$db);
		$additional_name = mysql_real_escape_string(implode(' ',$n),$db);

		$results = 0;

		if($_GET['pov']) {
			require('normalize_url.php');
			$pov = @mysql_fetch_assoc(mysql_query("SELECT person_id FROM urls WHERE url='".mysql_real_escape_string(normalize_url($_GET['pov']),$db)."'"));
			$pov = intval($pov['person_id']);
			$people = mysql_query("SELECT people.person_id,people.fn FROM contacts,urls,people WHERE contacts.person_id=$pov AND contacts.url=urls.url AND urls.person_id=people.person_id AND people.fn LIKE '%$nickname%'",$db) or die(mysql_error());
			$results += print_results($people, $db, 'Matches from Contacts');
		}//end if pov
		
		$people = mysql_query("SELECT person_id,fn FROM people WHERE `given-name` LIKE '%$given_name%' AND `family-name` LIKE '%$family_name%' AND `additional-name` LIKE '%$additional_name%'",$db) or die(mysql_error());
		$results += print_results($people, $db, 'Exact matches');
	
		$people = mysql_query("SELECT person_id,value AS fn FROM fields WHERE value LIKE '%$nickname%' AND (type='nickname' OR type='email')",$db) or die(mysql_error());
		$results += print_results($people, $db, 'Nickname matches');
		
		$people = mysql_query("SELECT person_id,fn FROM people WHERE fn LIKE '%$nickname%'",$db) or die(mysql_error());
		$results += print_results($people, $db, 'Fuzzy matches');

echo '}';

if($_GET['callback'])
	echo ')';
		
?>
