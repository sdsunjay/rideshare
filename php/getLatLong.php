<?php

function parseData($place,$data)
{
        //print_r($data);
      
//	$county = $data[0]['address_components'][1]['long_name'];
//	$county = preg_replace('/\W\w+\s*(\W*)$/', '$1', $county);
	//$ret=array($place,$county,$long_state,$short_state,$lat,$lng);
	/*
	foreach ($data[0]['geometry']['location'] as $key => $value) {
		echo $key;
	}
	*/
//        return $data;
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
	$five = "5";
	
	$str = preg_replace('/\W\w+\s*(\W*)$/', '$1', $data[0]);
	$stmt->bind_param('ssss',$str,$five,$data[4],$data[5]);
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
	//print_r($data);
}
function insertIntoCitesTable($mysqli,$data)
{


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
function outputPlaces($data)
{
//INSERT THESE FUCKERS!
print_r($data);
echo "<br>";
//echo $data[1]." ". $data[3]."<br>";
	return true;
}
function getStateID($mysqli,$state)
{

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
function getCountyNames($mysqli,$filename)
{
	$i=0;
	$handle = fopen($filename, "r");
	//$handle = fopen("temp.txt", "r");
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
function getLatAndLongForCity($mysqli,$city_name){
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

   $city_id = NULL;
   $lat = NULL;
   $long = NULL;
  /* bind result variables */
   if (!$stmt->bind_result($city_id, $lat, $long)) {
      echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
   }
    /* fetch values */
   //while ($stmt->fetch()) {
   if ($stmt->fetch()) {
    
      /* close statement */
      $stmt->close();
      printf("id = %s (%s), label = %s (%s)\n", $lat, gettype($lat), $long, gettype($long));
      return array($city_id, $lat,$long);
   }
    /* close statement */
   $stmt->close();
   return NULL;
}
function readFileLines($mysqli,$filename){
	$i=0;
	$handle = fopen($filename, "r");
	if ($handle) {
		while (($line = fgets($handle)) !== false) {
		  // process the line read.
		  if(empty($line) == false){
		     $pieces = explode(",", $line);
		     //university name and city
		     $placeIdAndCityId = getPlaceId($mysqli, $pieces[0], $pieces[2]);
		     $placeId = $placeIdAndCityId[0];
		     $cityId = $placeIdAndCityId[1];
		     if(empty($placeId) == false){
			$data = getGeoPosition($placeId, $mysqli);
			$lat = $data[0]['geometry']['location']['lat'];
			$lng = $data[0]['geometry']['location']['lng'];
			$name = $data[0]['name'];
			$address = $data[0]['formatted_address'];
			echo "Address: " . $address;
			//	insertPlace($name, $placeId, $lat, $lng, $address, $cityId);
		     } 
		  }
		  else
		  {
		     break;
		  }
		  $i++;
		}
		fclose($handle);
	} else {
	    echo "ERROR: on opening file";
	
	}
	echo "Read " . $i . " places"; 
	return $i;

}
function insertPlace($data){
   //print_r($data);
  
   $query_string="INSERT INTO PLACES (place_id) VALUES (?)"; 

   if (!($stmt = $mysqli->prepare($query_string))){	       
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      return false;
   }
   $stmt->bind_param('s',$placeId);
   // Execute the prepared query.
   if (!$stmt->execute()) {
      echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
      $stmt->close();
      return false;
   }
   $stmt->close();
   return true;
}
function getPlaceId($mysqli, $univerityName, $city){
   //get lat, long of city
   $latAndLong = getLatAndLongForCity($mysqli,$city);
   if(empty($latAndLong) == false){
      $city_id = $latAndLong[0];
      $lat = $latAndLong[1];
      $long = $latAndLong[2];
      $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?key=". MAPS_API_KEY  . "&location=" . urlencode($lat) . "," . urlencode($long) . "&radius=5000&name=" . urlencode(universityName) . "&type=university";
      $jsonData = file_get_contents($url);
      $data = json_decode($jsonData,TRUE);
      if($data['status']=="OK"){
         echo 'Status is okay';
	 $placeId = $data['results'][0]['place_id'];
	 return array($placeId, $city_id);
	 // return insertPlaceId($data);
      }
      else{
        echo 'Error: Status is not okay';
        return false;
      }
   }
   else{
      echo 'Error: lat and long array is empty';
      return false;
   }
}
function getGeoPosition($placeId,$mysqli){
   $url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=" . $placeId . "&key=" . MAPS_API_KEY;
     
   $jsonData   = file_get_contents($url);

   $data = json_decode($jsonData,TRUE);
   if($data['status']=="OK"){
      return $data['results'];
      //return insertIntoCitiesTable($mysqli,$arrayOfData);		
      //	return insertIntoCountiesTable($mysqli,$arrayOfData,$i);		
   }
   else
   {
      print_r($data);
   }
   return true;

}


$mysqli = new mysqli(HOST, USERNAME, PASSWORD, OLD_DATABASE_NAME);
if ($mysqli->connect_errno) 
{
   echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$filename = "one_university.csv";
//readFileLines($mysqli,"all_california_universities.csv");
//readFileLines($mysqli,"one_university.csv");
//getCityAndStateNames($mysqli,"popularCities.txt");
//getCountyNames($mysqli,"all_california_counties.txt");
//getCountyNames($mysqli,"other_counties.txt");
$placeId = readFileLines($mysqli,$filename);
$mysqli->close();
?>
