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
//Cali
date_default_timezone_set("America/Los_Angeles");

if (!session_id()) {
   ini_set('session.use_strict_mode', 1);
   $sid = md5(SESSION_ID_NUM);
   session_id($sid);
   session_start();
}

// initialize your app using your key and secret
$fb = new Facebook\Facebook([
      'app_id' => APP_ID,
      'app_secret' => APP_SECRET,
      'default_graph_version' => 'v2.8',
]);

// create a helper opject which is needed to create a login URL
// the REDIRECT_LOGIN_URL is the page a visitor will come to after login
$helper = $fb->getRedirectLoginHelper();
$permissions = ['email']; // optional
$loginUrl = $helper->getLoginUrl(REDIRECT_LOGIN_URL, $permissions);

//IF WE GOT A SESSION, leggo
if ( isset( $session ) ) {
   echo '<p>Visit <a href="'. REDIRECT_LOGIN_URL .'">here</a></p>';
} else {
   echo '<p>No Facebook Login Session.</p>'; 
   // we need to create a new session, provide a login link
   echo '<p><a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a><p>';
}
?>
