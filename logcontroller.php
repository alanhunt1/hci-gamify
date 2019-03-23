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
$logId = "";
if (array_key_exists('logid', $decoded_params)){
  $logId =  $decoded_params['logid'];
}
$time = "";
if (array_key_exists('time', $decoded_params)){
  $time =  $decoded_params['time'];
}
$userId = "";
if (array_key_exists('userid', $decoded_params)){
  $userId =  $decoded_params['userid'];
}
$category = "";
if (array_key_exists('category', $decoded_params)){
  $category =  $decoded_params['category'];
}
$logData = "";
if (array_key_exists('logdata', $decoded_params)){
  $logData =  $decoded_params['logdata'];
}
if ($action == "addOrEditLogs"){
$args = array();
if (IsNullOrEmpty($logId)){
 $sql = "INSERT INTO logs (log_id,time,user_id,category,log_data) VALUES ( ?,?,?,?,?);";
array_push($args, $logId);
array_push($args, $time);
array_push($args, $userId);
array_push($args, $category);
array_push($args, $logData);
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
$sql = "UPDATE logs SET time = ?,user_id = ?,category = ?,log_data = ? WHERE log_id = ?; ";
array_push($args, $time);
array_push($args, $userId);
array_push($args, $category);
array_push($args, $logData);
array_push($args, $logId);
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
} else if ($action == "deleteLogs"){
$sql = "DELETE FROM logs WHERE log_id = ?";
$args = array();
array_push($args, $logId);
if (!IsNullOrEmpty($logId)){
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
} else if ($action == "getLogs"){
    $args = array();
    $sql = "SELECT * FROM logs";
 $first = true;
if (!IsNullOrEmpty($logId)){
      if ($first) {
        $sql .= " WHERE log_id = ? ";
        $first = false;
      }else{
        $sql .= " AND log_id = ? ";
      }
      array_push ($args, $logId);
    }
if (!IsNullOrEmpty($time)){
      if ($first) {
        $sql .= " WHERE time = ? ";
        $first = false;
      }else{
        $sql .= " AND time = ? ";
      }
      array_push ($args, $time);
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
if (!IsNullOrEmpty($category)){
      if ($first) {
        $sql .= " WHERE category = ? ";
        $first = false;
      }else{
        $sql .= " AND category = ? ";
      }
      array_push ($args, $category);
    }
if (!IsNullOrEmpty($logData)){
      if ($first) {
        $sql .= " WHERE log_data = ? ";
        $first = false;
      }else{
        $sql .= " AND log_data = ? ";
      }
      array_push ($args, $logData);
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
        $json['logs'][] = $row1;
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
