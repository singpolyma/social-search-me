<?php

function timezone_offset($timestamp,$offset) {
   $servertime = ((date('O')*-1)/100);//the amount the server needs to be adjusted to reach UTC
   $hour = 60*60;//one hour
   return $timestamp + ($hour*$servertime) + ($hour*$offset);
}//end function timezone_offset

?>