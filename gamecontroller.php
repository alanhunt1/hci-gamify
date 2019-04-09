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
$json['Record Id'] = $last_id;
$json['Status'] = "SUCCESS - Inserted Id $last_id";
}catch (Exception $e) { 
    $json['Exception'] =  $e->getMessage();
}
}else{
$sql = "UPDATE games SET game_name = ?,game_type = ?,game_description = ? WHERE game_id = ?; ";
array_push($args, $gameName);
array_push($args, $gameType);
array_push($args, $gameDescription);
array_push($args, $gameId);
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
} else if ($action == "deleteGames"){
$sql = "DELETE FROM games WHERE game_id = ?";
$args = array();
array_push($args, $gameId);
if (!IsNullOrEmpty($gameId)){
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
    foreach($result as $row1 ) {
        $json['games'][] = $row1;
    }
} else if ($action == "getCompleteGames"){
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
    foreach($result as $row1 ) {
    $sql = "SELECT levels.* FROM games, levels WHERE 
             games.game_id = levels.game_id
              AND games.game_id = ".$row1['game_id'];
    $json['SQL levels'] = $sql; 
    try{
      $conn2 = getDbConnection();      $statement2 = $conn2->prepare($sql);
      $statement2->setFetchMode(PDO::FETCH_ASSOC);
      $statement2->execute();
      $result2 = $statement2->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result2 as $row2 ) {
    $sql = "SELECT level_elements.* FROM levels, level_elements WHERE 
             levels.level_id = level_elements.level_id
              AND levels.level_id = ".$row2['level_id'];
    $json['SQL level_elements'] = $sql; 
    try{
      $conn3 = getDbConnection();      $statement3 = $conn3->prepare($sql);
      $statement3->setFetchMode(PDO::FETCH_ASSOC);
      $statement3->execute();
      $result3 = $statement3->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result3 as $row3 ) {

if ($row3['element_type']=='Questions') {
    $sql = "SELECT questions.* FROM level_elements, questions WHERE 
             level_elements.element_key = questions.question_id
              AND level_elements.element_key = ".$row3['element_key'];
    $json['SQL questions'] = $sql; 
    try{
      $conn4 = getDbConnection();      $statement4 = $conn4->prepare($sql);
      $statement4->setFetchMode(PDO::FETCH_ASSOC);
      $statement4->execute();
      $result4 = $statement4->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result4 as $row4 ) {
    $sql = "SELECT answers.* FROM questions, answers WHERE 
             questions.question_id = answers.question_id
              AND questions.question_id = ".$row4['question_id'];
    $json['SQL answers'] = $sql; 
    try{
      $conn5 = getDbConnection();      $statement5 = $conn5->prepare($sql);
      $statement5->setFetchMode(PDO::FETCH_ASSOC);
      $statement5->execute();
      $result5 = $statement5->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result5 as $row5 ) {
        $row4['answers'][] = $row5;
    }
        $row3['questions'][] = $row4;
    }
}
if ($row3['element_type']=='Narratives') {
    $sql = "SELECT narratives.* FROM level_elements, narratives WHERE 
             level_elements.element_key = narratives.narrative_id
              AND level_elements.element_key = ".$row3['element_key'];
    $json['SQL narratives'] = $sql; 
    try{
      $conn4 = getDbConnection();      $statement4 = $conn4->prepare($sql);
      $statement4->setFetchMode(PDO::FETCH_ASSOC);
      $statement4->execute();
      $result4 = $statement4->fetchAll();
    }catch (Exception $e) { 
      $json['Exception'] =  $e->getMessage();
    }
    foreach($result4 as $row4 ) {
        $row3['narratives'][] = $row4;
    }
}
        $row2['level_elements'][] = $row3;
    }
        $row1['levels'][] = $row2;
    }
        $json['games'][] = $row1;
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
