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
/**
 * Remove contractions from the string (|str|)
 */
function removeContractions($str){

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

function removeSlang($str){
   $str = str_replace("l.a.",'la',$str);
   $str = str_replace("offerring",'offering',$str);
   $str = str_replace("seeeking",'seeking',$str);

   $str = str_replace("zzzzzzz",'z',$str);
   $str = str_replace("zzzzzz",'z',$str);
   $str = str_replace("zzzzz",'z',$str);
   $str = str_replace("zzzz",'z',$str);
   $str = str_replace("zzz",'z',$str);
   $str = str_replace("zz",'z',$str);

   $str = str_replace("deats",'details',$str);
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
   $str = str_replace("ya'll",'you all',$str);
   $str = str_replace("lmk",'let me know',$str);
   $str = str_replace("ppl",'people',$str);
   $str = str_replace("fyi",'for your information',$str);
   $str = str_replace("btw",'by the way',$str);
   $str = str_replace("tbd",'to be determined',$str);
   $str = str_replace("tba",'to be determined',$str);
   $str = str_replace("hmu",'hit me up',$str);
   $str = str_replace(" ty",' thank you',$str);
   $str = str_replace(" rn ",' right now ',$str);
   $str = str_replace(" ca ",' california ',$str);
   $str = str_replace("socal",'southern california',$str);
   $str = str_replace("ish",'',$str);
   //$str = str_replace("approx",'approximately',$str);
   //WTH"	What The Hell
   //omg	Oh My God
   //"ty" "thank you"
   //"dm" "direct message"
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
   $str = str_replace("====>>>>", ' to ', $str);
   $str = str_replace("===>>>", ' to ', $str);
   $str = str_replace("==>>>", ' to ', $str);
   $str = str_replace("===>>", ' to ', $str);
   $str = str_replace("==>>", ' to ', $str);
   $str = str_replace("==>", ' to ', $str);
   $str = str_replace("=>>", ' to ', $str);
   $str = str_replace("=>", ' to ', $str);
   $str = str_replace("---->>>>", ' to ', $str);
   $str = str_replace("---->>>", ' to ', $str);
   $str = str_replace("--->>>>", ' to ', $str);
   $str = str_replace("--->>>", ' to ', $str);
   $str = str_replace("----->>", ' to ', $str);
   $str = str_replace("---->>", ' to ', $str);
   $str = str_replace("--->>", ' to ', $str);
   $str = str_replace("-->>", ' to ', $str);
   $str = str_replace("-->>", ' to ', $str);
   $str = str_replace("->>", ' to ', $str);
   $str = str_replace("------->", ' to ', $str);
   $str = str_replace("------>", ' to ', $str);
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
   $str = str_replace("to to to to", 'to', $str);
   $str = str_replace("to to to", 'to', $str);
   $str = str_replace("to to", 'to', $str);

   return $str;
}

function removeHighways($str)
{

   $subst = '<HIGHWAY>';
   
   $re = '/101/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/405/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/680/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/880/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/80/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/134/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/210/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/280/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/85/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/87/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/71/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/126/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/hwy\s50/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/hwy\s5/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/highway\s5/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/i\s5/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/freeway\s5/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/the\s5/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/5\ssouth/';
   $str = preg_replace($re, $subst, $str);
   
   return $str;
}

function removeUniversityAbbreviations($str)
{

   //cal poly
   $str = str_replace("cal poly",'california polytechnic state university',$str);
   $str = str_replace("calpoly",'california polytechnic state university',$str);
   $str = str_replace(" poly ",'california polytechnic state university',$str);
   
   //university of california
   $re = '/\suc\s/';
   $subst = ' university of california ';
   $str = preg_replace($re, $subst, $str);
   
   //california state university
   $re = '/\scsu\s/';
   $subst = ' california state university ';
   $str = preg_replace($re, $subst, $str);

   //ucb
   $re = '/\sucb\s/';
   $subst = ' university of california berkeley ';
   $str = preg_replace($re, $subst, $str);

   //ucsb 
   $re = '/\sucsb\s/';
   $subst = ' university of california santa barbara ';
   $str = preg_replace($re, $subst, $str);

   //ucd
   $re = '/\sucd\s/';
   $subst = ' university of california davis ';
   $str = preg_replace($re, $subst, $str);
   
   //uci
   $re = '/\suci\s/';
   $subst = ' university of california irvine ';
   $str = preg_replace($re, $subst, $str);

   //ucla
   $re = '/\sucla\s/';
   $subst = ' university of california los angeles ';
   $str = preg_replace($re, $subst, $str);

   //ucm
   $re = '/\sucm\s/';
   $subst = ' university of california merced ';
   $str = preg_replace($re, $subst, $str);

   //ucr
   $re = '/\sucr\s/';
   $subst = ' university of california riverside ';
   $str = preg_replace($re, $subst, $str);

   //ucsd
   $re = '/\sucsd\s/';
   $subst = ' university of california san diego ';
   $str = preg_replace($re, $subst, $str);

   //ucsf
   $re = '/\sucsf\s/';
   $subst = ' university of california san francisco ';
   $str = preg_replace($re, $subst, $str);
   
   //ucsc
   $re = '/\sucsc\s/';
   $subst = ' university of california santa cruz ';
   $str = preg_replace($re, $subst, $str);

   //usc
   $re = '/\susc\s/';
   $subst = ' university of southern california ';
   $str = preg_replace($re, $subst, $str);

   //stanford
   $re = '/\sstanford\s/';
   $subst = ' stanford university ';
   $str = preg_replace($re, $subst, $str);
   
   //sjsu
   $re = '/\ssjsu\s/';
   $subst = ' san jose state university ';
   $str = preg_replace($re, $subst, $str);
   
   //sdsu
   $re = '/\ssdsu\s/';
   $subst = ' san diego state university ';
   $str = preg_replace($re, $subst, $str);
   
   //sfsu
   $re = '/\ssfsu\s/';
   $subst = ' san francisco state university ';
   $str = preg_replace($re, $subst, $str);
   
   //lmu
   $re = '/\slmu\s/';
   $subst = ' loyola marymount university ';
   $str = preg_replace($re, $subst, $str);

   return $str;
}

function removeDollars($str){
   $subst = ' <DOLLAR> ';

   #20$
   $re = '/\d+\$/';
   $str = preg_replace($re, $subst, $str);
   
   #$20
   $re = '/\$\d+/';
   $str = preg_replace($re, $subst, $str);
   
   $re = '/\$+/';
   $str = preg_replace($re, $subst, $str);
   
   return $str;
}



function removeAirportAbbreviations($str){

   //sf airport
   $str = str_replace("san francisco airport",'san francisco international airport',$str);
   
   //sfo
   $re = '/\ssfo\s/';
   $subst = ' san francisco international airport ';
   $str = preg_replace($re, $subst, $str);
   
   //la airport - los angeles international  airport
   $str = str_replace('los angeles airport','los angeles international airport',$str);

   //lax - los angeles international  airport
   $re = '/\slax\s/';
   $subst = ' los angeles international airport ';
   $str = preg_replace($re, $subst, $str);

   //sj airport
   $str = str_replace('san jose airport','mineta san jose international airport',$str);
   
   //sjc - mineta san jose international airport 
   $re = '/\ssjc\s/';
   $subst = ' mineta san jose international airport'; 
   $str = preg_replace($re, $subst, $str);
   
   //oakland airport
   $str = str_replace('oakland airport','oakland international airport',$str);
  
   //palm springs airport
   $str = str_replace('palm springs airport','palm springs international airport',$str);

   //sb airport
   $str = str_replace('santa barbara airport','santa barbara municipal airport',$str);
   
   //monterey airport
   $str = str_replace('monterey airport','monterey regional airport',$str);
   
   //santa maria airport
   $str = str_replace('santa maria airport','santa maria public airport',$str);
   
   //stockton airport
   $str = str_replace('stockton airport','stockton metropolitan airport',$str);
   
   //sd airport
   $str = str_replace('san diego airport','san diego international airport',$str);
   
   //oc airport
   $str = str_replace('orange county airport','john wayne airport',$str);
   $str = str_replace('santa ana airport','john wayne airport',$str);

   //slo airport - San Luis Obispo County Regional Airport
   $str = str_replace('san luis obispo airport','san luis obispo county regional airport',$str);

   //sbp - San Luis Obispo County Regional Airport
   $re = '/\ssbp\s/';
   $subst = ' san luis obispo county regional airport ';
   $str = preg_replace($re, $subst, $str);

   return $str;
}

//this function removes the obvious low-hanging fruit 
function removeCityAppreviations($str)
{

   //Los Angeles
   $re = '/la';
   $subst = 'los angeles';
   $str = preg_replace($re, $subst, $str);

   //sgv - san gabriel valley		
   $re = '/\ssgv\s/';
   $subst = ' san gabriel valley ';
   $str = preg_replace($re, $subst, $str);
   
   //sgv - san gabriel valley		
   $re = '/\sinland\sempire\s/';
   $subst = ' riverside county ';
   $str = preg_replace($re, $subst, $str);

   //sfv - san fernando valley
   $re = '/\ssfv\s/';
   $subst = ' san fernando valley ';
   $str = preg_replace($re, $subst, $str);
   
   //scv - santa clarita valley
   $re = '/\sscv\s/';
   $subst = ' santa clarita valley ';
   $str = preg_replace($re, $subst, $str);

   //sj - san jose
   $re = '/sj';
   $subst = 'san jose';
   $str = preg_replace($re, $subst, $str);

   //sf - san francisco
   $re = '/sf';
   $subst = 'san francisco';
   $str = preg_replace($re, $subst, $str);

   //slo - san luis obispo
   $re = '/slo/J';
   $subst = ' san luis obispo';
   $str = preg_replace($re, $subst, $str);

   //sb - santa barbara
   $re = '/sb';
   $subst = 'santa barbara';
   $str = preg_replace($re, $subst, $str);

   //sac - sacramento
   $re = '/sac';
   $subst = 'sacramento';
   $str = preg_replace($re, $subst, $str);

   //lb - long beach
   $subst = ' long beach';
   $re = '/\slb';
   $str = preg_replace($re, $subst, $str);
   $re = '/\slbc';
   $str = preg_replace($re, $subst, $str);

   //oc - orange county
   $re = '/oc';
   $subst = 'orange county';
   $str = preg_replace($re, $subst, $str);

   //iv - isla vista
   $re = '/iv';
   $subst = 'isla vista';
   $str = preg_replace($re, $subst, $str);

   //sd - san diego
   $re = '/sd';
   $subst = 'san diego';
   $str = preg_replace($re, $subst, $str);

   //tahoe - south lake tahoe
   //$re = '/\slatahoe\s/';
   //$subst = ' south lake tahoe ';
   //$str = preg_replace($re, $subst, $str);

   //southbay
   $str = str_replace("south bay area",'south bay',$str);
   //east bay area - east bay
   $str = str_replace("east bay area",'east bay',$str);
   //north bay area - north bay
   $str = str_replace("north bay area",'north bay',$str);
   //bayarea
   $str = str_replace("bay area",'bayarea',$str);

   return $str;

}
// Function for basic field validation (present and neither empty nor only white space
function isNullOrEmptyString($question){
   return (!isset($question) || trim($question)==='');
}

function removePunct($str)
{
   $str = str_replace("\n",' ',$str);
   $str = str_replace(";",'',$str);

   //remove st,rd,nd,th from numbers	
   $str = preg_replace('/\\b(\d+)(?:st|nd|rd|th)\\b/', '$1', $str);

   $str = str_replace("@", ' at ', $str);

   //this took forever to get right
   //remove slashes between words
   $re = "/([a-z]+)\\/(?=[a-z]+)/"; 
   $subst = "$1 ";  
   $str = preg_replace($re, $subst, $str);

   //remove slashes after a word
   $re = "/([a-z]+)\\/+ (?=[a-z]+)/"; 
   $subst = "$1 "; 
   $str = preg_replace($re, $subst, $str);

   //remove anything that is not letter, digit, slash, colon, or dollar sign
   $str = preg_replace('/[^a-z\:\$\d\/]+/i', ' ', $str);

   //Any 4 $ together)
   $re = "/\\$\\$\\$\\$/"; 
   $subst = "\$"; 
   $str = preg_replace($re, $subst, $str);

   //Any 3 $ together)
   $re = "/\\\$\\$\\$/"; 
   $subst = "\$"; 
   $str = preg_replace($re, $subst, $str);

   //Any 2 $ together)
   $re = "/\\\$\\$/"; 
   $subst = "\$"; 
   $str = preg_replace($re, $subst, $str);


   //remove colon not between two numbers
   $re = '/(\D{1}):(\D{1})/';
   $subst = '$1 ';
   $str = preg_replace($re, $subst, $str);
   return $str;
}

function cleanPost($str)
{
   if(isNullOrEmptyString($str))
   {
      echo 'Error: String is empty';
      return " ";
      //exit();
   }  
   //$str=strip_tags($str);
   $str=utf8_bad_strip_improved($str);
   $str = strtolower($str);

   //$str = remove_emoji($str);

   //Any 4 digits together)
   $re = "/\\d\\d\\d\\d/"; 
   $subst = ""; 
   $str = preg_replace($re, $subst, $str);

   //Remove area codes and highways
   //   $re = "/\\d\\d\\d/"; 
   //   $subst = ""; 
   //  $str = preg_replace($re, $subst, $str);

   $str = replaceTo($str);
   $str = removeContractions($str);
   $str = removePunct($str);
   $str = removeSlang($str);
   $str = removeCityAppreviations($str);
   $str = removeAirportAbbreviations($str);
   $str = removeUniversityAbbreviations($str);
   $str = removeHighways($str);
   $str = removeDollars($str);

   return $str;
}
function cleanName($str)
{
   $str=utf8_bad_strip_improved($str);
   $str = strtolower($str);

   $str = str_replace("\n",' ',$str);
   $str = remove_emoji($str);

   $str = str_replace(";",' ',$str);

   //remove anything that is not letter, digit or slash
   $str = preg_replace('/[^a-z\d\/]+/i', '', $str);
   return $str;
}
?>
