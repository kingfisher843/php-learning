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

	// Returning
	return $contents;
}

$contents = toJson($argv[1]);

// Extracting headers from contents
	$headers = array_shift($contents);

date_default_timezone_set('Europe/Berlin');

class Transaction {
	public int $time;
	public string $type;
	public string $sell_currency;
	public float $sell;
	public string $buy_currency;
	public float $buy;

}

// mincer() should take $contents and shape its subarrays into objects
function mincer($contents) {
			
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

$transactions = mincer($contents);



// merge() should take arrays of object, merge the proper ones, and remove leftovers

function merge($transactions)
{
	$transactions_merged = [];
	while (count($transactions)){
		$transaction = array_shift($transactions);
		if ($transaction->type === "Buy" || $transaction->type === "Sell"){
			$merged = false;
			foreach($transactions as $key => $pairable){

				if (isMergable($transaction, $pairable)) {
					$transactions_merged[]= mergeTransactions($transaction, $pairable);
						//echo "Objects merged!\n";
					unset($transactions[$key]);
					$merged = true;
					$break;
				}
			}
			if ($merged === false ) {
                print_r($transactions_merged);
                throw new Exception("without a match");
				}
		} else {
			//echo "it has no type required to merge\n";
			$transactions_merged[] = $transaction;
		}

	}
	return $transactions_merged;
}

function isMergable(Transaction $t1, Transaction $t2) {
	$result = false;
	if ($t1->time !== $t2->time ){
		//echo "Different time!\n";
		$result = false;
	} elseif ($t1->type !== $t2->type){
		//echo "Different type!\n";
		$result = false;
	} elseif (isset($t1->buy) && isset($t2->buy)){
		//echo "Can't be both buy!\n";
	} elseif (isset($t1->sell) && isset($t2->sell)){
		//echo "Can't be both sell!\n";
	} else {
		$result = true;
	}
	return $result;
}
function mergeTransactions(Transaction $t1, Transaction $t2)
{
	if (isset($t1->buy)) {
		$t1->sell = $t2->sell;
		$t1->sell_currency = $t2->sell_currency;
	} else {
		$t1->buy = $t2->buy;
		$t1->buy_currency = $t2->buy_currency;
	}
	$t1->type = "Trade";
	return $t1;
}
	
$transactions_merged [] = merge($transactions);


/*

function timestampSort ($arr1, $arr2){
	foreach ($transactions_merged as $key => $node) {
   $timestamps[$key] = $node[0];
}
array_multisort($timestamps, SORT_ASC, $transactions_merged);
}
timestampSort($transactions_merged);
*/


// sortinhHat() takes $transactions_merged and sorts them into $transactions_sorted
		
function sortingHat($transactions_merged)
{
	$transactions_sorted = [];
	while (count($transactions_merged)) {

		$firstTr = array_shift($transactions_merged);

		$trades = [];
		$fees = [];
		$rest = [];

		//first element is dumped to $transactions_sorted as it is
		$transactions_sorted [] = $firstTr;
		
		while (count($transactions_merged)) {

			$otherTr = array_shift($transactions_merged);

			if (timeCompare($firstTr, $otherTr) === false) {

				$trades = [];
				$fees = [];
				$rest = [];
				$firstTr = $otherTr;

			}

			switch ($otherTr[3]) {
				case 'Trade':
				$trades [] = $otherTr;
				break;
				case 'Fee':
				$fees [] = $otherTr;
				break;
				default:
				$rest [] = $otherTr;
				break;
			}
			
			for ($i = 0; $i <= count($trades); $i ++) {

				$transactions_sorted [] = array_shift($trades);
				$transactions_sorted [] = array_shift($fees);	
			}
			$transactions_sorted [] = array_merge($transactions_sorted, $rest);
		}
	}
	return $transactions_sorted;
}


function timeCompare($tr1,$tr2) {
	if ($tr1['time'] !== $tr2['time'])	{
		return false;
	}
}


$transactions_sorted = sortingHat($transactions_merged);
//printToJson($transactions_sorted);

function printToJson(array $transactions_sorted)
{

	foreach($transactions_sorted as $object) {
	echo json_encode($object, JSON_PRETTY_PRINT);

	}
}




?>
