<?php
require_once('config.php');
//require_once('libs/utf8/utf8.php');
//require_once('libs/utf8/utils/bad.php');
//require_once('libs/utf8/utils/validation.php');
//require_once('libs/utf8_to_ascii/utf8_to_ascii.php');

//--------------------------------------------------------------------

/**

 * Tests a string as to whether it's valid UTF-8 and supported by the

 * Unicode standard

 * Note: this function has been modified to simple return true or false

 * @author <hsivonen@iki.fi>

 * @param string UTF-8 encoded string

 * @return boolean true if valid

 * @see http://hsivonen.iki.fi/php-utf8/

 * @see utf8_compliant

 * @package utf8

 * @subpackage validation

 */

function utf8_is_valid($str) {



   $mState = 0;     // cached expected number of octets after the current octet

   // until the beginning of the next UTF8 character sequence

   $mUcs4  = 0;     // cached Unicode character

   $mBytes = 1;     // cached expected number of octets in the current sequence



   $len = strlen($str);



   for($i = 0; $i < $len; $i++) {



      $in = ord($str{$i});



      if ( $mState == 0) {



	 // When mState is zero we expect either a US-ASCII character or a

	 // multi-octet sequence.

	 if (0 == (0x80 & ($in))) {

	    // US-ASCII, pass straight through.

	    $mBytes = 1;



	 } else if (0xC0 == (0xE0 & ($in))) {

	    // First octet of 2 octet sequence

	    $mUcs4 = ($in);

	    $mUcs4 = ($mUcs4 & 0x1F) << 6;

	    $mState = 1;

	    $mBytes = 2;



	 } else if (0xE0 == (0xF0 & ($in))) {

	    // First octet of 3 octet sequence

	    $mUcs4 = ($in);

	    $mUcs4 = ($mUcs4 & 0x0F) << 12;

	    $mState = 2;

	    $mBytes = 3;



	 } else if (0xF0 == (0xF8 & ($in))) {

	    // First octet of 4 octet sequence

	    $mUcs4 = ($in);

	    $mUcs4 = ($mUcs4 & 0x07) << 18;

	    $mState = 3;

	    $mBytes = 4;



	 } else if (0xF8 == (0xFC & ($in))) {

	    /* First octet of 5 octet sequence.

	     *

	     * This is illegal because the encoded codepoint must be either

	     * (a) not the shortest form or

	     * (b) outside the Unicode range of 0-0x10FFFF.

	     * Rather than trying to resynchronize, we will carry on until the end

	     * of the sequence and let the later error handling code catch it.

	     */

	    $mUcs4 = ($in);

	    $mUcs4 = ($mUcs4 & 0x03) << 24;

	    $mState = 4;

	    $mBytes = 5;



	 } else if (0xFC == (0xFE & ($in))) {

	    // First octet of 6 octet sequence, see comments for 5 octet sequence.

	    $mUcs4 = ($in);

	    $mUcs4 = ($mUcs4 & 1) << 30;

	    $mState = 5;

	    $mBytes = 6;



	 } else {

	    /* Current octet is neither in the US-ASCII range nor a legal first

	     * octet of a multi-octet sequence.

	     */

	    return FALSE;



	 }



      } else {



	 // When mState is non-zero, we expect a continuation of the multi-octet

	 // sequence

	 if (0x80 == (0xC0 & ($in))) {



	    // Legal continuation.

	    $shift = ($mState - 1) * 6;

	    $tmp = $in;

	    $tmp = ($tmp & 0x0000003F) << $shift;

	    $mUcs4 |= $tmp;



	    /**

	     * End of the multi-octet sequence. mUcs4 now contains the final

	     * Unicode codepoint to be output

	     */

	    if (0 == --$mState) {



	       /*

		* Check for illegal sequences and codepoints.

		*/

	       // From Unicode 3.1, non-shortest form is illegal

	       if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||

		     ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||

		     ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||

		     (4 < $mBytes) ||

		     // From Unicode 3.2, surrogate characters are illegal

		     (($mUcs4 & 0xFFFFF800) == 0xD800) ||

		     // Codepoints outside the Unicode range are illegal

		     ($mUcs4 > 0x10FFFF)) {



		  return FALSE;



	       }



	       //initialize UTF8 cache

	       $mState = 0;

	       $mUcs4  = 0;

	       $mBytes = 1;

	    }



	 } else {

	    /**

	     *((0xC0 & (*in) != 0x80) && (mState != 0))

	     * Incomplete multi-octet sequence.

	     */



	    return FALSE;

	 }

      }

   }

   return TRUE;

}

