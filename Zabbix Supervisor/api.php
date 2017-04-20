<?php
	//process client request (Via URL)
	if(!empty($_GET['data'])){
		//
		$data=$_GET['data'];
		$data = str_replace('£', ' ', $data);
		echo("<br>\nVoici la chaine :<br><br>\n\n");
		echo $data;
		$file = fopen("data.txt","w");
		echo fwrite($file,$data);
		fclose($file);
		$mavariable=$data;
	}
	else {
		//throw invalid request
		
		http_response_code(404);
		include('404.php'); // provide your own HTML for the error page
		die();
		
		//echo "t'as pas dis le mot magique !!!!<br><br>\n\n";
		//echo $data;
	}
?>
