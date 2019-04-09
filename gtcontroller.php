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
$gameTypeId = "";
if (array_key_exists('gametypeid', $decoded_params)){
  $gameTypeId =  $decoded_params['gametypeid'];
}
$gameTypeName = "";
if (array_key_exists('gametypename', $decoded_params)){
  $gameTypeName =  $decoded_params['gametypename'];
}
$gameTypeDescription = "";
if (array_key_exists('gametypedescription', $decoded_params)){
  $gameTypeDescription =  $decoded_params['gametypedescription'];
}
if ($action == "addOrEditGameTypes"){
$args = array();
if (IsNullOrEmpty($gameTypeId)){
 $sql = "INSERT INTO game_types (game_type_id,game_type_name,game_type_description) VALUES ( ?,?,?);";
array_push($args, $gameTypeId);
array_push($args, $gameTypeName);
array_push($args, $gameTypeDescription);
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
$sql = "UPDATE game_types SET game_type_name = ?,game_type_description = ? WHERE game_type_id = ?; ";
array_push($args, $gameTypeName);
array_push($args, $gameTypeDescription);
array_push($args, $gameTypeId);
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
} else if ($action == "deleteGameTypes"){
$sql = "DELETE FROM game_types WHERE game_type_id = ?";
$args = array();
array_push($args, $gameTypeId);
if (!IsNullOrEmpty($gameTypeId)){
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
} else if ($action == "getGameTypes"){
    $args = array();
    $sql = "SELECT * FROM game_types";
 $first = true;
if (!IsNullOrEmpty($gameTypeId)){
      if ($first) {
        $sql .= " WHERE game_type_id = ? ";
        $first = false;
      }else{
        $sql .= " AND game_type_id = ? ";
      }
      array_push ($args, $gameTypeId);
    }
if (!IsNullOrEmpty($gameTypeName)){
      if ($first) {
        $sql .= " WHERE game_type_name = ? ";
        $first = false;
      }else{
        $sql .= " AND game_type_name = ? ";
      }
      array_push ($args, $gameTypeName);
    }
if (!IsNullOrEmpty($gameTypeDescription)){
      if ($first) {
        $sql .= " WHERE game_type_description = ? ";
        $first = false;
      }else{
        $sql .= " AND game_type_description = ? ";
      }
      array_push ($args, $gameTypeDescription);
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
        $json['game_types'][] = $row1;
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
