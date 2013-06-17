<?php

ob_start("ob_gzhandler");

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

$seconds_to_cache = 250;
$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
header("Expires: $ts");
header("Pragma: cache");
header("Cache-Control: public,max-age=$seconds_to_cache");


$now = time();
$file = filemtime('twitter.json');

$my_file = 'twitter.json';

if (($now - $file) > 200) {
	/*
	$ch = curl_init("https://api.twitter.com/1.1/search/tweets.json?q=%23RF13%2BOR%2B%20from%3Aorangefeeling");

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    'Authorization: OAuth oauth_consumer_key="DC0sePOBbQ8bYdC8r4Smg",oauth_signature_method="HMAC-SHA1",oauth_timestamp="1371137176",oauth_nonce="2715804563",oauth_version="1.0",oauth_token="99524611-UuWRo4MjZpXNyGVLl1RquN6wzgv4IhHBQJCWqVSoo",oauth_signature="or%2FRLhBnzgvvheMsLT9PXvV9G8g%3D"'
	));

	$response = curl_exec($ch);
	curl_close($ch);

	$response = json_decode($response);
	*/
	function buildBaseString($baseURI, $method, $params) { $r = array(); ksort($params); foreach($params as $key=>$value){ $r[] = "$key=" . rawurlencode($value); } return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r)); }


	function buildAuthorizationHeader($oauth) { $r = 'Authorization: OAuth '; $values = array(); foreach($oauth as $key=>$value) $values[] = "$key=\"" . rawurlencode($value) . "\""; $r .= implode(', ', $values); return $r; }


	$url = "https://api.twitter.com/1.1/search/tweets.json";


	$oauth_access_token = "99524611-yJ2PbqPvKXlvcZZ7Di2iyzSnu0bM8Wm54Krjhzc6e";
	$oauth_access_token_secret = "BYwZckRFgnlB8UzF9W1oY8qO8deCJNgCqX77cDOaTdY";
	$consumer_key = "4PaawD1MA7zzkEFdm3nug";
	$consumer_secret = "j0yyYEQ2sc5DKCy8x6XaO9aJaShuANuwrgpO8drKwM0";


	$oauth = array(
		'q' => '#RF13+OR+from:orangefeeling',
		'count' => 100,

		'oauth_consumer_key' => $consumer_key,
		'oauth_nonce' => time(),
		'oauth_signature_method' =>'HMAC-SHA1',
		'oauth_token' => $oauth_access_token,
		'oauth_timestamp' => time(),
		'oauth_version' => '1.0'
	);

	asort($oauth); // secondary sort (value)
	ksort($oauth);


	$base_info = buildBaseString($url, 'GET', $oauth);
	$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
	$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
	$oauth['oauth_signature'] = $oauth_signature;

	// Make Requests
	$header = array(
		buildAuthorizationHeader($oauth), 
		'Expect:', 
		'Content-Type: application/x-www-form-urlencoded'
	);
	$options = array( CURLOPT_HTTPHEADER => $header, CURLOPT_HEADER => false, CURLOPT_URL => $url.'?q=%23RF13%2BOR%2Bfrom%3Aorangefeeling&count=100', CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false);


	$feed = curl_init();
	curl_setopt_array($feed, $options);
	$json = curl_exec($feed);
	curl_close($feed);

	$response = json_decode($json);

	foreach ($response->statuses as &$value) {
		$entities = $value->entities;
		//echo count($entities->hashtags).' - '.count($entities->urls).'<br/>';
		unset($value->entities->user_mentions);
		if (count($entities->hashtags) === 0 && count($entities->urls) === 0) {
			unset($value->entities);
		}
		$value->img = str_replace('_normal', '_bigger', $value->user->profile_image_url);

		unset($value->profile_image_url);
		unset($value->profile_image_url_https);

		$value->html = preg_replace('/(#\w+)/', '<strong>$1</strong>', $value->text);
		$value->html = preg_replace('/(@\w+)/', '<strong>$1</strong>', $value->html);
	}

	$tweets = json_encode($response->statuses);

	if (isset($response->statuses)) { echo $tweets; }

	$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
	fwrite($handle, $tweets);

} else {
	$handle = fopen($my_file, 'r') or die('Cannot open file:  '.$my_file);
	echo fread($handle, filesize($my_file));
}

fclose($handle);
