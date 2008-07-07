<?php

header('Content-Type: text/plain');

require_once 'XNC/Services/Google/Gmail.php';
require_once 'pollpage.php';

$gmail = new XNC_Services_Google_Gmail('GMAIL ADDRESS', 'PASSWORD');

foreach($gmail as $message) {
    //$id = $message->id;
    //$from = $message->From;
    //$subject = $message->Subject;
    //$headers = $message->getHeader();
    $body = $message->getBody();
    echo $body;
    preg_match("/to <a href=\"(.*)\"/i",$body,$data);
    echo $data[1];
    pollpage($data[1]);
}//end foreach

?>
