<?php
session_start();
require_once('cleanText.php');
require_once('config.php');

date_default_timezone_set("America/Los_Angeles");
$debugFlag=false;


$mysqli = new mysqli(HOST, USERNAME, PASSWORD, DATABASE_NAME);
if ($mysqli->connect_errno) 
{
   echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
else
{
   //if(removeOldPostsFromCleanTable() != false)
  // {
      insertPostIntoCleanTable($mysqli,$debugFlag);
  // }
}
?>

