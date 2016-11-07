<?php
require_once('cleanText.php');
require_once('datetimeParse.php');
require_once('config.php');

function helpInsertPostIntoClean($mysqli, $id, $cleaned_post, $table, $debugFlag){

   if(!$cleaned_post){
      echo "<p>Error: Clean post is empty. </p>";
      return false;
   }
   if($debugFlag){
      //echo "<p>Original text:  ". $post."</p>";
      echo "<p>Cleaned text: ". $cleaned_post."</p>";
      //return true;
   }	
   //create table clean_posts (pid bigint unsigned not null comment 'post id',post text not null comment 'cleaned post', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,primary key (pid),foreign key(pid) references posts(id));	
   $query_string = "INSERT INTO . " . $table . "(pid,post) VALUES (?,?)"; 

   /* Prepared statement, stage 1: prepare */
   if (!($stmt = $mysqli->prepare($query_string))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      return false;
   }

   /* Prepared statement, stage 2: bind */
   if (!$stmt->bind_param("ss",$id, $cleaned_post)){	
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      return false;
   }	

   /* Prepared statement, stage 3: execute */
   if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      return false;
   }else{
      $stmt->close();
      if ($mysqli->warning_count) {
	 if ($result = $mysqli->query("SHOW WARNINGS")) {
	    $row = $result->fetch_row();
	    printf("%s (%d): %s\n", $row[0], $row[1], $row[2]);
	    $result->close();
	 }
      }
      return true;
   }	
   //echo "<p>Error: Inserting into clean_post";
   return false;
}

function insertPostIntoCleanTable($mysqli,$debugFlag, $table){

   // Get 'dirty' posts
   $query_string = "select id,message from " . POSTS_TABLE;
   $i = 0;
   // Attempt select query execution
   if($result = mysqli_query($mysqli, $query_string)){
      if(mysqli_num_rows($result) > 0){
	 while($row = mysqli_fetch_array($result)){
	    if(!(helpInsertPostIntoClean($mysqli,$row["id"],cleanPost($row["message"]),$table,$debugFlag))){
	       echo "<p>Error: with ".$i." record.</p>"; 
	       break;
	    }
	    $i++;

	 }
	 // Close result set
	 mysqli_free_result($result);
      } else{
	 echo "<p>Error: No records matching your query were found.</p>";
	 return false;
      }
   } else{
      echo "<p>Error: Not able to execute " . $query_string  . mysqli_error($mysqli) . "</p>";
      return false;
   }

   echo "<p>Successfully inserted ". $i." posts into ".$table." </p>";
   // Close connection
   //mysqli_close($mysqli);	
   return true;
}

function removeOldPostsFromCleanTable(){
   mysql_connect(HOST,USERNAME,PASSWORD ) or die( mysql_error() );
   mysql_select_db(DATABASE_NAME) or die( mysql_error() );
   return mysql_query( "DELETE FROM " . CLEAN_POSTS_TABLE); 
}

function insertPostIntoCleanDateTable($mysqli,$debugFlag){

   // Get 'dirty' posts
   $query_string = "select pid,post from " . CLEAN_POSTS_TABLE;
   $i = 0;
   // Attempt select query execution
   if($result = mysqli_query($mysqli, $query_string)){
      if(mysqli_num_rows($result) > 0){
	 while($row = mysqli_fetch_array($result)){
	    if(!(helpInsertPostIntoClean($mysqli, $row["pid"], removeDatesDaysAndTime($row["post"]), "clean_date_posts", $debugFlag))){
	       echo "<p>Error: with ".$i." record.</p>"; 
	       break;
	    }
	    $i++;
	 }
	 // Close result set
	 mysqli_free_result($result);
      } else{
	 echo "<p>Error: No records matching your query were found.</p>";
	 return false;
      }
   } else{
      echo "<p>Error: Not able to execute " . $query_string  . mysqli_error($mysqli) . "</p>";
      return false;
   }

   echo "Successfully inserted ". $i." posts. <br>";
   // Close connection
   mysqli_close($mysqli);	
   return true;
}

date_default_timezone_set("America/Los_Angeles");
$debugFlag = true;

$mysqli = new mysqli(HOST, USERNAME, PASSWORD, DATABASE_NAME);
if ($mysqli->connect_errno) {
   echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
} else {
   if(removeOldPostsFromCleanTable() != false){
      if(insertPostIntoCleanTable($mysqli,$debugFlag, CLEAN_POSTS_TABLE)){
	 insertPostIntoCleanDateTable($mysqli,$debugFlag);
      }
   }
}
?>

