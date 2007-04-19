<?php

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


require_once 'FileSystemFromYouOS.php';
require_once 'FileSystemFromWebFS.php';

$path = explode('/',$_SERVER['SCRIPT_URI']);
unset($path[0]);unset($path[1]);unset($path[2]);
$path = array_values($path);
$format = $path[0];
if($format == 'api') {
   unset($path[0]);
   $path = array_values($path);
} else {
   $format = 'html';
}//end if-else format is valid
$user = $path[0];
unset($path[0]);
$path = array_values($path);
$location = implode('/',$path);
if(!$location) $location = '/';

if(!$user) {
   if($format == 'html') {
      echo '<html><head><title>WebFS</title>';
      echo '<meta http-equiv="X-YADIS-Location" content="http://webfs.singpolyma.net/xrds" /></head><body>';
      echo '<ul><li><a href="/singpolyma/">singpolyma</a></li> <li><a href="/filess/">filess</a></li> </ul>';
      echo '<a href="/api/">See API</a>';
      echo '</body></html>';
   } else {
      header('Content-type: application/xml;');
      header('X-Moz-Is-Feed: 1');
      echo '<?xml version="1.0" ?>'."\n";
      echo '<rdf:RDF  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
      echo '   <channel/> <!-- may be removed, just forces firefox to render as feed -->'."\n";
      echo '   <item>'."\n";
      echo '      <dc:identifier>/singpolyma/</dc:identifier>'."\n";
      echo '      <link>http://webfs.singpolyma.net/singpolyma/</link>'."\n";
      echo '      <title>singpolyma</title>'."\n";
      echo '      <dc:created>'.htmlspecialchars(date('c',time())).'</dc:created>'."\n";
      echo '      <mime>inode/directory</mime>'."\n";
      echo '      <permissions>644</permissions>'."\n";
      echo '   </item>'."\n";
      echo '   <item>'."\n";
      echo '      <dc:identifier>/filess/</dc:identifier>'."\n";
      echo '      <link>http://webfs.singpolyma.net/filess/</link>'."\n";
      echo '      <title>filess</title>'."\n";
      echo '      <dc:created>'.htmlspecialchars(date('c',time())).'</dc:created>'."\n";
      echo '      <mime>inode/directory</mime>'."\n";
      echo '      <permissions>644</permissions>'."\n";
      echo '   </item>'."\n";
      echo '</rdf:RDF>';
   }//end if-else format
   exit;
}//end if ! user

require_once 'getpassword.php';
if($user == 'singpolyma')
   $fs = new FileSystemFromYouOS($user,$password);
else
   $fs = new FileSystemFromWebFS('http://filess.ning.com/api/','http://filess.ning.com/id/','','');
$stat = $fs->stat($location);
if($stat['mime'] == 'inode/directory') {
   if($location != '/') $fs->cd($location);//root is already cd
} else {
   $inode = $fs->wget($location);
   header('Content-type: '.$inode['mime']);
   header("Content-disposition: attachment; filename=".$inode['title']);
   echo $inode['content'];
   exit;
}//end if-else directory


if($format == 'html') {

echo '<html><head><title>WebFS</title>';
echo '<meta http-equiv="X-YADIS-Location" content="http://webfs.singpolyma.net/xrds" /></head><body>';
echo '<ul>'."\n";
foreach($fs->ls() as $inode) {
   if($inode['dc:identifier']{0} != '/') $inode['dc:identifier'] = '/'.$inode['dc:identifier'];
   echo '   <li><a href="/'.urlencode($user).str_replace('+','%20',str_replace('%2F','/',urlencode($inode['dc:identifier']))).'">'.htmlentities($inode['title']).'</a><br />';
   echo 'Created: '.date('c',$inode['dc:created']).'<br />';
   if($inode['dc:modified']) echo 'Modified: '.date('c',$inode['dc:modified']).'<br />';
   if($inode['mime']) echo 'Mimetype: '.htmlentities($inode['mime']).'<br />';
   if($inode['size']) echo 'Size: '.htmlentities($inode['size']/1000).' KB<br />';
   if($inode['permissions']) echo 'Permissions: '.$inode['permissions'];
   echo '</li>'."\n";
}//end foreach inode
echo '</ul>';

$scrpath = explode('/',$_SERVER['SCRIPT_URI']);
unset($scrpath[0]);unset($scrpath[1]);unset($scrpath[2]);
$scrpath = implode('/',$scrpath);
$scrpath = 'http://webfs.singpolyma.net/api/'.$scrpath;
echo '<a href="'.htmlentities($scrpath).'">See API</a>';

echo '</body></html>';

} else {

header('Content-type: application/xml;');
header('X-Moz-Is-Feed: 1');
echo '<?xml version="1.0" ?>'."\n";
echo '<rdf:RDF  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
echo '   <channel/> <!-- may be removed, just forces firefox to render as feed -->'."\n";
foreach($fs->ls() as $inode) {
   if($inode['dc:identifier']{0} != '/') $inode['dc:identifier'] = '/'.$inode['dc:identifier'];
   echo '   <item>'."\n";
   echo '      <dc:identifier>'.htmlspecialchars('/'.$user.$inode['dc:identifier']).'</dc:identifier>'."\n";
   echo '      <link>'.htmlspecialchars('http://webfs.singpolyma.net/'.urlencode($user).str_replace('+','%20',str_replace('%2F','/',urlencode($inode['dc:identifier'])))).'</link>'."\n";
   echo '      <title>'.htmlspecialchars($inode['title']).'</title>'."\n";
   echo '      <dc:created>'.htmlspecialchars(date('c',$inode['dc:created'])).'</dc:created>'."\n";
   if($inode['dc:modified'])  echo '      <dc:modified>'.htmlspecialchars(date('c',$inode['dc:modified'])).'</dc:modified>'."\n";
   if($inode['mime']) echo '      <mime>'.htmlentities($inode['mime']).'</mime>'."\n";
   if($inode['size']) echo '      <size>'.htmlentities($inode['size']).'</size>'."\n";
   if($inode['permissions']) echo '      <size>'.htmlentities($inode['permissions']).'</size>'."\n";
   echo '   </item>'."\n";
}//end foreach inode
echo '</rdf:RDF>';

}//end if-else html

?>