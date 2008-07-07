<?php

function bytime_sort($arr) {
	for($i = 0; $i < sizeof($arr); $i++) {
	   $lowest = $i;
	   for ($j = $i; $j < sizeof($arr); $j++) {
		   if (($arr[$j]->my->time?$arr[$j]->my->time:strtotime($arr[$j]->createdDate)) < ($arr[$lowest]->my->time?$arr[$lowest]->my->time:strtotime($arr[$lowest]->createdDate))) {
		      $lowest = $j;
                   }//end if <
	    }//end for j
	    $temp = $arr[$i];
	    $arr[$i] = $arr[$lowest];
	    $arr[$lowest] = $temp;
	}//end for i
	return $arr;
}//end function

?>