function utf8_bad_strip_improved($str) { // (C) SiMM, based on ru.wikipedia.org/wiki/Unicode
   $ret = '';
   $i = 0;
   $s = strlen($str);
   for (;$i < $s;) {
      $tmp = $str{$i++};
      $ch = ord($tmp);
      if ($ch > 0x7F) {
	 if ($ch < 0xC0) continue;
	 elseif ($ch < 0xE0) $di = 1;
	 elseif ($ch < 0xF0) $di = 2;
	 elseif ($ch < 0xF8) $di = 3;
	 elseif ($ch < 0xFC) $di = 4;
	 elseif ($ch < 0xFE) $di = 5;
	 else continue;

	 for ($j = 0;$j < $di;$j++) {
	    $tmp .= $ch = $str{$i + $j};
	    $ch = ord($ch);
	    if ($ch < 0x80 || $ch > 0xBF) continue 2;
	 }
	 $i += $di;
      }
      $ret .= $tmp;
   }
   return $ret;
}

/**
 * US-ASCII transliterations of Unicode text
 * @version $Id$
 * @package utf8_to_ascii
 */

if ( !defined('UTF8_TO_ASCII_DB') ) {
   define('UTF8_TO_ASCII_DB',dirname(__FILE__).'/db');
}

//--------------------------------------------------------------------
/**
 * US-ASCII transliterations of Unicode text
 * Ported Sean M. Burke's Text::Unidecode Perl module (He did all the hard work!)
 * Warning: you should only pass this well formed UTF-8!
 * Be aware it works by making a copy of the input string which it appends transliterated
 * characters to - it uses a PHP output buffer to do this - it means, memory use will increase,
 * requiring up to the same amount again as the input string
 * @see http://search.cpan.org/~sburke/Text-Unidecode-0.04/lib/Text/Unidecode.pm
 * @param string UTF-8 string to convert
 * @param string (default = ?) Character use if character unknown
 * @return string US-ASCII string
 * @package utf8_to_ascii
 */
