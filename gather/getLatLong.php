<?php
require_once('config.php');
function getCountyId($mysqli, $county_name){

   $query_string="SELECT county_id FROM COUNTIES WHERE county_name = ?";
   if (!($stmt = $mysqli->prepare($query_string))){	       
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
   }
   $stmt->bind_param('s',$county_name);
   // Execute the prepared query.
   if (!$stmt->execute()) {
      echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
   }
   //return false;

   $county_id = null;
   /* bind result variables */
   if (!$stmt->bind_result($county_id)) {
      echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
   }
   /* fetch values */
   //while ($stmt->fetch()) {
   if ($stmt->fetch()) {

      /* close statement */
      $stmt->close();
      //printf("%s\nlatitude = %s (%s), longitude = %s (%s)\n",$city_name, $lat, gettype($lat), $long, gettype($long));
      return $county_id; 
   }
   /* close statement */
   $stmt->close();
   return null;
}
function getStateId($mysqli, $state){

   $query_string="SELECT state_id FROM STATES WHERE state_name = ?";
   $stmt= $mysqli->prepare($query_string);
   $stmt->bind_param('s',$state);
   // Execute the prepared query.
   if ($stmt->execute()) 
   {
      $stmt->close();
      return true;
   }
   return false;
}
function getCountyNames($mysqli,$filename){
   $i=0;
   $handle = fopen($filename, "r");
   if ($handle) {
      while (($line = fgets($handle)) !== false) {
	 // process the line read.
	 $myArray = explode(',', $line);
	 //echo $line."<br>";
	 if(getGeoPosition(trim($myArray[0]),trim($myArray[1]),$mysqli,$i+1)==false)
	 {
	    echo "Error occurred after " . $i . " record";
	    return false;
	 }
	 $i++;
      }
      fclose($handle);
   } else {
      echo "on open file";
      return false;
   }
   echo "Read and inserted " . $i . " counties"; 
   return true;
}
function readAFile($mysqli,$filename){
   $i=0;
   $handle = fopen($filename, "r");
   if ($handle) {
      while (($line = fgets($handle)) !== false) {
	 // process the line read.
	 if(getGeoPosition(trim($line),"CA",$mysqli,$i+1)==false)
	    break;
	 $i++;
      }
      fclose($handle);
   } else {
      echo "on open file";
      // error opening the file.
   }
   echo "Read " . $i . " places"; 
}
function getCityAndStateNames($mysqli,$filename){
   $i=0;
   $handle = fopen($filename, "r");
   if ($handle) {
      while (($line = fgets($handle)) !== false) {
	 // process the line read.
	 $temp = explode(',', $line);;
	 if(getGeoPosition($temp[0],$temp[1],$mysqli,0)==false)
	 {
	    break;
	 }
	 //	break;
	 $i++;
      }
      fclose($handle);
   } else {
      echo "on open file";
      // error opening the file.
   }
   echo "Read" . $i . " places"; 
}
function getLatAndLongForCity($mysqli, $city_name){
   if(empty($city_name) == false){
      $query_string="SELECT city_id, latitude, longitude FROM CITIES WHERE city_name = ?";
      if (!($stmt = $mysqli->prepare($query_string))){	       
	 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      }
      $stmt->bind_param('s',$city_name);
      // Execute the prepared query.
      if (!$stmt->execute()) {
	 echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
      }
      //return false;

      $city_id = null;
      $lat = null;
      $long = null;
      /* bind result variables */
      if (!$stmt->bind_result($city_id, $lat, $long)) {
	 echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      }
      /* fetch values */
      //while ($stmt->fetch()) {
      if ($stmt->fetch()) {

	 /* close statement */
	 $stmt->close();
	 //printf("%s\nlatitude = %s (%s), longitude = %s (%s)\n",$city_name, $lat, gettype($lat), $long, gettype($long));
	 return array($city_id, $lat,$long);
      }
      /* close statement */
      $stmt->close();
   }
   return null;
   }


