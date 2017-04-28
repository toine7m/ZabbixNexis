<?php

/*  

	  _____     _        ____     ____                __  __   _   _   U _____ u __  __              ____
	 |"_  /uU  /"\  u U | __")uU | __")u    ___       \ \/"/  | \ |"|  \| ___"|/ \ \/"/      ___    / __"| u
	 U / //  \/ _ \/   \|  _ \/ \|  _ \/   |_"_|      /\  /\ <|  \| |>  |  _|"   /\  /\     |_"_|  <\___ \/
	 \/ /_   / ___ \    | |_) |  | |_) |    | |      U /  \ uU| |\  |u  | |___  U /  \ u     | |    u___) |
	 /____| /_/   \_\   |____/   |____/   U/| |\u     /_/\_\  |_| \_|   |_____|  /_/\_\    U/| |\u  |____/>>
	 _//<<,- \\    >>  _|| \\_  _|| \\_.-,_|___|_,-.,-,>> \\_ ||   \\,-.<<   >>,-,>> \\_.-,_|___|_,-.)(  (__)
	(__) (_/(__)  (__)(__) (__)(__) (__)\_)-' '-(_/  \_)  (__)(_")  (_/(__) (__)\_)  (__)\_)-' '-(_/(__)

    INTRODUCTION:
    ZabbixNexis, little project for Zabbix during my internship
    Copyright (C) 2017 Massinon Antoine

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License 
    along with this program.  If not, see <http://www.gnu.org/licenses/>. 

    Author: Massinon Antoine
    Initial Version: 1.0
    Date: 25/04/2017
    Modifications:
    - qdqdqd auhor: date: version: 
*/

function expand_arr($array) {	
	foreach ($array as $key => $value) {
		if (is_array($value)) {			
			echo "<i>".$key."</i>:<br>";
			expand_arr($value);
			echo "<br>\n";
		} else {			
			echo "<i>".$key."</i>: ".$value."<br>\n";
		}		
	}
}

function json_request($uri, $data) {
	$json_data = json_encode($data);	
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $uri);
	curl_setopt($c, CURLOPT_CUSTOMREQUEST, "POST");                                                  
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($c, CURLOPT_POST, $json_data);
	curl_setopt($c, CURLOPT_POSTFIELDS, $json_data);
	curl_setopt($c, CURLOPT_HTTPHEADER, array(                                                                          
		'Content-Type: application/json',                                                                                
		'Content-Length: ' . strlen($json_data))                                                                       
	);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);	
	$result = curl_exec($c);
	
	// Uncomment to see some debug info
/*  	echo "<b>JSON Request:</b><br>\n";
	echo $json_data."<br><br>\n";

	echo "<b>JSON Answer:</b><br>\n";
	echo $result."<br><br>\n";

	echo "<b>CURL Debug Info:</b><br>\n";
	$debug = curl_getinfo($c);
	echo expand_arr($debug)."<br><hr>\n";   */
	

	return json_decode($result, true);
}

function zabbix_auth($uri, $username, $password) {
	$data = array(
		'jsonrpc' => "2.0",
		'method' => "user.login",
		'params' => array(
			'user' => $username,
			'password' => $password
		),
		'id' => "1"
	);	
	$response = json_request($uri, $data);	
	return $response['result'];
}

function zabbix_get_hostgroups($uri, $api) {
	$data = array(
		'jsonrpc' => "2.0",
		'method' => "hostgroup.get",
		'params' => array(
			'output' => "extend",
			'sortfield' => "name"
		),
		'id' => "2",
		'auth' => $api
	);	
	$response = json_request($uri, $data);	
	return $response['result'];
}

function getContent(){
	$data=file_get_contents('./data.txt',true);
	$data = str_replace('|', ' ', $data);
/* 	echo $data;
	echo "<br>";
	echo "<br>"; */
	$decode=json_decode($data, true);
	//debug($decode);
	//echo "<br>";
	return($decode);
}

function debug() {
//**************************************************
// Function debug
// Description: Indent and shape function for array
// Arguments: Array or multi-dim array to shape
// Return value: Shaped array/multi-dim array
//**************************************************
    $trace = debug_backtrace();
    $rootPath = dirname(dirname(__FILE__));
    $file = str_replace($rootPath, '', $trace[0]['file']);
    $line = $trace[0]['line'];
    $var = $trace[0]['args'][0];
    $lineInfo = sprintf('<div><strong>%s</strong> (line <strong>%s</strong>)</div>', $file, $line);
    $debugInfo = sprintf('<pre>%s</pre>', print_r($var, true));
    print_r($lineInfo.$debugInfo);
}

