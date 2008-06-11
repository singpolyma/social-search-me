<?php

function get_tweets() {
   $ch = curl_init('http://twitter.com/direct_messages.xml?since_id='.file_get_contents(dirname(__FILE__).'/../last_twitter'));
   curl_setopt($ch, CURLOPT_USERPWD, file_get_contents(dirname(__FILE__).'/twitter.txt'));
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
   $response = curl_exec($ch);
   curl_close($ch);
	return simplexml_load_string($response);
}//end get_tweets

function send_tweet($to, $text) {
	$ch = curl_init('http://twitter.com/direct_messages/new.xml');
   curl_setopt($ch, CURLOPT_POST, TRUE);
   curl_setopt($ch, CURLOPT_POSTFIELDS, 'user='.$to.'&text='.urlencode($text));
   curl_setopt($ch, CURLOPT_USERPWD, file_get_contents(dirname(__FILE__).'/twitter.txt'));
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
   $response = curl_exec($ch);
   curl_close($ch);
	return $response;
}//end send_tweet

function post_tweet($text) {
	$ch = curl_init('http://twitter.com/statuses/update.xml');
   curl_setopt($ch, CURLOPT_POST, TRUE);
   curl_setopt($ch, CURLOPT_POSTFIELDS, 'status='.urlencode($text));
   curl_setopt($ch, CURLOPT_USERPWD, file_get_contents(dirname(__FILE__).'/twitter.txt'));
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
   $response = curl_exec($ch);
   curl_close($ch);
	return $response;
}//end send_tweet

?>
