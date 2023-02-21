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

function sorter($a, $b){
	return $a->time < $b->time;
}
usort ($transactions_merged, "sorter");


function timeCompare($tr1,$tr2) {
	if ($tr1 -> time === $tr2 -> time)	{
	return true;
	}
}

function funkySort($arr1,$arr2,$arr3)
{
	$result = [];

	for ($i = 0; $i < count($arr1); $i++){
		$result [] = $arr1[$i];
		$result [] = $arr2[$i];
	}
	$result [] = $arr3;
	return $result;
}

function slicer($array)
{

	while(count($array)){
	$chunk = [];
	$chunked_array = [];
		$slice = array_shift($array);
		

		foreach ($array as $object)	{
			$chunk [] = $slice;
			/*if ($slice === $object)	{
				echo "They are the same";
			} */ if ($slice -> time === $object -> time && $slice !== $object){
			echo "time to merge";
			$chunk [] = $object; 
			} else {
				$chunked_array [] = $chunk;
				$chunk = [];
			}

		}

		while(count($chunked_array)) {

		$piece = array_shift($chunked_array);
		
		$slightly_chunked = [];
			foreach ($chunked_array as $pairable) {
				$big_chunk [] = $piece;
				if ($piece === $pairable) {
					echo "They are the same";
				} else if ($piece[0] -> time === $pairable[0] -> time){
				$big_chunk []= $pairable;
				} else {
					$slightly_chunked [] = $big_chunk;
					$big_chunk = [];

				}
			}
		}
	}
}

$a = new Transaction;
$a -> time = 13;
$b = new Transaction;
$b -> time = 13;
$c = new Transaction;
$c -> time = 22;
$d = new Transaction;
$d -> time = 22;
$e = new Transaction;
$e -> time = 22;
$f = new Transaction;
$f -> time = 36;
$g = new Transaction;
$g -> time = 36;
$array = [$a, $b, $c, $d, $e, $f , $g];
//print_r($array);

echo "===========";
$sliced_array = slicer($array);
print_r($sliced_array);
//slicer($array);




// sortingHat() takes $transactions_merged and sorts them into $transactions_sorted

/*
function sortingHat($transactions_merged)
{
	$trades = [];
	$fees = [];
	$rest = [];
	$transactions_sorted = [];
	
	$transactions_sliced = slicer($transactions_merged);
	foreach ($transactions_sliced as $slice){
		$result = funkySort($slice);
		$transactions_sorted [] = $result;
	}
}
	*/
/*
	while (count($transactions_merged))	{

		$transaction = array_shift($transactions_merged);

			foreach($transactions_merged as $pairable)	{

				if (timeCompare($transaction, $pairable) === true) {
					switch ($pairable -> type)
					{
					case "Trade":
					$trades [] = $pairable;
					break;
					case "Other fee":
					$fees [] = $pairable;
					break;
					default:
					$rest [] = $pairable;
					break;
					} 
				}else {
					$trades = [];
					$fees = [];
					$rest = [];
				}
				unset($transactions[$key]);
				$result = funkySort($trades,$fees,$rest);
				$transactions_sorted [] = $result;
			}
	}
	
	return $transactions_sorted;
}
*/
 

function printToJson(array $transactions_sorted)
{

	foreach($transactions_sorted as $object) {
	echo json_encode($object, JSON_PRETTY_PRINT);

	}
}
//$transactions_sorted [] =  sortingHat($transactions_merged);printToJson($transactions_sorted);
// printToJson($transactions_sorted);


?>
