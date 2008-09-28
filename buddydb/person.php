<?php

header('Content-Type: text/html;charset=utf-8');
require('db.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
		<title>Search the Social Web!</title>
		<link rel="stylesheet" type="text/css" href="main.css" />
		<link rel="shortcut icon" href="img/user_green_magnify.png" type="image/png" />
		<style type="text/css">
			#profile {
				float: left;
			}
			#contacts {
				float: right;
			}
			#photos {
				float:left;
				margin-top: -0.5em;
				margin-right: 1em;
			}
			#photos img {
				display: block;
				margin-bottom: 1em;
				clear: left;
			}
			.fn {
				display: inline;
			}
			#profiles {
				margin-left: 80px;
			}
			#communicate {
			/*	clear: left;*/
			}
			img {
				border-width: 0px;
			}
			ul, li {
				list-style-type: none;
			}
			#contacts ul, #contacts li {
				padding-left: 0px;
			}
		</style>
	</head>


	<body>
	<?php

	require('header.php');

	ob_flush();
	flush();

	if($_GET['id']) {
		$person_id = mysql_real_escape_string($_GET['id'],$db);
	} else {
		require('normalize_url.php');
		$url = mysql_real_escape_string(normalize_url($_GET['url']),$db);
		if(!isset($_GET['nofetch'])) shell_exec("ruby fetch_profile.rb \"$url\"");
		$person_id = @mysql_fetch_assoc(mysql_query("SELECT person_id FROM urls WHERE url='$url'",$db));
		$person_id = $person_id['person_id'];
	}//end if id

	if(!isset($_GET['contacts'])) :

	$person = mysql_fetch_assoc(mysql_query("SELECT * FROM people WHERE person_id=$person_id",$db));

	echo "\t\t".'<div id="profile" class="vcard">'."\n";

	$photos = mysql_query("SELECT value FROM fields WHERE type='photo' AND person_id=$person_id",$db);
	if(mysql_num_rows($photos)) {
		echo "\t\t\t<p id=\"photos\">";
		while($photo = mysql_fetch_assoc($photos)) {
			echo ' <img class="photo" src="'.htmlspecialchars($photo['value']).'" alt="Photo" style="max-width:100px;" /> ';
		}
		echo "</p>\n";
	}
	
	echo "\t\t\t<h1 class=\"fn\">".htmlspecialchars($person['fn'])."</h1>\n";

	ob_flush();
	flush();
	
	$url = mysql_fetch_assoc(mysql_query("SELECT url FROM urls WHERE verified=1 AND person_id=$person_id ORDER BY LENGTH(url) LIMIT 1",$db));
	echo ' <script type="text/javascript" src="http://singpolyma.net/diso-contact-add.php?fn='.urlencode($person['fn']).'&amp;url='.urlencode($url['url']).'&amp;label=Add+Me&amp;image=http%3A%2F%2Fscrape.singpolyma.net%2Fprofile%2Fimg%2Fadd-me.png"></script> ';
	
	echo "\t\t\t".' <p class="n"> ( <span class="given-name">'.htmlspecialchars($person['given-name']).'</span>'
	                            . ' <span class="additional-name">'.htmlspecialchars($person['additional-name']).'</span>'
	                            . ' <span class="family-name">'.htmlspecialchars($person['family-name']).'</span> ) </p>'."\n";

	$nicknames = mysql_query("SELECT value FROM fields WHERE type='nickname' AND person_id=$person_id",$db);
	if(mysql_num_rows($nicknames)) {
		echo "\t\t\t<p>Nicknames: ";
		$nn = array();
		while($nickname = mysql_fetch_assoc($nicknames)) {
			$nn[] = htmlspecialchars($nickname['value']);
		}
		echo implode(', ',$nn);
		echo "</p>\n";
	}

	echo "\t\t\t<p>";
	if($person['bday']) { echo 'Birthday: <span class="bday">'.date('Y-m-d',$person['bday']).'</span>'; }
	if($person['tz']) { echo ' Timezone: <span class="tz">'.$person['tz'].'</span>'; }
	echo "</p>\n";

	$communicate = array();
	$follow = array();
	$urls = mysql_query("SELECT url FROM urls WHERE verified=1 AND person_id=$person_id ORDER BY LENGTH(url)",$db);
	echo "\t\t\t<h2>Profiles</h2>\n\t\t\t<ul id=\"profiles\">";
	while($url = mysql_fetch_assoc($urls)) {
		if(preg_match('/twitter\.com\/([^\/]*?)(\/.*)?$/',$url['url'],$match)) {
			if($communicate['Twitter@'.htmlspecialchars($match[1])]) continue;
			$communicate['Twitter@'.htmlspecialchars($match[1])] = array(
				'url' => htmlspecialchars($url['url']),
				'fn' => '@'.htmlspecialchars($match[1]),
				'logo' => 'img/twitter.png',
				'org' => 'Twitter'
			);
		} elseif(preg_match('/pownce\.com\/([^\/]*?)(\/.*)?$/',$url['url'],$match)) {
			if($communicate['Pownce!'.htmlspecialchars($match[1])]) continue;
			$communicate['Pownce!'.htmlspecialchars($match[1])] = array(
				'url' => htmlspecialchars($url['url']),
				'fn' => '!'.htmlspecialchars($match[1]),
				'logo' => 'img/pownce.png',
				'org' => 'Pownce'
			);
		} elseif(preg_match('/identi\.ca\/([^\/]*?)(\/.*)?$/',$url['url'],$match)) {
			if($communicate['identi.ca@'.htmlspecialchars($match[1])]) continue;
			$communicate['identi.ca@'.htmlspecialchars($match[1])] = array(
				'url' => htmlspecialchars($url['url']),
				'fn' => '@'.htmlspecialchars($match[1]),
				'logo' => 'img/identica.png',
				'org' => 'identi.ca'
			);
		} elseif(preg_match('/ma\.gnolia\.com\/people\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($communicate['ma.gnolia'.htmlspecialchars($match[1])]) continue;
			$communicate['ma.gnolia'.htmlspecialchars($match[1])] = array(
				'url' => htmlspecialchars($url['url']),
				'fn' => htmlspecialchars($match[1]),
				'logo' => 'img/magnolia.png',
				'org' => 'ma.gnolia'
			);
		} elseif(preg_match('/flickr\.com\/(photos|people)\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($communicate['Flickr'.htmlspecialchars($match[2])]) continue;
			$communicate['Flickr'.htmlspecialchars($match[2])] = array(
				'url' => htmlspecialchars($url['url']),
				'fn' => htmlspecialchars($match[2]),
				'logo' => 'img/flickr.png',
				'org' => 'Flickr'
			);
		} elseif(preg_match('/digg\.com\/users\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($follow['Digg'.htmlspecialchars($match[1])]) continue;
			$follow['Digg'.htmlspecialchars($match[1])] = array(
				'url' => htmlspecialchars($url['url']),
				'fn' => htmlspecialchars($match[1]),
				'logo' => 'img/digg.png',
				'org' => 'Digg'
			);
		} elseif(preg_match('/last\.fm\/user\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($follow['last.fm'.htmlspecialchars($match[1])]) continue;
			$follow['last.fm'.htmlspecialchars($match[1])] = array(
				'url' => htmlspecialchars($url['url']),
				'fn' => htmlspecialchars($match[1]),
				'logo' => 'img/lastfm.png',
				'org' => 'last.fm'
			);
		} elseif(preg_match('/friendfeed\.com\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($follow['FriendFeed'.htmlspecialchars($match[1])]) continue;
			$follow['FriendFeed'.htmlspecialchars($match[1])] = array(
				'url' => htmlspecialchars($url['url']),
				'fn' => htmlspecialchars($match[1]),
				'logo' => 'img/friendfeed.png',
				'org' => 'FriendFeed'
			);
		} elseif(preg_match('/awriterz\.org\/p\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($follow['Amateur Writerz'.htmlspecialchars($match[1])]) continue;
			$follow['Amateur Writerz'.htmlspecialchars($match[1])] = array(
				'url' => htmlspecialchars($url['url']),
				'fn' => htmlspecialchars($match[1]),
				'logo' => 'img/awriterz.ico',
				'org' => 'Amateur Writerz'
			);
		} elseif(preg_match('/dopplr\.com\/traveller\/([^\/]*?)\/?$/',$url['url'],$match)) {
			if($follow['Dopplr'.htmlspecialchars($match[1])]) continue;
			$follow['Dopplr'.htmlspecialchars($match[1])] = array(
				'url' => htmlspecialchars($url['url']),
				'fn' => htmlspecialchars($match[1]),
				'logo' => 'img/dopplr.png',
				'org' => 'Dopplr'
			);
		} else {
			echo "\t\t\t\t".'<li><a class="url" rel="me" href="'.htmlspecialchars($url['url']).'">'.htmlspecialchars(preg_replace('/^www\./','',preg_replace('/^http:\/\//','',$url['url'])))."</a></li>\n";
		}
	}//end while url = fetch urls
	echo "\t\t\t</ul>\n";

	$emails = mysql_query("SELECT value FROM fields WHERE type='email' AND person_id=$person_id",$db);
	if(count($communicate) || mysql_num_rows($emails))
		echo "\t\t\t<h3>Communicate and Share</h3>\n";
	if(count($communicate)) {
		echo "\t\t\t<ul id=\"communicate\">\n";
		foreach($communicate as $url) {
			echo "\t\t\t\t<li><img src=\"{$url['logo']}\" alt=\"{$url['org']}:\" /> <a class=\"url\" rel=\"me\" href=\"{$url['url']}\">{$url['fn']}</a></li>";
		}//end foreach communicate
		echo "\t\t\t</ul>\n";
	}//end if communicate
	
	$ims = mysql_query("SELECT url FROM urls WHERE verified=2 AND person_id=$person_id ORDER BY LENGTH(url)",$db);
	if(mysql_num_rows($ims)) {
		echo "\t\t\t<h4>Instant Messaging</h4>\n";
		echo "\t\t\t<ul>\n";
		while($im = mysql_fetch_assoc($ims)) {
			$t = explode(':',$im['url']);
			$protocol = $t[0];
			array_shift($t);
			$fn = preg_split('/[=\?]/',implode(':',$t));
			$fn = array_pop($fn);
			echo "\t\t\t\t<li>";
			echo '<img src="img/'.htmlspecialchars($protocol).'.png" alt="'.htmlspecialchars($protocol).':" /> <a class="url im" href="'.htmlspecialchars($im['url']).'">'.htmlspecialchars($fn).'</a>';
			echo "</li>\n";
		}
		echo "\t\t\t</ul>\n";
	}
	
	if(mysql_num_rows($emails)) {
		echo "\t\t\t<p>Email: ";
		$nn = array();
		while($email = mysql_fetch_assoc($emails)) {
			$nn[] = '<a class="email" href="mailto:'.htmlspecialchars($email['value']).'">'.htmlspecialchars($email['value']).'</a>';
		}
		echo implode(', ',$nn);
		echo "</p>\n";
	}

	if(count($follow)) {
		echo "<h3>Follow</h3>\t\t\t<ul>\n";
		foreach($follow as $url) {
			echo "\t\t\t\t<li><img src=\"{$url['logo']}\" alt=\"{$url['org']}:\" /> <a class=\"url\" rel=\"me\" href=\"{$url['url']}\">{$url['fn']}</a></li>";
		}//end foreach follow
		echo "\t\t\t</ul>\n";
	}//end if follow

	echo "\t\t</div>\n";

	ob_flush();
	flush();

	endif; //!contacts

	if(!isset($_GET['nocontacts'])) :

	echo '<div id="contacts">';
	$urls = mysql_query("SELECT people.person_id,people.fn,contacts.url FROM contacts,urls,people WHERE contacts.person_id=$person_id AND urls.url=contacts.url AND people.person_id=urls.person_id ORDER BY people.fn",$db);
	echo "\t\t<h2>Contacts</h2>\n\t\t<ul>";
	$done = array();
	while($url = mysql_fetch_assoc($urls)) {
		if(!$url['fn']) continue;
		if(in_array($url['person_id'],$done)) continue;
		$done[] = $url['person_id'];
		$fields = mysql_query('SELECT * FROM fields WHERE person_id='.$url['person_id']);
		while($field = mysql_fetch_assoc($fields)) {
			if(!$url['photo'] && $field['type'] == 'photo') $url['photo'] = $field['value'];
			if($_GET['contacts'] == 'email' && !$url['email'] && $field['type'] == 'email') $url['email'] = $field['value'];
		}
		if($_GET['contacts'] == 'email' && !$url['email']) continue;
		echo '<li class="vcard">';
		echo '<a class="url uid" href="'.htmlspecialchars($url['url']).'">';
		if($url['photo']) echo '<img src="'.htmlspecialchars($url['photo']).'" class="photo" alt="" style="max-width:1.5em;" />';
		echo '</a> ';
		echo '<a class="fn url" href="/profile/person.php?id='.htmlspecialchars($url['person_id']).'">'.htmlspecialchars($url['fn']).'</a>';
		if($_GET['contacts'] == 'email') echo ' (<a href="mailto:'.htmlspecialchars($url['email']).'" class="email">'.htmlspecialchars($url['email']).'</a>)';
		echo '</li>';
	}
	echo "\t\t</ul>\n</div>";

	endif;//!nocontacts
	
	?>
	</body>
</html>