function indentJSON($json) {
//**************************************************
// Function identJSON
// Description: Indent and shape function for JSON
// Arguments: $json : JSON encoded data
// Return value: $result : indented and shaped JSON
//**************************************************
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

function row(){
//**************************************************
// Function row
// Description: simply draw double row with a first
// one "noshade"
//**************************************************
	printf("<hr noshade>");
    printf("<hr>");
}

function readDatas($decode,$api,$uri,$delay,$conn){
// Client reading loop (compatible multi-site)
foreach ($decode as $data) {
	//debug($data);
	$hgName=$data['name'];
	$hgId=hostgroupExist($hgName,$api,$uri);
	row();
	echo $hgId;
	row();
	// Hosts reading loop
	// echo "<br> Liste des HOSTS : <br><br>";
	foreach ($data['hosts'] as $hosts) {
		$hostName=$hosts['name'];
		$hostNameUnique=$hosts['host'];
		$hostId=hostExist($hgId,$hostName,$hostNameUnique,$api,$uri);
		
 		/* echo "____ HOST : ". $hosts['host'] . "<br>";
		echo "____ NAME : ". $hosts['name'] . "<br>";
		echo "____ STATUS : ". $hosts['status'] . "<br>";
		echo "____ DESCRIPTION : ". $hosts['description'] . "<br>";
		echo "<br>";  */
		
		//$cpt=0;
		// Alerts reading loop
		foreach ($hosts['items'] as $items) {
			$result=databaseFill($conn,$items,$hostName,$hgName);
			//debug($result);
			//debug($items);
			//itemCreate($hostId,$items,$delay,$api,$uri);
			/*  echo "________ NAME : ". $items['name'] . "<br>";
			echo "________ TYPE : ". $items['type'] . "<br>";
			echo "________ DELAY : ". $items['delay'] . "<br>";
			
			echo "____________ VALUE : ". $items['value'] . "<br>";
			echo "____________ VALUE_TYPE : ". $items['value_type'] . "<br>";
			$cpt++;
			if($cpt %2 ==0)
				echo "<br>"; */
			
		}
	}
}
}

function hostgroupExist($hgName,$api,$uri){
	
	$data = array(
		'jsonrpc' => "2.0",
		'method' => "hostgroup.get",
		'params' => array(
			'output' => array("groupid","name"),
			'sortfield' => "name",
			'filter' => array(
				'name' => $hgName)
		),
		'id' => "2",
		'auth' => $api
	);	
	$hg = json_request($uri, $data);	
	

	row();
	debug($hg);
	if($hg['result']){
		echo("Trouve!<br>");
		return $hg['result'][0]['groupid'];
	}
	else {
		echo "Impossible to find the Hostgroup, create it before launching this script !!";
	}
}

function hostExist($hgId,$hostName,$hostNameUnique,$api,$uri){
	
	$data = array(
		'jsonrpc' => "2.0",
		'method' => "host.get",
		'params' => array(
			'output' => array("hostid","host"),
			'filter' => array("host" => $hostNameUnique)
		),
		'id' => "2",
		'auth' => $api
	);	
	$host = json_request($uri, $data);	
	
	/* $host=$api->hostGet([
		'output' => ['hostid','host'],
		'filter' => ['host' => $hostName],
		'groupids' => $hgId
		]); */
		
	//row();
	//debug($host);
	if($host['result']){
		//echo "Found !!! : ".$hostName."<br>";
	}
	else {
		echo "not found : ".$hostName."<br>";
		hostCreate($hgId,$hostName,$hostNameUnique,$api,$uri);
	}
}

function hostCreate($hgId,$hostName,$hostNameUnique,$api,$uri){
	
	$data = array(
		'jsonrpc' => "2.0",
		'method' => "host.create",
		'params' => array(
			'host' => $hostNameUnique,
			'interfaces' => [array('type' => 1, 'main' => 1, 'useip' => 1, 'ip' => '0.0.0.0', 'dns' => '', 'port' => '10050')],
			'groups' => [array('groupid' => '17')],
			'name' => $hostName
		),
		'id' => "2",
		'auth' => $api
	);	
	$host = json_request($uri, $data);
	debug($host);

/* 	echo(gettype($hgId));
	echo(gettype($hostName));
	echo(gettype($hostNameUnique)); */
	echo $hgId."*".$hostName."*".$hostNameUnique."<br>";
	
	//hostExist($hgId,$hostName,$hostNameUnique,$api,$uri);
}

function itemCreate($hostId,$items,$delay,$api,$uri){
	debug($items);
	echo($hostId);
	$data = array(
		'jsonrpc' => "2.0",
		'method' => "item.get",
		'params' => array(
			'output' => array('extend'),
			'hostids' => $hostId,
			'sortfield' => "name"
		),
		'id' => "2",
		'auth' => $api
	);	
	$items = json_request($uri, $data);	
	

	row();
	//debug($items);
	if($items['result']){
		echo("Trouve!<br>");
		//return $items['result'][0]['groupid'];
	}
	else {
		echo "Impossible to find the item !!";
	}
	
}

function databaseConnection($dbuser,$dbpassword,$dbserver,$db){

	// Create connection
	$conn = new mysqli($dbserver, $dbuser, $dbpassword,$db);
	
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 
	
	
	/* /* Change la base de données en "world" */
	//$conn->select_db("supervisor");
 
/* Retourne le nom de la base de données courante */
 	if ($result = $conn->query("SELECT DATABASE()")) {
		$row = $result->fetch_row();
		//printf("La base de donnees courante est %s.\n", $row[0]);
		//debug ($row);
	} 
	return $conn;
	//debug($conn);
}

function databaseFill($conn,$items,$hostName,$hgName){
	
	// SELECT * FROM `alerts` WHERE host='host1' and hostgroup='client1'
	echo "client : ".$hgName."<br>";
	echo "host : ".$hostName."<br>";
	
	$sql = "SELECT * FROM `alerts` WHERE host='".$hostName."' and hostgroup='".$hgName."'";
	
	$result = mysqli_query($conn,$sql);
	//debug($result);
	
	row();
	//debug($result->num_rows);
	
	
	if ($result->num_rows == 0){
		echo "c'est 0 <br>";
		//insert des valeurs dans la table alerts !
		$sql="INSERT INTO `alerts`(`hostgroup`, `host`, `status`, `alert`) VALUES ('".$hgName."','".$hostName."',2,'".$items['value']."')";
		echo $sql;
		$result = mysqli_query($conn,$sql);
		row();
		debug($result);
		row();
	} else {
		echo "c'est 1 !<br>";
		
		//update le status et l'alert de la table alerts du client1 et host1
		$sql="UPDATE `alerts` SET `status`=2,`alert`='".$items['value']."' WHERE `host`='".$hostName."' and `hostgroup`='".$hgName."'";
		echo $sql;
	}
	row();
	/*
	if (mysqli_query($conn,$sql)) {
		echo "<br> Ca devrait aller <br>";
	} else {
		echo "Error: " . $sql . "<br>" . mysqli_error($conn)."<br>";
	} */
	
	
/* 	debug($items);
	if(is_numeric($items['value'])){
		
		echo $hostNameUnique." Ha ca doit aller dans les numeriques !  ".$items['value']."<br>";
		echo $items['name']. " est le nom du host !<br>";
	} else {
		echo "Ha ca doit PAS aller dans les numeriques !  ".$items['value']."<br>";
	} */
	
}

/***************************************************/
/* programme principale 						   */
/***************************************************/

// Variables fichier config
$delay=300;
$clientID=25;
$clientName='Client1';
$ipClient='10.254.0.123';
$username='admin';
$password='zabbix';
$dbserver="127.0.0.1";
$dbuser="root";
$dbpassword="zabbix";
$db="supervisor";

// setup Zabbix connection
try {
	
	// connect to Zabbix API
	$uri = "https://".$ipClient."/api_jsonrpc.php";
	$api = zabbix_auth($uri, $username, $password);
	//expand_arr(zabbix_get_hostgroups($uri, $api));
	
	// connect to database
	$conn=databaseConnection($dbuser,$dbpassword,$dbserver,$db);
	debug($conn->stat);
	
	// Get content from file named 'data.txt' and decode the JSON string to the variable '$data'
	$data=getContent();

	// readDatas function call, will call different function to check if hostgroup/host/item exist
	// and create it if doesn't exist
	readDatas($data,$api,$uri,$delay,$conn);
	//debug($conn);
	
	// Print all the datas
	row();
	debug($data);
	
	// close MySQL connection
	mysqli_close($link);
	
} catch (Exception $e) {
    // Exception in ZabbixApi catched
    echo $e->getMessage();

	/* fonctions :
	créer host +
		items + 
		trigger
		trigger supp : date derniere collecte BDD pour alerter si jamais pas reçu depuis X min
	stocker status + alerte BDD
	Hostgroup | Host | status | alert | timeout
	
	afficher tout de alerte ou 'host' correspond à 'host1' et ou 'client' est égal a 'client1'
	SELECT * FROM `alerts` WHERE `host`='host1' and `hostgroup`='client1'

	update le status et l'alert de la table alerts du client1 et host1
	UPDATE `alerts` SET `status`=1,`alert`='detail alerte' WHERE `host`='host1' and `hostgroup`='client1'

	insert des valeurs dans la table alerts !
	INSERT INTO `alerts`(`hostgroup`, `host`, `status`, `alert`) VALUES ('clientxx','hostxx',2,'oups une alerte !!')
	
	 */
	
}
?>
