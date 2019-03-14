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
$stId = "";
if (array_key_exists('stid', $decoded_params)){
  $stId =  $decoded_params['stid'];
}
$parentId = "";
if (array_key_exists('parentid', $decoded_params)){
  $parentId =  $decoded_params['parentid'];
}
$skillId = "";
if (array_key_exists('skillid', $decoded_params)){
  $skillId =  $decoded_params['skillid'];
}
if ($action == "addOrEditSkillTree"){
$args = array();
if (IsNullOrEmpty($stId)){
 $sql = "INSERT INTO skill_tree (st_id,parent_id,skill_id) VALUES ( ?,?,?);";
array_push($args, $stId);
array_push($args, $parentId);
array_push($args, $skillId);
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
$sql = "UPDATE skill_tree SET parent_id = ?,skill_id = ? WHERE st_id = ?; ";
array_push($args, $parentId);
array_push($args, $skillId);
array_push($args, $stId);
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
} else if ($action == "deleteSkillTree"){
$sql = "DELETE FROM skill_tree WHERE st_id = ?";
$args = array();
array_push($args, $stId);
if (!IsNullOrEmpty($stId)){
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
} else if ($action == "getSkillTree"){
    $args = array();
    $sql = "SELECT * FROM skill_tree";
 $first = true;
if (!IsNullOrEmpty($stId)){
      if ($first) {
        $sql .= " WHERE st_id = ? ";
        $first = false;
      }else{
        $sql .= " AND st_id = ? ";
      }
      array_push ($args, $stId);
    }
if (!IsNullOrEmpty($parentId)){
      if ($first) {
        $sql .= " WHERE parent_id = ? ";
        $first = false;
      }else{
        $sql .= " AND parent_id = ? ";
      }
      array_push ($args, $parentId);
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
        $json['skill_tree'][] = $row;
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
