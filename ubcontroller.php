<?php

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
  $decoded_params = json_decode($json_params, TRUE);
  $action = $decoded_params['action'];
  $json['action'] = $action;
  // uncomment the following line if you want to turn PHP error reporting on for debug - note, this will break the JSON response
  //ini_set('display_errors', 1); error_reporting(-1);
$ubId = "";
if (array_key_exists('ubid', $decoded_params)){
  $ubId =  $decoded_params['ubid'];
}
$userId = "";
if (array_key_exists('userid', $decoded_params)){
  $userId =  $decoded_params['userid'];
}
$badgeId = "";
if (array_key_exists('badgeid', $decoded_params)){
  $badgeId =  $decoded_params['badgeid'];
}
if ($action == "addOrEditUserBadges"){
$args = array();
if (IsNullOrEmpty($ubId)){
 $sql = "INSERT INTO user_badges (ub_id,user_id,badge_id) VALUES ( ?,?,?);";
array_push($args, $ubId);
array_push($args, $userId);
array_push($args, $badgeId);
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
$sql = "UPDATE user_badges SET user_id = ?,badge_id = ? WHERE ub_id = ?; ";
array_push($args, $userId);
array_push($args, $badgeId);
array_push($args, $ubId);
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
} else if ($action == "deleteUserBadges"){
$sql = "DELETE FROM user_badges WHERE ub_id = ?";
$args = array();
array_push($args, $ubId);
if (!IsNullOrEmpty($ubId)){
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
} else if ($action == "getUserBadges"){
    $args = array();
    $sql = "SELECT * FROM user_badges";
 $first = true;
if (!IsNullOrEmpty($ubId)){
      if ($first) {
        $sql .= " WHERE ub_id = ? ";
        $first = false;
      }else{
        $sql .= " AND ub_id = ? ";
      }
      array_push ($args, $ubId);
    }
if (!IsNullOrEmpty($userId)){
      if ($first) {
        $sql .= " WHERE user_id = ? ";
        $first = false;
      }else{
        $sql .= " AND user_id = ? ";
      }
      array_push ($args, $userId);
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
    $json['SQL'] = $sql; 
    try{
      $statement = $conn->prepare($sql);
      $statement->setFetchMode(PDO::FETCH_ASSOC);
      $statement->execute($args);
      $result = $statement->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result as $row ) {
        $json['user_badges'][] = $row;
    }
} else { 
    $json['Exeption'] = "Unrecognized Action ";
} 
} 
else{
  $json['Exeption'] = "Invalid JSON on Inbound Request";
} 
echo json_encode($json);
$conn = null; 
?>
