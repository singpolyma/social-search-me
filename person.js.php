<?php

header('Content-Type: text/javascript;charset=utf-8');
require('db.php');

if($_GET['callback'])
	echo $_GET['callback'].'(';

	echo '{ ';

	if($_GET['id']) {
		$person_id = mysql_real_escape_string($_GET['id'],$db);
	} else {
		require('normalize_url.php');
		$url = mysql_real_escape_string(normalize_url($_GET['url']),$db);
		$person_id = @mysql_fetch_assoc(mysql_query("SELECT person_id FROM urls WHERE url='$url'",$db));
		$person_id = $person_id['person_id'];
	}//end if id

	if($person_id) {

	$person = mysql_fetch_assoc(mysql_query("SELECT ".(isset($_GET['compact'])?'fn':'*')." FROM people WHERE person_id=$person_id",$db));

	$photos = mysql_query("SELECT value FROM fields WHERE type='photo' AND person_id=$person_id".(isset($_GET['compact'])?' LIMIT 1':''),$db);
	if(mysql_num_rows($photos)) {
		echo '"photo":[';
		while($photo = mysql_fetch_assoc($photos)) {
			echo '"'.addslashes($photo['value']).'",';
		}
		echo "],";
	}
	
	echo '"fn": "'.addslashes($person['fn']).'", ';

	if(!isset($_GET['compact'])) {

		echo '"n": { "given-name": "'.addslashes($person['given-name']).'",'
											 . ' "additional-name": "'.addslashes($person['additional-name']).'",'
											 . ' "family-name": "'.addslashes($person['family-name']).'" }, ';

		$nicknames = mysql_query("SELECT value FROM fields WHERE type='nickname' AND person_id=$person_id",$db);
		if(mysql_num_rows($nicknames)) {
			echo '"nickname": [';
			$nn = array();
			while($nickname = mysql_fetch_assoc($nicknames)) {
				$nn[] = addslashes($nickname['value']);
			}
			echo '"'.implode('", "',$nn).'"';
			echo '], ';
		}

		if($person['bday']) { echo '"bday": '.$person['bday'].', '; }
		if($person['tz']) { echo '"tz": "'.$person['tz'].'", '; }

	}

	$communicate = array();
	$follow = array();
	$urls = mysql_query("SELECT url FROM urls WHERE verified=1 AND person_id=$person_id ORDER BY LENGTH(url)".(isset($_GET['compact'])?' LIMIT 1':''), $db);
	echo '"url": [';
	while($url = mysql_fetch_assoc($urls)) {
		if(preg_match('/twitter\.com\/([^\/]*?)(\/.*)?$/',$url['url'],$match)) {
			if($communicate['Twitter@'.addslashes($match[1])]) continue;
			$communicate['Twitter@'.addslashes($match[1])] = array(
				'url' => addslashes($url['url']),
				'fn' => '@'.addslashes($match[1]),
				'logo' => 'http://scrape.singpolyma.net/profile/img/twitter.png',
				'org' => 'Twitter'
			);
		} elseif(preg_match('/pownce\.com\/([^\/]*?)(\/.*)?$/',$url['url'],$match)) {
			if($communicate['Pownce!'.addslashes($match[1])]) continue;
			$communicate['Pownce!'.addslashes($match[1])] = array(
				'url' => addslashes($url['url']),
				'fn' => '!'.addslashes($match[1]),
				'logo' => 'http://scrape.singpolyma.net/profile/img/pownce.png',
				'org' => 'Pownce'
			);
		} elseif(preg_match('/identi\.ca\/([^\/]*?)(\/.*)?$/',$url['url'],$match)) {
			if($communicate['identi.ca@'.addslashes($match[1])]) continue;
			$communicate['identi.ca@'.addslashes($match[1])] = array(
				'url' => addslashes($url['url']),
				'fn' => '@'.addslashes($match[1]),
				'logo' => 'http://scrape.singpolyma.net/profile/img/identica.png',
				'org' => 'identi.ca'
			);
		} elseif(preg_match('/ma\.gnolia\.com\/people\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($communicate['ma.gnolia'.addslashes($match[1])]) continue;
			$communicate['ma.gnolia'.addslashes($match[1])] = array(
				'url' => addslashes($url['url']),
				'fn' => addslashes($match[1]),
				'logo' => 'http://scrape.singpolyma.net/profile/img/magnolia.png',
				'org' => 'ma.gnolia'
			);
		} elseif(preg_match('/flickr\.com\/(photos|people)\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($communicate['Flickr'.addslashes($match[2])]) continue;
			$communicate['Flickr'.addslashes($match[2])] = array(
				'url' => addslashes($url['url']),
				'fn' => addslashes($match[2]),
				'logo' => 'http://scrape.singpolyma.net/profile/img/flickr.png',
				'org' => 'Flickr'
			);
		} elseif(preg_match('/digg\.com\/users\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($follow['Digg'.addslashes($match[1])]) continue;
			$follow['Digg'.addslashes($match[1])] = array(
				'url' => addslashes($url['url']),
				'fn' => addslashes($match[1]),
				'logo' => 'http://scrape.singpolyma.net/profile/img/digg.png',
				'org' => 'Digg'
			);
		} elseif(preg_match('/last\.fm\/user\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($follow['last.fm'.addslashes($match[1])]) continue;
			$follow['last.fm'.addslashes($match[1])] = array(
				'url' => addslashes($url['url']),
				'fn' => addslashes($match[1]),
				'logo' => 'http://scrape.singpolyma.net/profile/img/lastfm.png',
				'org' => 'last.fm'
			);
		} elseif(preg_match('/friendfeed\.com\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($follow['FriendFeed'.addslashes($match[1])]) continue;
			$follow['FriendFeed'.addslashes($match[1])] = array(
				'url' => addslashes($url['url']),
				'fn' => addslashes($match[1]),
				'logo' => 'http://scrape.singpolyma.net/profile/img/friendfeed.png',
				'org' => 'FriendFeed'
			);
		} elseif(preg_match('/awriterz\.org\/p\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($follow['Amateur Writerz'.addslashes($match[1])]) continue;
			$follow['Amateur Writerz'.addslashes($match[1])] = array(
				'url' => addslashes($url['url']),
				'fn' => addslashes($match[1]),
				'logo' => 'http://scrape.singpolyma.net/profile/img/awriterz.ico',
				'org' => 'Amateur Writerz'
			);
		} elseif(preg_match('/dopplr\.com\/traveller\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($follow['Dopplr'.addslashes($match[1])]) continue;
			$follow['Dopplr'.addslashes($match[1])] = array(
				'url' => addslashes($url['url']),
				'fn' => addslashes($match[1]),
				'logo' => 'http://scrape.singpolyma.net/profile/img/dopplr.png',
				'org' => 'Dopplr'
			);
		} else {
			echo '{ "url": "'.addslashes($url['url']).'" }, ';
		}
	}//end while url = fetch urls

	if(count($communicate)) {
		foreach($communicate as $url) {
			echo '{ "logo": "'.$url['logo'].'", "org": "'.$url['org'].'", "url": "'.$url['url'].'", "fn": "'.$url['fn'].'" }, ';
		}//end foreach communicate
	}//end if communicate

	if(count($follow)) {
		foreach($follow as $url) {
			echo '{ "logo": "'.$url['logo'].'", "org": "'.$url['org'].'", "url": "'.$url['url'].'", "fn": "'.$url['fn'].'" }, ';
		}//end foreach follow
	}//end if follow

	echo '], ';

	if(!isset($_GET['compact'])) {
		$emails = mysql_query("SELECT value FROM fields WHERE type='email' AND person_id=$person_id",$db);
		if(mysql_num_rows($emails)) {
			echo '"email": [';
			$nn = array();
			while($email = mysql_fetch_assoc($emails)) {
				$nn[] = addslashes($email['value']);
			}
			echo '"'.implode('", "',$nn).'"';
			echo '], ';
		}
	}
	
	if(!isset($_GET['compact'])) {
		
		$urls = mysql_query("SELECT people.person_id,people.fn,people.`given-name`,people.`family-name`,contacts.url FROM contacts,urls,people WHERE contacts.person_id=$person_id AND urls.url=contacts.url AND people.person_id=urls.person_id ORDER BY people.fn, people.`given-name`, people.person_id",$db);
		echo '"contacts": [';
		$done = array();
		while($url = mysql_fetch_assoc($urls)) {
			if(!$url['fn']) continue;
			if(in_array($url['person_id'],$done)) continue;
			$done[] = $url['person_id'];
			echo addslashes($url['person_id']).', ';
		}
		echo '] ';

	}

	}//end if person_id

	echo '}';

	if($_GET['callback'])
		echo ')';

	?>
