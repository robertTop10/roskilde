<?php

ob_start("ob_gzhandler");
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

function xmlToArray($xml, $options = array()) {
    $defaults = array(
        'namespaceSeparator' => ':',//you may want this to be something other than a colon
        'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
        'alwaysArray' => array(),   //array of xml tag names which should always become arrays
        'autoArray' => true,        //only create arrays for tags which appear more than once
        'textContent' => '$',       //key used for the text content of elements
        'autoText' => true,         //skip textContent key if node has no attributes or child nodes
        'keySearch' => false,       //optional search and replace on tag and attribute names
        'keyReplace' => false       //replace values for above search values (as passed to str_replace())
    );
    $options = array_merge($defaults, $options);
    $namespaces = $xml->getDocNamespaces();
    $namespaces[''] = null; //add base (empty) namespace
 
    //get attributes from all namespaces
    $attributesArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
            //replace characters in attribute name
            if ($options['keySearch']) $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
            $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
            $attributesArray[$attributeKey] = (string)$attribute;
        }
    }
 
    //get child nodes from all namespaces
    $tagsArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->children($namespace) as $childXml) {
            //recurse into child nodes
            $childArray = xmlToArray($childXml, $options);
            list($childTagName, $childProperties) = each($childArray);
 
            //replace characters in tag name
            if ($options['keySearch']) $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
            //add namespace prefix, if any
            if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
 
            if (!isset($tagsArray[$childTagName])) {
                //only entry with this key
                //test if tags of this type should always be arrays, no matter the element count
                $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                        ? array($childProperties) : $childProperties;
            } elseif (
                is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                === range(0, count($tagsArray[$childTagName]) - 1)
            ) {
                //key already exists and is integer indexed array
                $tagsArray[$childTagName][] = $childProperties;
            } else {
                //key exists so convert to integer indexed array with previous value in position 0
                $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
            }
        }
    }
 
    //get text content of node
    $textContentArray = array();
    $plainText = trim((string)$xml);
    if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;
 
    //stick it all together
    $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;
 
    //return node as array
    return array(
        $xml->getName() => $propertiesArray
    );
}

$lang = (isset($_GET["dn"]) && $_GET["dn"] === 'true') ? 'dk' : 'uk';

$xmlNode = simplexml_load_file('lineup2012-'.$lang.'.xml');
$arrayData = xmlToArray($xmlNode);

date_default_timezone_set('Europe/Copenhagen');

$stages = array();

foreach ($arrayData['bandPreview']['item'] as &$value) {
	$stage = $value['scene'];
	if (is_string($stage)) {
		array_push($stages, $stage);
	}
}

$stages     = array_unique($stages);
unset($stages[array_search('Apollo Countdown', $stages)]);
unset($stages[array_search('Pavilion Junior', $stages)]);
$stageNames = array_values($stages);
$stages     = array_fill_keys($stages, array());


// This year
//$days 	= (object) array("29" => $stages, "30" => $stages, "1" => $stages, "2" => $stages, "3" => $stages, "4" => $stages, "5" => $stages, "6" => $stages, "7" => $stages );
// Last year
$days 	= array("30" => $stages, "1" => $stages, "2" => $stages, "3" => $stages, "4" => $stages, "5" => $stages, "6" => $stages, "7" => $stages, "8" => $stages );

foreach ($arrayData['bandPreview']['item'] as &$value) {
	if (is_string($value['timestamp'])) {

		$t = $value['original_timestamp'];

		//echo date('H', $t).' --- '.$value['tidspunkt'].' --- '.(date('H', $t) < 8).' --- '.date('j', $t).' --- '.date('j', strtotime($value['tidspunkt'].' -1 day')).PHP_EOL;
		//$key	= (date('H', $t) < 8) ? date('j', strtotime($value['tidspunkt'].' -1 day')) : date('j', $t);
		$key	= (date('H', $t) < 8) ? date('j',  $value['original_timestamp'] - 86400) : date('j', $t);
		$stage	= $value['scene'];
		
		$stage  = ($stage === 'Apollo Countdown') ? 'Apollo' : $stage;
		$stage  = ($stage === 'Pavilion Junior')  ? 'Pavilion' : $stage;

		if (is_string($stage)) {
			//array_push($days[$key][$stage], $value);
			$days[$key][$stage][$value['original_timestamp']] = $value;
		}
	}
	
	/*
	// Dealing with timestamps
	$ts = $value['timestamp'];
	if (is_string($ts)) {
		$t = strtotime($value['tidspunkt']);
		//echo $value['timestamp'].' === '.$t.' === '.$value['tidspunkt']." === ".date('D', $t)." === ".date('H', $t)." === ".(date('H', $t) < 8)." === ".date('j', strtotime($value['tidspunkt'].' -1 day')).PHP_EOL;
	}
	*/

}
/*
foreach ($days as $key=>$day) {
	$month = ($key > 15) ? 'Jun' : 'Jul';
	$days[$key.$month] = $days[$key];
	unset($days[$key]);
}
*/

foreach ($days as $key=>$day) {
    $artists = 0;
	foreach ($day as &$stage) {
        $artists = $artists + count($stage);
		/*
		usort($stage, function($a, $b) {
			return $a['timestamp'] - $b['timestamp'];
		});
		*/
		ksort($stage);		
	}

    if ($artists === 0) {
        unset($days[$key]);
    }
}

$result = (object) array("results"=>$days, "keys"=>array_keys($days), "stages"=>$stageNames);

echo json_encode($result);