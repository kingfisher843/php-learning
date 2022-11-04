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
	
	/*$transactions[0][1]="\ntime: ";
	$transactions[0][3]="type: ";
	$transactions[0][4]="buy_currency: ";
	$transactions[0][5]="buy: ";
	*/


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
		$lines[] = $headers[3] . ": " . $transaction[3] . ",\n";
	}
	return $lines;
}

?>
