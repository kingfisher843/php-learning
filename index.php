<?php

toJson("sample.csv");

$contents = [];
// toJson read the data as the csv file and push it into array $contents
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
	
	
	
	// pushing data into $contents
	
	while($data = fgetcsv($open, 1024, ",")){
		$contents[] = $data;
	}
	//var_dump($contents);
	//die();
	
	// Extracting headers from contents
	$headers = array_shift($contents);
	// Returning
	return $contents;
}


date_default_timezone_set('Europe/Berlin');

class Transaction{
public $time, $type, $sell_currency, $sell, $buy_currency, $buy;
}
$transactions = [];
// Mincer() should take $contents and 'shape' its subarrays into objects
function Mincer($contents, $transactions) {
			
	foreach ($contents as $content){
	$object = new Transaction();
	$object -> time = strtotime($content[1]);
	$object -> type = $content[3];

	if ($content[5] < 0){
		$object -> sell_currency = $content[4];
		$object -> sell = 0 - $content[5];

	} else {
			$object -> buy_currency = $content[4];
			$object -> buy = $content[5];
	}

		$transactions[] = $object;
		print_r($transactions);
	}
}
Mincer($contents, $transactions);
Merge($transactions);

// Merge() should take arrays of object, merge the proper ones, and remove leftovers
function Merge($transactions)
{	$merged_object = [];
	foreach ($transactions as $transaction){
		$time = $transaction -> time;
		$type = $transaction -> type;
		$current_transaction = current($transaction);

		if ($type === "Buy" || $type === "Sell"){

			foreach($transactions as $transaction){

				$time_2 = $transaction -> time;
				$type_2 = $transaction -> type;
				$other_transaction = current($transaction);
				if ($time === $time_2 && ($type_2 === "Buy" || $type_2 === "Sell" ) && $current_transaction !== $other_transaction){
					$merged_object[] = (object) array_merge((array)$current_transaction, (array)$other_transaction);
					// I need these two messages for testing
					echo "Objects merged!/n";
				}else{

					echo "These object cannot be merged!/n";
				}
			}
		}
	}
	var_dump($merged_object);
}


?>
