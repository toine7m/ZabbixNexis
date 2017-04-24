<?php
/*  ZabbixNexis, little project for Zabbix during my internship
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
    along with this program.  If not, see <http://www.gnu.org/licenses/>. */
	
function debug() {
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
	printf("<hr noshade>");
    printf("<hr>");
}
// load ZabbixApi
require_once 'lib/ZabbixApi.class.php';
use ZabbixApi\ZabbixApi;
try {
    // connect to Zabbix API
    $api = new ZabbixApi('http://10.254.0.10/api_jsonrpc.php', 'zabirepo', 'nexis369*');
    /* ... do your stuff here ... */
	
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
 
  // Configuration of the parameters
 $prefixclient="NX_"; //Prefix added to the host->host, different from the visible name host->name
 
 // Array's initialisation
$data = [];
$clientrec = array('name' => '', 'groupid' => '', array());
$hgrec = array('name' => '', 'groupid' => '', 'hosts' => array());
$hostrec = array('host' => '', 'name' => '', 'hostid' => '', 'description' => '', 'status' => '', 
'alerts' => array());
$alertrec = array('triggerid' => '', 'description' => '', 'lastchange' => '', 'priority' => '', 
'expression' => '', 'status' => '', 'value' => '', 'ack' => '');

// Query vers les hostgroups : $lhg
foreach ($lhg as $hgid => $hg) {
  //echo "hostgroup: $hgid $hg->name<br>\n";
  $hgrec['name'] = "client1";
  $hgrec['groupid'] = "666gotohell";
  $data[$hgid] = $hgrec;

  // query des host dans le hostgroup
  $groupid=$hg->groupid;
  $lhphg=$api->hostGet([
	'output' => ['hostid','host','description','name','status'],
	'groupids' => $groupid]);
	// debug($lhphg);
  $myhostlist = $lhphg;

  // boucle de lecture des host
  foreach ($myhostlist as $hostid => $host) {
    //echo "\thost: $hostid $host->name<br>\n";
    $hostrec['hostid'] = $hg->groupid;
	$hostrec['host'] = $prefixclient.$hg->name;
	$hostrec['description'] = "No description at the moment bro";
	$hostrec['name'] = $hg->name;
	$hostrec['status'] = "Active/Inactive";
    $data[$hgid]['hosts'][$hostid] = $hostrec;
	$idhost=$host->hostid;
	//echo "hostid: $hostid";
	
    // Query alertes ack
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
/* 	row();
	debug($tab1);
	row(); */

	// Query alertes unAck	
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
/* 	row();
	debug($tab2);
	row(); */

	// Merge des 2 array's "ack" et "unack"
	$alerts=array_merge_recursive($tab1,$tab2);
	
    // Boucle de lecture des alertes du host
    foreach ($alerts as $aid => $alert) {
      //echo "\t\t<br>alert: $aid $alert->triggerid<br>\n";
		$alertrec['triggerid'] = $alert->triggerid;
		
		// Recherche de la valeur "host.name" dans la description du trigger et la remplace directement,
		// ce qui evite de devoir relier les deux ensuite
		$hostname='{HOST.NAME}';
		$pos= strpos($alert->description,$hostname);
		/* if ($pos !== false){
			echo "trouvé dans : $alert->description";
			row();
		} */
		$alert->description=str_replace($hostname,$host->name,$alert->description);
		/* if ($pos !== false){
			echo "modifié en : $alert->description";
			row();
		} */
		$alertrec['description'] = $alert->description. " (PROBLEM ON " . $host->host .")" ;
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
row();
//$test=array_unique($data, SORT_REGULAR);
//debug($test);
$cpt=0;
foreach($data as $data){
	$flattened=implode(",",$data);
	$newarray[$cpt]=$flattened;
	$cpt++;
}
row();
debug($newarray);
row();
debug($data);
row();
echo "<br>";
$json=json_encode($data);
echo "<br>";
echo $json;
echo "<br>";
$jsondecode=json_decode($json);
debug($jsondecode);
echo "<br>";
var_dump($data);
row();
$json = str_replace(' ', '|', $json);
$data ="?data=" . $json;
$url = 'http://10.254.0.123/html/api.php'.$data;

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: text/html",
		//'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query(array($data,$json))
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
row();
if ($result === FALSE) {
	/* Handle error */
	echo "ouille !!" ;
	}

var_dump($result);
row();
} catch (Exception $e) {
    // Exception in ZabbixApi catched
    echo $e->getMessage();
}
?>
