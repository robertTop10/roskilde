<?php

require 'fb/facebook.php';

ob_start("ob_gzhandler");

//Set no caching
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate, private, max-age=0"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

mysql_connect('roskilde.robert-daly.com', 'dalyr95', 'internet1') or die('Unable to connect to databases');
mysql_select_db('roskilde') or die('Unable to select database "roskilde"');

if ($_POST) {

	$facebook = new Facebook(array(
		'appId'  => '357860537664045',
		'secret' => 'a8a4b0405be32159babf93ab054f35d2'
	));

	// Get User ID
	$FBuser = $facebook->getUser();

	if (!$_COOKIE["roskildeapp"] || $_COOKIE["roskildeapp"] !== $FBuser) {
		header('HTTP/1.0 401 Unauthorized');

		$echo = (object) array('status'=>false, 'result'=>(object) array('error'=>'authfailure'));
		echo json_encode($echo);
		exit;
	}
	
	$status = false;
	$data	= array();

	$date 	= new DateTime();
	$date 	= $date->getTimestamp() * 1000;
	
    // Check if user exists
    if ($_POST['action'] === 'auth' && $_POST['fb_id']) {
        $id     = mysql_real_escape_string($_POST['fb_id']);
        $result = getUser($id);
        
        $num_rows = mysql_num_rows($result);
        
        if ($num_rows === 0) {
            // Create new user
            $values = array('fb_id','name','first_name','last_name','gender','locale','link','languages','timezone','username','verified','hometown','location');
            $field  = array();
            $value  = array();
			
            foreach($_POST as $key => $val) {
                if (in_array($key, $values)) {
                    array_push($field, "`".$key."`");
                    $val = (is_array($val)) ? json_encode($val) : $val;
                    array_push($value, "'".$val."'");
                }
            }
            
            $field  = implode(",", $field);
            $value  = implode(",", $value);
            
            $query  = "INSERT INTO `roskilde`.`users` (".$field.") VALUES (".$value.")";
            $result = mysql_query($query);
			
			if ($result) {
        		$result = getUser($id);
				$user	= parseUser($result);

				$query  = "INSERT INTO `roskilde`.`friends` (`fb_id`,`name`) VALUES (".$user['fb_id'].",'".$user['name']."')";
				$result = mysql_query($query);
			}
        } else {
			$user = parseUser($result);
		}

		$status = ($result) ? true : false;

		if ($status) {
			$jsonRows = array('languages', 'hometown', 'location');
			foreach ($jsonRows as &$value) {
				$user[$value] = json_decode($user[$value]);
			}
		}
		
		$data = ($user) ? $user : $data;
    }
	
	if ($_POST['action'] === 'createCheckIn' || $_POST['action'] === 'createLocation') {
		$table 	= ($_POST['action'] === 'createCheckIn') ? 'checkins' : 'locations'; 
		$values = array('latitude','longitude','accuracy');
		
		$field  = array();
		$value 	= array();
		
		$field[0]			= '`user_id`';
		$field[1]			= '`fb_id`';
		$field[2]			= '`user`';
		$value['user_id']	= mysql_real_escape_string($_POST['event']['user_id']);
		$value['fb_id']		= mysql_real_escape_string($_POST['event']['fb_id']);
		$value['user']		= mysql_real_escape_string($_POST['event']['user']);
		$value['user']		= "'".$value['user']."'";


		if (array_key_exists("message", $_POST['event'])) {
			$field[3]			= '`message`';
			$value['message']	= "'".mysql_real_escape_string($_POST['event']['message'])."'";	
		}

		if (array_key_exists("title", $_POST['event'])) {
			$field[4]			= '`title`';
			$value['title']	= "'".mysql_real_escape_string($_POST['event']['title'])."'";	
		}
		
		foreach($_POST['event'] as $key => $val) {
			if (in_array($key, $values)) {
				if (!empty($val)) {
					array_push($field, "`".$key."`");
					array_push($value, mysql_real_escape_string($val));
				}

			}
		}
		

        array_push($field, "`timestamp`");
        array_push($value, $date);

		$field = implode(",", $field);
		$value = implode(",", $value);

		$query  = "INSERT INTO `roskilde`.`".$table."` (".$field.") VALUES (".$value.")";
		$result = mysql_query($query);

		$status = ($result) ? true : false;
	}

	
	if ($_POST['action'] === 'friends') {
		$status = parseFBfriends($_POST['fb_id'], $_POST['friends']);
	}


	if ($_POST['action'] === 'findFriends') {
		if ($_POST['fb_id']) {

			getFBfriends();

			$checkins	= array();

			$query  = "SELECT `friend_ids` FROM `roskilde`.`friends` WHERE `fb_id`=".mysql_real_escape_string($_POST['fb_id']);
			$result = mysql_query($query);
			
			$num_rows = mysql_num_rows($result);
			
			if ($num_rows > 0) {
	
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					$friends = $row;
				}
	
				$friends = json_decode($friends['friend_ids']);
				
				$friendIds = array();
				
				foreach ($friends as $value) {
					array_push($friendIds, $value->id);
				}
	
				$query  = "SELECT * FROM `roskilde`.`checkins` WHERE `fb_id` in(".implode(",", $friendIds).") ORDER BY `id` DESC";
				$result = mysql_query($query);
				
				$ids = array();
				
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					if (!in_array($row['fb_id'], $ids)) {
						$checkins[] = $row;
						array_push($ids, $row['fb_id']);
					}
				}
				
				$status	= ($result) ? true : false; 
				$data 	= $checkins;
			} else {
				$status	= false;
			}
		}
	}
	
	if ($_POST['action'] === 'getLocations' || $_POST['action'] === 'getEvents') {
    	if ($_POST['action'] === 'getLocations') {
        	$query  = "SELECT * FROM `roskilde`.`locations` WHERE `fb_id`=".mysql_real_escape_string($_POST['fb_id']);
		} else {
    		$query  = "SELECT * FROM `roskilde`.`events`";
		}
		
		$result = mysql_query($query);
		
		$num_rows = mysql_num_rows($result);
		
		$status = ($result) ? true : false;
		
		if ($num_rows > 0) {
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$data[] = $row;
			}
		}

	}
	
	if ($_POST['action'] === 'createEvent') {
		$values = array('name','description','start','end','latitude', 'longitude', 'user_id');

		$field  = array();
		$value 	= array();
	
		foreach($_POST['event'] as $key => $val) {
			if (in_array($key, $values)) {
				if (!empty($val)) {
					array_push($field, "`".$key."`");
					$val = (is_numeric($val)) ? $val : "'".mysql_real_escape_string($val)."'";
					array_push($value, $val);
				}

			}
		}

        array_push($field, "`fstart`");
        array_push($field, "`fend`");
        array_push($value, mysql_real_escape_string(roundTime($_POST['event']['start'])));
        array_push($value, mysql_real_escape_string(roundTime($_POST['event']['end'])));

		$field = implode(",", $field);
		$value = implode(",", $value);

		$query  = "INSERT INTO `roskilde`.`events` (".$field.") VALUES (".$value.")";
		$result = mysql_query($query);

		$status = ($result) ? true : false;
	}
	
	$echo = (object) array('status'=>$status, 'result'=>$data);
	echo json_encode($echo);
}


