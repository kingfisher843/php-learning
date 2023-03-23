
<?php


// toJson read the data as the csv file and push it into array $contents
function toJson($file){
	//getting data
	$open = fopen($file,"r+");

	//in case of wrong file
	if(!$open){
		die("Wrong file!");
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
	if ($content[3] === "Fee") {
		$object -> type = "Other fee";
	} else {
	$object -> type = $content[3];
	}
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
					break;
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
		$result = false;
	} elseif (isset($t1->sell) && isset($t2->sell)){
		//echo "Can't be both sell!\n";
		$result = false;
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


function sorter($a, $b){
	return $a->time < $b->time;
}
usort ($transactions_merged, "sorter");


function funkySort($arr1,$arr2,$arr3)
{
	$semifinal = [];

foreach ($arr1 as $obj1){
	foreach ($arr2 as $obj2){
		array_push($semifinal, $obj1, $obj2);
	}
}

	$result = array_merge($semifinal,$arr3);
	$result = array_filter($result);
	return $result;
}


// sortingHat() takes $transactions_merged and sorts them into $transactions_sorted

function sortingHat($transactions_merged)
{
	$transactions_sorted = [];
	//return array
	$trades = [];
	$fees = [];
	$rest = [];
	//temporary arrays for sorting

//turns out all transactions was in the subarray of $transactions_merged

		while (count($transactions_merged)) {

		$transaction = array_shift($transactions_merged);
		switch ($transaction){
			case 'Trade':
				$trades [] = $transaction;
				break;
				case 'Other fee':
				$fees [] = $transaction;
				break;
				default:
				$rest [] = $transaction;
				break;
		}

// $transaction is now in one of the temporary arrays
			while(count($transactions_merged)) {
//now we want to find objects with the same time
				$pairable = array_shift($transactions_merged);

				if ($transaction->time === $pairable->time) {
// if this object has the same time as previous, it's now being sorted.
					switch ($pairable){
						case 'Trade':
							$trades [] = $pairable;
							break;
							case 'Other fee':
							$fees [] = $pairable;
							break;
							default:
							$rest [] = $pairable;
							break;
						}

	//$pairable is also sorted to temporary arrays
				} else {
				//if time happens to change we need to dump contents of temporary arrays into final array
					$sorted = funkySort($trades,$fees,$rest);
					foreach ($sorted as $element){
						$transactions_sorted [] = $element;
					}
				//pairable unshift (it will be new $transaction)
				array_unshift($transactions_merged, $pairable);
				//erasing contents from temporary arrays
				$trades = [];
				$fees = [];
				$rest = [];

				}
			}
		}
			//at the end last chunk is sorted and pushed into final array
			$sorted = funkySort($trades,$fees,$rest);
			foreach ($sorted as $element){
				$transactions_sorted [] = $element;
			}
			$transactions_sorted  = array_filter($transactions_sorted);

				return $transactions_sorted;
	}

	$transactions_sorted = sortingHat($transactions_merged);



function printToJson($transactions_sorted)
{

	foreach($transactions_sorted as $object) {
		$values = array_values($object);
	echo json_encode($values, JSON_PRETTY_PRINT);
	}
}

 printToJson($transactions_sorted);

?>
