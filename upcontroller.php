<?php

//testing
require 'utils.php';
require 'connect.php';

// the response will be a JSON object
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");$json = array();
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
$upId = "";
if (array_key_exists('upid', $decoded_params)){
  $upId =  $decoded_params['upid'];
}
$userId = "";
if (array_key_exists('userid', $decoded_params)){
  $userId =  $decoded_params['userid'];
}
$leId = "";
if (array_key_exists('leid', $decoded_params)){
  $leId =  $decoded_params['leid'];
}
$notes = "";
if (array_key_exists('notes', $decoded_params)){
  $notes =  $decoded_params['notes'];
}
if ($action == "addOrEditUserProgress"){
$args = array();
if (IsNullOrEmpty($upId)){
 $sql = "INSERT INTO user_progress (up_id,user_id,le_id,notes) VALUES ( ?,?,?,?);";
array_push($args, $upId);
array_push($args, $userId);
array_push($args, $leId);
array_push($args, $notes);
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
$sql = "UPDATE user_progress SET user_id = ?,le_id = ?,notes = ? WHERE up_id = ?; ";
array_push($args, $userId);
array_push($args, $leId);
array_push($args, $notes);
array_push($args, $upId);
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
} else if ($action == "deleteUserProgress"){
$sql = "DELETE FROM user_progress WHERE up_id = ?";
$args = array();
array_push($args, $upId);
if (!IsNullOrEmpty($upId)){
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
} else if ($action == "getUserProgress"){
    $args = array();
    $sql = "SELECT * FROM user_progress";
 $first = true;
if (!IsNullOrEmpty($upId)){
      if ($first) {
        $sql .= " WHERE up_id = ? ";
        $first = false;
      }else{
        $sql .= " AND up_id = ? ";
      }
      array_push ($args, $upId);
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
if (!IsNullOrEmpty($leId)){
      if ($first) {
        $sql .= " WHERE le_id = ? ";
        $first = false;
      }else{
        $sql .= " AND le_id = ? ";
      }
      array_push ($args, $leId);
    }
if (!IsNullOrEmpty($notes)){
      if ($first) {
        $sql .= " WHERE notes = ? ";
        $first = false;
      }else{
        $sql .= " AND notes = ? ";
      }
      array_push ($args, $notes);
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
        $json['user_progress'][] = $row1;
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
