<?php
/*
Plugin Name: Social Networking
Plugin URI: http://wptest.awriterz.org/
Description: Distributed social networking experiment.  Depends on OpenID plugin from http://blog.verselogic.net/projects/wordpress/wordpress-openid-plugin/
Author: Stephen Paul Weber
Author URI: http://singpolyma-tech.blogspot.com/
Version: 0.10
Licence: GPL
*/

$social_netoworking_default_fields = array('display_name','nickname','full_name','user_email','user_url','description','jabber','aim','yim');

function social_networking_get_page_author() {
   if(!$author_name && isset($_GET['author_name'])) $author_name = $_GET['author_name'];
   if($author_name) {//get author info by name
      $curauth = get_userdatabylogin($_GET['author_name']);
   } else {//by id or default
      if($_GET['author']) {//by id
         $curauth = get_userdata(intval($_GET['author']));
      } else {//get default
         global $wpdb;
         $user_id = $wpdb->get_var("SELECT user_id FROM $wpdb->usermeta WHERE meta_key='wp_user_level' AND meta_value=10");
         $curauth = get_userdata($user_id);
      }//end if-else author (by id)
   }//end if author_name
   return $curauth;
}//end function social_networking_get_page_author

function social_networking_ping($id) {
   global $wpdb;
   wp_remote_fopen('http://pingerati.net/ping/'.get_bloginfo('url'));
   $user_ids = $wpdb->get_results("SELECT DISTINCT post_author FROM $wpdb->posts");
   foreach($user_ids as $aid)
      wp_remote_fopen('http://pingerati.net/ping/'.get_bloginfo('url').'?author='.$aid->ID);
   return $id;
}//end function social_networking_ping
add_action('publish_post', 'social_networking_ping');

function social_networking_process_forms() {
   if(isset($_REQUEST['social_networking_profile_public'])) {
      global $user_level;
      get_currentuserinfo();
      if($user_level <  8) die('Nice try, you cheeky monkey!');
      add_option('social_networking_profile_public');
      update_option('social_networking_profile_public',$_REQUEST['social_networking_profile_public']);
   }//handle form submit

   if(isset($_REQUEST['social_networking_profile_private'])) {
      global $user_level;
      get_currentuserinfo();
      if($user_level <  8) die('Nice try, you cheeky monkey!');
      add_option('social_networking_profile_private');
      update_option('social_networking_profile_private',$_REQUEST['social_networking_profile_private']);
   }//handle form submit

   if(isset($_REQUEST['social_networking_messaging'])) {
      global $user_level;
      get_currentuserinfo();
      if($user_level <  8) die('Nice try, you cheeky monkey!');
      add_option('social_networking_messaging');
      update_option('social_networking_messaging',$_REQUEST['social_networking_messaging']);
   }//handle form submit

   if($_REQUEST['subject'] && $_REQUEST['from']) {//handle messages
      if(strlen($_REQUEST['from']) > 100 || strip_tags($_REQUEST['from']) != $_REQUEST['from']) die('<h1>You seem to be a SPAMmer.  I am sorry if this is incorrect.  Your message has been blocked.</h1>');
      $curauth = social_networking_get_page_author();
      switch($_REQUEST['subject']) {
         case 'add_friend':
            wp_mail($curauth->user_email, 'You have been added as a friend', 'The owner of '.$_REQUEST['from'].' has added you as a friend on their webpage.  This means that you can now see their private profile (if they have one and you log in via OpenID).');
            echo '<h1 style="padding:10px;background-color:black;color:white;">Friend notification sent!</h1>';
            break;
         default:
            if(get_settings('social_networking_messaging')) {wp_mail($curauth->user_email, strip_tags($_REQUEST['subject']), strip_tags($_REQUEST['body'])."\n\nFrom the owner of ".$_REQUEST['from']); echo '<h1 style="padding:10px;background-color:black;color:white;">Message sent!</h1>';}
            else die('<h1>Messaging Disabled!</h1>');
      }//end switch
   }//end if subject

   if($_REQUEST['link_url'] && $_REQUEST['link_rel']) {//friend add notifications
      $rel = explode(' ',$_REQUEST['link_rel']);
      $link = explode('?',$_REQUEST['link_url']);
      if($link[1])
         $link = $_REQUEST['link_url'].'&';
      else
         $link = $_REQUEST['link_url'].'?';
      $link .= 'from='.get_bloginfo('url').'&subject=add_friend';
      if(in_array('friend',$rel))
         wp_remote_fopen($link);
   }//end if link_rel

}//end function social_networking_process_forms
add_action('init', 'social_networking_process_forms');

