<?php

function debug() {
    $trace = debug_backtrace();
    $rootPath = dirname(dirname(__FILE__));
    $file = str_replace($rootPath, '', $trace[0]['file']);
    $line = $trace[0]['line'];
    $var = $trace[0]['args'][0];
    $lineInfo = sprintf('<div><strong>%s</strong> (line <strong>%s</strong>)</div>', $file, $line);
    $debugInfo = sprintf('<pre>%s</pre>', print_r($var, true));
    print_r("<h3><br>____-START DEBUG-____<br></h3>".$lineInfo.$debugInfo);
	echo "<h3>____-END DEBUG-____</h3>";
}
function indentJSON($json) {
$result = '';
$pos = 0;
$strLen = strlen($json);
$indentStr = '__';
$newLine = "<br>\n";
$prevChar = '';
$outOfQuotes = true;
for($i = 0; $i <= $strLen; $i++) {
// Grab the next character in the string
$char = substr($json, $i, 1);
// Are we inside a quoted string?
if($char == '"' && $prevChar != '\\') {
$outOfQuotes = !$outOfQuotes;
}
// If this character is the end of an element, 
// output a new line and indent the next line
else if(($char == '}' || $char == ']') && $outOfQuotes) {
$result .= $newLine;
$pos --;
for ($j=0; $j<$pos; $j++) {
$result .= $indentStr;
}
}
// Add the character to the result string
$result .= $char;
// If the last character was the beginning of an element, 
// output a new line and indent the next line
if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
$result .= $newLine;
if ($char == '{' || $char == '[') {
$pos ++;
}
for ($j = 0; $j < $pos; $j++) {
$result .= $indentStr;
}
}
$prevChar = $char;
}
return $result;
}

	$data=file_get_contents('./data.txt',true);
	$data = str_replace('|', ' ', $data);
/* 	echo $data;
	echo "<br>";
	echo "<br>"; */
	$decode=json_decode($data, true);
	//debug($decode);
	//echo "<br>";
	
// Client reading loop (compatible multi-site)
foreach ($decode as $data) {
	//debug($data);
	
	// Hosts reading loop
	echo "<br> Liste des HOSTGROUPS : <br><br>";
	foreach ($data['hosts'] as $hosts) {
		echo "____ HOST : ". $hosts['host'] . "<br>";
		echo "____ NAME : ". $hosts['name'] . "<br>";
		echo "____ STATUS : ". $hosts['status'] . "<br>";
		echo "____ DESCRIPTION : ". $hosts['description'] . "<br>";
		echo "<br>";
		
		// Alerts reading loop
		$cpt=0;
		foreach ($hosts['items'] as $items) {
			
			echo "________ NAME : ". $items['name'] . "<br>";
			echo "________ TYPE : ". $items['type'] . "<br>";
			echo "________ DELAY : ". $items['delay'] . "<br>";
			
			echo "____________ VALUE : ". $items['value'] . "<br>";
			echo "____________ VALUE_TYPE : ". $items['value_type'] . "<br>";
			$cpt++;
			if($cpt %2 ==0){
				echo "<br>";
			}
		}
		echo "<br>";
	}
}
	echo "<br>";

/*
Liste des HG affichés à la ligne 70 :
	- NXTemplates
	- TESTNX
	- NXTEST
	- NXXXXXX
	- testduNX
?>
