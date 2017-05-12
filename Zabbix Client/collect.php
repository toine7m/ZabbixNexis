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
function collectDatas($api){
//**************************************************
// Function collectDatas
// Description: Collect data from the Zabbix Server
// using the API
// Arguments: $api containing Zabbix auth token
// Return value: $collectedData
// multi-dim array containing all the hostgroups,
// the hosts and the alerts
//**************************************************	
	// Hostgroups collect
	$lhg=$api->hostgroupGet([
		'output' => ['groupid','name','flags']
		]);
	/*
	$hg1 = (object) array('id' => 5, 'name' => 'HG1');
	$hg2 = (object) array('id' => 6, 'name' => 'HG2');
	$h1 = (object) array('id' => 10, 'name' => 'HOST1');
	$a1 = (object) array('id' => 100, 'name' => 'ALERT1');
	$lhg[$hg1->id] = $hg1;
	$lhphg[$hg1->id][$h1->id] = $h1;
	$laph[$h1->id][$a1->id] = $a1;
	*/
 
	// Array's initialisation
	$data = [];
	$clientrec = array('name' => '', 'groupid' => '', array());
	$hgrec = array('name' => '', 'groupid' => '', 'hosts' => array());
	$hostrec = array('host' => '', 'name' => '', 'hostid' => '', 'description' => '', 'status' => '', 'alerts' => array());
	$alertrec = array('triggerid' => '', 'description' => '', 'lastchange' => '', 'priority' => '', 'expression' => '', 'status' => '', 'value' => '', 'ack' => '');

	// Hostgroups reading loop : $lhg
	foreach ($lhg as $hgid => $hg) {
		
		// Fill the hostgroup array
		$hgrec['name'] = $hg->name;
		$hgrec['groupid'] = $hg->groupid;
		$data[$hgid] = $hgrec;

		// Query hosts in the hostgroup
		$groupid=$hg->groupid;
		$lhphg=$api->hostGet([
			'output' => ['hostid','host','description','name','status'],
			'groupids' => $groupid]);
		$myhostlist = $lhphg;

		// Hosts reading loop
		foreach ($myhostlist as $hostid => $host) {
			
			// Fill the host array
			$hostrec['hostid'] = $host->hostid;
			$hostrec['host'] = $host->host;
			$hostrec['description'] = $host->description;
			$hostrec['name'] = $host->name;
			$hostrec['status'] = $host->status;
			$data[$hgid]['hosts'][$hostid] = $hostrec;
			$idhost=$host->hostid;
	
			// Query acked alerts
			$laph=$api->triggerGet([
				'output' => ['description','lastchange','priority','expression','status','value','state'],
				'hostids' => $idhost,
				'selectHosts' => ['host','maintenance_status'],
				'min_severity' => '0',
				'monitored' => true,
				'skipDependent' => true,
				'withAcknowledgedEvents' => true,
				'only_true' => true]);
			$tab1=$laph;
			if(!in_array(null, $tab1)){
				foreach($tab1 as $otab1){
				$otab1->ack = '1';
				}
			}

			// Query unacked alerts	
			$alertsunack=$api->triggerGet([
				'output' => ['description','lastchange','priority','expression','status','value','ack'],
				'hostids' => $idhost,
				'selectHosts' => ['host','maintenance_status'],
				'min_severity' => '0',
				'monitored' => true,
				'skipDependent' => true,
				'withUnacknowledgedEvents' => true,
				'only_true' => true]);
			$tab2=$alertsunack;
			if(!in_array(null, $tab2)){
				foreach($tab2 as $otab2){
					$otab2->ack = '0';
				}
			}

			// Merge of the "acked" and "unacked" arrays
			$alerts=array_merge_recursive($tab1,$tab2);
	
			// Alerts reading loop
			foreach ($alerts as $aid => $alert) {

				// Fill the alerts array
				$alertrec['triggerid'] = $alert->triggerid;
				// Replace the "{HOST.NAME}" value in the trigger description by the real host name
				$hostname='{HOST.NAME}';
				$pos= strpos($alert->description,$hostname);
				$alert->description=str_replace($hostname,$host->name . " (" . $host->host .")" ,$alert->description);
				$alertrec['description'] = $alert->description;
				$alertrec['lastchange'] = $alert->lastchange;
				$alertrec['priority'] = $alert->priority;
				$alertrec['expression'] = $alert->expression;
				$alertrec['status'] = $alert->status;
				$alertrec['value'] = $alert->value;
				$alertrec['ack'] = $alert->ack;
				$data[$hgid]['hosts'][$hostid]['alerts'][$aid] = $alertrec;
			}
		}
	}
	return($data);
}
function transformDatas($collectedData, $clientName, $clientId, $delay){
//**************************************************
// Function transformDatas
// Description: Transform the datas from an array
// and add a new level to Zabbix, the "client"
// Arguments:
// $collectedData: multi-dim array containe all 
// previously collected datas (see function collectdata)
// $clientName: Name of the clint (hostgroup)
// $clientID: ID of the client (hostgroup)
// $delay: The interval between each collect in the DB
// Return value: $transformedData
// multi-dim array containing the transformed datas
// plus a new level, the "client"
//**************************************************

	// Array's initialisation
	// We must create the required records to update the supervisor database.
	// We need 4 types of records: hostgroup, host, item (2x) and trigger
	// the hostgroup will contain the client name and id
	// The host will contain the hostgroup of the monitoring system
	// The item (status) will contain the host status (= highest alert priority)alert of the monitoring system
	// The item (alert) will contain the alert information of the monitoring system
	// The trigger associated with the item "status" is identical for all items of all hosts and will trigger in case
	// the value of item status changes 

	// client record corresponds to a hostgroup record in the supervisor database	
	$clientRecTmpl = array('name' => $clientName, 'groupid' => $clientId, 'hosts' => array()); 
	// hostgroup corresponds to a host record in the supervisor database	
	$hgRecTmpl = array('host' => '', 'name' => '', 'hostid' => '', 'description' => '', 'status' => '0', 'items' => array());
	// host corresponds to a item record in the supervisor database	
	$itemStatusRecTmpl = array('delay' => $delay, 'hostid' => '', 'interfaceid' => '', 'key_' => '', 'name' => '', 'type' => '10', 'value_type' => '3', 'value' => '');
	$itemAlertRecTmpl = array('delay' => $delay, 'hostid' => '', 'interfaceid' => '', 'key_' => '', 'name' => '', 'type' => '10', 'value_type' => '4', 'value' => '');
    $data=[];

	// Init first and only client record
    $data[] = $clientRecTmpl;
	$hostIdx=0;
	
	// reading all hostgroups in array collectedData. Goes into a host record
	foreach ($collectedData as $hgRec) {
		$hgRecTmpl['host'] = $clientName . '_' . $hgRec['name'];
		$hgRecTmpl['name'] = $hgRec['name'];
		$hgRecTmpl['description'] = 'No description at the moment';
                
        $data[0]['hosts'][$hostIdx] = $hgRecTmpl;
        $itemIdx=0;
		
		// Hosts reading loop. Goes into an item record
		foreach ($hgRec['hosts'] as $hostRec) {
			$itemStatusRecTmpl['name'] = $hostRec['name']; //ajouter . "_status" à la fin
			$itemAlertRecTmpl['name'] = $hostRec['name']; //ajouter . "_alert" à la fin

			// Definition
			$memPriority = -1;
			$alertsFound = false;
			
			// Alerts reading loop
			foreach ($hostRec['alerts'] as $alert) {
				$alertsFound = true;
				if($memPriority < $alert['priority']){
					$memPriority = $alert['priority'];
					$memAlertTxt = $alert['description'];
				}
			}
			if($memPriority!==-1){
				$itemStatusRecTmpl['value'] = $memPriority;
				$itemAlertRecTmpl['value'] = $memAlertTxt;
			}
                        else {
				$itemStatusRecTmpl['value'] = 0;
				$itemAlertRecTmpl['value'] = 'No alerts found';
			}
			$data[0]['hosts'][$hostIdx]['items'][$itemIdx] = $itemStatusRecTmpl;
			$itemIdx++;
			$data[0]['hosts'][$hostIdx]['items'][$itemIdx] = $itemAlertRecTmpl;
			$itemIdx++;
		}
		$hostIdx++;
	}
	return($data);
}
function sendData($data,$ipNexis,$clientName){
	$jsonData=json_encode($data);
	$data = str_replace(' ', '|', $jsonData);
	$myfile = fopen('data', 'w') or die('Unable to open file!');
	//echo "<br> le nom du client est : ". $clientName;
	$txt = $data;
	fwrite($myfile, $txt);
	fclose($myfile);
	
	$target_url = 'http://' . $ipNexis . '/html/api.php';
        //This needs to be the full path to the file you want to send.
	$file_name_with_full_path = realpath('./data');
        /* curl will accept an array here too.
         * Many examples I found showed a url-encoded string instead.
         * Take note that the 'key' in the array will be the key that shows up in the
         * $_FILES array of the accept script. and the at sign '@' is required before the
         * file name.
         */
	$post = array('extra_info' => '123456','file_contents'=>'@'.$file_name_with_full_path);
 
        $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$target_url);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$result=curl_exec ($ch);
	curl_close ($ch);
	//echo $result;
	
	// Log the results
	//$result = strval($result);
	$exploded=explode('\n',$result);
	
/* 	foreach($exploded as $row){
	echo $row."<br>";
	} */
	$logFile = fopen('/var/log/ZabbixNexis/data.log', 'a+') or die('Unable to open file!');
	foreach($exploded as $row){
	fwrite($logFile,$row."\n");
	}
	fclose($logFile);
	return $data;
	
}

/***************************************************/
/* programme principale 						   */
/***************************************************/

// Variables fichier config
include('config.php');

// load ZabbixApi
require_once 'lib/ZabbixApi.class.php';
use ZabbixApi\ZabbixApi;

// setup Zabbix connection
try {
    // connect to Zabbix API
    $api = new ZabbixApi('http://'.$ipClient.'/api_jsonrpc.php', $userZabbix, $passwordZabbix);

	// Collect alerts
	$collectedData = collectDatas($api);

	/* row();
	debug($collectedData);
	row(); */
	
	// trasnform collected data
	$transformedData = transformDatas($collectedData, $clientName, $clientID, $delay);

	//debug($transformedData);

	
	// Send data to supervisor
	$jsonData=sendData($transformedData,$ipNexis,$clientName);

} catch (Exception $e) {
    // Exception in ZabbixApi catched
    echo $e->getMessage();
}
?>
