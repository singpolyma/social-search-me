<?php
/*
Plugin Name: FreshTags
Version: 0.11
Plugin URI: http://singpolyma-tech.blogspot.com/2006/03/freshtags-for-wordpress.html
Description: Integrates FreshTags functionality into WordPress
Author: Stephen Paul Weber
Author URI: http://singpolyma-tech.blogspot.com/
*/

/*

LICENSE


This program is free software; you can redistribute it 
and/or modify it under the terms of the GNU General Public 
License (GPL) as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any 
later version.

This program is distributed in the hope that it will be 
useful, but WITHOUT ANY WARRANTY; without even the 
implied warranty of MERCHANTABILITY or FITNESS FOR A 
PARTICULAR PURPOSE.  See the GNU General Public License 
for more details.

To read the license please visit
http://www.gnu.org/copyleft/gpl.html

*/

   $freshtags_curr_tags = '';

   function freshtags_filter_curr_tags() {
      global $freshtags_curr_tags;
      global $wpdb;
      $freshtags_curr_tags = str_replace(' ','+',$freshtags_curr_tags);
      $freshtags_curr_tags .= '+'.str_replace('_','+',str_replace('-','+',str_replace('.','+',$freshtags_curr_tags)));
      $cats = $wpdb->get_results("SELECT cat_name FROM ".$wpdb->categories." ORDER BY category_count DESC");
      $cleancats = array();
      foreach($cats as $cat)
         $cleancats[] = strtolower($cat->cat_name);
      $foundtags = array();
      foreach(explode('+',$freshtags_curr_tags) as $tag) {
         if(in_array(strtolower($tag),$cleancats))
            $foundtags[] = strtolower($tag);
      }//end foreach tag
      $foundtags = array_unique($foundtags);
      $freshtags_curr_tags = implode('+',$foundtags);
   }//end function freshtags

   function freshtags_get_curr_tags() {
      global $freshtags_curr_tags;

      //try to get from our url
      if(isset($_REQUEST['tags'])) {
         $freshtags_curr_tags = $_REQUEST['tags'];
         freshtags_filter_curr_tags();
         return;
      }//end if isset tags
      if(isset($_REQUEST['tag'])) {
         $freshtags_curr_tags = $_REQUEST['tag'];
         freshtags_filter_curr_tags();
         return;
      }//end if isset tag
      if(isset($_REQUEST['cat']) && !is_numeric($_REQUEST['cat'])) {
         $freshtags_curr_tags = $_REQUEST['cat'];
         freshtags_filter_curr_tags();
         return;
      }//end if isset cat

      //try to get from referer url
      if($_SERVER['HTTP_REFERER']) {
         //grab from refer query vars
         $refer = explode('?',$_SERVER['HTTP_REFERER']);
         $refervars = explode('&',$refer[1]);
         foreach($refervars as $refervar) {
            $tmp = explode('=',$refervar);
            $referpairs[strtolower($tmp[0])] = $tmp[1];
         }//end foreach
         if($referpairs['tags'])
            $freshtags_curr_tags = $referpairs['tags'];
         if($referpairs['tag'])
            $freshtags_curr_tags = $referpairs['tag'];
         if($referpairs['cat'])
            $freshtags_curr_tags = $referpairs['cat'];
         if($referpairs['q'])
            $freshtags_curr_tags = $referpairs['q'];
         if($referpairs['p'])
            $freshtags_curr_tags = $referpairs['p'];
         if($referpairs['query'])
            $freshtags_curr_tags = $referpairs['query'];
         if($referpairs['search'])
            $freshtags_curr_tags = $referpairs['search'];
         if($referpairs['topics'])
            $freshtags_curr_tags = $referpairs['topics'];
         if($referpairs['topic'])
            $freshtags_curr_tags = $referpairs['topic'];
         freshtags_filter_curr_tags();
         if($freshtags_curr_tags) return;//if we've got something, return

         //grab from refer path
         $referpath = array_reverse(explode('/',$refer[0]));
         if($referpath[0]) 
            $referpathindex = 1;
         else
            $referpathindex = 2;
         switch(strtolower($referpath[$referpathindex])) {
            case 'tag':
            case 'tags':
            case 'cat':
            case 'category':
            case 'wiki':
            case 'search':
            case 'topics':
            case 'topic':
               $freshtags_curr_tags = $referpath[$referpathindex-1];
         }//end switch
      }//end if referer
      freshtags_filter_curr_tags();
      if($freshtags_curr_tags) return;//if we've got something, we're done

      //get from current page
      if(is_single()) {
         $cats = get_the_category();
         $freshtags_curr_tags = $cats[0]->cat_name;
      }//end if is_single
      if(is_category()) {
         $cat = intval( get_query_var('cat') );
         $cat = get_the_category_by_ID($cat);
         $freshtags_curr_tags = strip_tags($cat);
      }//end if is_category
      freshtags_filter_curr_tags();
   }//end function freshtags_get_currtags

   function freshtags_tags_drop($catlink,$cats) {
      global $freshtags_curr_tags;
      $tags_array = explode('+',$freshtags_curr_tags);
      $rtrn = '';
      $rtrn .= '<select onchange="window.location=this.value;">'."\n";
      if(!$freshtags_curr_tags) {
         $rtrn .= '   <option value="">- Tags -</option>'."\n";
      }//end if ! freshtags_curr_tags
      foreach($cats as $cat) {
         if(!$cat->category_count) continue;
         $prefix = '';
         $selected = '';
         if(strtolower($cat->cat_name) == $tags_array[0])
            $selected = ' selected="selected"';
         if(in_array(strtolower($cat->cat_name), $tags_array))
            $prefix = '&gt;&gt; ';
         if(empty($catlink)) {
            $rtrn .= '   <option value="'.get_settings('home').'/?cat='.$cat->cat_id.'"'.$selected.'>'.$prefix.$cat->cat_name.' ('.$cat->category_count.')'.'</option>'."\n";
         } else {
            $catnm = $cat->category_nicename;
            if($parent = $cat->category_parent)
               $catnm = get_category_parents($parent, false, '/', true) . $catnm . '/';
            $catnm = str_replace('%category%', $catnm, $catlink);
            $catnm = get_settings('home') . trailingslashit($catnm);
            $rtrn .= '   <option value="'.$catnm.'"'.$selected.'>'.$prefix.$cat->cat_name.' ('.$cat->category_count.')'.'</option>'."\n";
         }//end if-else empty catlink
      }//end foreach
      $rtrn .= '</select>'."\n";
      return $rtrn;
   }//end function freshtsags_drop_list

   function freshtags_tags_list($catlink,$cats) {
      global $freshtags_curr_tags;
      $tags_array = explode('+',$freshtags_curr_tags);
      $rtrn = '';
      $rtrn .= '<ul>'."\n";
      foreach($cats as $cat) {
         if(!$cat->category_count) continue;
         $prefix = '';
         $selected = '';
         if(empty($catlink)) {
            $rtrn .= '   <li>';
            $rtrn .= '<a href="'.get_settings('home').'/?cat='.$cat->cat_id.'">';
            if(in_array(strtolower($cat->cat_name), $tags_array)) 
               $rtrn .= '<b>';
            $rtrn .= $cat->cat_name;
            $rtrn .= '</a>';
            if(in_array(strtolower($cat->cat_name), $tags_array)) 
               $rtrn .= '</b>';
            $rtrn .= ' ('.$cat->category_count.')';
            $rtrn .= '</li>'."\n";
         } else {
            $catnm = $cat->category_nicename;
            if($parent = $cat->category_parent)
               $catnm = get_category_parents($parent, false, '/', true) . $catnm . '/';
            $catnm = str_replace('%category%', $catnm, $catlink);
            $catnm = get_settings('home') . trailingslashit($catnm);
            $rtrn .= '   <li>';
            $rtrn .= '<a href="'.$catnm.'">';
            if(in_array(strtolower($cat->cat_name), $tags_array))
               $rtrn .= '<b>';
            $rtrn .= $cat->cat_name;
            $rtrn .= '</a>';
            if(in_array(strtolower($cat->cat_name), $tags_array))
               $rtrn .= '</b>';
            $rtrn .= '&nbsp;('.$cat->category_count.') ';
            $rtrn .= '</li>'."\n";
         }//end if-else empty catlink
      }//end foreach
      $rtrn .= '</ul>'."\n";
      return $rtrn;
   }//end function freshtsags_drop_list
   
   function freshtags_load_searchbox() {
      global $freshtags_curr_tags;
      if(!$freshtags_curr_tags)
         freshtags_get_curr_tags();
      if($freshtags_curr_tags) {
         echo "\n".'<script type="text/javascript">'."\n";
         echo '//<![CDATA['."\n";
         echo '   var sftxt = document.getElementById("s");'."\n";
         echo '   sftxt.value="'.str_replace('+',' ',$freshtags_curr_tags).'";'."\n";
         echo '//]]>'."\n";
         echo '</script>'."\n";
      }//end if freshtags_curr_tags
   }//end function freshtags_load_searchbox

   function freshtags_taglist($type='list') {
      global $freshtags_curr_tags;
      global $wpdb;
      global $wp_rewrite;
      if(!$freshtags_curr_tags)
         freshtags_get_curr_tags();
      $cats = $wpdb->get_results("SELECT cat_name,cat_id,category_count,category_nicename,category_parent FROM ".$wpdb->categories." ORDER BY category_count DESC");
      $catlink = $wp_rewrite->get_category_permastruct();
      switch($type) {
         case 'drop': return freshtags_tags_drop($catlink,$cats);
         case 'list': return freshtags_tags_list($catlink,$cats);
      }//end switch
      return '';
   }//end function external_comment_list

   function freshtags_getposts() {
      global $freshtags_curr_tags;
      if(!$freshtags_curr_tags)
         freshtags_get_curr_tags();
      if(!$freshtags_curr_tags)
         return false;
      $tags_array = explode('+',$freshtags_curr_tags);
      global $wpdb;
      $catids = array();
      foreach($tags_array as $tag) {
         $catid = $wpdb->get_results("SELECT cat_id FROM ".$wpdb->categories." WHERE cat_name='".$tag."'");
         $catids[] = $catid[0]->cat_id;
      }//end foreach tags
      $postids = array();
      foreach($catids as $catid) {
         $postid = $wpdb->get_results("SELECT post_id FROM ".$wpdb->post2cat." WHERE category_id=".$catid);
         if(count($postids) == 0) {
            foreach($postid as $anid)
               $postids[] = $anid->post_id;
         } else {
            $postids2 = array();
            foreach($postid as $anid)
               $postids2[] = $anid->post_id;
            $postidstmp = array();
            foreach($postids as $anid) {
               if(in_array($anid,$postids2))
                  $postidstmp[] = $anid;
            }//end foreach postids
            $postids = $postidstmp;
         }//end if-else count postids == 0
      }//end foreach catids
      rsort($postids);
      $posts = array();
      foreach($postids as $count => $postid) {
         $post = $wpdb->get_results("SELECT ID,post_title,post_content,post_status,post_date FROM ".$wpdb->posts." WHERE id=".$postid);
         $paddedcount = $count;
         if($count < 10)
            $paddedcount = '0'.$paddedcount;
         if($count < 100)
            $paddedcount = '0'.$paddedcount;
         if($post[0]->post_status == 'publish')
            $posts[strtotime($post[0]->post_date).$paddedcount] = $post[0];
      }//end foreach postids
      krsort($posts);
      return $posts;
   }//end function freshtags_getpostids

   function freshtags_postlist($max=10,$header='') {
      global $wpdb;
      global $freshtags_curr_tags;
      $posts = freshtags_getposts();
      if(!$posts || !count($posts))
         return '';
      $posts = array_slice($posts,0,$max);

      $rtrn = '';
      $rtrn .= str_replace('%tags%',$freshtags_curr_tags,$header)."\n";
      $rtrn .= '<ul>'."\n";
      foreach($posts as $post) {
         $content = preg_replace('/[\t\n\r\0\x0B\xA0 ][\t\n\r\0\x0B\xA0 ]+/',' ',substr(strip_tags($post->post_content),0,100));
         $rtrn .= '   <li>';
         $rtrn .= '<a href="'.get_permalink($post->ID).'" title="'.$content.'...">';
         $rtrn .= $post->post_title;
         $rtrn .= '</a>';
         $rtrn .= '</li>'."\n";
      }//end foreach posts
      $rtrn .= '</ul>'."\n";
      return $rtrn;
   }//end function freshtags_postlist

   function freshtags_postlink_filter($url='') {
      global $freshtags_curr_tags;
      if(!$freshtags_curr_tags)
         freshtags_get_curr_tags();
      if(!$freshtags_curr_tags)
         return $url;
      $url = stristr($url,'?') ? $url.'&amp;tags='.$freshtags_curr_tags : $url.'?tags='.$freshtags_curr_tags;
      return $url;
   }//end function freshtags_postlink_filter
   
   if(isset($_REQUEST['json'])) {//if outputting JSON(P)
      require_once(dirname(__FILE__).'/../../' .'wp-config.php');
      header('Content-Type: text/javascript;charset=utf-8');
      $posts = freshtags_getposts();
      if(isset($_REQUEST['raw'])) {
         echo '{"posts":';
      } else {
         if($_REQUEST['callback']) {
            echo $_REQUEST['callback'].'(';
         } else {
            echo "if(typeof(FreshTags) != 'object') FreshTags = {};\n";
            echo 'FreshTags.posts = ';
         }//end if-else callback
      }//end if-else raw
      echo '[';
      if($posts) {
         $_REQUEST['count'] = $_REQUEST['count'] ? $_REQUEST['count'] : 100;
         $posts = array_slice($posts,0,$_REQUEST['count']);
         $posts = array_values($posts);
         foreach($posts as $index => $post) {
            $tagstr = '';
            foreach(get_the_category($post->ID) as $tag)
               $tagstr .= ',"'.addslashes($tag->cat_name).'"';
            if($index != 0)
               echo ',';
            echo '{';
            echo '"d":"'.str_replace("\r",'',str_replace("\n",'\n',addslashes($post->post_title))).'",';
            echo '"n":"'.str_replace("\r",'',str_replace("\n",'\n',addslashes($post->post_content))).'",';
            echo '"u":"'.str_replace("\r",'',str_replace("\n",'\n',addslashes(get_permalink($post->ID)))).'",';
            echo '"t":['.str_replace("\r",'',str_replace("\n",'\n',substr($tagstr,1,strlen($tagstr)))).']';
            echo '}';
         }//end foreach postids
      }//end if postids
      echo ']';
      if(isset($_REQUEST['raw'])) {
         echo '}';
      } else {
         if($_REQUEST['callback']) {
            echo ');';
         } else {
            echo ";\n";
            echo "if(FreshTags.callbacks && FreshTags.callbacks.posts)\n";
            echo "   FreshTags.callbacks.posts(FreshTags.posts);";
         }//end if-else callback
      }//end if-else raw
   } else
      add_filter('post_link','freshtags_postlink_filter');

?>