function cleanText($str)
{
   $str = strtolower($str);

   $str = str_replace("\n",' ',$str);
   $str = str_replace(";",' ',$str);

   //remove anything that is not letter, digit, slash, or space
   $str = preg_replace('/[^a-z\d\/\s]+/i', '', $str);
   return $str;
}
function insertIntoCountiesTable($mysqli,$data,$i)
{
/**

	$ret=array($city,$county,$long_state,$short_state,$lat,$lng);
	$data[0] = countyName
	$data[4] = lat
	$data[5] = lng
*/
//create table COUNTIES (county_id smallint unsigned not null auto_increment comment 'PK: Unique county ID', county_name varchar(128) not null comment 'county name', state_id smallint not null comment 'state where county is located)',latitude decimal(10,7) not null, longitude decimal(10,7) not null, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,primary key (county_id),foreign key(state_id) references STATES(state_id));
	$query_string="INSERT INTO COUNTIES (county_name,state_id,latitude,longitude) VALUES (?,?,?,?)"; 
	$stmt= $mysqli->prepare($query_string);
	
	$str = preg_replace('/\W\w+\s*(\W*)$/', '$1', $data[0]);
	$stmt->bind_param('ssss',$str,"5",$data[4],$data[5]);
	// Execute the prepared query.
	if ($stmt->execute())
	{
		$stmt->close();
		return true;
	}
	 echo "error!";
	 return false;

/*
	echo 'name: ' . $data[0];
	echo "   " .$data[4];
	echo ", " .$data[5];
	echo "<br>";
	return true;
*/
}
function insertCity($mysqli, $city_name){
      $data = getGeoPositionByAddress($city_name . ",CA", $mysqli);
      if($data == false)
      {
	 echo "An unknown error with data has occurred \n";
	 break;
      }
      elseif(empty($data) == false){
	 $lat = $data['geometry']['location']['lat'];
	 $lng = $data['geometry']['location']['lng'];
	 $countyName = $data['address_components'][1]['long_name'];
         $countyName = str_replace("County",'',$countyName);
	 $countyId = getCountyId($mysqli, trim($countyName));
	 $queryString="INSERT INTO CITIES (city_name, latitude, longitude, county_id) VALUES (?, ?, ?, ?)"; 
	 if (!($stmt = $mysqli->prepare($queryString))){	       
	    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	    return false;
	 }
	 $stmt->bind_param('sssi', trim($city_name), $lat, $lng, intval($countyId));
	 // Execute the prepared query.
	 if (!$stmt->execute()) {
	    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
	    $stmt->close();
	    return false;
	 }
	 $stmt->close();
	 return true;
      }
      return false;
}
function insertIntoCitesTable($mysqli,$data){
/**
	$ret=array($city,$county,$long_state,$short_state,$lat,$lng);
	$data[0] = cityName
	$data[1] = county
	$data[2] = long_state
	$data[3] = short_state
	$data[4] = lat
	$data[5] = lng
*/
//CREATE TABLE CITIES (id INT NOT NULL AUTO_INCREMENT, city VARCHAR(255) NOT NULL, county VARCHAR(255), long_state VARCHAR(255),short_state VARCHAR(5), latitude DECIMAL(10, 6),longitude DECIMAL(10, 6),created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);
	$query_string="INSERT INTO CITIES(city,county,long_state,short_state,latitude,longitude) VALUES (?,?,?,?,?,?)"; 
	$stmt= $mysqli->prepare($query_string);
	$stmt->bind_param('ssssss',$data[0],$data[1],$data[2],$data[3],$data[4],$data[5]);
	// Execute the prepared query.
	if ($stmt->execute()) 
	{
	   $stmt->close();
	   return true;
	}
	return false;

}
function readFileLines($mysqli,$filename){
   $i=0;
   $handle = fopen($filename, "r");
   if ($handle) {
      while (($line = fgets($handle)) !== false) {
	 // process the line read.
	 if(empty($line) == false){
	    $pieces = explode(",", $line);
	    // university name
	    $pieces[0] = cleanText($pieces[0]);
	    // city
	    //$pieces[2] = cleanText($pieces[2]);
	    $cityId = getCityId($mysqli, $pieces[2], false);
	    //$placeIdAndCityId = getPlaceId($mysqli, $pieces[0], $pieces[2]);
	    //$placeId = $placeIdAndCityId[0];
	    //$cityId = $placeIdAndCityId[1];
	    if(empty($cityId) == false){
	       $address = $pieces[1] . ", " . $pieces[2] . ", " . $pieces[3] . " " . $pieces[4];
	       $data = getGeoPositionByAddress($address, $mysqli);
	       if($data == false)
	       {
		  echo "An unknown error with data has occurred \n";
		  break;
	       }
	       elseif(empty($data) == false){
		  $lat = $data['geometry']['location']['lat'];
		  $lng = $data['geometry']['location']['lng'];
		  echo "name: " . $pieces[0] . "\n";
		  //$name = $data['name'];
		  //$address = $data['formatted_address'];
		  //$zip = $data['address_components'][5]['long_name'];
		  if( $i!= 0 && $i % 10 == 0){
		     echo "Sleeping for 10 seconds \n";
		     // current time
		     echo date('h:i:s') . "\n";
		     sleep(10);
		     // wake up !
		     echo date('h:i:s') . "\n";
		  }
		  if(insertPlace($mysqli, $pieces[0], $lat, $lng, $pieces[1], $pieces[4], $cityId) == false){
		     echo "Error inserting " . $pieces[0] . "\n" ;
		     break;
		  }
	       }
	    }
	    else
	    {
	       echo "City ID is NULL.\n";
	       break;
	    }	
	 }
	 else{
	    echo "Error line in file is null or blank \n";
	    break;
	 }
	 $i++;
      }
      fclose($handle);
   } else {
      echo "Error opening file \n";

   }
   if($i <= 1){
      echo "Read " . $i . " place.\n"; 
   }
   else{
      echo "Read " . $i . " places.\n"; 
   }
   return $i;
}

