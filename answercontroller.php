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
$answerId = "";
if (array_key_exists('answerid', $decoded_params)){
  $answerId =  $decoded_params['answerid'];
}
$questionId = "";
if (array_key_exists('questionid', $decoded_params)){
  $questionId =  $decoded_params['questionid'];
}
$answerText = "";
if (array_key_exists('answertext', $decoded_params)){
  $answerText =  $decoded_params['answertext'];
}
$answerPic = "";
if (array_key_exists('answerpic', $decoded_params)){
  $answerPic =  $decoded_params['answerpic'];
}
$correctFlag = "";
if (array_key_exists('correctflag', $decoded_params)){
  $correctFlag =  $decoded_params['correctflag'];
}
if ($action == "addOrEditAnswers"){
$args = array();
if (IsNullOrEmpty($answerId)){
 $sql = "INSERT INTO answers (answer_id,question_id,answer_text,answer_pic,correct_flag) VALUES ( ?,?,?,?,?);";
array_push($args, $answerId);
array_push($args, $questionId);
array_push($args, $answerText);
array_push($args, $answerPic);
array_push($args, $correctFlag);
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
$sql = "UPDATE answers SET question_id = ?,answer_text = ?,answer_pic = ?,correct_flag = ? WHERE answer_id = ?; ";
array_push($args, $questionId);
array_push($args, $answerText);
array_push($args, $answerPic);
array_push($args, $correctFlag);
array_push($args, $answerId);
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
} else if ($action == "deleteAnswers"){
$sql = "DELETE FROM answers WHERE answer_id = ?";
$args = array();
array_push($args, $answerId);
if (!IsNullOrEmpty($answerId)){
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
} else if ($action == "getAnswers"){
    $args = array();
    $sql = "SELECT * FROM answers";
 $first = true;
if (!IsNullOrEmpty($answerId)){
      if ($first) {
        $sql .= " WHERE answer_id = ? ";
        $first = false;
      }else{
        $sql .= " AND answer_id = ? ";
      }
      array_push ($args, $answerId);
    }
if (!IsNullOrEmpty($questionId)){
      if ($first) {
        $sql .= " WHERE question_id = ? ";
        $first = false;
      }else{
        $sql .= " AND question_id = ? ";
      }
      array_push ($args, $questionId);
    }
if (!IsNullOrEmpty($answerText)){
      if ($first) {
        $sql .= " WHERE answer_text = ? ";
        $first = false;
      }else{
        $sql .= " AND answer_text = ? ";
      }
      array_push ($args, $answerText);
    }
if (!IsNullOrEmpty($answerPic)){
      if ($first) {
        $sql .= " WHERE answer_pic = ? ";
        $first = false;
      }else{
        $sql .= " AND answer_pic = ? ";
      }
      array_push ($args, $answerPic);
    }
if (!IsNullOrEmpty($correctFlag)){
      if ($first) {
        $sql .= " WHERE correct_flag = ? ";
        $first = false;
      }else{
        $sql .= " AND correct_flag = ? ";
      }
      array_push ($args, $correctFlag);
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
        $json['answers'][] = $row1;
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
