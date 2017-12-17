<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ripple_threshold = 0.777;
$bitcoin_threshold = 20000.0;
$api = "https://www.bitstamp.net/api/v2/ticker/";

class coin_data {
	public $list = ['bitcoin', 'ripple'];
	public $bitcoin = 'btcusd';
	public $ripple = 'xrpusd';
}

function get_content($URL) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $URL);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
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

function begin($api, $bitcoin_threshold, $ripple_threshold) {
	echo "<h3>CoinAlerts Main Script</h3>\n";
	$coins = new coin_data();
	foreach ($coins->list as $coin) {
		echo "<p style='font-weight:bold;'>" . $coin . " (" . $coins->$coin . ")</p>\n";
		$coinData = get_content($api . $coins->$coin . "/");
		$coin_USDPrice = json_decode($coinData, TRUE)["bid"];
		if ($coin == "ripple") {
			$threshold = $ripple_threshold;
		}
		elseif ($coin == "bitcoin") {
			$threshold = $bitcoin_threshold;
		}
		surpassed_question($threshold, $coin, $coin_USDPrice);
		echo "</br>\n";
	}
}

begin($api, $bitcoin_threshold, $ripple_threshold);
echo "Complete.";
?>