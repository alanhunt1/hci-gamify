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
$skillId = "";
if (array_key_exists('skillid', $decoded_params)){
  $skillId =  $decoded_params['skillid'];
}
$skillName = "";
if (array_key_exists('skillname', $decoded_params)){
  $skillName =  $decoded_params['skillname'];
}
$skillDescription = "";
if (array_key_exists('skilldescription', $decoded_params)){
  $skillDescription =  $decoded_params['skilldescription'];
}
if ($action == "addOrEditSkills"){
$args = array();
if (IsNullOrEmpty($skillId)){
 $sql = "INSERT INTO skills (skill_id,skill_name,skill_description) VALUES ( ?,?,?);";
array_push($args, $skillId);
array_push($args, $skillName);
array_push($args, $skillDescription);
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
$sql = "UPDATE skills SET skill_name = ?,skill_description = ? WHERE skill_id = ?; ";
array_push($args, $skillName);
array_push($args, $skillDescription);
array_push($args, $skillId);
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
} else if ($action == "deleteSkills"){
$sql = "DELETE FROM skills WHERE skill_id = ?";
$args = array();
array_push($args, $skillId);
if (!IsNullOrEmpty($skillId)){
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
} else if ($action == "getSkills"){
    $args = array();
    $sql = "SELECT * FROM skills";
 $first = true;
if (!IsNullOrEmpty($skillId)){
      if ($first) {
        $sql .= " WHERE skill_id = ? ";
        $first = false;
      }else{
        $sql .= " AND skill_id = ? ";
      }
      array_push ($args, $skillId);
    }
if (!IsNullOrEmpty($skillName)){
      if ($first) {
        $sql .= " WHERE skill_name = ? ";
        $first = false;
      }else{
        $sql .= " AND skill_name = ? ";
      }
      array_push ($args, $skillName);
    }
if (!IsNullOrEmpty($skillDescription)){
      if ($first) {
        $sql .= " WHERE skill_description = ? ";
        $first = false;
      }else{
        $sql .= " AND skill_description = ? ";
      }
      array_push ($args, $skillDescription);
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
        $json['skills'][] = $row1;
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
