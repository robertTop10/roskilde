<?php

ob_start("ob_gzhandler");

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

$my_file = 'locations.json';
$handle = fopen($my_file, 'r') or die('Cannot open file:  '.$my_file);
$json 	= json_decode(fread($handle, filesize($my_file)));
fclose($handle);

$cat 	= array();

foreach ($json->locations as &$value) {
	switch ($value->category->id) {
	    case 1:
	        $value->category->id = 3;	// Shuttlebus to Lake
	        break;
	    case 6:
	        $value->category->id = 2;	// First Aid to Pharmacy
	        break;
	    case 4:
	        $value->category->id = 3;	// Fishing Lake to Lake
	        break;
	    case 5:
	        $value->category->id = 3;	// Radio to Lake
	        break;
	    case 9:
	        $value->category->id = 3;	// Information to Lake
	        break;
	    case 10:
	        $value->category->id = 3;	// Internet Cafe to Lake
	        break;
	    case 17:
	        $value->category->id = 3;	// Laudromat to Lake
	        break;
	    case 13:
	        $value->category->id = 12;	// Handicap toilets to Handicap Camping
	        break;
	    case 15:
	        $value->category->id = 14;	// Showers
	        break;
	    case 20:
	        $value->category->id = 19;	// Girls pisser to toilets
	        break;
	    case 24:
	        $value->category->id = 21;	// Tuborg Bar to Bar
	        break;
	}

	if (array_key_exists($value->category->id, $cat)) {
		array_push($cat[$value->category->id], $value);
	} else {
		$cat[$value->category->id] = array($value);
	}
}

echo json_encode($cat);
