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
$gameId = "";
if (array_key_exists('gameid', $decoded_params)){
  $gameId =  $decoded_params['gameid'];
}
$gameName = "";
if (array_key_exists('gamename', $decoded_params)){
  $gameName =  $decoded_params['gamename'];
}
$gameType = "";
if (array_key_exists('gametype', $decoded_params)){
  $gameType =  $decoded_params['gametype'];
}
$gameDescription = "";
if (array_key_exists('gamedescription', $decoded_params)){
  $gameDescription =  $decoded_params['gamedescription'];
}
if ($action == "addOrEditGames"){
$args = array();
if (IsNullOrEmpty($gameId)){
 $sql = "INSERT INTO games (game_id,game_name,game_type,game_description) VALUES ( ?,?,?,?);";
array_push($args, $gameId);
array_push($args, $gameName);
array_push($args, $gameType);
array_push($args, $gameDescription);
try{
$statement = $conn->prepare($sql);
$statement->execute($args);
$last_id = $conn->lastInsertId();
}catch (Exception $e) {
    $json['Exception'] =  $e->getMessage();
}
$json['Record Id'] = $last_id;
$json['Status'] = "SUCCESS - Inserted Id $last_id";
}else{
$sql = "UPDATE games SET game_name = ?,game_type = ?,game_description = ? WHERE game_id = ?; ";
array_push($args, $gameName);
array_push($args, $gameType);
array_push($args, $gameDescription);
array_push($args, $gameId);
try{
$statement = $conn->prepare($sql);
$statement->execute($args);
}catch (Exception $e) {
    $json['Exception'] =  $e->getMessage();
}
$count = $statement->rowCount();
if ($count > 0){
$json['Status'] = "SUCCESS - Updated $count Rows";
} else {
$json['Status'] = "ERROR - Updated 0 Rows - Check for Valid Ids ";
}
$json['Action'] = $action;
}
} else if ($action == "deleteGames"){
$sql = "DELETE FROM games WHERE game_id = ?";
$args = array();
array_push($args, $gameId);
if (!IsNullOrEmpty($gameId)){
try{
  $statement = $conn->prepare($sql);
  $statement->execute($args);
}catch (Exception $e) {
    $json['Exception'] =  $e->getMessage();
}
$count = $statement->rowCount();
if ($count > 0){
$json['Status'] = "SUCCESS - Deleted $count Rows";
} else {
$json['Status'] = "ERROR - Deleted 0 Rows - Check for Valid Ids ";
}
} else {
$json['Status'] = "ERROR - Id is required";
}
$json['Action'] = $action;
} else if ($action == "getGames"){
    $args = array();
    $sql = "SELECT * FROM games";
 $first = true;
if (!IsNullOrEmpty($gameId)){
      if ($first) {
        $sql .= " WHERE game_id = ? ";
        $first = false;
      }else{
        $sql .= " AND game_id = ? ";
      }
      array_push ($args, $gameId);
    }
if (!IsNullOrEmpty($gameName)){
      if ($first) {
        $sql .= " WHERE game_name = ? ";
        $first = false;
      }else{
        $sql .= " AND game_name = ? ";
      }
      array_push ($args, $gameName);
    }
if (!IsNullOrEmpty($gameType)){
      if ($first) {
        $sql .= " WHERE game_type = ? ";
        $first = false;
      }else{
        $sql .= " AND game_type = ? ";
      }
      array_push ($args, $gameType);
    }
if (!IsNullOrEmpty($gameDescription)){
      if ($first) {
        $sql .= " WHERE game_description = ? ";
        $first = false;
      }else{
        $sql .= " AND game_description = ? ";
      }
      array_push ($args, $gameDescription);
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
        $json['games'][] = $row;
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
