<?php

require_once 'xn-app://xoxotools/OutlineClasses/OutlineFromXOXO.php';
require_once 'xn-app://xoxotools/OutlineClasses/OutlineFromHATOM.php';
require_once 'xn-app://singpolymaplay/getTidy.php';

function pollpage($url) {
   $XMLdata = getTidy($url);
   $obj = new OutlineFromXOXO($XMLdata,array('classes' => array('xoxo','posts')));
   $struct = $obj->toArray();
   if(!count($struct)) {$obj = new OutlineFromHATOM($XMLdata);$struct = $obj->toArray();}
   if(!count($struct)) {$obj = new OutlineFromXOXO($XMLdata,array('classes' => array()));$struct = $obj->toArray();}
//header('Content-Type: text/plain');
//var_dump($struct);exit;
   foreach($struct as $structid => $node) {
      if(!is_numeric($structid)) continue;
      $postTitle = $node['text'];
      $postURL = $node['href'];
      foreach($node as $id => $comment) {
         if(!is_numeric($id)) {continue;}
         $comment = array_change_key_case($comment);
         $url = $comment['href'];
         if($url{0} == '#') {$url = $postURL.$url;}
         $query = XN_Query::create('Content')
                     ->filter('owner','=')
                     ->filter('type','eic','Comment')
                     ->filter('my.url','=',$url);
         $items = $query->execute();
         if(count($items) > 0) {continue;}
         if($comment['author']) {
            $theParser = xml_parser_create();
            xml_parse_into_struct($theParser,$comment['author'],$tmp);
            xml_parser_free($theParser);
            $authorname = $tmp[0]['value'];
            $authorurl = $tmp[0]['attributes']['HREF'];
         } else {
            $authorname = $comment['text#1'];
            $authorurl = $comment['href#1'];
         }//end if-else comment[author]
         $time = (((int)($comment['title']/1000000000)) < 100) ? ((int)$comment['title']) : ((int)($comment['title']/1000000000));
         $obj = XN_Content::create('Comment')
                ->my->add('content',$comment['body'])
                ->my->add('url',$url)
                ->my->add('authorname',$authorname)
                ->my->add('authorurl',$authorurl)
                ->my->add('time',$time)
                ->my->add('posturl',$postURL)
                ->my->add('posturl2',str_replace('-','',str_replace('/',' ',$postURL)))
                ->my->add('posttitle',$postTitle);
         $obj->saveAnonymous();
      }//end foreach node
   }//end foreach struct
}//end function pollpage

?>