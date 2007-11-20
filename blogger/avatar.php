<?php

function getTidy($url) {
//   $curl = curl_init('http://cgi.w3.org/cgi-bin/tidy?docAddr='.urlencode($url).'&forceXML=on');
   $curl = curl_init($url);
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
   curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.4) Gecko/20060508 Firefox/2.0');
   $rtrn = curl_exec($curl);
   curl_close($curl);
//   $tidy = new tidy;
//   $tidy->parseString($rtrn, array('output-xml' => true, 'doctype' => 'loose', 'add-xml-decl' => true),'utf8');
//   $tidy->cleanRepair();
//   return str_replace('&nbsp;','&#160;',$tidy);
   return str_replace('&nbsp;','&#160;',$rtrn);
}//end function getTidy

$bloggerdata = getTidy($_REQUEST['url']);
@$doc = new DOMDocument();
@$doc->preserveWhiteSpace = false;
@$doc->loadHTML($bloggerdata);
@$bloggerdata = $doc->saveXML();
$theParser = xml_parser_create();
xml_parse_into_struct($theParser,$bloggerdata,$vals);
xml_parser_free($theParser);

$img = array();
foreach($vals as $el) {
   if($el['tag'] == 'IMG' && ($el['attributes']['ALT'] == 'My Photo' || in_array('photo',explode(' ',$el['attributes']['CLASS'])))) {$img['photo'] = array();$img['photo']['url'] = $el['attributes']['SRC'];$img['photo']['width'] = $el['attributes']['WIDTH'];$img['photo']['height'] = $el['attributes']['HEIGHT'];}
}//end foreach
$img = $img['photo'];

header('Content-type: text/javascript;');
if($_REQUEST['maxwidth']) {
   $img['height'] = '';//size proportionately
   if(!$img['width'] || $img['width'] > $_REQUEST['maxwidth']) $img['width'] = $_REQUEST['maxwidth'];
}//end if maxwidth
if($_REQUEST['maxheight']) {
   $img['width'] = '';//size proportionately
   if(!$img['height'] || $img['height'] > $_REQUEST['maxheight']) $img['height'] = $_REQUEST['maxheight'];
}//end if maxheight
if($img['url'])
   echo 'document.getElementById("'.$_REQUEST['id'].'").innerHTML = "<img src=\"'.htmlentities($img['url']).'\"'.($img['width'] ? ' width=\"'.$img['width'].'\"' : '').($img['height'] ? ' height=\"'.$img['height'].'\"' : '').' alt=\"\" />";';
else if($_REQUEST['defaultimage'])
   echo 'document.getElementById("'.$_REQUEST['id'].'").innerHTML = "<img src=\"'.htmlentities($_REQUEST['defaultimage']).'\"'.($img['width'] ? ' width=\"'.$img['width'].'\"' : '').($img['height'] ? ' height=\"'.$img['height'].'\"' : '').' alt=\"\" />";';

?>