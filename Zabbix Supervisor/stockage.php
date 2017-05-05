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
	/* echo "<b>JSON Request:</b><br>\n";
	echo $json_data."<br><br>\n";

	echo "<b>JSON Answer:</b><br>\n";
	echo $result."<br><br>\n"; */

	/* echo "<b>CURL Debug Info:</b><br>\n";
	$debug = curl_getinfo($c);
	echo expand_arr($debug)."<br><hr>\n";*/
	

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
	
	/* row();
	echo $hgId;
	row(); */
	
	// Hosts reading loop
	// echo "<br> Liste des HOSTS : <br><br>";
	foreach ($data['hosts'] as $hosts) {
		$hostName=$hosts['name'];
		$hostNameUnique=$hosts['host'];
		$hostId=hostExist($hgId,$hostName,$hostNameUnique,$api,$uri);
		//echo $hostId;
		// Alerts reading loop
		foreach ($hosts['items'] as $items) {
			$result=databaseFill($conn,$items,$hostName,$hgName,$hostId,$hostNameUnique,$api,$uri,$delay);
			//debug($result);
			//debug($items);
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
	

	//row();
	//debug($hg);
	if($hg['result']){
		//echo("Trouve!<br>");
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
		return $host['result']['0']['hostid'];
	}
	else {
		//echo "not found : ".$hostName."<br>";
		$host=hostCreate($hgId,$hostName,$hostNameUnique,$api,$uri);
		return $host;
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
	//debug($host);

/* 	echo(gettype($hgId));
	echo(gettype($hostName));
	echo(gettype($hostNameUnique)); */
	//echo $hgId."*".$hostName."*".$hostNameUnique."<br>";
	return $host['result']['hostids'][0];
	//hostExist($hgId,$hostName,$hostNameUnique,$api,$uri);
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

function databaseFill($conn,$items,$hostName,$hgName,$hostId,$hostNameUnique,$api,$uri,$delay){
	$fill_start = microtime(true);
	// SELECT * FROM `alerts` WHERE host='host1' and hostgroup='client1'
	/* echo "client : ".$hgName."<br>";
	echo "host : ".$hostName."<br>";
	echo "item : ".$items['name']."<br>"; */
	
	//echo "<br>".$delay." est le delai<br>";
	
	if(is_numeric($items['value'])){
		
		// ICI C EST NUMERIQUE
		
		$sql = "SELECT `id` FROM `alerts` WHERE `host`='".$hostName."' and `hostgroup`='".$hgName."' and `name`='".$items['name']."' and `status`='".$items['value']."'";
		//echo $sql."<br>";
		$result = mysqli_query($conn,$sql);
		//row();
		//debug($result);
		/* row();
		debug($result->num_rows);
		row(); */
		
		if ($result->num_rows == 0){
			$db_start = microtime(true);
			//echo "<br>Numerique et n'existe pas<br>";
			
			// insert priority value in a new row for the new host
			$sql="INSERT INTO `alerts`(`hostgroup`, `host`, `name`, `status`) VALUES ('".$hgName."','".$hostName."','".$items['name']."','".$items['value']."')";
			//echo $sql;
			$result = mysqli_query($conn,$sql);
			
			$hostNameS="'".$hostName."'";
			$hgNameS="'".$hgName."'";
			$itemNameS="'".$items['name']."'";
			$data = array(
			'jsonrpc' => "2.0",
			'method' => "item.create",
			'params' => array(
				'name' => $items['name'].'_priority',
				'hostid' => $hostId,
				'type' => 11,
				'key_' => 'db.odbc.select['.$items['name'].'_priority,test]',
				'username' => 'root',
				'params' => 'SELECT `status` FROM `alerts` WHERE `host`='.$hostNameS.' and `hostgroup`='.$hgNameS.' and `name`='.$itemNameS.';',
				'value_type' => 3,
				'data_type' => 0,
				'delay' => 30,
				'history' => 90,
				'trends' => 365,
				'delta' => 0,
				'enabled' => 0
			),
			'id' => "2",
			'auth' => $api
			);
			$item = json_request($uri, $data);
			//debug($item);

			for ($i = 1; $i <6; $i++){
				$trigger_start= microtime(true);
			$expression="{".$hostNameUnique.":db.odbc.select[".$items['name']."_priority,test].last()}=".$i;
			$description="Happened on : ".$items['name'];
			$trigger = array(
			'jsonrpc' => "2.0",
			'method' => "trigger.create",
			'params' => array(
				'hostid' => $hostId,
				'expression' => $expression,
				'description' => $description,
				'priority' => $i
			),
			'id' => "2",
			'auth' => $api
			);
			$result = json_request($uri, $trigger);
			//debug($result);
			$trigger_end = microtime(true);
			$trigger = $trigger_end - $trigger_start;
			echo '______Insert 1 trigger in Zabbix via API executed in '.$trigger.' seconds<br>';
			}
			
			$db_end = microtime(true);
			$db = $db_end - $db_start;
			echo '___Total Insert item + trigger BDD + API executed in '.$db.' seconds<br>';
		} else {
			
			//echo "<br>Numerique et existe<br>";
			
			// Update of the database with the new values
			$sql="UPDATE `alerts` SET `timeout` = SYSDATE(), `status`=".$items['value']." WHERE `host`='".$hostName."' and `hostgroup`='".$hgName."' and `name`='".$items['name']."'";
			//echo $sql;
			$result = mysqli_query($conn,$sql);
		}
	} else {
		
		// ICI C EST PAS NUMERIQUE
		
		$sql = "SELECT `id` FROM `alerts` WHERE `host`='".$hostName."' and `hostgroup`='".$hgName."' and `name`='".$items['name']."' and `alert`='".$items['value']."'";
		//echo $sql."<br>";
		$result = mysqli_query($conn,$sql);
		/* row();
		debug($result); */
		/* row();
		debug($result->num_rows);
		row(); */
		
		if ($result->num_rows == 0){
			$num_start = microtime(true);
			//echo "<br>Pas numerique et n'existe pas<br>";
			
			//echo "Ha ca doit PAS aller dans les numeriques !  ".$items['value']."<br>";
			//echo $items['name']. " est le nom du host !<br>";
			
			// insert description value in a new row for the new host
			//$sql="INSERT INTO `alerts`(`hostgroup`, `host`, `name`, `alert`) VALUES ('".$hgName."','".$hostName."','".$items['name']."','".$items['value']."')";
			$sql="UPDATE `alerts` SET `alert`='".$items['value']."' WHERE `host`='".$hostName."' and `hostgroup`='".$hgName."' and `name`='".$items['name']."'";
			//echo $sql;
			$result = mysqli_query($conn,$sql);		

			$hostNameS="'".$hostName."'";
			$hgNameS="'".$hgName."'";
			$itemNameS="'".$items['name']."'";
			$data = array(
			'jsonrpc' => "2.0",
			'method' => "item.create",
			'params' => array(
				'name' => $items['name'].'_description',
				'hostid' => $hostId,
				'type' => 11,
				'key_' => 'db.odbc.select['.$items['name'].'_description,test]',
				'username' => 'root',
				'params' => 'SELECT `alert` FROM `alerts` WHERE `host`='.$hostNameS.' and `hostgroup`='.$hgNameS.' and `name`='.$itemNameS.';',
				'value_type' => 4,
				'delay' => 30,
				'history' => 90,
				'delta' => 0,
				'enabled' => 0
			),
			'id' => "2",
			'auth' => $api
			);
			$item = json_request($uri, $data);
			//debug($item);
			$num_end = microtime(true);
			$num = $num_end - $num_start;
			echo '___Fill DB + item.create Zabbix API non numeric executed in '.$num.' seconds<br>';
		} else {
			
			//echo "<br>Pas numerique et existe<br>";
			
			// Update of the database with the new values
			$sql="UPDATE `alerts` SET `alert`='".$items['value']."' WHERE `host`='".$hostName."' and `hostgroup`='".$hgName."' and `name`='".$items['name']."'";
			//echo $sql;
			$result = mysqli_query($conn,$sql);
			
		}
		
	}
	$fill_end = microtime(true);
	$fill = $fill_end - $fill_start;
	echo 'Fill executed in '.$fill.' seconds<br>';
}

/***************************************************/
/* programme principale 						   */
/***************************************************/

$time_start = microtime(true);
// Variables fichier config
include('config.php');

// setup Zabbix connection
try {
	// connect to Zabbix API
	$uri = "https://".$ipSupervisor."/api_jsonrpc.php";
	$api = zabbix_auth($uri, $username, $password);
	//expand_arr(zabbix_get_hostgroups($uri, $api));
	
	
	// connect to database
	$conn=databaseConnection($dbuser,$dbpassword,$dbserver,$db);
	//debug($conn);
	
	// Get content from file named 'data.txt' and decode the JSON string to the variable '$data'
	$data=getContent();

	// readDatas function call, will call different function to check if hostgroup/host/item exist
	// and create it if doesn't exist
	readDatas($data,$api,$uri,$delay,$conn);
	//debug($conn);
	
	// Print all the datas
	//row();
	//debug($data);
	
	// close MySQL connection
	mysqli_close($conn);
	
	// Little script to see the execution time of the script
	$time_end = microtime(true);
	$time = $time_end - $time_start;
	echo '<br> -_- Script executed in '.$time.' seconds<br><br><br>';
	
} catch (Exception $e) {
    // Exception in ZabbixApi catched
    echo $e->getMessage();

	/* fonctions :
	créer host +
		items + 
		trigger
		trigger supp : date derniere collecte BDD pour alerter si jamais pas reçu depuis X min
	stocker status + alerte BDD
	
	Hostgroup | Host | Name | Status | Alert | Timeout

	 */
}
?>
