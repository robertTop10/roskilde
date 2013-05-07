<?php

ob_start("ob_gzhandler");

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

$ch = curl_init("http://search.twitter.com/search.json?q=from:orangefeeling&rpp=50&result_type=recent&include_entities=true");

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

$response = curl_exec($ch);
curl_close($ch);

$response = json_decode($response);

foreach ($response->results as &$value) {
	$entities = $value->entities;
	//echo count($entities->hashtags).' - '.count($entities->urls).'<br/>';
	unset($value->entities->user_mentions);
	if (count($entities->hashtags) === 0 && count($entities->urls) === 0) {
		unset($value->entities);
	}
}

if (isset($response->results)) { echo json_encode($response->results); }
