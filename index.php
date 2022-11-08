<?php
toJson("sample.csv");

function toJson($file){
	//getting data
		$open = fopen($file,"r+");

	//in case of wrong file
		if(!$open){
			echo "Wrong file!";
			die();
		} else {
			echo "Fitting file, time to proceed!\n";
		}
		$data = array();
		$transactions = array();
	
	while($content=fgetcsv($open, 1024, ",")){
		$transactions[]=$content;
	}
	//var_dump($transactions);
	//die();
	
	
	
	$headers = array_shift($transactions);
	
	$headers[1]="\ntime";
	$headers[3]="type";
	$headers[4]="buy_currency";
	$headers[5]="buy";
	


	//function getLines() requires two arrays
	
	
	$lines = getLines($transactions, $headers);

	foreach($lines as $line){
		echo $line;
	}

	// print_r($transactions);
}


function getLines($transactions, $headers){
//var_dump($input); die();
	$lines = [];
	foreach ($transactions as $transaction){
		$lines[] = "{\n";
		$lines[] = $headers[0] . ": " . $transaction[0] . ",\n";
		$lines[] = $headers[1] . ": " . strtotime($transaction[1]) . ",\n";
		$lines[] = $headers[2] . ": " . $transaction[2] . ",\n";
		$lines[] = $headers[3] . ": " . $transaction[3] . ",\n";
		$lines[] = $headers[4] . ": " . $transaction[4] . ",\n";
		$lines[] = $headers[5] . ": " . $transaction[5] . ",\n";
		$lines[] = $headers[6] . ": " . $transaction[6] . ",\n";
		$lines[] = "},\n";
	} 
	return $lines;
}

?>
