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


// Mincer() should take $contents, merge its parts wherever it is needed, and return shaped, shiny array ready to print!
function Mincer($contents) {
		
	$transactions = [];
	foreach ($contents as $content){

	class Transaction
	{

		function __construct(){
		
			$this -> time = strtotime($content[1]);
			$this -> type = $content[3];
			if ($content[5]<0){
				
				$this -> sell_currency = $content[4];
				$this -> sell = 0 - $content[5];
			} else {
			$this -> buy_currency = $content[4];
			$this -> buy = $content[5];
		}	
	}
	$object = new Transaction;
	// Something is wrong with $object in line above, need to work on this one
	$transactions[] = $object;

}

foreach ($transactions as $transaction){
	$time = $transaction -> time;
	$type = $transaction -> type;
	$current_transaction = current($transaction);
	if ($type === "Buy" || $type === "Sell"){
		foreach($transactions as $transaction){
			$time_2 = $transaction -> time;
			$type_2 = $transaction -> type;
			$other_transaction = current($transaction);
			if ($time === $time_2 && ($type_2 === "Buy" || $type_2 === "Sell")){
			$merged_object = (object) array_merge((array)$current_transaction, (array)$other_transaction);
			// I need these two messages for testing
			echo "Objects merged!/n";
			}else{
				echo "These object cannot be merged!/n";
			}
		}
	}
}
}



?>
