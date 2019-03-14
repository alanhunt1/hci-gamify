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
$friendId = "";
if (array_key_exists('friendid', $decoded_params)){
  $friendId =  $decoded_params['friendid'];
}
$userId = "";
if (array_key_exists('userid', $decoded_params)){
  $userId =  $decoded_params['userid'];
}
$userFriendId = "";
if (array_key_exists('userfriendid', $decoded_params)){
  $userFriendId =  $decoded_params['userfriendid'];
}
if ($action == "addOrEditFriends"){
$args = array();
if (IsNullOrEmpty($friendId)){
 $sql = "INSERT INTO friends (friend_id,user_id,user_friend_id) VALUES ( ?,?,?);";
array_push($args, $friendId);
array_push($args, $userId);
array_push($args, $userFriendId);
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
$sql = "UPDATE friends SET user_id = ?,user_friend_id = ? WHERE friend_id = ?; ";
array_push($args, $userId);
array_push($args, $userFriendId);
array_push($args, $friendId);
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
} else if ($action == "deleteFriends"){
$sql = "DELETE FROM friends WHERE friend_id = ?";
$args = array();
array_push($args, $friendId);
if (!IsNullOrEmpty($friendId)){
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
} else if ($action == "getFriends"){
    $args = array();
    $sql = "SELECT * FROM friends";
 $first = true;
if (!IsNullOrEmpty($friendId)){
      if ($first) {
        $sql .= " WHERE friend_id = ? ";
        $first = false;
      }else{
        $sql .= " AND friend_id = ? ";
      }
      array_push ($args, $friendId);
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
if (!IsNullOrEmpty($userFriendId)){
      if ($first) {
        $sql .= " WHERE user_friend_id = ? ";
        $first = false;
      }else{
        $sql .= " AND user_friend_id = ? ";
      }
      array_push ($args, $userFriendId);
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
        $json['friends'][] = $row;
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
