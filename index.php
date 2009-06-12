<?php

require_once 'bad-behavior-generic.php';

header('Content-Type: application/xhtml+xml;charset=utf-8');
require('db.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
		<title>socialsearchme.com</title>
		<link rel="stylesheet" type="text/css" href="/profile/main.css" />
		<link rel="shortcut icon" href="img/user_green_magnify.png" type="image/png" />
	</head>

	<body>
	<?php

	require('header.php');

	if($_GET['q']) {

		$time = microtime(true);

		function print_results($people, $db, $title='') {
			static $done;
			if(!$done) $done = array();
			if($matches = mysql_num_rows($people)) {
				if($title) echo "\n\t\t<h2>".htmlspecialchars(str_replace('%m',$matches,$title))."</h2>\n";
				echo "\t\t<ul>\n";

				while($person = mysql_fetch_assoc($people)) {
					if(in_array($person['person_id'],$done)) continue;
					$done[] = $person['person_id'];
					echo "\t\t\t".'<li clas="vcard">';

					echo '<a class="url" href="'.$person['url'].'">';

					$photos = mysql_query("SELECT value FROM fields WHERE type='photo' AND person_id={$person['person_id']} LIMIT 1",$db);
					if(mysql_num_rows($photos)) {
						while($photo = mysql_fetch_assoc($photos)) {
							echo ' <img src="'.htmlspecialchars($photo['value']).'" alt="Photo" class="photo" style="max-width:50px;" /> ';
						}
					}

					echo '</a>';

					echo '<a class="fn url" href="/profile/person.php?id='.htmlspecialchars($person['person_id']).'">';
					
					echo htmlspecialchars($person['fn']);

					echo "</a></li>\n";
				}//end while person = fetch people

				echo "\t\t</ul>\n";
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
			$people = mysql_query("SELECT people.person_id,people.fn,contacts.url FROM contacts,urls,people WHERE contacts.person_id=$pov AND contacts.url=urls.url AND urls.person_id=people.person_id AND people.fn LIKE '$nickname%' LIMIT ".intval($_GET['count']-$results),$db) or die(mysql_error());
			$results += print_results($people, $db, 'Matches from Contacts');
		}//end if pov
		
		$people = mysql_query("SELECT people.person_id,fn,url FROM people,urls WHERE `given-name` LIKE '$given_name%' AND `family-name` LIKE '$family_name%' AND `additional-name` LIKE '$additional_name%' AND people.person_id=urls.person_id AND urls.verified=1".($_GET['count'] ? ' LIMIT '.intval($_GET['count']-$results) : ''),$db) or die(mysql_error());
		$results += print_results($people, $db, 'Exact matches');
	
		$people = mysql_query("SELECT fields.person_id,value AS fn,url FROM fields,urls WHERE value LIKE '$nickname%' AND (type='nickname' OR type='email') AND fields.person_id=urls.person_id AND urls.verified=1".($_GET['count'] ? ' LIMIT '.intval($_GET['count']-$results) : ''),$db) or die(mysql_error());
		$results += print_results($people, $db, 'Nickname matches');
		
		$people = mysql_query("SELECT people.person_id,fn,url FROM people,urls WHERE (fn LIKE '$nickname%' OR `family-name` LIKE '$nickname%') AND people.person_id=urls.person_id AND urls.verified=1".($_GET['count'] ? ' LIMIT '.intval($_GET['count']-$results) : ''),$db) or die(mysql_error());
		$results += print_results($people, $db, 'Fuzzy matches');
		
		if(!$results) echo '<p>There were no results for your search.</p>';

		mysql_close($db);

	} else { //display search form
		?>
		<form method="get" action=""><div>
			<h2>Search by name/nickname</h2>
			<input type="text" name="q" />
			<input type="hidden" name="count" value="30" />
			<input type="submit" value="Search" />
		</div></form>
		
		<form method="get" action="person.php"><div>
			<h2>Look up a specific URL</h2>
			<input type="text" name="url" />
			<input type="submit" value="Search" />
		</div></form>
		<?php
	}//end if-else q

	?>

	<p id="footer">
		<?php if($time) echo '<p>'.(microtime(true)-$time).' seconds...</p>'; ?>
		<a href="http://singpolyma.net/2008/08/diso-gets-search/" rel="about">About / Feedback</a>
		| <a href="http://github.com/singpolyma/social-search-me">Source Code</a>
		| <a href="http://singpolyma.net/2008/08/socialsearchmecom-api/">API</a>
	</p>

	</body>
	
</html>
