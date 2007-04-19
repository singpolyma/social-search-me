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


require_once dirname(__FILE__).'/FileSystem.php';

class FileSystemFromWebFS extends FileSystem {

   protected $listAPI;
   protected $downloadAPI;
   protected $realroot;//for 

   public function __construct($url,$url2,$root='',$realroot=FALSE) {
//THIS SHOULD BE TAKING THE URL OF THE SERVICE AND DETECTING ENDPOINTS FROM XRDS
      $this->listAPI = $url;
      $this->downloadAPI = $url2;
      $this->root = $root ? $root.'/' : '';
      $this->realroot = $realroot!==FALSE ? $realroot : $this->root;
      $this->cd('/');
   }//end constructor

   public function wget($file,$head=false) {//get contents and attributes of $file
      $tmp = $this->url_get($this->downloadAPI.$this->realroot.$file, true, ($head ? 'HEAD' : 'GET'));
      $inode = array();
      $inode['mime'] = explode(';',$tmp['headers']['content-type']);
      $inode['mime'] = $inode['mime'][0];
      $inode['title'] = explode('=',$tmp['headers']['content-disposition']);
      $inode['title'] = $inode['title'][1];
      if(!$inode['title']) $inode['title'] = $file;
      if($tmp['body']) $inode['content'] = $tmp['body'];
      return $inode;
   }//end public function wget

   public function stat($file) {//get attributes of $file (which may be a directory)
      return $this->wget($file,true);
   }//end public function stat

   protected function processListXML($directoryvals,$rootlevel=1) {
      $newitem = false;
      $numofleveltwos = 0;
      $numoflevelfours = 0;
      foreach($directoryvals as $el) {
         if($el['level'] <= $rootlevel) continue;//root immaterial
         if($el['level'] == $rootlevel+1 && ($el['type'] == 'complete' || $el['type'] == 'open')) $numofleveltwos++;
         if($el['level'] == $rootlevel+3 && ($el['type'] == 'complete' || $el['type'] == 'open')) $numoflevelfours++;
         if($el['level'] == $rootlevel+1 && $el['type'] == 'complete') continue;//item-level cannot be valid without child nodes
         if($el['level'] == $rootlevel+1 && (($el['type'] == 'open' && $newitem) || $el['type'] == 'close')) {//close element
            if($newitem['enclosure']) $newitem['link'] = $newitem['enclosure'];
            if(!$newitem['dc:identifier'] && $newitem['link']) {
               $tmp = explode('/',$newitem['link']);
               $newitem['dc:identifier'] = array_pop($tmp);
               if(!$newitem['dc:identifier']) $newitem['dc:identifier'] = array_pop($tmp);
            }//end if !id && link
            if(!$newitem['dc:created'] && $newitem['dc:date']) $newitem['dc:created'] = $newitem['dc:date'];
            if(!$newitem['dc:created'] && $newitem['dc:modified']) $newitem['dc:created'] = $newitem['dc:modified'];
            if(!$newitem['dc:created'] && $newitem['pubDate']) $newitem['dc:created'] = $newitem['pubDate'];
            if($newitem['dc:created']) $newitem['dc:created'] = strtotime($newitem['dc:created']);
            if($newitem['dc:modified']) $newitem['dc:modified'] = strtotime($newitem['dc:modified']);
            if(!$newitem['title'] && $newitem['dc:title']) $newitem['title'] = $newitem['dc:title'];
            if(!$newitem['mime']) $newitem['mime'] = 'inode/file';
            if(!$newitem['permissions']) $newitem['permissions'] = 600;
            if($newitem['dc:identifier']) $this->currentDirectory[$newitem['dc:identifier']] = $newitem;
            $newitem = false;
         }//end if close
         if($el['level'] == $rootlevel+2 && $el['type'] == 'complete') {//get field
            $newitem[strtolower($el['tag'])] = trim($el['value']);
         }//end if level == 3 && type == complete
      }//end foreach $directoryvals as el
      if($numofleveltwos == 1 && $numoflevelfours > 0) {
         $this->currentDirectory = array();
         $this->processListXML($directoryvals,$rootlevel+1);
      }//end MAY clause for RSS 2.0
   }//end protected function processListXML

   public function cd($directory) {
      if($directory == '/') $directory = '';
      $this->currentDirectory = array();
      $fromdir = $this->root;
      $this->currentDirectoryPath = $fromdir.$directory;
      $directoryxml = $this->url_get($this->listAPI.$this->currentDirectoryPath);
      $theParser = xml_parser_create();
      xml_parse_into_struct($theParser,$directoryxml,$directoryvals);
      xml_parser_free($theParser);
      $this->processListXML($directoryvals);
   }//end function cd

}//end class FileSystemFromYouOS

?>