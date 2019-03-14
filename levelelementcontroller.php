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
$elementId = "";
if (array_key_exists('elementid', $decoded_params)){
  $elementId =  $decoded_params['elementid'];
}
$levelId = "";
if (array_key_exists('levelid', $decoded_params)){
  $levelId =  $decoded_params['levelid'];
}
$elementType = "";
if (array_key_exists('elementtype', $decoded_params)){
  $elementType =  $decoded_params['elementtype'];
}
$elementKey = "";
if (array_key_exists('elementkey', $decoded_params)){
  $elementKey =  $decoded_params['elementkey'];
}
$elementSequence = "";
if (array_key_exists('elementsequence', $decoded_params)){
  $elementSequence =  $decoded_params['elementsequence'];
}
if ($action == "addOrEditLevelElements"){
$args = array();
if (IsNullOrEmpty($elementId)){
 $sql = "INSERT INTO level_elements (element_id,level_id,element_type,element_key,element_sequence) VALUES ( ?,?,?,?,?);";
array_push($args, $elementId);
array_push($args, $levelId);
array_push($args, $elementType);
array_push($args, $elementKey);
array_push($args, $elementSequence);
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
$sql = "UPDATE level_elements SET level_id = ?,element_type = ?,element_key = ?,element_sequence = ? WHERE element_id = ?; ";
array_push($args, $levelId);
array_push($args, $elementType);
array_push($args, $elementKey);
array_push($args, $elementSequence);
array_push($args, $elementId);
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
} else if ($action == "deleteLevelElements"){
$sql = "DELETE FROM level_elements WHERE element_id = ?";
$args = array();
array_push($args, $elementId);
if (!IsNullOrEmpty($elementId)){
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
} else if ($action == "getLevelElements"){
    $args = array();
    $sql = "SELECT * FROM level_elements";
 $first = true;
if (!IsNullOrEmpty($elementId)){
      if ($first) {
        $sql .= " WHERE element_id = ? ";
        $first = false;
      }else{
        $sql .= " AND element_id = ? ";
      }
      array_push ($args, $elementId);
    }
if (!IsNullOrEmpty($levelId)){
      if ($first) {
        $sql .= " WHERE level_id = ? ";
        $first = false;
      }else{
        $sql .= " AND level_id = ? ";
      }
      array_push ($args, $levelId);
    }
if (!IsNullOrEmpty($elementType)){
      if ($first) {
        $sql .= " WHERE element_type = ? ";
        $first = false;
      }else{
        $sql .= " AND element_type = ? ";
      }
      array_push ($args, $elementType);
    }
if (!IsNullOrEmpty($elementKey)){
      if ($first) {
        $sql .= " WHERE element_key = ? ";
        $first = false;
      }else{
        $sql .= " AND element_key = ? ";
      }
      array_push ($args, $elementKey);
    }
if (!IsNullOrEmpty($elementSequence)){
      if ($first) {
        $sql .= " WHERE element_sequence = ? ";
        $first = false;
      }else{
        $sql .= " AND element_sequence = ? ";
      }
      array_push ($args, $elementSequence);
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
        $json['level_elements'][] = $row;
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
