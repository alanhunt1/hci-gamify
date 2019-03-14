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
$ucId = "";
if (array_key_exists('ucid', $decoded_params)){
  $ucId =  $decoded_params['ucid'];
}
$userId = "";
if (array_key_exists('userid', $decoded_params)){
  $userId =  $decoded_params['userid'];
}
$challengeId = "";
if (array_key_exists('challengeid', $decoded_params)){
  $challengeId =  $decoded_params['challengeid'];
}
$progressNotes = "";
if (array_key_exists('progressnotes', $decoded_params)){
  $progressNotes =  $decoded_params['progressnotes'];
}
if ($action == "addOrEditUserChallenges"){
$args = array();
if (IsNullOrEmpty($ucId)){
 $sql = "INSERT INTO user_challenges (uc_id,user_id,challenge_id,progress_notes) VALUES ( ?,?,?,?);";
array_push($args, $ucId);
array_push($args, $userId);
array_push($args, $challengeId);
array_push($args, $progressNotes);
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
$sql = "UPDATE user_challenges SET user_id = ?,challenge_id = ?,progress_notes = ? WHERE uc_id = ?; ";
array_push($args, $userId);
array_push($args, $challengeId);
array_push($args, $progressNotes);
array_push($args, $ucId);
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
} else if ($action == "deleteUserChallenges"){
$sql = "DELETE FROM user_challenges WHERE uc_id = ?";
$args = array();
array_push($args, $ucId);
if (!IsNullOrEmpty($ucId)){
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
} else if ($action == "getUserChallenges"){
    $args = array();
    $sql = "SELECT * FROM user_challenges";
 $first = true;
if (!IsNullOrEmpty($ucId)){
      if ($first) {
        $sql .= " WHERE uc_id = ? ";
        $first = false;
      }else{
        $sql .= " AND uc_id = ? ";
      }
      array_push ($args, $ucId);
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
if (!IsNullOrEmpty($challengeId)){
      if ($first) {
        $sql .= " WHERE challenge_id = ? ";
        $first = false;
      }else{
        $sql .= " AND challenge_id = ? ";
      }
      array_push ($args, $challengeId);
    }
if (!IsNullOrEmpty($progressNotes)){
      if ($first) {
        $sql .= " WHERE progress_notes = ? ";
        $first = false;
      }else{
        $sql .= " AND progress_notes = ? ";
      }
      array_push ($args, $progressNotes);
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
        $json['user_challenges'][] = $row;
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
