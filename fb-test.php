<?php

require 'php/fb/facebook.php';

$facebook = new Facebook(array(
	'appId'  => '357860537664045',
	'secret' => 'a8a4b0405be32159babf93ab054f35d2'
));

echo date('h:i:s a', time())."<br/>";

$user = $facebook->getUser();

echo "Facebook User: ".$user."<br/>";
echo "Cookie: ".$_COOKIE["facebook_id"]."<br/>";
echo ($user === $_COOKIE["facebook_id"])."<br/>";

echo date('h:i:s a', time())."<br/>";

$indexes = array("#" => 0, "a" => 1, "b" => 15, "c" => 33, "d" => 47, "e" => 58, "f" => 63, "g" => 72, "h" => 80, "i" => 87, "j" => 90, "k" => 93, "l" => 104, "m" => 109, "n" => 125, "o" => 130, "p" => 134, "r" => 140, "s" => 151, "t" => 171, "u" => 182, "v" => 183, "w" => 187, "y" => 194);
print_r(array_flip($indexes));