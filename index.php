<?php


date_default_timezone_set('Europe/Berlin');

$fileName = $argv[1];

$rows = readCSV($fileName);

// Remove headers from rows
array_shift($rows);

$transactions = mapRawDataToTransactions($rows);

$transactions = linkBuyAndSellTogether($transactions);

usort($transactions, function ($a, $b){
    return $a->time - $b->time;
});

$transactions = adjustOrderByType($transactions);

printToJson($transactions);



class Transaction {
    public int $time;
    public string $type;
    public string $sell_currency;
    public float $sell;
    public string $buy_currency;
    public float $buy;

    public function isBuyType(): bool {
        return $this->type === "Buy";
    }

    public function isSellType(): bool {
        return $this->type === "Sell";
    }

    public function isBuy(): bool {
        return isset($this->buy);
    }
}

/**
 * @param string $fileName
 * @return array
 */
function readCSV(string $fileName): array {
    //getting data
    $file = fopen($fileName,"r+");

    //case of wrong file
    if(!$file){
        die("Wrong file!");
    }

    // pushing data into $contents
    while($data = fgetcsv($file, 1024, ",")){
        $lines[] = $data;
    }

    fclose($file);

    // Returning
    return $lines;
}

class Cols {
    const TIME = 1;
    const TYPE = 3;
    const CURRENCY = 4;
    const AMOUNT  = 5;
}

/**
 * @param array $records
 * @return array []Transaction
 */
function mapRawDataToTransactions(array $records): array {
    $types = [
        "Fee" => "Other fee",
        "Super BNB Mining" => "Mining",
        "Referral Kickback" => "Reward/Bonus",
    ];
    foreach ($records as $line){
        $tx = new Transaction();
        $tx->time = strtotime($line[Cols::TIME]);
        $tx->type = $types[$line[Cols::TYPE]] ?? $line[Cols::TYPE];

        if ($line[Cols::AMOUNT] < 0){
            $tx -> sell_currency = $line[Cols::CURRENCY];
            $tx -> sell = abs($line[Cols::AMOUNT]);

        } else {
            $tx -> buy_currency = $line[Cols::CURRENCY];
            $tx -> buy = $line[Cols::AMOUNT];
        }

        $transactions[] = $tx;

    }
    return $transactions;
}

/**
 * @param array $transactions []Transaction
 * @return array []Transaction
 */
function linkBuyAndSellTogether(array $transactions): array {
    $result = [];
    $buyFromBuyTypeTxs = [];
    $sellFromBuyTypeTxs = [];
    $buyFromSellTypeTxs = [];
    $sellFromSellTypeTxs = [];

    /** @var Transaction $transaction */
    foreach($transactions as $transaction) {
        if ($transaction->isBuyType() || $transaction->isSellType()) {

            if ($transaction->isBuyType()) {
                if ($transaction->isBuy()) {
                    $buyFromBuyTypeTxs[] = $transaction;
                } else {
                    $sellFromBuyTypeTxs[] = $transaction;
                }
            } else if ($transaction->isSellType()) {
                if ($transaction->isBuy()) {
                    $buyFromSellTypeTxs[] = $transaction;
                } else {
                    $sellFromSellTypeTxs[] = $transaction;
                }
            }

            if (count($buyFromBuyTypeTxs) > 0 && count($sellFromBuyTypeTxs) > 0) {
                $result[] = mergeTransactions(array_shift($buyFromBuyTypeTxs), array_shift($sellFromBuyTypeTxs));
            }

            if (count($buyFromSellTypeTxs) > 0 && count($sellFromSellTypeTxs) > 0) {
                $result[] = mergeTransactions(array_shift($buyFromSellTypeTxs), array_shift($sellFromSellTypeTxs));
            }

        } else {
            $result[] = $transaction;
        }
    }

    if ((count($buyFromBuyTypeTxs) + count($sellFromBuyTypeTxs) + count($buyFromSellTypeTxs) + count($sellFromSellTypeTxs)) > 0) {
        throw new Exception("Some buy /sell transactions does not have a counterpart");
    }
    return $result;
}


function mergeTransactions(Transaction $t1, Transaction $t2)
{
    $t1->sell = $t2->sell;
    $t1->sell_currency = $t2->sell_currency;

    $t1->type = "Trade";
    return $t1;
}


function sortBatch(array $currentStack): array {
    $trades = [];
    $fees = [];
    $rest = [];
    $result = [];


    foreach ($currentStack as $element){
        if ($element->type === 'Trade'){
            $trades [] = $element;
        } elseif ($element->type === 'Other fee') {
            $fees [] = $element;
        } else {
            $rest [] = $element;
        }
    }


    while((count($trades) + count($fees)) > 0) {
        if (count($trades)) {
            $result []= array_shift($trades);
        }
        if (count($fees)) {
            $result []= array_shift($fees);
        }
    }

    $result = array_merge($result, $rest);


    return $result;
}

function adjustOrderByType(array $txs)
{
    $currentStack = [];
    $result = [];

    $txTime = -1;
    foreach($txs as $key => $tx) {
        if (($txTime !== $tx->time)) {
            $result = array_merge($result, sortBatch($currentStack));
            $currentStack = [];
        }
        $txTime = $tx->time;
        $currentStack[] = $tx;
    }
    $result = array_merge($result, sortBatch($currentStack));

    return $result;
}



function printToJson($transactions_sorted)
{
    echo json_encode($transactions_sorted, JSON_PRETTY_PRINT);
}
