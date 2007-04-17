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

   public function __construct($username,$password) {
      $authxml = $this->url_get('https://www.youos.com/api?apiname=login&username='.urlencode($username).'&password='.urlencode($password));
      $theParser = xml_parser_create();
      xml_parse_into_struct($theParser,$authxml,$authvals);
      xml_parser_free($theParser);
      foreach($authvals as $el) {
         if($el['tag'] == 'LOGIN') {$this->authkey = $el['attributes']['TOKEN'];}
      }//end foreach authvals as el
      if(!$this->authkey) die('Login failed');
      $this->root = '/'.$username.'/youfs/';
      $this->cd('/');
   }//end constructor

   public function wget($file) {//get contents and attributes of $file
      $directory = explode('/',$file);
      $file = array_pop($directory);
      $directory = implode('/',$directory).'/';
      if($directory{0} == '/')
         $fromdir = substr($this->root,0,strlen($this->root)-1);
      else
         $fromdir = substr($this->root,0,strlen($this->currentDirectoryPath)-1);
      $file = $fromdir.$directory.$file;
      if($this->currentDirectoryPath != $fromdir.$directory) {
         $oldcd = $this->currentDirectoryPath;
         $this->cd($fromdir.$directory);
      }//end if cd
      $inodes = $this->ls();
      $inode = $inodes[$file];
      if(!$inode['content']) $inode['content'] = $this->url_get('https://www.youos.com/api?apiname=fs_download&path='.str_replace('+','%20',urlencode($file)).'&est='.urlencode($this->authkey));
      if($oldcd) $this->cd($oldcd);
      return $inode;
   }//end public function wget

   protected function youosdate($date) {
      $thedate = substr($date,0,10);
      $thetime = explode('-',substr($date,10));
      if(!$thetime[1]) {$thetime = explode('+',substr($date,10)); $thetimezone = '+'.$thetime[1];}
      else $thetimezone = '-'.$thetime[1];
      $thetime = $thetime[0];
      return strtotime($thedate.' '.$thetime.' '.$thetimezone);
   }//end protected function youosdate

   public function cd($directory) {
      $this->currentDirectory = array();
      if($directory{0} == '/')
         $fromdir = substr($this->root,0,strlen($this->root)-1);
      else
         $fromdir = substr($this->root,0,strlen($this->currentDirectoryPath)-1);
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
//Name for attribute and contents are unstable.  file should be the corrent name.  directory may become dir or similar.  May all end up in mime as inode/file or inode/dir.  Needs research.
            $newitem['inode'] = (strtoupper($el['attributes']['ISDIR']) == 'TRUE') ? 'directory' : 'file';
            if($el['attributes']['MIMETYPE'] && $el['attributes']['MIMETYPE'] != 'None') $newitem['mime'] = $el['attributes']['MIMETYPE'];
            if($el['attributes']['SIZE'] && $el['attributes']['SIZE'] != 'None') $newitem['size'] = $el['attributes']['SIZE'];
            $this->currentDirectory[$newitem['dc:identifier']] = $newitem;
         }//end if FSITEM
      }//end foreach $directoryvals as el
   }//end function cd

}//end class FileSystemFromYouOS

?>