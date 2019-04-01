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
$userId = "";
if (array_key_exists('userid', $decoded_params)){
  $userId =  $decoded_params['userid'];
}
$userName = "";
if (array_key_exists('username', $decoded_params)){
  $userName =  $decoded_params['username'];
}
$userLevel = "";
if (array_key_exists('userlevel', $decoded_params)){
  $userLevel =  $decoded_params['userlevel'];
}
$userPoints = "";
if (array_key_exists('userpoints', $decoded_params)){
  $userPoints =  $decoded_params['userpoints'];
}
$playerName = "";
if (array_key_exists('playername', $decoded_params)){
  $playerName =  $decoded_params['playername'];
}
$avatarImage = "";
if (array_key_exists('avatarimage', $decoded_params)){
  $avatarImage =  $decoded_params['avatarimage'];
}
$userCategory = "";
if (array_key_exists('usercategory', $decoded_params)){
  $userCategory =  $decoded_params['usercategory'];
}
$userAttributes = "";
if (array_key_exists('userattributes', $decoded_params)){
  $userAttributes =  $decoded_params['userattributes'];
}
if ($action == "addOrEditUsers"){
$args = array();
if (IsNullOrEmpty($userId)){
 $sql = "INSERT INTO users (user_id,user_name,user_level,user_points,player_name,avatar_image,user_category,user_attributes) VALUES ( ?,?,?,?,?,?,?,?);";
array_push($args, $userId);
array_push($args, $userName);
array_push($args, $userLevel);
array_push($args, $userPoints);
array_push($args, $playerName);
array_push($args, $avatarImage);
array_push($args, $userCategory);
array_push($args, $userAttributes);
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
$sql = "UPDATE users SET user_name = ?,user_level = ?,user_points = ?,player_name = ?,avatar_image = ?,user_category = ?,user_attributes = ? WHERE user_id = ?; ";
array_push($args, $userName);
array_push($args, $userLevel);
array_push($args, $userPoints);
array_push($args, $playerName);
array_push($args, $avatarImage);
array_push($args, $userCategory);
array_push($args, $userAttributes);
array_push($args, $userId);
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
} else if ($action == "deleteUsers"){
$sql = "DELETE FROM users WHERE user_id = ?";
$args = array();
array_push($args, $userId);
if (!IsNullOrEmpty($userId)){
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
} else if ($action == "getUsers"){
    $args = array();
    $sql = "SELECT * FROM users";
 $first = true;
if (!IsNullOrEmpty($userId)){
      if ($first) {
        $sql .= " WHERE user_id = ? ";
        $first = false;
      }else{
        $sql .= " AND user_id = ? ";
      }
      array_push ($args, $userId);
    }
if (!IsNullOrEmpty($userName)){
      if ($first) {
        $sql .= " WHERE user_name = ? ";
        $first = false;
      }else{
        $sql .= " AND user_name = ? ";
      }
      array_push ($args, $userName);
    }
if (!IsNullOrEmpty($userLevel)){
      if ($first) {
        $sql .= " WHERE user_level = ? ";
        $first = false;
      }else{
        $sql .= " AND user_level = ? ";
      }
      array_push ($args, $userLevel);
    }
if (!IsNullOrEmpty($userPoints)){
      if ($first) {
        $sql .= " WHERE user_points = ? ";
        $first = false;
      }else{
        $sql .= " AND user_points = ? ";
      }
      array_push ($args, $userPoints);
    }
if (!IsNullOrEmpty($playerName)){
      if ($first) {
        $sql .= " WHERE player_name = ? ";
        $first = false;
      }else{
        $sql .= " AND player_name = ? ";
      }
      array_push ($args, $playerName);
    }
if (!IsNullOrEmpty($avatarImage)){
      if ($first) {
        $sql .= " WHERE avatar_image = ? ";
        $first = false;
      }else{
        $sql .= " AND avatar_image = ? ";
      }
      array_push ($args, $avatarImage);
    }
if (!IsNullOrEmpty($userCategory)){
      if ($first) {
        $sql .= " WHERE user_category = ? ";
        $first = false;
      }else{
        $sql .= " AND user_category = ? ";
      }
      array_push ($args, $userCategory);
    }
if (!IsNullOrEmpty($userAttributes)){
      if ($first) {
        $sql .= " WHERE user_attributes = ? ";
        $first = false;
      }else{
        $sql .= " AND user_attributes = ? ";
      }
      array_push ($args, $userAttributes);
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
        $json['users'][] = $row1;
    }
} else if ($action == "getCompleteUsers"){
    $args = array();
    $sql = "SELECT * FROM users";
 $first = true;
if (!IsNullOrEmpty($userId)){
      if ($first) {
        $sql .= " WHERE user_id = ? ";
        $first = false;
      }else{
        $sql .= " AND user_id = ? ";
      }
      array_push ($args, $userId);
    }
if (!IsNullOrEmpty($userName)){
      if ($first) {
        $sql .= " WHERE user_name = ? ";
        $first = false;
      }else{
        $sql .= " AND user_name = ? ";
      }
      array_push ($args, $userName);
    }
if (!IsNullOrEmpty($userLevel)){
      if ($first) {
        $sql .= " WHERE user_level = ? ";
        $first = false;
      }else{
        $sql .= " AND user_level = ? ";
      }
      array_push ($args, $userLevel);
    }
if (!IsNullOrEmpty($userPoints)){
      if ($first) {
        $sql .= " WHERE user_points = ? ";
        $first = false;
      }else{
        $sql .= " AND user_points = ? ";
      }
      array_push ($args, $userPoints);
    }
if (!IsNullOrEmpty($playerName)){
      if ($first) {
        $sql .= " WHERE player_name = ? ";
        $first = false;
      }else{
        $sql .= " AND player_name = ? ";
      }
      array_push ($args, $playerName);
    }
if (!IsNullOrEmpty($avatarImage)){
      if ($first) {
        $sql .= " WHERE avatar_image = ? ";
        $first = false;
      }else{
        $sql .= " AND avatar_image = ? ";
      }
      array_push ($args, $avatarImage);
    }
if (!IsNullOrEmpty($userCategory)){
      if ($first) {
        $sql .= " WHERE user_category = ? ";
        $first = false;
      }else{
        $sql .= " AND user_category = ? ";
      }
      array_push ($args, $userCategory);
    }
if (!IsNullOrEmpty($userAttributes)){
      if ($first) {
        $sql .= " WHERE user_attributes = ? ";
        $first = false;
      }else{
        $sql .= " AND user_attributes = ? ";
      }
      array_push ($args, $userAttributes);
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
    $sql = "SELECT badges.* FROM users, user_badges, badges WHERE 
             users.user_id = user_badges.user_id
             AND user_badges.badge_id = badges.badge_id AND users.user_id = ".$row1['user_id'];
    $json['SQL user_badges'] = $sql; 
    try{
      $conn2 = getDbConnection();      $statement2 = $conn2->prepare($sql);
      $statement2->setFetchMode(PDO::FETCH_ASSOC);
      $statement2->execute();
      $result2 = $statement2->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result2 as $row2 ) {
        $row1['badges'][] = $row2;
    }
    $sql = "SELECT skills.* FROM users, user_skills, skills WHERE 
             users.user_id = user_skills.user_id
             AND user_skills.skill_id = skills.skill_id AND users.user_id = ".$row1['user_id'];
    $json['SQL user_skills'] = $sql; 
    try{
      $conn2 = getDbConnection();      $statement2 = $conn2->prepare($sql);
      $statement2->setFetchMode(PDO::FETCH_ASSOC);
      $statement2->execute();
      $result2 = $statement2->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result2 as $row2 ) {
        $row1['skills'][] = $row2;
    }
    $sql = "SELECT challenges.* FROM users, user_challenges, challenges WHERE 
             users.user_id = user_challenges.user_id
             AND user_challenges.challenge_id = challenges.challenge_id AND users.user_id = ".$row1['user_id'];
    $json['SQL user_challenges'] = $sql; 
    try{
      $conn2 = getDbConnection();      $statement2 = $conn2->prepare($sql);
      $statement2->setFetchMode(PDO::FETCH_ASSOC);
      $statement2->execute();
      $result2 = $statement2->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result2 as $row2 ) {
        $row1['challenges'][] = $row2;
    }
    $sql = "SELECT level_elements.* FROM users, user_progress, level_elements WHERE 
             users.user_id = user_progress.user_id
             AND user_progress.le_id = level_elements.element_id AND users.user_id = ".$row1['user_id'];
    $json['SQL user_progress'] = $sql; 
    try{
      $conn2 = getDbConnection();      $statement2 = $conn2->prepare($sql);
      $statement2->setFetchMode(PDO::FETCH_ASSOC);
      $statement2->execute();
      $result2 = $statement2->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result2 as $row2 ) {
        $row1['level_elements'][] = $row2;
    }
    $sql = "SELECT friends.* FROM users, friends WHERE 
             users.user_id = friends.user_id
              AND users.user_id = ".$row1['user_id'];
    $json['SQL friends'] = $sql; 
    try{
      $conn2 = getDbConnection();      $statement2 = $conn2->prepare($sql);
      $statement2->setFetchMode(PDO::FETCH_ASSOC);
      $statement2->execute();
      $result2 = $statement2->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result2 as $row2 ) {
        $row1['friends'][] = $row2;
    }
        $json['users'][] = $row1;
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