function social_networking_page() {
   global $social_netoworking_default_fields;
   if(!function_exists('is_user_openid') || !class_exists('WordpressOpenIDRegistration')) {
      echo '<br /><h3 style="text-align:center;">OpenID plugin not detected, some plugin features may note work.  Please <a href="http://blog.verselogic.net/projects/wordpress/wordpress-openid-plugin/">install it</a>.</h3>';
   }//end if-else function_exists is_user_openid

   echo '<div class="wrap">';
   echo '<h2>Social Networking Plugin Settings</h2>';

   echo '<h3>Setup Information</h3>';
   echo "<p>The public/private profile information is displayed wherever you put this code: <code>&lt;?php social_networking_profile(); ?&gt;</code>.  It is reccomended to put this on the author archive page, or on the main page/in the sidebar for single-author blogs.  Advanced users can pass a username to this function to specify what user's data to output (instead of having it determined automatically).</p>";
   echo "<p>The link lists in your sidebar can be output with code more in line with this plugin by using this code: <code>&lt;?php social_networking_get_links_list(); ?&gt;</code>. Advanced users should note the additional existance of a social_networking_get_links as well.</p>";

   $social_networking_profile_public = get_settings('social_networking_profile_public') ? get_settings('social_networking_profile_public') : 'display_name,nickname,full_name,user_url,description';
   echo '<form method="post"><h3>Public Profile Fields <a href="#" onclick="document.getElementById(&quot;social_networking_profile_public_help&quot;).style.display = (document.getElementById(&quot;social_networking_profile_public_help&quot;).style.display == &quot;none&quot; ? &quot;block&quot; : &quot;none&quot;);return false;">(?)</a></h3>';
   echo '<div style="display:none;" id="social_networking_profile_public_help">Comma-separated list of fields to be displayed publicly by the social_networking_profile tag.  Comma separated. <br />Possible fields include:<ul><li>'.implode('</li><li>',$social_netoworking_default_fields).'</li></ul> As well as any user-defined fields.</div>';
   echo '<input type="text" style="width:200px;" name="social_networking_profile_public" value="'.htmlentities($social_networking_profile_public).'" /> <input type="submit" value="Update" /></form>';

   $social_networking_profile_private = get_settings('social_networking_profile_private') ? get_settings('social_networking_profile_private') : 'display_name,nickname,full_name,user_url,description,user_email,jabber,aim,yim';
   echo '<form method="post"><h3>Private Profile Fields <a href="#" onclick="document.getElementById(&quot;social_networking_profile_private_help&quot;).style.display = (document.getElementById(&quot;social_networking_profile_private_help&quot;).style.display == &quot;none&quot; ? &quot;block&quot; : &quot;none&quot;);return false;">(?)</a></h3>';
   echo '<div style="display:none;" id="social_networking_profile_private_help">Comma-separated list of fields to be displayed by the social_networking_profile tag <i>to friends and administrators only</i>.  Comma separated. <br />Possible fields include:<ul><li>'.implode('</li><li>',$social_netoworking_default_fields).'</li></ul> As well as any user-defined fields.</div>';
   echo '<input type="text" style="width:200px;" name="social_networking_profile_private" value="'.htmlentities($social_networking_profile_private).'" /> <input type="submit" value="Update" /></form>';

   echo '<br /><br /><form method="post"><span title="Disabling this DOES NOT disable friend notifications, etc.  Only messaging.">Enable social networking messaging</span>: <input type="checkbox" name="social_networking_messaging" '.(get_settings('social_networking_messaging') ? 'checked="checked"' : '').' /> <input type="submit" value="Update" /></form>';

   echo '</div>';
}//end function social_networking_page

function social_networking_tab($s) {
   add_submenu_page('options-general.php', 'Social Networking', 'Social Networking', 1, __FILE__, 'social_networking_page');
   return $s;
}//end function social_networking_tab
add_action('admin_menu', 'social_networking_tab');

