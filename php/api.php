<?php

//error_reporting(0);

require 'fb/facebook.php';

//ob_start("ob_gzhandler");

//Set no caching
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate, private, max-age=0"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

//$db = mysqli_connect('roskilde.robert-daly.com', 'dalyr95', 'internet1', 'roskilde');
$db = mysqli_connect($_SERVER['RDS_HOSTNAME'], $_SERVER['RDS_USERNAME'], $_SERVER['RDS_PASSWORD'], $_SERVER['RDS_DB_NAME'], $_SERVER['RDS_PORT']);

if (mysqli_connect_errno()) {
	header("HTTP/1.1 500 Internal Server Error");

	$echo = (object) array('status'=>false, 'result'=>(object) array('error'=>'dbconnectfailure'));
	echo json_encode($echo);
	exit;
}

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
        $id     = mysqli_real_escape_string($db, $_POST['fb_id']);
        $result = getUser($id);
        if ($result) { echo 'Fuck Yeah';} else {echo 'No';}
        
        $num_rows = ($result) ? mysqli_num_rows($result) : 0;
        
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
            $result = mysqli_query($db, $query);
			
			if ($result) {
        		$result = getUser($id);
				$user	= parseUser($result);

				$query  = "INSERT INTO `roskilde`.`friends` (`fb_id`,`name`) VALUES (".$user['fb_id'].",'".$user['name']."')";
				$result = mysqli_query($db, $query);
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
		$value['user_id']	= mysqli_real_escape_string($db, $_POST['event']['user_id']);
		$value['fb_id']		= mysqli_real_escape_string($db, $_POST['event']['fb_id']);
		$value['user']		= mysqli_real_escape_string($db, $_POST['event']['user']);
		$value['user']		= "'".$value['user']."'";


		if (array_key_exists("message", $_POST['event'])) {
			$field[3]			= '`message`';
			$value['message']	= "'".mysqli_real_escape_string($db, $_POST['event']['message'])."'";	
		}

		if (array_key_exists("title", $_POST['event'])) {
			$field[4]			= '`title`';
			$value['title']	= "'".mysqli_real_escape_string($db, $_POST['event']['title'])."'";	
		}
		
		foreach($_POST['event'] as $key => $val) {
			if (in_array($key, $values)) {
				if (!empty($val)) {
					array_push($field, "`".$key."`");
					array_push($value, mysqli_real_escape_string($db, $val));
				}

			}
		}
		

        array_push($field, "`timestamp`");
        array_push($value, $date);

		$field = implode(",", $field);
		$value = implode(",", $value);

		$query  = "INSERT INTO `roskilde`.`".$table."` (".$field.") VALUES (".$value.")";
		$result = mysqli_query($db, $query);

		$status = ($result) ? true : false;
	}

	
	if ($_POST['action'] === 'friends') {
		$status = parseFBfriends($_POST['fb_id'], $_POST['friends']);
	}


	if ($_POST['action'] === 'findFriends') {
		if ($_POST['fb_id']) {

			getFBfriends();

			$checkins	= array();

			$query  = "SELECT `friend_ids` FROM `roskilde`.`friends` WHERE `fb_id`=".mysqli_real_escape_string($db, $_POST['fb_id']);
			$result = mysqli_query($db, $query);
			
			$num_rows = mysqli_num_rows($result);
			
			if ($num_rows > 0) {
	
				while ($row = mysqli_fetch_assoc($result)) {
					$friends = $row;
				}
	
				$friends = json_decode($friends['friend_ids']);

				if ($friends === null) {
					// Just double check FB didn't fail the first time
					getFBfriends();
				}
				
				$friendIds = array();
				
				foreach ($friends as $value) {
					array_push($friendIds, $value->id);
				}
	
				$query  = "SELECT * FROM `roskilde`.`checkins` WHERE `fb_id` in(".implode(",", $friendIds).") AND `timestamp` > ".(strtotime("2 days ago") * 1000)." ORDER BY `id` DESC";
				$result = mysqli_query($db, $query);
				
				if ($result) {
					$ids = array();

					$num_rows = mysqli_num_rows($result);

					if ($num_rows > 0) {			
						while ($row = mysqli_fetch_assoc($result)) {
							if (!in_array($row['fb_id'], $ids)) {
								$checkins[] = $row;
								array_push($ids, $row['fb_id']);
							}
						}
						$status	= ($result) ? true : false; 
						$data 	= $checkins;
					} else {
						$status	= true;
					}
				} else {
					$status	= true;
				}
			} else {
				$status	= true;
			}
		}
	}
	
	if ($_POST['action'] === 'getLocations' || $_POST['action'] === 'getEvents') {
    	if ($_POST['action'] === 'getLocations') {
        	$query  = "SELECT * FROM `roskilde`.`locations` WHERE `fb_id`=".mysqli_real_escape_string($db, $_POST['fb_id']);
		} else {
    		$query  = "SELECT * FROM `roskilde`.`events` WHERE `end` > ".(strtotime("now") * 1000);
		}
		
		$result = mysqli_query($db, $query);
		
		$num_rows = mysqli_num_rows($result);
		
		$status = ($result) ? true : false;
		
		if ($num_rows > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
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
					$val = (is_numeric($val)) ? $val : "'".mysqli_real_escape_string($db, $val)."'";
					array_push($value, $val);
				}

			}
		}

        array_push($field, "`fstart`");
        array_push($field, "`fend`");
        array_push($value, mysqli_real_escape_string($db, roundTime($_POST['event']['start'])));
        array_push($value, mysqli_real_escape_string($db, roundTime($_POST['event']['end'])));

		$field = implode(",", $field);
		$value = implode(",", $value);

		$query  = "INSERT INTO `roskilde`.`events` (".$field.") VALUES (".$value.")";
		$result = mysqli_query($db, $query);

		$status = ($result) ? true : false;
	}


	if ($_POST['action'] === 'backupSchedule') {
		$schedule 	= mysqli_real_escape_string($db, $_POST['data']);
		$query  	= "INSERT INTO `roskilde`.`schedules` (`fb_id`, `schedule`) VALUES (".$FBuser.", '".$schedule."')";
		$query 		.= " ON DUPLICATE KEY UPDATE `schedule`='".$schedule."';";
		$result 	= mysqli_query($db, $query);

		if ($result) {
			$query  	= "UPDATE `roskilde`.`users` SET `backup` = '1' WHERE `users`.`fb_id`=".$FBuser;
			$result 	= mysqli_query($db, $query);
		}

		$status = ($result) ? true : false;
	}


	if ($_POST['action'] === 'restoreSchedule') {
		$query  	= "SELECT `schedule` FROM `roskilde`.`schedules` WHERE `fb_id`=".$FBuser;
		$result 	= mysqli_query($db, $query);

		if ($result) {
			$data = mysqli_fetch_row($result);
		}

		$status = ($result) ? true : false;
	}
	
	$echo = (object) array('status'=>$status, 'result'=>$data);
	echo json_encode($echo);
}


function getUser($id) {
	global $db;
	$result = mysqli_query($db, "SELECT * FROM `users` where fb_id=".$id);
	return $result;
}


function parseUser($result) {
	while ($row = mysqli_fetch_assoc($result)) {
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
	global $db;
	$json 		= json_encode($data);
	$safeJSON	= mysqli_real_escape_string($db, $json);

	$query = "INSERT INTO `roskilde`.`friends` (`fb_id`, `friend_ids`) VALUE (".mysqli_real_escape_string($db, $id).",'".$safeJSON."')";
	$query .= "ON DUPLICATE KEY UPDATE `friend_ids`='".$safeJSON."';";

	$result = mysqli_query($db, $query);
	
	$status = ($result) ? true : false;

	return $status;
}


function getFBfriends() {
	global $facebook;
	global $FBuser;

	$status = true;

	if ($FBuser) {
		try {
			$friends = $facebook->api('/me/friends');
			parseFBfriends($FBuser, $friends['data']);
		} catch (FacebookApiException $e) {
			$status = false;
		}
	} else {
		$status = false;
	}
}
