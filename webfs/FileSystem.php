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

   public function wget($file,$head=false) {}//get contents of $file

   public function stat($file) {}//get attributes of $file (which may be a directory)

   public function cd($directory) {}//change to /root/directory/

   public function ls($directory=false) {//change to /root/directory/ if specified, return a list of contents
      if($directory) $this->cd($directory);
      return $this->currentDirectory;
   }//end public function ls

   protected function url_get($url,$getheaders=false,$method='GET') {
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_USERAGENT, 'PHP WebFS Client');
      if($getheaders) curl_setopt($curl, CURLOPT_HEADER, true);
      if($method != 'GET') curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
      $rtrn = curl_exec($curl);
      curl_close($curl);
      if($getheaders) {
         $rtrn = explode("\r\n\r\n",$rtrn);
         foreach($rtrn as $idx => $section) {
            $tmp = explode("\r\n",$section);
            if(substr($tmp[0],-6,6) == '200 OK') {
               unset($tmp[0]);
               $headers = array();
               foreach($tmp as $header) {
                  $nh = explode(': ',$header);
                  $key = strtolower($nh[0]);
                  unset($nh[0]);
                  $headers[$key] = trim(implode(': ',$nh));
               }//end foreach tmp
               $headeridx = $idx;
               break;
            }//end if 200 OK
         }//end foreach rtrn
         $rtrn = array_slice($rtrn,$headeridx+1,count($rtrn)-($headeridx+1));
         $rtrn = implode("\r\n\r\n",$rtrn);
         return array('headers' => $headers, 'body' => $rtrn);
      }//end if getheaders
      return $rtrn;
   }//end function url_get

}//end class FileSystem

?>