function social_networking_getTidy($url) {
   return wp_remote_fopen('http://cgi.w3.org/cgi-bin/tidy?docAddr='.urlencode($url).'&forceXML=on');
}//end function getTidy

$social_networking_normalized_urls = array();
function social_networking_get_normalized_urls($url,$level=0) {
   global $social_networking_normalized_urls;
   $openIDc = new WordpressOpenIDRegistration();
   $page = social_networking_getTidy($url);
   if(is_int($url{strlen($url)-1})) unset($url{strlen($url)-1});
   $social_networking_normalized_urls[] = $openIDc->normalize_username(normalize_url($url));
   $theParser = xml_parser_create();
   xml_parse_into_struct($theParser,$page,$vals);
   xml_parser_free($theParser);
   foreach($vals as $el) {
      if(!in_array('me',explode(' ',strtolower(trim($el['attributes']['REL']))))) continue;
      $el['attributes']['HREF'] = trim($el['attributes']['HREF']);
      if(!$level) social_networking_get_normalized_urls($el['attributes']['HREF'],$level+1);
      if(is_int($el['attributes']['HREF']{strlen($el['attributes']['HREF'])-1})) unset($el['attributes']['HREF']{strlen($el['attributes']['HREF'])-1});
      $tmp[] = $openIDc->normalize_username(normalize_url($el['attributes']['HREF']));
   }//end foreach vals as el
}//end function social_networking_get_normalized_urls

/* PROVIDE AUTHOR DATA FOR AUTHOR PAGE */
function social_networking_profile($author_name='') {

   global $social_netoworking_default_fields,$wpdb,$user_ID,$user_login,$social_networking_normalized_urls;
   $social_networking_normalized_urls = array();

   $curauth = social_networking_get_page_author();

   $social_networking_profile = explode(',',get_option('social_networking_profile_public') ? get_settings('social_networking_profile_public') : 'display_name,nickname,full_name,user_url,description');

   get_currentuserinfo();
   if($user_level > 9 || (function_exists('is_user_openid') && class_exists('WordpressOpenIDRegistration') && is_user_openid())) {//only allow authing on OpenID users
      $tmp = array();
      if($user_level < 10) {
         foreach($wpdb->get_results("SELECT link_url FROM {$wpdb->links} WHERE link_owner={$curauth->ID} AND (link_rel LIKE '%friend%' OR link_rel LIKE '%me%')") as $link) {
            social_networking_get_normalized_urls($link->link_url);
         }//end foreach SQL...
      }//end if user_level < 10
      $userlogin = $user_login;
      if(is_int($userlogin{strlen($userlogin)-1})) unset($userlogin{strlen($userlogin)-1});
      if($user_level > 9 || in_array($user_login,$social_networking_normalized_urls))
         $social_networking_profile = explode(',',get_option('social_networking_profile_private') ? get_settings('social_networking_profile_private') : 'display_name,nickname,full_name,user_url,description,user_email,jabber,aim,yim');
   }//end if openid

   //echo hCard
   echo '<div class="vcard">'."\n";
   if(in_array('display_name',$social_networking_profile))
      echo '   <div class="style-fn">Display Name: <span class="fn">'.htmlentities($curauth->display_name).'</span></div>'."\n";
   if(in_array('nickname',$social_networking_profile))
      echo '   <div class="style-nickname">Nickname: <span class="nickname">'.htmlentities($curauth->nickname).'</span></div>'."\n";
   if(in_array('full_name',$social_networking_profile))
      echo '   <div class="style-n">Full Name: <span class="n"><span class="given-name">'.htmlentities($curauth->first_name).'</span> <span class="family-name">'.htmlentities($curauth->last_name).'</span></span></div>'."\n";
   if($curauth->user_email && in_array('user_email',$social_networking_profile))
      echo '   <div class="style-email">Email: <a class="email" href="mailto:'.htmlentities($curauth->user_email).'">'.htmlentities($curauth->user_email).'</a></div>'."\n";
   if($curauth->user_url && in_array('user_url',$social_networking_profile))
      echo '   <div class="style-url">URL: <a class="url" href="'.htmlentities($curauth->user_url).'">'.htmlentities($curauth->user_url).'</a></div>'."\n";
   if($curauth->description && in_array('description',$social_networking_profile))
      echo '   <div class="style-note">Description: <span class="note">'.htmlentities($curauth->description).'</span></div>'."\n";
   if($curauth->jabber && in_array('jabber',$social_networking_profile))
      echo '   <div class="style-jabber">Jabber: <a class="url" href="xmpp:'.htmlentities($curauth->jabber).'">'.htmlentities($curauth->jabber).'</a></div>'."\n";
   if($curauth->aim && in_array('aim',$social_networking_profile))
      echo '   <div class="style-aim">AIM: <a class="url" href="aim:goim?screenname='.htmlentities($curauth->aim).'">'.htmlentities($curauth->aim).'</a></div>'."\n";
   if($curauth->yim && in_array('yim',$social_networking_profile))
      echo '   <div class="style-yim">Y!IM: <a class="url" href="ymsgr:sendIM?'.htmlentities($curauth->yim).'">'.htmlentities($curauth->yim).'</a></div>'."\n";
   foreach($social_networking_profile as $field) {
      if(in_array($field,$social_netoworking_default_fields)) continue;
      $field = strtolower($field);
      $label = str_replace('_',' ',$field);
      $label{0} = strtoupper($label{0});
      echo '   <div class="style-'.$field.'">'.$label.': <span class="'.$field.'">'.$curauth->{$field}.'</span></div>'."\n";
   }//end foreach
   echo '</div>'."\n";

}//end function social_networking_profile

