
<?php
function toJson($file){
	//getting data
		$open = fopen($file,"r+");

	//in case of wrong file
		if(!$open){
			echo "Wrong file!";
			die;
		} else {
			echo "Fitting file, time to proceed!\n";
		}
		$data = array();
		$arranged = array();
	
	while($content=fgetcsv($open, 1024, ",")){
		$arr[]=$content;
	}
	$head = $arr[0];
	
	$arr[0][1]="\ntime: ";
	$arr[0][3]="type: ";
	$arr[0][4]="buy_currency: ";
	$arr[0][5]="buy: ";

	$one = array();

	//function getLines() requires two arrays and an 
	function getLines($input, $container){
		$b = count($input);
		for($a=1;$a<=$b;$a++){
			$d = $input[0][$a];
			$e = $input[$a];
			$f = "${d} ${e},\n";
			array_push($container, $f);
		}
	}

	getLines($arr[1], $one);
	print_r($one);
	

	// print_r($arr);
}

toJson("sample.csv");

?>
