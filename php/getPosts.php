<?php

require_once('config.php');
require_once('cleanText.php');
require __DIR__ . '/vendor/autoload.php';
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphUser;

function outputToFile($myfile,$text){

   $text = cleanPost($text);          
   fwrite($myfile, $text);
   fwrite($myfile,"\n\n"); 
}

function outputToScreen($postCount, $postId, $message, $createdTime){
   //echo $post_number . '.'; 
   //echo '<br>';
   //echo 'ID: ' . $post_id;
   //echo '<br>';
   $message = strip_tags($message);
   //echo 'Original text: ' . $message;
   //echo '<br>';
   $message = cleanPost($message);
   //echo 'Cleaned text: ' . $message;
   echo $message;
 //  echo '<br>';
  // echo "Posted at: " . $created_time;
  // echo '<br>';
   echo '<br>';
   /*echo '<br>';
     echo "Link: ".$link;
     echo '<br>';
     echo "Posted at: " . $created_time;
     echo '<br>';
     echo "Updated at: " . $updated_time;
    */
   /*$hasComments = true;
     if($dTime != $dTime1)
     {
     $hasComments = true;
   //handleComments($id)
   }
   else
   {
   $hasComments = false;
   }*/
   echo '<br>';
}

function storePostInDB($post_id, $message, $created_time, $mysqli)
{
   
   /*
   $query_string = "CREATE TABLE IF NOT EXISTS POSTS (id VARCHAR(100) PRIMARY KEY, message text NOT NULL, created_time DATETIME NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
   
   if (mysqli_query($mysqli, $query_string)) {
       echo "Table POSTS created successfully";
   } else {
       echo "Error creating table: " . mysqli_error($mysqli);
   }
   */
   //echo $post_id . '<br>';
  // echo $message . '<br>';
   //echo $created_time . '<br>';
   $query_string="INSERT IGNORE INTO POSTS(id,message,created_time) VALUES (?,?,?)"; 
   /* Prepared statement, stage 1: prepare */
   if (!($stmt = $mysqli->prepare($query_string))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      return false;
   }
   
   /* Prepared statement, stage 2: bind */
   if (!$stmt->bind_param("sss",$post_id, $message, $created_time)){	
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      return false;
   }	

   /* Prepared statement, stage 3: execute */
   if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      return false;
   }
   else{
      $stmt->close();
      if ($mysqli->warning_count){
	 if ($result = $mysqli->query("SHOW WARNINGS")) {
	    $row = $result->fetch_row();
	    printf("%s (%d): %s\n", $row[0], $row[1], $row[2]);
	    $result->close();
	 }
      }
   }
   echo "Successfully inserted. <br>";
   return true;

}

function getMoreInfo($fb, $post_id){

   $requestString = '/' . $post_id;
   $request = $fb->request('GET', $requestString);
   $response = $fb->getClient()->sendRequest($request);
   $graphObject = $response->getGraphObject();
   echo $graphObject;
   echo '<br>';
}


function parsePost($fb, $postCount, $post,$outputToScreenFlag,$outputToFileFlag,$myfile,$mysqli,$outputToDBFlag){

   $post_id = $post["id"];
   //getMoreInfo($fb, $post_id); 
   $message = $post["message"]; 
   $updated_time = $post["updated_time"];
   $created_time = $updated_time->format('Y-m-d H:i:s');

   //DO NOT DELETE, this is useful for debugging
   //print_r($id);

   if($outputToScreenFlag){	
      outputToScreen($postCount, $post_id, $message, $created_time);
   }
   if($outputToFileFlag){
      outputToFile($myfile,$message);
   }
   if($outputToDBFlag){
      if(storePostInDB($post_id, $message, $created_time, $mysqli) == false){
	 echo '<p>Error: Unable to store item in DB</p>';
      }
      else{

      }
   }

}

function handlePostsRequest($fb, $requestString,$myfile,$mysqli,$outputToFileFlag,$outputToScreenFlag,$outputToDBFlag)
{
   try {
      $request = $fb->request('GET', $requestString);
      $response = $fb->getClient()->sendRequest($request);
      $pagesEdge = $response->getGraphEdge();
      $pageCount = 0;
      $postCount = 0;

      do {
	 // Iterate over all the GraphNode's returned from the edge
	 foreach ($pagesEdge as $page) {
	    $post_data = $page->asArray();
	    parsePost($fb, $postCount, $post_data,$outputToScreenFlag,$outputToFileFlag,$myfile,$mysqli,$outputToDBFlag);
	    $postCount++;
	    //var_dump($post_data);
	 }
	 $pageCount++;
	 // Get the next page of results
      }while ($pagesEdge = $fb->next($pagesEdge));
      echo "Post Total: " . $postCount;
   } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      echo 'Graph returned an error: ' . $e->getMessage();
      return false;
   } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      return false;
   }

   return true;
}
/**
  *
  */
function initPostsRequest($fb, $outputToScreenFlag,$outputToFileFlag,$outputToDBFlag)
{
   //file descriptor
   $myfile = NULL;

   //mysql
   $mysqli = NULL;
   try {
      //get posts up until now
      if (($until = strtotime("now")) === false) {
	 echo "The string ($str) is bogus";
	 throw new Exception('timestamp is bogus');
      }		

      //get posts from 30 days ago
      if (($since = strtotime("-30 day")) === false) {
	 echo "The string ($str) is bogus";
	 throw new Exception('timestamp is bogus');
      }
   } catch( Exception $e ) {
      // Any other error
      echo 'Other (session) request error: '.$e->getMessage();
      return false;
   }
   if($outputToFileFlag){
      $name_of_file=date("Y:n:d:H:i:s")."_training.txt";
      $fp = fopen($name_of_file, "a") or die("Unable to open file!");
      if ( !$fp ) {
	 $outputToFileFlag = false;
	 echo '<p>Error: Unable to write to file</p>';
      }  
   }
   if($outputToScreenFlag){
      echo '<p>Date: '.date("Y:n:d:H:i:s").'</p>';
   }
   if($outputToDBFlag){
      $mysqli = new mysqli(HOST, USERNAME, PASSWORD, DATABASE_NAME);
      if ($mysqli->connect_errno) 
      {
	 echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
      }
      $outputToDBFlag = false;
   }
   //we only get 100 posts per page
   $requestString='/'. GROUP_ID .'/feed?limit=100&since='.$since;
   if(handlePostsRequest($fb, $requestString, $fp, $mysqli, $outputToFileFlag, $outputToScreenFlag, $outputToDBFlag)){
      if($outputToFileFlag){
	 fclose($fp);
      }
      if($outputToDBFlag){
	 $mysqli->close();
      }
      return true;
   }
   return false;
}

function main() {

   if (isset($_SESSION['fb_access_token'])) {
      //do we want to output to a file
      $outputToFileFlag = false;
      //do we want to output to a database
      $outputToDBFlag = false;
      //do we want to output to the screen (for debugging purposes)
      $outputToScreenFlag = true;

      try {
	 if(initPostsRequest($fb, $outputToScreenFlag,$outputToFileFlag,$outputToDBFlag)) {
	    return 0;
	 }
	 else{
	    echo 'An unkown error occured';
	 }
      } catch( FacebookRequestException $e ) {
	 // Facebook has returned an error
	 echo 'Facebook (session) request error: '.$e->getMessage();
      } catch( Exception $e ) {
	 // Any other error
	 echo 'Other (session) request error: '.$e->getMessage();
      }
   }
   else {
      echo 'No facebook session';
      echo '<p>Please <a href=\'login.php\'>log in</a>';
   }
}
/**
 * Calls the main program function
 */
main();
?>
