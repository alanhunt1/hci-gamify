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
$levelId = "";
if (array_key_exists('levelid', $decoded_params)){
  $levelId =  $decoded_params['levelid'];
}
$levelName = "";
if (array_key_exists('levelname', $decoded_params)){
  $levelName =  $decoded_params['levelname'];
}
$gameId = "";
if (array_key_exists('gameid', $decoded_params)){
  $gameId =  $decoded_params['gameid'];
}
$levelDescription = "";
if (array_key_exists('leveldescription', $decoded_params)){
  $levelDescription =  $decoded_params['leveldescription'];
}
if ($action == "addOrEditLevels"){
$args = array();
if (IsNullOrEmpty($levelId)){
 $sql = "INSERT INTO levels (level_id,level_name,game_id,level_description) VALUES ( ?,?,?,?);";
array_push($args, $levelId);
array_push($args, $levelName);
array_push($args, $gameId);
array_push($args, $levelDescription);
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
$sql = "UPDATE levels SET level_name = ?,game_id = ?,level_description = ? WHERE level_id = ?; ";
array_push($args, $levelName);
array_push($args, $gameId);
array_push($args, $levelDescription);
array_push($args, $levelId);
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
} else if ($action == "deleteLevels"){
$sql = "DELETE FROM levels WHERE level_id = ?";
$args = array();
array_push($args, $levelId);
if (!IsNullOrEmpty($levelId)){
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
} else if ($action == "getLevels"){
    $args = array();
    $sql = "SELECT * FROM levels";
 $first = true;
if (!IsNullOrEmpty($levelId)){
      if ($first) {
        $sql .= " WHERE level_id = ? ";
        $first = false;
      }else{
        $sql .= " AND level_id = ? ";
      }
      array_push ($args, $levelId);
    }
if (!IsNullOrEmpty($levelName)){
      if ($first) {
        $sql .= " WHERE level_name = ? ";
        $first = false;
      }else{
        $sql .= " AND level_name = ? ";
      }
      array_push ($args, $levelName);
    }
if (!IsNullOrEmpty($gameId)){
      if ($first) {
        $sql .= " WHERE game_id = ? ";
        $first = false;
      }else{
        $sql .= " AND game_id = ? ";
      }
      array_push ($args, $gameId);
    }
if (!IsNullOrEmpty($levelDescription)){
      if ($first) {
        $sql .= " WHERE level_description = ? ";
        $first = false;
      }else{
        $sql .= " AND level_description = ? ";
      }
      array_push ($args, $levelDescription);
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
        $json['levels'][] = $row1;
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
