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
//Cali
date_default_timezone_set("America/Los_Angeles");

if (!session_id()) {
       session_start();
}

// initialize your app using your key and secret
$fb = new Facebook\Facebook([
      'app_id' => APP_ID,
      'app_secret' => APP_SECRET,
      'default_graph_version' => 'v2.5',
]);

// create a helper opject which is needed to create a login URL
// the REDIRECT_LOGIN_URL is the page a visitor will come to after login
$helper = $fb->getRedirectLoginHelper();
$permissions = ['email']; // optional
$loginUrl = $helper->getLoginUrl(REDIRECT_LOGIN_URL, $permissions);

// First check if this is an existing PHP session
/*
// Choose your app context helper
$helper = $fb->getCanvasHelper();
//$helper = $fb->getPageTabHelper();
//$helper = $fb->getJavaScriptHelper();

// Grab the signed request entity
$sr = $helper->getSignedRequest();

// Get the user ID if signed request exists
$user = $sr ? $sr->getUserId() : null;

if ( $user ) {
   try {

      // Get the access token
      $accessToken = $helper->getAccessToken();
   } catch( Facebook\Exceptions\FacebookSDKException $e ) {

      // There was an error communicating with Graph
      echo $e->getMessage();
      exit;
   }
}

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
}*/
//IF WE GOT A SESSION, leggo
if ( isset( $session ) ) {
   echo '<p>Visit <a href="'. REDIRECT_LOGIN_URL .'">here</a></p>';
} else {
   echo '<p>No Facebook Login Session.</p>'; 
   // we need to create a new session, provide a login link
   echo '<p><a href="' . $loginUrl . '">Log in with Facebook!</a></p>';
   //echo 'No session, please <a href="'. $helper->getLoginUrl( array( 'publish_actions' ) ).'">login</a>.';
}
?>
