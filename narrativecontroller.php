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
$narrativeId = "";
if (array_key_exists('narrativeid', $decoded_params)){
  $narrativeId =  $decoded_params['narrativeid'];
}
$url = "";
if (array_key_exists('url', $decoded_params)){
  $url =  $decoded_params['url'];
}
$pic = "";
if (array_key_exists('pic', $decoded_params)){
  $pic =  $decoded_params['pic'];
}
$text = "";
if (array_key_exists('text', $decoded_params)){
  $text =  $decoded_params['text'];
}
if ($action == "addOrEditNarratives"){
$args = array();
if (IsNullOrEmpty($narrativeId)){
 $sql = "INSERT INTO narratives (narrative_id,url,pic,text) VALUES ( ?,?,?,?);";
array_push($args, $narrativeId);
array_push($args, $url);
array_push($args, $pic);
array_push($args, $text);
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
$sql = "UPDATE narratives SET url = ?,pic = ?,text = ? WHERE narrative_id = ?; ";
array_push($args, $url);
array_push($args, $pic);
array_push($args, $text);
array_push($args, $narrativeId);
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
} else if ($action == "deleteNarratives"){
$sql = "DELETE FROM narratives WHERE narrative_id = ?";
$args = array();
array_push($args, $narrativeId);
if (!IsNullOrEmpty($narrativeId)){
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
} else if ($action == "getNarratives"){
    $args = array();
    $sql = "SELECT * FROM narratives";
 $first = true;
if (!IsNullOrEmpty($narrativeId)){
      if ($first) {
        $sql .= " WHERE narrative_id = ? ";
        $first = false;
      }else{
        $sql .= " AND narrative_id = ? ";
      }
      array_push ($args, $narrativeId);
    }
if (!IsNullOrEmpty($url)){
      if ($first) {
        $sql .= " WHERE url = ? ";
        $first = false;
      }else{
        $sql .= " AND url = ? ";
      }
      array_push ($args, $url);
    }
if (!IsNullOrEmpty($pic)){
      if ($first) {
        $sql .= " WHERE pic = ? ";
        $first = false;
      }else{
        $sql .= " AND pic = ? ";
      }
      array_push ($args, $pic);
    }
if (!IsNullOrEmpty($text)){
      if ($first) {
        $sql .= " WHERE text = ? ";
        $first = false;
      }else{
        $sql .= " AND text = ? ";
      }
      array_push ($args, $text);
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
        $json['narratives'][] = $row;
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
