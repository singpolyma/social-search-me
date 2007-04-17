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


class FileSystem {

   protected $currentDirectory = array();
   protected $currentDirectoryPath = '/';
   protected $root = '/';

   public function __construct() {}//Set up object

   public function wget($file) {}//get contents of $file

   public function cd($directory) {}//change to /root/directory/

   public function ls($directory=false) {//change to /root/directory/ if specified, return a list of contents
      if($directory) $this->cd($directory);
      return $this->currentDirectory;
   }//end public function ls

   protected function url_get($url) {
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.4) Gecko/20060508 Firefox/2.0');
      $rtrn = curl_exec($curl);
      curl_close($curl);
      return $rtrn;
   }//end function url_get

}//end class FileSystem

?>