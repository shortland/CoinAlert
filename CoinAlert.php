<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ripple_threshold = 0.777;
$bitcoin_threshold = 20000.0;

$APIurl = "https://api.coinmarketcap.com/v1/ticker/";
$coins = ['bitcoin', 'ripple'];//, 'ethereum', 'zencash', 'neo', 'vertcoin', 'stellar', 'district0x'];

function curl_get_content($URL) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $URL);
      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
}

function make_dir_ifne($path) {
	if (!file_exists($path)) {
    	mkdir($path, 0777, true);
	}
}

function make_file_ifne($path) {
	if (!file_exists($path)) {
		$fh = fopen($path, 'w');
		fwrite($fh, "[]");
		fclose($fh);
	}
}

class recordedCoinData {
	public $c_usdPrice, $c_btcPrice, $c_updateTime, $c_symbol = "";
}

echo "<h3>CoinAlerts Main Script</h3>\n";

foreach ($coins as $coin) {
	// make coin directory if doesn't already exist
	make_dir_ifne($coin);

	// get the coin data from api
	echo "<p style='font-weight:bold;'>" . $coin . "</p>\n";
	$coinData = curl_get_content($APIurl . $coin . "/");

	// mainly for debugging, print out the coin data
	echo "<p>(USD) " . $coin_USDPrice = (json_decode($coinData, TRUE))[0]["price_usd"];
	echo "</p>\n";

	echo "<p>(BTC) " . $coin_BTCPrice = (json_decode($coinData, TRUE))[0]["price_btc"];
	echo "</p><br/>\n";

	// write the raw json data to a file in the coin dir, with the name of file being current timestamp time()
	// I don't think I necessarily need to save this data. but big data is good data? welcome to 21st century :)
	// // change my mind, I don't want to fill disk space with this rn... maaybe later if I think it's necessary.
	/*
	$coinTimeData = fopen($coin."/".time().".json", "w") or die("Unable to open file!");
	fwrite($coinTimeData, $coinData);
	fclose($coinTimeData);
	*/

	// create db file if not exists
	make_file_ifne($coin . "/CoinTable.json");
	chmod($coin, 0777);
	chmod($coin . "/CoinTable.json", 0777);
	// append data to the db file
	// first creating json object
	$coinObject = new recordedCoinData();
	$coinObject->c_usdPrice = $coin_USDPrice;
	$coinObject->c_btcPrice = $coin_BTCPrice;
	$coinObject->c_updateTime = (json_decode($coinData, TRUE))[0]["last_updated"];
	$coinObject->c_symbol = (json_decode($coinData, TRUE))[0]["symbol"];
	// now appending this object of json to a json array of the coin DB
	$currentDB = json_decode(file_get_contents($coin . "/CoinTable.json"), TRUE);
	array_push($currentDB, $coinObject);
	$currentDB = json_encode($currentDB);
	// finalize, and close file
	$myfile = fopen($coin . "/CoinTable.json", "w") or die("Unable to open file!");
	fwrite($myfile, $currentDB);
	fclose($myfile);

	// sleep for a 1/4 of a second.
	//usleep(250000);

	if ($coin == "ripple") {
		$threshold = $ripple_threshold;
	}
	elseif ($coin == "bitcoin") {
		$threshold = $bitcoin_threshold;
	}
	surpassed_question($threshold, $coin, $coin_USDPrice);
}

function surpassed_question($threshold, $coin_type, $now_val) {
	echo "THRESHOLD IS: " . $threshold . "</br>\n";
	echo "COIN_TYPE IS: " . $coin_type . "</br>\n";
	echo "NOW_VAL IS: " . $now_val . "</br>\n";
	$addresses = ['6464643484@tmomail.net', 'ilankleiman@gmail.com'];
	foreach ($addresses as $address) {
		if (floatval($now_val) > floatval($threshold)) {
			echo "threshold met</br>\n";
			$to      = $address;
			$subject = $coin_type . " THRESHOLD MET\n";
			$message = "\nThreshold was set at " . $threshold . "\nCURRENT VALUE: " . $now_val;
			$headers = 'From: CoinAlert@ilankleiman.com' . "\r\n" .
			    'Reply-To: ' . $address . "\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			mail($to, $subject, $message, $headers);
			echo "Sent successfully<br/>\n";
		}
		else {
			echo "Mail not sent to " . $address . "<br/>\n";
		}
	}
}


echo "Complete.";
?>