function utf8_to_ascii($str, $unknown = '?') {

# The database for transliteration stored here
   static $UTF8_TO_ASCII = array();

# Variable lookups faster than accessing constants
   $UTF8_TO_ASCII_DB = UTF8_TO_ASCII_DB;

   if ( strlen($str) == 0 ) { return ''; }

   $len = strlen($str);
   $i = 0;

# Use an output buffer to copy the transliterated string
# This is done for performance vs. string concatenation - on my system, drops
# the average request time for the example from ~0.46ms to 0.41ms
# See http://phplens.com/lens/php-book/optimizing-debugging-php.php
# Section  "High Return Code Optimizations"
   ob_start();

   while ( $i < $len ) {

      $ord = NULL;
      $increment = 1;

      $ord0 = ord($str{$i});

# Much nested if /else - PHP fn calls expensive, no block scope...

# 1 byte - ASCII
      if ( $ord0 >= 0 && $ord0 <= 127 ) {

	 $ord = $ord0;
	 $increment = 1;

      } else {

# 2 bytes
	 $ord1 = ord($str{$i+1});

	 if ( $ord0 >= 192 && $ord0 <= 223 ) {

	    $ord = ( $ord0 - 192 ) * 64 + ( $ord1 - 128 );
	    $increment = 2;

	 } else {

# 3 bytes
	    $ord2 = ord($str{$i+2});

	    if ( $ord0 >= 224 && $ord0 <= 239 ) {

	       $ord = ($ord0-224)*4096 + ($ord1-128)*64 + ($ord2-128);
	       $increment = 3;

	    } else {

# 4 bytes
	       $ord3 = ord($str{$i+3});

	       if ($ord0>=240 && $ord0<=247) {

		  $ord = ($ord0-240)*262144 + ($ord1-128)*4096 
		     + ($ord2-128)*64 + ($ord3-128);
		  $increment = 4;

	       } else {

		  ob_end_clean();
		  trigger_error("utf8_to_ascii: looks like badly formed UTF-8 at byte $i");
		  return FALSE;

	       }

	    }

	 }

      }

      $bank = $ord >> 8;

# If we haven't used anything from this bank before, need to load it...
      if ( !array_key_exists($bank, $UTF8_TO_ASCII) ) {

	 $bankfile = UTF8_TO_ASCII_DB. '/'. sprintf("x%02x",$bank).'.php';

	 if ( file_exists($bankfile) ) {

# Load the appropriate database
	    if ( !include  $bankfile ) {
	       ob_end_clean();
	       trigger_error("utf8_to_ascii: unable to load $bankfile");
	    }

	 } else {

# Some banks are deliberately empty
	    $UTF8_TO_ASCII[$bank] = array();

	 }
      }

      $newchar = $ord & 255;

      if ( array_key_exists($newchar, $UTF8_TO_ASCII[$bank]) ) {
	 echo $UTF8_TO_ASCII[$bank][$newchar];
      } else {
	 echo $unknown;
      }

      $i += $increment;

   }

   $str = ob_get_contents();
   ob_end_clean();
   return $str;

}
function remove_emoji($str){


   $str = str_replace(":)",'',$str);
   $str = str_replace("(:",'',$str);
   $str = str_replace("(-:",'',$str);
   $str = str_replace(":-)",'',$str);
   $str = str_replace(":d",'',$str);
   $str = str_replace("d:",'',$str);

   return preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $str);
}
function helpInsertPostIntoClean($mysqli,$id,$post,$debugFlag)
{

   $cleaned_post = cleanPost($post);
   if($debugFlag)
   {
      echo "Original text:  ". $post."<br>";
      echo "Cleaned text: ". $cleaned_post."<br><br>";
      return true;
   }	
   //create table clean_posts (pid bigint unsigned not null comment 'post id',post text not null comment 'cleaned post', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,primary key (pid),foreign key(pid) references posts(id));	
   $query_string = "INSERT INTO clean_posts(pid,post) VALUES (?,?)"; 
   $stmt= $mysqli->prepare($query_string);
   $stmt->bind_param('ss',$id,$cleaned_post);
   // Execute the prepared query.
   if ($stmt->execute()) 
   {
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
   echo "ERROR in inserting into clean_post";
   return false;
}
function removeOldPostsFromCleanTable()
{
   mysql_connect(HOST,USERNAME,PASSWORD ) or die( mysql_error() );
   mysql_select_db(DATABASE_NAME) or die( mysql_error() );
   return mysql_query( "DELETE FROM clean_posts"); 
}
function insertPostIntoCleanTable($mysqli,$debugFlag)
{

   // GET 'dirty' posts
   $query_string = "select id,post from posts";

   $result = mysqli_query($mysqli, $query_string);
   $i = 0;
   if (mysqli_num_rows($result) > 0) {
      // output data of each row
      while($row = mysqli_fetch_assoc($result)) {
	 if(helpInsertPostIntoClean($mysqli,$row["id"],$row["post"],$debugFlag))
	 {
	    //echo $i."<br>";
	    /*if($i > 100)
	      {
	      echo "breaking at 100\n";
	      break;

	      }*/
	 }
	 else
	 {
	    echo "Error with ".$i." record. <br>"; 
	    break;
	 }
	 $i++;
      }
   } else {
      echo "<br>Error! No results <br>";
   }

   echo "SUCCESSFULLY INSERTED: ". $i." posts. <br>";
   mysqli_close($mysqli);	

}
//make contractions go away
function removeContractions($str)
{

   $str = str_replace("i'm",'i am',$str);
   $str = str_replace("i'll",'i will',$str);
   $str = str_replace("i'd",'i would',$str);
   $str = str_replace("i've",'i have',$str);

   $str = str_replace("you're",'you are',$str);
   $str = str_replace("you'll",'you will',$str);
   $str = str_replace("you'd",'you would',$str);
   $str = str_replace("you've",'you have',$str);

   $str = str_replace("he's",'he is',$str);
   $str = str_replace("he'll",'he will',$str);
   $str = str_replace("he'd",'he would',$str);
   $str = str_replace("he's",'he has',$str);
   $str = str_replace("she's",'she is',$str);
   $str = str_replace("she'll",'she will',$str);
   $str = str_replace("she'd",'she would',$str);
   $str = str_replace("she's",'she has',$str);

   $str = str_replace("you're",'you are',$str);
   $str = str_replace("you'll",'you will',$str);
   $str = str_replace("you'd",'you would',$str);
   $str = str_replace("you've",'you have',$str);


   $str = str_replace("it's",'it is',$str);
   $str = str_replace("it'll",'it will',$str);
   $str = str_replace("it'd",'it would',$str);

   $str = str_replace("we're",'we are',$str);
   $str = str_replace("we'll",'we will',$str);
   $str = str_replace("we'd",'we would',$str);
   $str = str_replace("we've",'we have',$str);

   $str = str_replace("they're",'they are',$str);
   $str = str_replace("they'll",'they will',$str);
   $str = str_replace("they'd",'they would',$str);
   $str = str_replace("they've",'they have',$str);

   $str = str_replace("that's",'that is',$str);
   $str = str_replace("that'll",'that will',$str);
   $str = str_replace("that'd",'that would',$str);

   $str = str_replace("who's",'who is',$str);
   $str = str_replace("who'll",'who will',$str);
   $str = str_replace("who'd",'who would',$str);

   $str = str_replace("what's",'what is',$str);
   $str = str_replace("what're",'what are',$str);
   $str = str_replace("what'll",'what will',$str);
   $str = str_replace("what'd",'what would',$str);


   $str = str_replace("where's",'where is',$str);
   $str = str_replace("where're",'where are',$str);
   $str = str_replace("where'll",'where will',$str);
   $str = str_replace("where'd",'where would',$str);

   $str = str_replace("when's",'when is',$str);
   $str = str_replace("when're",'when are',$str);
   $str = str_replace("when'll",'when will',$str);
   $str = str_replace("when'd",'when would',$str);

   $str = str_replace("why's",'why is',$str);
   $str = str_replace("why're",'why are',$str);
   $str = str_replace("why'll",'why will',$str);
   $str = str_replace("why'd",'why would',$str);

   $str = str_replace("how's",'how is',$str);
   $str = str_replace("how're",'how are',$str);
   $str = str_replace("how'll",'how will',$str);
   $str = str_replace("how'd",'how would',$str);

   $str = str_replace("p.s.",'postscript',$str);
   
   $str = str_replace("aren't",'are not',$str);

   $str = str_replace("couldn't",'could not',$str);
   $str = str_replace("can't",'cannot',$str);
   $str = str_replace("don't",'do not',$str);
   $str = str_replace("doesn't",'does not',$str);
   $str = str_replace("didn't",'did not',$str);

   $str = str_replace("haven't",'have not',$str);
   $str = str_replace("hasn't",'has not',$str);
   $str = str_replace("hadn't",'had not',$str);
   $str = str_replace("isn't",'is not',$str);

   $str = str_replace("shouldn't",'should not',$str);
   $str = str_replace("mightn't",'might not',$str);
   $str = str_replace("mustn't",'must not',$str);

   $str = str_replace("wasn't",'was not',$str);
   $str = str_replace("weren't",'were not',$str);
   $str = str_replace("won't",'will not',$str);
   $str = str_replace("wouldn't",'would not',$str);

   $str = str_replace("would've",'would have',$str);
   $str = str_replace("should've",'should have',$str);
   $str = str_replace("could've",'could have',$str);
   $str = str_replace("might've",'might have',$str);
   $str = str_replace("must've",'must have',$str);
   $str = str_replace("o'clock",'of the clock',$str);

   return $str;
}

function removeSlang($str)
{
   $str = str_replace("l.a.",'la',$str);

   $str = str_replace("zzzzzzz",'z',$str);
   $str = str_replace("zzzzzz",'z',$str);
   $str = str_replace("zzzzz",'z',$str);
   $str = str_replace("zzzz",'z',$str);
   $str = str_replace("zzz",'z',$str);
   $str = str_replace("zz",'z',$str);

   $str = str_replace("tmrw",'tomorrow',$str);
   $str = str_replace("tmr",'tomorrow',$str);
   $str = str_replace("wanna",'want to',$str);
   $str = str_replace("asap","as soon as possible",$str);
   $str = str_replace("lol",'',$str);
   $str = str_replace("haha",'',$str);
   $str = str_replace("idk",'i do not know',$str);
   $str = str_replace("plz",'please',$str);
   $str = str_replace("pls",'please',$str);
   $str = str_replace("thx",'thanks',$str);
   $str = str_replace("msg",'message',$str);
   $str = str_replace("ya'll",'you all',$str);
   //WTH"	What The Hell
   //LMK"	Let Me Know
   //omg	Oh My God
   //"ty" "thank you"
   //"dm" "direct message"
   //"idk"	"I do not know"
   //"bf"	Boy Friend
   //GF	Girl Friend
   //BFF	Best Friend Forever
   //DAT	That
   //K	Okay
   //B4	Before
   //L8	Later
   //L8ER	Later
   //ME2	Me Too
   //N	And
   //NOPE	No
   //PPL	People
   //SRY	Sorry
   //THANX, TNX, TX	Thanks
   //U	You
   //"ur"	Your
   //w/o	Without
   //"BTW"	By The Way
   //BRB	Be Right Back
   //JK	Just Kidding
   //FYI	For Your Information
   //"FB" "Facebook"

   return $str;
}
function replaceTo($str)
{

   $str = str_replace("----->", ' to ', $str);
   $str = str_replace("---->", ' to ', $str);
   $str = str_replace("--->", ' to ', $str);
   $str = str_replace("-->", ' to ', $str);
   $str = str_replace("-->", ' to ', $str);
   $str = str_replace("->", ' to ', $str);
   $str = str_replace(">>>>>>>>", ' to ', $str);
   $str = str_replace(">>>>>>>", ' to ', $str);
   $str = str_replace(">>>>>>", ' to ', $str);
   $str = str_replace(">>>>>", ' to ', $str);
   $str = str_replace(">>>>", ' to ', $str);
   $str = str_replace(">>>", ' to ', $str);
   $str = str_replace(">>", ' to ', $str);
   $str = str_replace(">", ' to ', $str);
   $str = str_replace("-----", ' to ', $str);
   $str = str_replace("----", ' to ', $str);
   $str = str_replace("---", ' to ', $str);
   $str = str_replace("--", ' to ', $str);
   $str = str_replace("-", ' to ', $str);

   return $str;
}

function removeHighways($str)
{

   $str = str_replace("101",'highway',$str);
   $str = str_replace("405",'highway',$str);
   $str = str_replace("680",'highway',$str);
   $str = str_replace("880",'highway',$str);
   $str = str_replace("80",'highway',$str);
   return $str;
}

//this function removes the obvious low-hanging fruit 
function removeObvious($str)
{

   //condense popular places first 
   //sgv - san gabriel valley		
   $str = str_replace("san gabriel valley",'sgv',$str);
   //sfv - san fernando valley
   $str = str_replace("san fernando valley",'sfv',$str);
   //sj
   $str = str_replace("san jose",'sj',$str);
   //sf
   $re = "/san\\sfran+[a-z]*/"; 
   $subst = "sf"; 
   $str = preg_replace($re, $subst, $str);
   //la
   $re = "/los\\sange+[a-z]*/"; 
   $subst = "la"; 
   $str = preg_replace($re, $subst, $str);

   //slo
   $re = "/san\\sluis\\sob+[a-z]*/"; 
   $subst = "slo";  
   $str = preg_replace($re, $subst, $str);

   //sb
   $re = "/santa\\sbar+[a-z]*/"; 
   $subst = "sb";  
   $str = preg_replace($re, $subst, $str);

   //sac
   $str = str_replace("sacremento",'sac',$str);
   $str = str_replace("sacramento",'sac',$str);
   //OC 
   $str = str_replace("orange county",'oc',$str);
   //OC 
   $str = str_replace("north county",'nc',$str);
   //LB
   $str = str_replace("long beach",'lb',$str);
   //IV
   $str = str_replace("isla vista",'iv',$str);
   //sd
   $str = str_replace("san diego",'sd',$str);
   //sd
   $str = str_replace("san deigo",'sd',$str);
   //tahoe
   $str = str_replace("south lake tahoe",'tahoe',$str);
   $str = str_replace("lake tahoe",'tahoe',$str);
   //southbay
   $str = str_replace("south bay area",'southbay',$str);
   $str = str_replace("south bay",'southbay',$str);
   //eastbay
   $str = str_replace("east bay area",'eastbay',$str);
   $str = str_replace("east bay",'eastbay',$str);
   //northbay
   $str = str_replace("north bay area",'northbay',$str);
   $str = str_replace("north bay",'northbay',$str);
   //bayarea
   $str = str_replace("bay area",'bayarea',$str);

   //create abbreviations and slang

   //sf airport
   $str = str_replace("sf airport",'sfo',$str);
   //la airport
   $str = str_replace("la airport",'lax',$str);
   //sj airport
   $str = str_replace("sj airport",'sjc',$str);
   //sb airport
   $str = str_replace("sb airport",'sba',$str);
   //slo airport
   $str = str_replace("slo airport",'sbp',$str);
   //cal poly
   $str = str_replace("cal poly",'calpoly',$str);
   return $str;

}   
function cleanPost($str)
{

   $str=strip_tags($str);
   $str=utf8_bad_strip_improved($str);
   $str = strtolower($str);

   $str = str_replace("\n",' ',$str);
   $str = remove_emoji($str);



   //remove area codes and highways (any 3 digits together)
   //$str = str_replace("880",'',$str);
   //$str = str_replace("209",'',$str);
   //$str = str_replace("310",'',$str);
   $re = "/\\d\\d\\d/"; 
   $subst = ""; 
   $str = preg_replace($re, $subst, $str);


   $str = str_replace(":",'',$str);
   $str = str_replace(";",'',$str);


#$str = str_replace("la",'los angeles',$str);
#$str = str_replace("slo",'san luis obispo',$str);
#$str = str_replace("sac",'sacramento',$str);
#$str = str_replace("sf",'san francisco',$str);
#$str = str_replace("sj",'san jose',$str);
#$str = str_replace("sd",'san diego',$str);
#$str = str_replace("sb",'santa barbara',$str);
#$str = str_replace("lb",'long beach',$str);

   $str = removeSlang($str);
   $str = replaceTo($str);
   $str = removeContractions($str);
   $str = removeObvious($str);
   $str = removeHighways($str);

   //remove st,rd,nd,th from numbers	
   $str = preg_replace('/\\b(\d+)(?:st|nd|rd|th)\\b/', '$1', $str);

   $str = str_replace("@", ' at ', $str);


   //remove $# from string
   $re = "/\\$\\d*/"; 
   $subst = ""; 

   $str = preg_replace($re, $subst, $str);

   //OMFG this took forever to get right
   //remove slashes between words
   $re = "/([a-z]+)\\/(?=[a-z]+)/"; 
   $subst = "$1 ";  
   $str = preg_replace($re, $subst, $str);

   //remove slashes after a word
   $re = "/([a-z]+)\\/+ (?=[a-z]+)/"; 
   $subst = "$1 "; 
   $str = preg_replace($re, $subst, $str);

   //remove anything that is not letter, digit or slash
   $str = preg_replace('/[^a-z\d\/]+/i', ' ', $str);
   return $str;
}
function cleanName($str)
{
   $str=utf8_bad_strip_improved($str);
   $str = strtolower($str);

   $str = str_replace("\n",' ',$str);
   $str = remove_emoji($str);

   $str = str_replace(":",' ',$str);
   $str = str_replace(";",' ',$str);

   //remove anything that is not letter, digit or slash
   $str = preg_replace('/[^a-z\d\/]+/i', '', $str);
   return $str;
}
?>
