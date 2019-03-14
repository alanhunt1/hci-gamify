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
$questionId = "";
if (array_key_exists('questionid', $decoded_params)){
  $questionId =  $decoded_params['questionid'];
}
$questionTitle = "";
if (array_key_exists('questiontitle', $decoded_params)){
  $questionTitle =  $decoded_params['questiontitle'];
}
$questionText = "";
if (array_key_exists('questiontext', $decoded_params)){
  $questionText =  $decoded_params['questiontext'];
}
$questionType = "";
if (array_key_exists('questiontype', $decoded_params)){
  $questionType =  $decoded_params['questiontype'];
}
$hintText = "";
if (array_key_exists('hinttext', $decoded_params)){
  $hintText =  $decoded_params['hinttext'];
}
if ($action == "addOrEditQuestions"){
$args = array();
if (IsNullOrEmpty($questionId)){
 $sql = "INSERT INTO questions (question_id,question_title,question_text,question_type,hint_text) VALUES ( ?,?,?,?,?);";
array_push($args, $questionId);
array_push($args, $questionTitle);
array_push($args, $questionText);
array_push($args, $questionType);
array_push($args, $hintText);
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
$sql = "UPDATE questions SET question_title = ?,question_text = ?,question_type = ?,hint_text = ? WHERE question_id = ?; ";
array_push($args, $questionTitle);
array_push($args, $questionText);
array_push($args, $questionType);
array_push($args, $hintText);
array_push($args, $questionId);
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
} else if ($action == "deleteQuestions"){
$sql = "DELETE FROM questions WHERE question_id = ?";
$args = array();
array_push($args, $questionId);
if (!IsNullOrEmpty($questionId)){
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
} else if ($action == "getQuestions"){
    $args = array();
    $sql = "SELECT * FROM questions";
 $first = true;
if (!IsNullOrEmpty($questionId)){
      if ($first) {
        $sql .= " WHERE question_id = ? ";
        $first = false;
      }else{
        $sql .= " AND question_id = ? ";
      }
      array_push ($args, $questionId);
    }
if (!IsNullOrEmpty($questionTitle)){
      if ($first) {
        $sql .= " WHERE question_title = ? ";
        $first = false;
      }else{
        $sql .= " AND question_title = ? ";
      }
      array_push ($args, $questionTitle);
    }
if (!IsNullOrEmpty($questionText)){
      if ($first) {
        $sql .= " WHERE question_text = ? ";
        $first = false;
      }else{
        $sql .= " AND question_text = ? ";
      }
      array_push ($args, $questionText);
    }
if (!IsNullOrEmpty($questionType)){
      if ($first) {
        $sql .= " WHERE question_type = ? ";
        $first = false;
      }else{
        $sql .= " AND question_type = ? ";
      }
      array_push ($args, $questionType);
    }
if (!IsNullOrEmpty($hintText)){
      if ($first) {
        $sql .= " WHERE hint_text = ? ";
        $first = false;
      }else{
        $sql .= " AND hint_text = ? ";
      }
      array_push ($args, $hintText);
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
        $json['questions'][] = $row;
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
