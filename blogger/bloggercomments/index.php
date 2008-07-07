<?php

require_once 'timezone_offset.php';
require_once 'pollpage.php';
require_once 'bytime_sort.php';

if(!$_GET['url']) {
   ?>

Welcome to Blogger Recent Comments.  This applicaton's purpose is to cache, catalogue, and syndicate comments from Blogger blogs for the primary purpose of providing comment feeds, which Blogger sadly lacks.  Blog owners may be interested in the <a href="instructions.php">instructions on setting up your blog</a> to work with this service.  Once a blog is set up, entering the URL of the blog (or any post page on the blog) and filling out the options on the form below will send you to the URL of the feed with those options for that blog/post.<br />
<br />
<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>"><div>
   <input type="hidden" name="xn_auth" value="no" />
   Blog/Post URL: <input type="text" name="url" value="" /><br />
   This is a post URL: <input type="checkbox" name="post" /><br />
   Max Items to output (default 100): <input type="text" name="max" value="" /><br />
   Max characters of content to output: <input type="text" name="chars" value="" /><br />
   Timezone offset for timestamps (in the form +1 for UTC+1): <input type="text" name="tz" value="" /><br />
   <select name="format">
      <option value="xhtml">Output XHTML</option>
      <option value="rss20">Output RSS 2.0</option>
      <option value="js">Output JavaScript</option>
      <option value="json">Output JSON</option>
   </select><br />
   If JSON output, output raw object? <input type="checkbox" name="raw" /><br />
   Optional JSON callback function: <input type="text" name="callback" /><br />
   <input type="submit" value="Syndicate" />
</div></form>
   <?php
} else {

pollpage($_GET['url']);

if($_GET['post']) {
   $query = XN_Query::create('Content')
                ->filter('owner','=')
                ->filter('type','eic','Comment')
                ->filter('my.posturl','=',$_GET['url'])
                ->order('my.time','desc',XN_Attribute::NUMBER);
} else {
   $query = XN_Query::create('Content')
                ->filter('owner','=')
                ->filter('type','eic','Comment')
                ->filter('my.posturl2','likeic',str_replace('-','',str_replace('/',' ',$_GET['url'])))
                ->order('my.time','desc',XN_Attribute::NUMBER);
}//end if-else ! post
$items = $query->execute();

$items = array_reverse(bytime_sort($items));
if($_GET['max']) {$items = array_slice($items,0,$_GET['max']);}

$_GET['chars'] = $_GET['chars'] ? $_GET['chars'] : 25;

if($_GET['format'] == 'rss20') {
   header("Content-Type: application/xml");
   ?>

<rss version="2.0">
<channel>
<title>Recent Comments @ <?php echo htmlspecialchars($_GET['url']); ?></title>
<link><?php echo htmlspecialchars($_GET['url']); ?></link>
<description></description>
<docs>http://blogs.law.harvard.edu/tech/rss</docs>
<generator>PHP script</generator>

   <?php
}//end if format == rss20

if($_GET['format'] == 'js') {
   header("Content-Type: text/javascript");
   echo 'document.writeln("<ul id=\"recently\">");'."\n";
}//end if format == js

if($_GET['format'] == 'json') {
   header("Content-Type: text/javascript");
   if(!$_REQUEST['raw'] && $_REQUEST['callback']) {echo "\nif(".$_REQUEST['callback'].")\n   ".$_REQUEST['callback']."(";}
    if(!$_REQUEST['raw'] && !$_REQUEST['callback']) {echo "if(typeof(BloggerRecentComments) != 'object') BloggerRecentComments = {};\nBloggerRecentComments.comments = ";}
   echo '[';
}//end if format == json

$urls = array();
foreach($items as $comment) {
   if(in_array($comment->my->url,$urls)) {continue;}
   $urls[] = $comment->my->url;
   $time = timezone_offset($comment->my->time,$_GET['tz']);
   $comment->my->content = str_replace('<BR/>','<br />',$comment->my->content);
   if($_GET['format'] == 'rss20') {
?>

<item>
<title>Comment on <?php echo htmlspecialchars($comment->my->posttitle); ?> by <?php echo htmlspecialchars($comment->my->authorname); ?></title>
<link><?php echo htmlspecialchars($comment->my->url); ?></link>
<description><?php echo htmlspecialchars($comment->my->content); ?></description>
<pubDate><?php echo date("D, d M Y H:i:s T", $time); ?></pubDate>
<guid><?php echo htmlspecialchars($comment->my->url); ?></guid>
</item>

<?php
   } elseif($_GET['format'] == 'js') {
      echo "document.writeln('<li><a title=\"Comment on ".$comment->my->posttitle."\" href=\"".$comment->my->url.'">'.addslashes(substr(str_replace("\n",' ',str_replace("\r",' ',strip_tags($comment->my->content))),0,$_GET['chars']))."...</a></li>');\n";
   } elseif($_GET['format'] == 'json') {
      $out .= '{"url":"'.$comment->my->url.'","authorname":"'.$comment->my->authorname.'","authorurl":"'.$comment->my->authorurl.'","posturl":"'.$comment->my->posturl.'","posttitle":"'.$comment->my->posttitle.'","time":"'.$time.'","content":"'.str_replace("\r",'',str_replace("\n",'\n',$comment->my->content)).'"},';
   } else {
      echo ' <a href="'.$comment->my->h('url').'">Comment</a> by ';
      if($comment->my->authorurl) {
         echo '<a href="'.$comment->my->h('authorurl').'">'.$comment->my->h('authorname').'</a>';
      } else {
         echo $comment->my->h('authorname');
      }//end if-else authorurl
      echo ' on post <a href="'.$comment->my->h('posturl').'">'.$comment->my->h('posttitle').'</a>';
      echo ' at '.date('Y-m-d H:i',$time);
      echo ' <a href="delete.php?id='.$comment->id.'">Delete</a> <br /><br />';
      echo $comment->my->content;
      echo '<hr />';
   }//end if-else formats
}//end foreach indicies

if($_GET['format'] == 'rss20') {
   echo '</channel>';
   echo '</rss>';
}//end if format == rss20

if($_GET['format'] == 'js') {
   echo 'document.writeln("</ul>");';
}//end if format == js

if($_GET['format'] == 'json') {
   header("Content-Type: text/javascript");
   echo substr($out,0,strlen($out)-1);
   echo ']';
    if(!$_REQUEST['raw'] && !$_REQUEST['callback']) {echo ";\nif(BloggerRecentComments.callbacks && BloggerRecentComments.callbacks.comments)\n   BloggerRecentComments.callbacks.comments(BloggerRecentComments.comments);";}
   if(!$_REQUEST['raw'] && $_REQUEST['callback']) {echo ");";}}//end if format == json

}//end if-else !GET[blog]

?>