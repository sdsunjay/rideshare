<?php
session_start();
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

function outputToFile($myfile,$text)
{

   $text = cleanPost($text);          
   fwrite($myfile, $text);
   fwrite($myfile,"\n\n"); 
}

function outputToScreen($name,$post_id,$text,$link,$created_time,$updated_time)
{
   //echo '<br>';
   $name = cleanName($name);
   echo 'Name: '.$name;
   //echo '<br>';
   //echo 'ID: ' . $post_id;
   echo '<br>';
   $text = strip_tags($text);
   echo 'Original text: '.$text;
   echo '<br>';
   $text = cleanPost($text);
   echo 'Cleaned text: '.$text;
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
function handleComments($id)
{
   //deal with comments on posts later
   //same with $id["likes"]
   $comments = $id["comments"];
   //echo 'comments: ';
   //print_r($comments);
}
function handleNext($next_url)
{

   if($next_url)
   {
      $parts = parse_url($next_url);
      parse_str($parts['query'], $query);
      $time = $query['until'];
      echo 'in next';
      return $time;
   }
   return NULL;

}
function handlePrevious($previous_url)
{

   if($previous_url)
   {
      $parts = parse_url($previous_url);
      parse_str($parts['query'], $query);
      $time = $query['since'];
      echo 'in previous';
      return $time;
   }
   return NULL;
}

function emptyElementExists($arr) 
{
   return array_search("", $arr) !== false;
}

function storePostInDB($data,$mysqli)
{
   if(emptyElementExists($data))
   {
      echo "Empty element exists in data <br>";
      print_r($data);
      return false;
   }
   /*	$user_name = $data[0];
	$post_id = $data[1];
	$post = $data[2];
	$link = $data[3];
	$created_time = $data[4];
	$updated_time = $data[5];
    */
   $parts = explode(" ", $data[0]);
   $last_name = $parts[1];
   $first_name = $parts[0];
   $first_name = cleanName($first_name);
   $last_name = cleanName($last_name);

   // NO LONGER storing users as difficult to deduplicate
   // CREATE TABLE users (user_id BIGINT UNSIGNED NOT NULL PRIMARY KEY, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, email VARCHAR(50), BIRTHDATE DATETIME, gender VARCHAR(20), hometown VARCHAR(100), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
   /*
      $query_string="INSERT IGNORE INTO users(firstname,lastname) VALUES (?,?,?)"; 
      $stmt= $mysqli->prepare($query_string);

      $stmt->bind_param('ss',$first_name,$last_name);
   // Execute the prepared query.
   if ($stmt->execute()) 
   {
   $stmt->close();
   //return true;
   }
   else
   {
   return false;
   }*/
   //Table only needs to be created once
   //CREATE TABLE posts (id BIGINT UNSIGNED NOT NULL PRIMARY KEY, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, post text NOT NULL, link VARCHAR(2084) NOT NULL, created_time DATETIME NOT NULL, updated_time DATETIME NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
   /*
      $post_id = $data[1];
      $post = $data[2];
      $link = $data[3];
      $created_time = $data[4];
      $updated_time = $data[5];
    */
   $query_string="INSERT IGNORE INTO posts(id,first_name,last_name,post,link,created_time,updated_time) VALUES (?,?,?,?,?,?,?)"; 
   $stmt= $mysqli->prepare($query_string);
   $stmt->bind_param('sssssss',$data[1],$first_name,$last_name,$data[2],$data[3],$data[4],$data[5]);
   // Execute the prepared query.
   if ($stmt->execute()) 
   {
      $stmt->close();
      if ($mysqli->warning_count)
      {
	 if ($result = $mysqli->query("SHOW WARNINGS")) 
	 {
	    $row = $result->fetch_row();
	    printf("%s (%d): %s\n", $row[0], $row[1], $row[2]);
	    $result->close();
	 }
      }
   }
   else
   {
      echo "ERROR in inserting into post table";
      return false;
   }

   return true;

}

function parseUsersResponse($as,$outputToScreenFlag,$outputToFileFlag,$myfile,$outputToDBFlag)
{
   //	print_r($as);
   /*	foreach($as as $data): 

	$id = get_object_vars($as["data"][$i]); 
   //DO NOT DELETE, this is useful for debugging
   print_r($id);
   $i++;	
   //echo '<br>';
   //print_r($as);
   endforeach;
    */
}
function parsePostsResponse($as,$outputToScreenFlag,$outputToFileFlag,$myfile,$mysqli,$outputToDBFlag)
{
   $i = 0;
   foreach($as["data"] as $data): 

      $id = get_object_vars($as["data"][$i]); 
   //DO NOT DELETE, this is useful for debugging
   //print_r($id);
   $from = get_object_vars($id["from"]);
   $created_time = $id["created_time"];
   $updated_time = $id["updated_time"];
   $post_id = $from["id"];
   $name = $from["name"]; 
   $text = $id["message"]; 
   $lin = get_object_vars($id["actions"][0]);
   $link = $lin["link"];

   $dTime = strtotime($created_time);
   $created_time = date("Y-m-d H:i:s",$dTime);
   $dTime = strtotime($updated_time);
   $updated_time = date("Y-m-d H:i:s",$dTime);
   if($outputToFileFlag)
   {
      outputToFile($myfile,$text);
   }
   if($outputToScreenFlag)
   {	
      outputToScreen($name,$post_id,$text,$link,$created_time,$updated_time);
   }
   if($outputToDBFlag)
   {
      $array = array($name,$post_id,$text,$link,$created_time,$updated_time);
      if(storePostInDB($array,$mysqli) == false)
      {
	 echo "<br>Unable to store item in DB<br>";
	 //throw new Exception('unable to store in DB');
      }
   }
   $i++;
   //echo '<br>';
   //print_r($as);
   endforeach;
}

function handlePostsRequest($session,$requestString,$myfile,$mysqli,$outputToFileFlag,$outputToScreenFlag,$outputToDBFlag)
{


   // FOR LONG LIVED ACCESS TOKEN
   /// User logged in, get the AccessToken entity.
   //  $accessToken = $session->getAccessToken();
   // Exchange the short-lived token for a long-lived token.
   // $longLivedAccessToken = $accessToken->extend();	
   try {
      // with this session I will post a message to my own timeline
      /*	$request = new FacebookRequest(
		$session,
		'POST',
		'/me/feed',
		array('link' => 'www.google.com','message' => 'this is a test'));*/
      //$requestString='/GROUP_ID/feed?limit=2&since='.$since.'&until='.$until;
      //$requestString='/GROUP_ID/feed?limit=2';
      $request = new FacebookRequest($session,'GET',$requestString);
      //$request = new FacebookRequest($session,'POST',$requestString,array("method"=>"GET"));


      $counter = 0;
      //many many thanks to https://stackoverflow.com/questions/28230895/how-to-use-paging-next-in-the-new-facebook-php-sdk-4 for the code below	
      do {
	 //loop through each page, we only get 100 posts per page
	 $response = $request->execute();
	 $graphObject = $response->getGraphObject(GraphUser::className());
	 $data = $graphObject->asArray();
	 parsePostsResponse($data,$outputToScreenFlag,$outputToFileFlag,$myfile,$mysqli,$outputToDBFlag);
	 $counter+=100;
      } while ($request = $response->getRequestForNextPage());
      echo 'Got '.$counter.' posts. <br>';
   } catch ( FacebookRequestException $e ) {
      echo "Exception occured, code: " . $e->getCode();
      // show any error for this facebook request
      echo 'Facebook (post/get) request error: '.$e->getMessage();
   }
   catch(Exception $ex) {
      // When validation fails or other local issues
      echo "Unknown error! : (";

   }
   
   return true;
}
function initUserRequest($session,$groupID,$outputToScreenFlag,$outputToFileFlag,$outputToDBFlag)
{
   try {
      $request = new FacebookRequest($session,'GET',$requestString);


      //many many thanks to https://stackoverflow.com/questions/28230895/how-to-use-paging-next-in-the-new-facebook-php-sdk-4 for the code below	
      do {
	 $response = $request->execute();
	 $graphObject = $response->getGraphObject(GraphUser::className());
	 $data = $graphObject->asArray();
	 print_r($data);
      } while ($request = $response->getRequestForNextPage());
   } catch ( FacebookRequestException $e ) {
      echo "Exception occured, code: " . $e->getCode();
      // show any error for this facebook request
      echo 'Facebook (post/get) request error: '.$e->getMessage();
   }
   catch(Exception $ex) {
      // When validation fails or other local issues
      echo "Unknown error! : (";

   }
}
function initUsersRequest($session,$groupID,$outputToScreenFlag,$outputToFileFlag,$outputToDBFlag)
{
   $requestString = '/'.$groupID.'/members?limit=100';
   try {
      $request = new FacebookRequest($session,'GET',$requestString);
      do {
	 $response = $request->execute();
	 $graphObject = $response->getGraphObject(GraphUser::className());
	 $data = $graphObject->asArray();
	 print_r($data);
	 parseUsersResponse($data,$outputToScreenFlag,$outputToFileFlag,$myfile,$outputToDBFlag);
      } while ($request = $response->getRequestForNextPage());
   } catch ( FacebookRequestException $e ) {
      echo "Exception occured, code: " . $e->getCode();
      // show any error for this facebook request
      echo 'Facebook (post/get) request error: '.$e->getMessage();
   }
   catch(Exception $ex) {
      // When validation fails or other local issues
      echo "Unknown error! : (";

   }
}
function initPostsRequest($session,$groupID,$outputToScreenFlag,$outputToFileFlag,$outputToDBFlag)
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

      //get posts from 1 days ago
      if (($since = strtotime("-1 day")) === false) {
	 echo "The string ($str) is bogus";
	 throw new Exception('timestamp is bogus');
      }
   } catch( Exception $e ) {
      // Any other error
      echo 'Other (session) request error: '.$e->getMessage();
      return false;
   }
   if($outputToFileFlag)
   {
      $name_of_file=date("Y:n:d:H:i:s")."_training.txt";
      $myfile = fopen($name_of_file, "a") or die("Unable to open file!");
   }
   if($outputToScreenFlag)
   {
      echo 'Date: '.date("Y:n:d:H:i:s");
   }
   if($outputToDBFlag)
   {

      $mysqli = new mysqli(HOST, USERNAME, PASSWORD, DATABASE_NAME);
      if ($mysqli->connect_errno) 
      {
	 echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
      }
   }
   $requestString='/'.$groupID.'/feed?limit=100&since='.$since;
   if(handlePostsRequest($session,$requestString,$myfile,$mysqli,$outputToFileFlag,$outputToScreenFlag,$outputToDBFlag))
   {
      if($outputToFileFlag)
      {
	 //write to file
	 fclose($myfile);
      }
      if($outputToDBFlag)
      {
         $mysqli->close();
      }
      return true;
   }
   return false;

}



