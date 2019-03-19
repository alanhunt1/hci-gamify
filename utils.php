<?php
// Function for basic field validation (present and neither empty nor only white space
function IsNullOrEmpty($question){
  return (!isset($question) || trim($question)==='');
}
function isDateBetweenDates(DateTime $date, DateTime $startDate, DateTime $endDate) {
    return $date > $startDate && $date < $endDate;
}

function isValidJSON($str) {
   json_decode($str);
   return json_last_error() == JSON_ERROR_NONE;
}

function formatNull($str){
   if (IsNullOrEmpty($str)){
     return null;
   }
   return $str;
}
?>
