
<?php
//https://regex101.com/r/zP6aO1/1
// ^ this is for getting 07/17 
echo "Original:  ";
$str="ffering slo to san diego departing thursday dec 20 around 4pm san diego to slo departing friday jan 2";


echo $str;
//https://regex101.com/r/oQ1tZ9/1
//this works beter than below
//\b(?:jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sept(?:ember)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?)(\s)*(?:\d*)(?=\D|$)


// THIS WORKS BETTER THAN BELOW
//\b(?:jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sept(?:ember)|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?)(\s)*(?:\d*)(?=\D|$)


//\b(?:jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sept(?:ember)|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?) (?:\d*)(?=\D|$)
//works on regex101.com, just need to replace each month with number and then include number/day

$re = "/\\b(?:jan(?:uary)?|feb(?:ruary)?|dec(?:ember)?) (?:\\d*)(?=\\D|$)/"; 
$str = ""; 
$subst = ""; 
 
$str = preg_replace($re, $subst, $str);
echo "<br>";
echo "Actual: ";
echo $str;
echo "<br>";
echo "Expected:  ";
$exp="ffering slo to san diego departing thursday around 4pm san diego to slo departing friday";
echo $exp;

