<?php

//testing
require 'utils.php';
require 'connect.php';

// the response will be a JSON object
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
$json = array();
// pull the input, which should be in the form of a JSON object
$json_params = file_get_contents('php://input');
// check to make sure that the JSON is in a valid format
if (isValidJSON($json_params)){
 //load in all the potential parameters.  These should match the database columns for the objects. 
  $conn = getDbConnection();  $decoded_params = json_decode($json_params, TRUE);
  $action = $decoded_params['action'];
  $json['action'] = $action;
  // uncomment the following line if you want to turn PHP error reporting on for debug - note, this will break the JSON response
  //ini_set('display_errors', 1); error_reporting(-1);
$challengeId = "";
if (array_key_exists('challengeid', $decoded_params)){
  $challengeId =  $decoded_params['challengeid'];
}
$challengeName = "";
if (array_key_exists('challengename', $decoded_params)){
  $challengeName =  $decoded_params['challengename'];
}
$challengeDescription = "";
if (array_key_exists('challengedescription', $decoded_params)){
  $challengeDescription =  $decoded_params['challengedescription'];
}
$challengeStart = "";
if (array_key_exists('challengestart', $decoded_params)){
  $challengeStart =  $decoded_params['challengestart'];
}
$challengeEnd = "";
if (array_key_exists('challengeend', $decoded_params)){
  $challengeEnd =  $decoded_params['challengeend'];
}
$challengeType = "";
if (array_key_exists('challengetype', $decoded_params)){
  $challengeType =  $decoded_params['challengetype'];
}
$challengeDuration = "";
if (array_key_exists('challengeduration', $decoded_params)){
  $challengeDuration =  $decoded_params['challengeduration'];
}
$badgeId = "";
if (array_key_exists('badgeid', $decoded_params)){
  $badgeId =  $decoded_params['badgeid'];
}
$points = "";
if (array_key_exists('points', $decoded_params)){
  $points =  $decoded_params['points'];
}
$linkedId = "";
if (array_key_exists('linkedid', $decoded_params)){
  $linkedId =  $decoded_params['linkedid'];
}
if ($action == "addOrEditChallenges"){
$args = array();
if (IsNullOrEmpty($challengeId)){
 $sql = "INSERT INTO challenges (challenge_id,challenge_name,challenge_description,challenge_start,challenge_end,challenge_type,challenge_duration,badge_id,points,linked_id) VALUES ( ?,?,?,?,?,?,?,?,?,?);";
array_push($args, $challengeId);
array_push($args, $challengeName);
array_push($args, $challengeDescription);
array_push($args, $challengeStart);
array_push($args, $challengeEnd);
array_push($args, $challengeType);
array_push($args, $challengeDuration);
array_push($args, $badgeId);
array_push($args, $points);
array_push($args, $linkedId);
try{
$statement = $conn->prepare($sql);
$statement->execute($args);
$last_id = $conn->lastInsertId();
$json['Record Id'] = $last_id;
$json['Status'] = "SUCCESS - Inserted Id $last_id";
}catch (Exception $e) { 
    $json['Exception'] =  $e->getMessage();
}
}else{
$sql = "UPDATE challenges SET challenge_name = ?,challenge_description = ?,challenge_start = ?,challenge_end = ?,challenge_type = ?,challenge_duration = ?,badge_id = ?,points = ?,linked_id = ? WHERE challenge_id = ?; ";
array_push($args, $challengeName);
array_push($args, $challengeDescription);
array_push($args, $challengeStart);
array_push($args, $challengeEnd);
array_push($args, $challengeType);
array_push($args, $challengeDuration);
array_push($args, $badgeId);
array_push($args, $points);
array_push($args, $linkedId);
array_push($args, $challengeId);
try{
$statement = $conn->prepare($sql);
$statement->execute($args);
$count = $statement->rowCount();
if ($count > 0){
$json['Status'] = "SUCCESS - Updated $count Rows";
} else {
$json['Status'] = "ERROR - Updated 0 Rows - Check for Valid Ids ";
}
}catch (Exception $e) { 
    $json['Exception'] =  $e->getMessage();
}
$json['Action'] = $action;
}
} else if ($action == "deleteChallenges"){
$sql = "DELETE FROM challenges WHERE challenge_id = ?";
$args = array();
array_push($args, $challengeId);
if (!IsNullOrEmpty($challengeId)){
try{
  $statement = $conn->prepare($sql);
  $statement->execute($args);
$count = $statement->rowCount();
if ($count > 0){
$json['Status'] = "SUCCESS - Deleted $count Rows";
} else {
$json['Status'] = "ERROR - Deleted 0 Rows - Check for Valid Ids ";
}
}catch (Exception $e) { 
    $json['Exception'] =  $e->getMessage();
}
} else {
$json['Status'] = "ERROR - Id is required";
}
$json['Action'] = $action;
} else if ($action == "getChallenges"){
    $args = array();
    $sql = "SELECT * FROM challenges";
 $first = true;
if (!IsNullOrEmpty($challengeId)){
      if ($first) {
        $sql .= " WHERE challenge_id = ? ";
        $first = false;
      }else{
        $sql .= " AND challenge_id = ? ";
      }
      array_push ($args, $challengeId);
    }
if (!IsNullOrEmpty($challengeName)){
      if ($first) {
        $sql .= " WHERE challenge_name = ? ";
        $first = false;
      }else{
        $sql .= " AND challenge_name = ? ";
      }
      array_push ($args, $challengeName);
    }
if (!IsNullOrEmpty($challengeDescription)){
      if ($first) {
        $sql .= " WHERE challenge_description = ? ";
        $first = false;
      }else{
        $sql .= " AND challenge_description = ? ";
      }
      array_push ($args, $challengeDescription);
    }
if (!IsNullOrEmpty($challengeStart)){
      if ($first) {
        $sql .= " WHERE challenge_start = ? ";
        $first = false;
      }else{
        $sql .= " AND challenge_start = ? ";
      }
      array_push ($args, $challengeStart);
    }
if (!IsNullOrEmpty($challengeEnd)){
      if ($first) {
        $sql .= " WHERE challenge_end = ? ";
        $first = false;
      }else{
        $sql .= " AND challenge_end = ? ";
      }
      array_push ($args, $challengeEnd);
    }
if (!IsNullOrEmpty($challengeType)){
      if ($first) {
        $sql .= " WHERE challenge_type = ? ";
        $first = false;
      }else{
        $sql .= " AND challenge_type = ? ";
      }
      array_push ($args, $challengeType);
    }
if (!IsNullOrEmpty($challengeDuration)){
      if ($first) {
        $sql .= " WHERE challenge_duration = ? ";
        $first = false;
      }else{
        $sql .= " AND challenge_duration = ? ";
      }
      array_push ($args, $challengeDuration);
    }
if (!IsNullOrEmpty($badgeId)){
      if ($first) {
        $sql .= " WHERE badge_id = ? ";
        $first = false;
      }else{
        $sql .= " AND badge_id = ? ";
      }
      array_push ($args, $badgeId);
    }
if (!IsNullOrEmpty($points)){
      if ($first) {
        $sql .= " WHERE points = ? ";
        $first = false;
      }else{
        $sql .= " AND points = ? ";
      }
      array_push ($args, $points);
    }
if (!IsNullOrEmpty($linkedId)){
      if ($first) {
        $sql .= " WHERE linked_id = ? ";
        $first = false;
      }else{
        $sql .= " AND linked_id = ? ";
      }
      array_push ($args, $linkedId);
    }
    $json['SQL'] = $sql; 
    try{
      $statement = $conn->prepare($sql);
      $statement->setFetchMode(PDO::FETCH_ASSOC);
      $statement->execute($args);
      $result = $statement->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result as $row1 ) {
        $json['challenges'][] = $row1;
    }
} else { 
    $json['Exeption'] = "Unrecognized Action ";
} 
} 
else{
  $json['Exeption'] = "Invalid JSON on Inbound Request";
} 
echo json_encode($json);
closeConnections(); 
?>
