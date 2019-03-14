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
$usId = "";
if (array_key_exists('usid', $decoded_params)){
  $usId =  $decoded_params['usid'];
}
$skillId = "";
if (array_key_exists('skillid', $decoded_params)){
  $skillId =  $decoded_params['skillid'];
}
$userId = "";
if (array_key_exists('userid', $decoded_params)){
  $userId =  $decoded_params['userid'];
}
if ($action == "addOrEditUserSkills"){
$args = array();
if (IsNullOrEmpty($usId)){
 $sql = "INSERT INTO user_skills (us_id,skill_id,user_id) VALUES ( ?,?,?);";
array_push($args, $usId);
array_push($args, $skillId);
array_push($args, $userId);
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
$sql = "UPDATE user_skills SET skill_id = ?,user_id = ? WHERE us_id = ?; ";
array_push($args, $skillId);
array_push($args, $userId);
array_push($args, $usId);
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
} else if ($action == "deleteUserSkills"){
$sql = "DELETE FROM user_skills WHERE us_id = ?";
$args = array();
array_push($args, $usId);
if (!IsNullOrEmpty($usId)){
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
} else if ($action == "getUserSkills"){
    $args = array();
    $sql = "SELECT * FROM user_skills";
 $first = true;
if (!IsNullOrEmpty($usId)){
      if ($first) {
        $sql .= " WHERE us_id = ? ";
        $first = false;
      }else{
        $sql .= " AND us_id = ? ";
      }
      array_push ($args, $usId);
    }
if (!IsNullOrEmpty($skillId)){
      if ($first) {
        $sql .= " WHERE skill_id = ? ";
        $first = false;
      }else{
        $sql .= " AND skill_id = ? ";
      }
      array_push ($args, $skillId);
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
        $json['user_skills'][] = $row;
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
