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
  $badgeId = "";
  if (array_key_exists('badgeid', $decoded_params)){
    $badgeId =  $decoded_params['badgeid'];
  }
  $badgeName = "";
  if (array_key_exists('badgename', $decoded_params)){
    $badgeName =  $decoded_params['badgename'];
  }
  $badgePic = "";
  if (array_key_exists('badgepic', $decoded_params)){
    $badgePic =  $decoded_params['badgepic'];
  }
  $multipleFlag = "";
  if (array_key_exists('multipleflag', $decoded_params)){
    $multipleFlag =  $decoded_params['multipleflag'];
  }
  $badgeDescription = "";
  if (array_key_exists('badgedescription', $decoded_params)){
    $badgeDescription =  $decoded_params['badgedescription'];
  }
  if ($action == "addOrEditBadges"){
    $args = array();
    if (IsNullOrEmpty($badgeId)){
      $sql = "INSERT INTO badges (badge_id,badge_name,badge_pic,multiple_flag,badge_description) VALUES ( ?,?,?,?,?);";
      array_push($args, $badgeId);
      array_push($args, $badgeName);
      array_push($args, $badgePic);
      array_push($args, $multipleFlag);
      array_push($args, $badgeDescription);
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
      $sql = "UPDATE badges SET badge_name = ?,badge_pic = ?,multiple_flag = ?,badge_description = ? WHERE badge_id = ?; ";
      array_push($args, $badgeName);
      array_push($args, $badgePic);
      array_push($args, $multipleFlag);
      array_push($args, $badgeDescription);
      array_push($args, $badgeId);
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
  } else if ($action == "deleteBadges"){
    $sql = "DELETE FROM badges WHERE badge_id = ?";
    $args = array();
    array_push($args, $badgeId);
    if (!IsNullOrEmpty($badgeId)){
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
  } else if ($action == "getBadges"){
    $args = array();
    $sql = "SELECT * FROM badges";
    $first = true;
    if (!IsNullOrEmpty($badgeId)){
      if ($first) {
        $sql .= " WHERE badge_id = ? ";
        $first = false;
      }else{
        $sql .= " AND badge_id = ? ";
      }
      array_push ($args, $badgeId);
    }
    if (!IsNullOrEmpty($badgeName)){
      if ($first) {
        $sql .= " WHERE badge_name = ? ";
        $first = false;
      }else{
        $sql .= " AND badge_name = ? ";
      }
      array_push ($args, $badgeName);
    }
    if (!IsNullOrEmpty($badgePic)){
      if ($first) {
        $sql .= " WHERE badge_pic = ? ";
        $first = false;
      }else{
        $sqcml .= " AND badge_pic = ? ";
      }
      array_push ($args, $badgePic);
    }
    if (!IsNullOrEmpty($multipleFlag)){
      if ($first) {
        $sql .= " WHERE multiple_flag = ? ";
        $first = false;
      }else{
        $sql .= " AND multiple_flag = ? ";
      }
      array_push ($args, $multipleFlag);
    }
    if (!IsNullOrEmpty($badgeDescription)){
      if ($first) {
        $sql .= " WHERE badge_description = ? ";
        $first = false;
      }else{
        $sql .= " AND badge_description = ? ";
      }
      array_push ($args, $badgeDescription);
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
      $json['badges'][] = $row;
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
