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

class FileSystemFromYouOS extends FileSystem {

   protected $authkey;

   public function __construct($username,$password,$root='') {
      $authxml = $this->url_get('https://www.youos.com/api?apiname=login&username='.urlencode($username).'&password='.urlencode($password));
      $theParser = xml_parser_create();
      xml_parse_into_struct($theParser,$authxml,$authvals);
      xml_parser_free($theParser);
      foreach($authvals as $el) {
         if($el['tag'] == 'LOGIN') {$this->authkey = $el['attributes']['TOKEN'];}
      }//end foreach authvals as el
      if(!$this->authkey) die('Login failed');
      $this->root = $root ? '/'.$username.'/youfs'.$root : '/'.$username.'/youfs/';
      $this->cd('/');
   }//end constructor

   public function wget($file,$head=false) {//get contents and attributes of $file
      $directory = explode('/',$file);
      $file = array_pop($directory);
      $directory = implode('/',$directory).'/';
      $fromdir = $this->root;
      $tmp = $this->url_get('https://www.youos.com/api?apiname=fs_download&path='.str_replace('+','%20',urlencode($fromdir.$directory.$file)).'&est='.urlencode($this->authkey), true);
      $inode = array();
      $inode['mime'] = explode(';',$tmp['headers']['content-type']);
      $inode['mime'] = $inode['mime'][0];
      $inode['title'] = explode('=',$tmp['headers']['content-disposition']);
      $inode['title'] = $inode['title'][1];
      if(!$inode['title']) $inode['title'] = $file;
      $inode['content'] = $tmp['body'];
      return $inode;
   }//end public function wget

   public function stat($file) {//get attributes of $file (which may be a directory)
      if($file == '/') $file = '';
      $directoryxml = $this->url_get('https://www.youos.com/api?apiname=fs_stat&path='.urlencode($this->root.$file).'&est='.urlencode($this->authkey));
      $theParser = xml_parser_create();
      xml_parse_into_struct($theParser,$directoryxml,$directoryvals);
      xml_parser_free($theParser);
      foreach($directoryvals as $el) {
         if($el['tag'] == 'STAT') {
            $newitem = array();
            $newitem['title'] = $el['attributes']['FILENAME'];
            $newitem['dc:identifier'] = str_replace(substr($this->root,0,strlen($this->root)-1),'',$el['attributes']['PATH']);
            if($newitem['dc:identifier'] == $directory) continue;
            $newitem['dc:created'] = $this->youosdate($el['attributes']['LAST_UPDATED_DATE']);
            $newitem['dc:modified'] = $this->youosdate($el['attributes']['LAST_UPDATED_DATE']);
            if($el['attributes']['MIMETYPE'] && $el['attributes']['MIMETYPE'] != 'None') $newitem['mime'] = $el['attributes']['MIMETYPE'];
            if(strtoupper($el['attributes']['ISDIR']) == 'TRUE') $newitem['mime'] = 'inode/directory';
            $newitem['mime'] = $newitem['mime'] ? $newitem['mime'] : 'inode/file';
            if($el['attributes']['SIZE'] && $el['attributes']['SIZE'] != 'None') $newitem['size'] = $el['attributes']['SIZE'];
            return $newitem;
         }//end if FSITEM
      }//end foreach
   }//end public function iget

   protected function youosdate($date) {
      $thedate = substr($date,0,10);
      $thetime = explode('-',substr($date,10));
      if(!$thetime[1]) {$thetime = explode('+',substr($date,10)); $thetimezone = '+'.$thetime[1];}
      else $thetimezone = '-'.$thetime[1];
      $thetime = $thetime[0];
      return strtotime($thedate.' '.$thetime.' '.$thetimezone);
   }//end protected function youosdate

   public function cd($directory) {
      if($directory == '/') $directory = '';
      $this->currentDirectory = array();
      $fromdir = $this->root;
      $this->currentDirectoryPath = $fromdir.$directory;
      $directoryxml = $this->url_get('https://www.youos.com/api?apiname=fs_ls&path='.urlencode($this->currentDirectoryPath).'&est='.urlencode($this->authkey));
      $theParser = xml_parser_create();
      xml_parse_into_struct($theParser,$directoryxml,$directoryvals);
      xml_parser_free($theParser);
      foreach($directoryvals as $el) {
         if($el['tag'] == 'FSITEM') {
            $newitem = array();
            $newitem['title'] = $el['attributes']['FILENAME'];
            $newitem['dc:identifier'] = str_replace(substr($this->root,0,strlen($this->root)-1),'',$el['attributes']['PATH']);
            if($newitem['dc:identifier'] == $directory) continue;
            $newitem['dc:created'] = $this->youosdate($el['attributes']['LAST_UPDATED_DATE']);
            $newitem['dc:modified'] = $this->youosdate($el['attributes']['LAST_UPDATED_DATE']);
            if($el['attributes']['MIMETYPE'] && $el['attributes']['MIMETYPE'] != 'None') $newitem['mime'] = $el['attributes']['MIMETYPE'];
            if(strtoupper($el['attributes']['ISDIR']) == 'TRUE') $newitem['mime'] = 'inode/directory';
            $newitem['mime'] = $newitem['mime'] ? $newitem['mime'] : 'inode/file';
            if($el['attributes']['SIZE'] && $el['attributes']['SIZE'] != 'None') $newitem['size'] = $el['attributes']['SIZE'];
            if($newitem['dc:identifier'] != '/'.$directory && !($newitem['dc:identifier'] == '/' && !$directory)) $this->currentDirectory[$newitem['dc:identifier']] = $newitem;
         }//end if FSITEM
      }//end foreach $directoryvals as el
   }//end function cd

}//end class FileSystemFromYouOS

?>