function getUser($id) {
	$result = mysql_query("SELECT * FROM `users` where fb_id=".$id);
	return $result;
}


function parseUser($result) {
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$user = $row;
	}
	
	return $user;
}


function roundTime($timestamp) {
	$time = $timestamp;
	$time = $time / 1000;
	$newtime = date('D d M Y', $time);
    $minutes = date('i', $time);
    $minutes = ($minutes - ($minutes % 15) < 10) ? '00' : $minutes - ($minutes % 15);
    $newtime = $newtime.' '.date('H', $time).':'.$minutes.':00';
    return strtotime($newtime) * 1000;
}


function parseFBfriends($id, $data) {
	$json 		= json_encode($data);
	$safeJSON	= mysql_real_escape_string($json);

	$query = "INSERT INTO `roskilde`.`friends` (`fb_id`, `friend_ids`) VALUE (".mysql_real_escape_string($id).",'".$safeJSON."')";
	$query .= "ON DUPLICATE KEY UPDATE `friend_ids`='".$safeJSON."';";
	$result = mysql_query($query);
	
	$status = ($result) ? true : false;

	return $status;
}


function getFBfriends() {
	$status = true;

	if ($FBuser) {
		try {
			$friends = $facebook->api('/me/friends');
			parseFBfriends($user, $friends['data']);
		} catch (FacebookApiException $e) {
			$status = false;
		}
	} else {
		$status = false;
	}
}