/* LINK LIST TEMPLATE TAG */
function social_networking_get_links($category = -1,$show_images = true,$order = 'name',$show_description = true,$show_rating = false,$limit = -1,$show_updated = 1) {
   echo str_replace('<a','<a class="url fn"',get_links($category, '   <li class="vcard">','</li>'."\n",'<br />',$show_images,$order,$show_description,$show_rating,$limit,$show_updated,false));
}//end function social_networking_links

function social_networking_get_links_list($order = 'name') {
	global $wpdb;

	$order = strtolower($order);

	// Handle link category sorting
	if (substr($order,0,1) == '_') {
		$direction = ' DESC';
		$order = substr($order,1);
	}

	// if 'name' wasn't specified, assume 'id':
	$cat_order = ('name' == $order) ? 'cat_name' : 'cat_id';

	if (!isset($direction)) $direction = '';
	// Fetch the link category data as an array of hashesa
	$cats = $wpdb->get_results("
		SELECT DISTINCT link_category, cat_name, show_images, 
			show_description, show_rating, show_updated, sort_order, 
			sort_desc, list_limit
		FROM `$wpdb->links` 
		LEFT JOIN `$wpdb->linkcategories` ON (link_category = cat_id)
		WHERE link_visible =  'Y'
			AND list_limit <> 0
		ORDER BY $cat_order $direction ", ARRAY_A);

	// Display each category
	if ($cats) {
		foreach ($cats as $cat) {
			// Handle each category.
			// First, fix the sort_order info
			$orderby = $cat['sort_order'];
			$orderby = (bool_from_yn($cat['sort_desc'])?'_':'') . $orderby;

			// Display the category name
			echo '	<li id="linkcat-' . $cat['link_category'] . '"><h2>' . $cat['cat_name'] . "</h2>\n\t<ul class=\"xoxo blogroll\">\n";
			// Call get_links() with all the appropriate params
			social_networking_get_links($cat['link_category'],
				bool_from_yn($cat['show_images']),
				$orderby,
				bool_from_yn($cat['show_description']),
				bool_from_yn($cat['show_rating']),
				$cat['list_limit'],
				bool_from_yn($cat['show_updated']));

			// Close the last category
			echo "\n\t</ul>\n</li>\n";
		}
	}
}


if( !function_exists( 'normalize_url' ) )
{
    function normalize_url( $url )
    {
        $url = trim( $url );
        
        $parts = parse_url( $url );
        $scheme = isset( $parts['scheme'] ) ? $parts['scheme'] : null;

        if( !$scheme )
        {
            $url = 'http://' . $url;
            $parts = parse_url( $url );
        }

        $path = isset( $parts['path'] ) ? $parts['path'] : null;
        
        if( !$path )
            $url .= '/';
        
        return $url;
    }
}

?>