//Cali
date_default_timezone_set("America/Los_Angeles");
//date_default_timezone_set('UTC');

// adapted from
// https://www.webniraj.com/2014/05/01/facebook-api-php-sdk-updated-to-v4-0-0/

// initialize your app using your key and secret
FacebookSession::setDefaultApplication(API_KEY, API_SECRET);

// create a helper opject which is needed to create a login URL
// the REDIRECT_LOGIN_URL is the page a visitor will come to after login
$helper = new FacebookRedirectLoginHelper(REDIRECT_LOGIN_URL);

// First check if this is an existing PHP session
if ( isset( $_SESSION ) && isset( $_SESSION['fb_token'] ) ) {
   // create new session from the existing PHP sesson
   $session = new FacebookSession( $_SESSION['fb_token'] );
   try {
      // validate the access_token to make sure it's still valid
      if ( !$session->validate() ) 
	 $session = null;
   } catch ( Exception $e ) {
      // catch any exceptions and set the sesson null
      $session = null;
      echo 'No session: '.$e->getMessage();
   }
}  elseif ( empty( $session ) ) {
   // the session is empty, we create a new one
   try {
      // the visitor is redirected from the login, let's pickup the session
      $session = $helper->getSessionFromRedirect();
   } catch( FacebookRequestException $e ) {
      // Facebook has returned an error
      echo 'Facebook (session) request error: '.$e->getMessage();
   } catch( Exception $e ) {
      // Any other error
      echo 'Other (session) request error: '.$e->getMessage();
   }
}
//IF WE GOT A SESSION, leggo
if ( isset( $session ) ) {

   //do we want to output to a file
   $outputToFileFlag = false;
   //do we want to output to a database
   $outputToDBFlag = true;
   //do we want to output to the screen (for debugging purposes)
   $outputToScreenFlag = false;


   try {
      // store the session token into a PHP session
      $_SESSION['fb_token'] = $session->getToken();
      // and create a new Facebook session using the cururent token
      // or from the new token we got after login
      $session = new FacebookSession( $session->getToken() );
   } catch( FacebookRequestException $e ) {
      // Facebook has returned an error
      echo 'Facebook (session) request error: '.$e->getMessage();
   } catch( Exception $e ) {
      // Any other error
      echo 'Other (session) request error: '.$e->getMessage();
   }

   //	initUserRequest($session,GROUP_ID,$outputToScreenFlag,$outputToFileFlag,$outputToDBFlag);
   //	initUsersRequest($session,GROUP_ID,$outputToScreenFlag,$outputToFileFlag,$outputToDBFlag);
   if(initPostsRequest($session,GROUP_ID,$outputToScreenFlag,$outputToFileFlag,$outputToDBFlag))
   {
      echo "Success!";
   }
} else {
   // we need to create a new session, provide a login link
   echo 'No session, please <a href="'. $helper->getLoginUrl( array( 'publish_actions' ) ).'">login</a>.';
}

// use this for testing only
//unset($_SESSION['fb_token']);
?>
