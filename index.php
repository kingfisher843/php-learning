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
	date_default_timezone_set('Europe/Berlin');
	$contents = [];	
	
	
	while($data = fgetcsv($open, 1024, ",")){
		$contents[] = $data;
	}
	
	
	
	$headers = array_shift($contents);
	
	/*$headers[1]="\ntime";
	$headers[3]="type";
	$headers[4]="buy_currency";
	$headers[5]="buy";*/
}



class Transaction{

	function __construct(){
		
			$transaction -> time = strtotime($content[1]);
			$transaction -> type = $content[3];
			if ($content[5]<0){
				$transaction -> sell_currency = $content[4];
				$transaction -> sell = 0 - $content[5];
			} else {
			$transaction -> buy_currency = $content[4];
			$transaction -> buy = $content[5];
			}	
		
	}
}
$transactions = [];
foreach ($contents as $content){
	$transactions[] = new Transaction();
	}
	// The problem is: even if I could launch this, how am I going to refer to these new objects? They'd have no given name.


?>