function insertPlace($mysqli, $name, $lat, $lng, $address, $zipcode, $cityId){
   $query_string="INSERT INTO PLACES (long_name, latitude, longitude, address_1, zipcode, city_id) VALUES (?, ?, ?, ?, ?, ?)"; 
   if (!($stmt = $mysqli->prepare($query_string))){	       
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      return false;
   }
   $stmt->bind_param('sssssi', trim($name), $lat, $lng, trim($address), trim($zipcode), intval($cityId));
   // Execute the prepared query.
   if (!$stmt->execute()) {
      echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
      $stmt->close();
      return false;
   }
   $stmt->close();
   return true;
}
function getCityId($mysqli, $city_name, $flag){

   if(empty($city_name) == false){
      $query_string="SELECT city_id FROM CITIES WHERE city_name = ?";
      if (!($stmt = $mysqli->prepare($query_string))){	       
	 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      }
      $stmt->bind_param('s',$city_name);
      // Execute the prepared query.
      if (!$stmt->execute()) {
	 echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
      }
      //return false;

      $city_id = null;
      /* bind result variables */
      if (!$stmt->bind_result($city_id)) {
	 echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      }
      /* fetch values */
      //while ($stmt->fetch()) {
      if ($stmt->fetch()) {

	 /* close statement */
	 $stmt->close();
	 //printf("%s\nlatitude = %s (%s), longitude = %s (%s)\n",$city_name, $lat, gettype($lat), $long, gettype($long));
	 return $city_id;
      }
      else{
	 if($flag){
	    return null;
	 }
	 
	 // City does not exist in DB, insert it
	 if(insertCity($mysqli, $city_name)){
	    echo "Inserted: " . $city_name;
	    getCityId($mysqli, $city_name, true); 
	 }
      
      }
      /* close statement */
      $stmt->close();
   }
   return null;
   }
   function getPlaceId($mysqli, $universityName, $city){
      //get lat, long of city
      $latAndLong = getLatAndLongForCity($mysqli,$city);
      if(empty($latAndLong) == false){
	 $city_id = $latAndLong[0];
	 $lat = $latAndLong[1];
	 $long = $latAndLong[2];
	 $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?key=". MAPS_API_KEY  . "&location=" . urlencode($lat) . "," . urlencode($long) . "&radius=10000&name=" . urlencode($universityName) . "&type=university";
	 $jsonData = file_get_contents($url);
	 $data = json_decode($jsonData,TRUE);
	 if(empty($data) == false){
	    if($data['status']=="OK"){
	       $placeId = $data['results'][0]['place_id'];
	       //echo 'Place ID: ' . $placeId;
	       return array($placeId, $city_id);
	    }
	    else{
	       echo "Error: Status is not okay \n";
	    }
	 }
	 else{
	    echo "Error: Data is empty \n";
	 }
      }
      else{
	 echo "Error: lat and long array is empty\n";
      }
      return false;
   }
   function getGeoPositionByAddress($address, $mysqli){
      $url ="https://maps.google.com/maps/api/geocode/json?address=" . urlencode($address) . "&sensor=false";
      if(empty($address) == false){

	 $jsonData   = file_get_contents($url);
	 $data = json_decode($jsonData,TRUE);

	 if($data['status']=="OK"){
	    return $data['results'][0];
	 }
	 else{
	    echo "Error: There is no data for " . $address . "\n";
	 }
      }
      else{
	 echo "Error: Address is blank or null. \n";
      }
      return false;
   }
   function getGeoPositionByPlaceID($placeId,$mysqli){
      $url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=" . $placeId . "&key=" . MAPS_API_KEY;

      $jsonData   = file_get_contents($url);
      $data = json_decode($jsonData,TRUE);

      // for pretty printing
      //$json_string = json_encode($data['result'], JSON_PRETTY_PRINT);
      // echo $json_string;

      if($data['status']=="OK"){
	 return $data['result'];
      }
      else{
	 echo "Data does not contain 'result'\n";
      }
      return false;

   }


   $mysqli = new mysqli(HOST, USERNAME, PASSWORD, OLD_DATABASE_NAME);
   if ($mysqli->connect_errno) {
      echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
   }
   date_default_timezone_set("America/Los_Angeles");
   //readFileLines($mysqli,"all_california_universities.csv");
   //readFileLines($mysqli,"one_university.csv");
   //getCityAndStateNames($mysqli,"popularCities.txt");
   //getCountyNames($mysqli,"all_california_counties.txt");
   //getCountyNames($mysqli,"other_counties.txt");
   $filename = "cleaned_all_universities1.csv";
   //$filename = "one_university.csv";
   readFileLines($mysqli,$filename);
   $mysqli->close();
   ?>
