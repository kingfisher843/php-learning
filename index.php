<?php


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

	
	// Extracting headers from contents
	$headers = array_shift($contents);
	// Returning
	return $contents;
}
$contents = toJson("sample.csv");

date_default_timezone_set('Europe/Berlin');

class Transaction{
}

// Mincer() should take $contents and 'shape' its subarrays into objects
function Mincer($contents) {
			
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
		
	}
	return $transactions;
}

$transactions = Mincer($contents);





// Merge() should take arrays of object, merge the proper ones, and remove leftovers

function Merge($transactions)
{
	$transactions_merged = [];
	foreach ($transactions as $transaction){
	
		$time = $transaction -> time;
		$current = current($transactions);
		$type = $transaction -> type;	
		if ($type === "Buy" || $type === "Sell"){

			foreach($transactions as $transaction){

				$time_2 = $transaction -> time;
				$type_2 = $transaction -> type;
				$other_transaction = current($transactions);
				if ($time === $time_2 && ($type_2 === "Buy" || $type_2 === "Sell" ) && $current !== $other_transaction){
					$transactions_merged[] = (object) array_merge((array)$current, (array)$other_transaction);
					
					// I need these two messages for testing
					echo "Objects merged!/n";
					unset($transactions[$current_transaction]);
					unset($transactions[$other_transaction]);
				}
			}
		} else {
			echo "no match \n";
		}
	}
}
$transactions_merged [] = Merge($transactions);
print_r($transactions_merged);
?>
