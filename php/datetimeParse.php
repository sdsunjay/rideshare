<?php
function matchTime($str){
   $re = '/anytime|morning|lunch|noon|morning\snoon|afternoon|evening|afternoon\sevening|night|tonight|\slate\s|early\smorning/';
   $str = preg_replace($re, "<\$TIME\$>", $str);
   $re = '/(?!\d{1,2}\/\d{1,2})\d{1,2}(\s)*(am|pm)*\sor\s\d{1,2}\s*(am|pm)*|(\d{1,2}:\d{1,2}(\s)*(am|pm)*)|(\d{1,2}(\s)*(am|pm)*)/';
   $str = preg_replace($re, "<\$TIME\$>", $str);
   return $str;
}

function matchQuantifier($str){
   $re = '/(\sat\s|\saround\s|\sin\sthe\s|\sat\saround\s|\sright\snow|\snext\s|\safter\s|\sin\s|\sbefore\s|\searly\s|\ssuper\searly\s|\svery\searly\s|\ssuper\slate\s|\svery\slate\s)*+((anytime|morning|lunch|noon|morning\snoon|afternoon|evening|afternoon\sevening|night|tonight|late|early\smorning)| (?!\d{1,2}\/\d{1,2})\d{1,2}(\s)*(am|pm)*\sor\s\d{1,2}\s*(am|pm)*|(\d{1,2}:\d{1,2}(\s)*(am|pm)*)|(\d{1,2}(\s)*(am|pm)*))/';
   $str = preg_replace($re, " <\$TIME\$> ", $str);
   $str = preg_replace($re, " <\$TIME\$> ", $str);
   return $str;
}

function matchDay($str){
   $re = '/monday|\smon\s|tuesday|tues\s|tue\s|weds\s|wednesday|wed\s|thursday|thurs\s|thur\s|friday|fri\s|saturday|sat\s|sunday|sun\s|today|tonight|tomorrow/';
   $str = preg_replace($re, "<\$DAY\$> ", $str);
   return $str;
}

function matchDate($str){
$re = '/((january|jan|february|feb|march|mar|april|apr|may|june|jun|july|jul|august|aug|september|sep|sept|october|oct|november|nov|december|dec)+[[:blank:]]+(\d+)([[:blank:]]+(\d{0,4}))?)|((\d{1,2}\/\d{1,2}(\/\d{1,2})*))/';
   $str = preg_replace($re, "<\$DATE\$>", $str);
   $str = preg_replace($re, "<\$DATE\$>", $str);
   return $str; 
}

function removeDatesDaysAndTime($str){
   //echo "<p> Original: " . $str . "</p>";
   $str = matchDate($str);
   $str = matchDay($str);
   $str = matchQuantifier($str);
   
   $str = matchTime($str);
   $re = '/<\$DAY\$>\s*<\$TIME\$>\s*<\$DATE\$>/';
   $str = preg_replace($re, "<\$DATESTAMP\$> ", $str);
   $re = '/<\$DAY\$>\s*<\$DATE\$>\s*<\$TIME\$>/';
   $str = preg_replace($re, "<\$DATESTAMP\$> ", $str);
   //<$DATE$><$DAY$><$TIME$>
   $re = '/<\$DATE\$>\s*<\$DAY\$>\s*<\$TIME\$>/';
   $str = preg_replace($re, "<\$DATESTAMP\$> ", $str);
   //<$DAY$><$TIME$>
   $re = '/<\$DAY\$>\s*<\$TIME\$>/';
   $str = preg_replace($re, "<\$DATESTAMP\$> ", $str);
   
   $re = '/<\$DAY\$>\s*<\$DATE\$>/';
   $str = preg_replace($re, "<\$DATESTAMP\$> ", $str);
   $re = '/<\$DATE\$>\s*<\$DAY\$>/';
   $str = preg_replace($re, "<\$DATESTAMP\$> ", $str);
   $re = '/<\$DATE\$>\s*<\$TIME\$>/';
   $str = preg_replace($re, "<\$DATESTAMP\$> ", $str);
   //echo "<p> Cleaned: " . $str . "</p>";
   return $str;
}
function testDateTimeParser(){

   $lines = array(
	 //[day] [date] [quantifier]* [time]
	 "seeking: slo to oakland thursday 11/10 7pm",
	 // [date] [day] [time]
	 "seeking: slo ---> sacramento / davis november 10 thursday after 9am will provide gas $$$",
	 //[date] [day] [quantifier] [time]
	 "seeking: slo to oakland 11/10 thursday around 7pm",
	 //[date] [quantifier] [time]
	 "seeking: slo to oakland 11/10/2016 around 7 or 8pm",
	 "seeking: SLO to Berkeley friday 11/11 anytime or slo to sac 11/11 arriving in sac before 11 am and Berkeley to SLO 11/13",
	 "seeking: SLO to bay area tuesday nov 22 afternoon/evening",
	 "offering from bayarea san mateo area to slo departing friday 8/7 between 6 to 7pm message me if interested",
	 "offering sac to slo this saturday august 8 can pick up along the way",
	 "offering north oc sgv la to slo sunday evening 8/9 around 6 or 7 returning tue 8/11 late afternoon can pickup or dropoff anywhere along the way",
	 "offering tahoe to slo 8/5/15 will leave around 4 pm from tahoe will take hwy 50 through sac and take int 5 can pick up along the way down asking for for gas ",
	 "offering west hills calabasas area to slo august 6 thursday morning around 9am",
	 "offering ride to slo petaluma northbay to slo august 7 8/7 at 6 pm can pick up anywhere along on the way slo to petaluma on sunday august 9 8/9 in the evening can drop off anywhere along my route",
	 "offering slo to bayarea menlo park / on the way to sfo thursday around 5pm",
	 "offering carpinteria right by sb up to sj friday night can pick up along the way",
	 "offering sj to la burbank area friday aug 7 super early am like 4 or 5 la anaheim to sj sunday august 9 2pm",
	 "offering slo to bayarea thursday afternoon",
	 "offering nc sd to slo wednesday 8/5 in the evening",
	 "offering slo to to eastbay oakland alameda thursday around 5pm eastbay to to slo next monday around 10am gas money appreciated",
	 "offering slo to bayarea august 7");
   foreach ($lines as $line) {
      removeDatesDaysAndTime($line);
   }
}
?>
