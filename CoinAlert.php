<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ripple_threshold = floatval(file_get_contents("ripple.txt"));
$bitcoin_threshold = floatval(file_get_contents("bitcoin.txt"));
$api = "https://www.bitstamp.net/api/v2/ticker/";

function change_threshold_btc($amt) {
	$fh = fopen("bitcoin.txt", "w") or die("Unable to open file!");
	fwrite($fh, $amt);
	fclose($fh);
	return;
}

function change_threshold_xrp($amt) {
	$fh = fopen("ripple.txt", "w") or die("Unable to open file!");
	fwrite($fh, $amt);
	fclose($fh);
	return;
}

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

function last_sent_update($time) {
	$fh = fopen("last.txt", "w") or die("Unable to open file!");
	fwrite($fh, $time);
	fclose($fh);
	return;
}

function last_sent_get() {
	$fc = file_get_contents("last.txt");
	return floatval($fc);
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
			if ((last_sent_get() - time()) < 3600) {
				// don't update cause it's been less than 1 hr from last notification
				echo "Already notified within last 1 hr. Not sending.</br>\n";
			}
			else {
				mail($to, $subject, $message, $headers);
				last_sent_update(time());
				echo "Sent successfully</br>\n";
			}
		}
		else {
			echo "Mail not sent to " . $address . "<br/>\n";
		}
	}
	return;
}

function begin($api, $bitcoin_threshold, $ripple_threshold) {
	if (isset($_POST['amt']) && isset($_POST['type'])) {
		if ($_POST['type'] == "BTC") {
			change_threshold_btc($_POST['amt']);
			echo "Changed BTC threshold. May need to reload page.</br>\n";
		}
		elseif ($_POST['type'] == "XRP") {
			change_threshold_xrp($_POST['amt']);
			echo "Changed XRP threshold. May need to reload page.</br>\n";
		}
		else {
			echo "Unknown type error.";
		}
	}
	echo "<h3>CoinAlerts Main Script</h3>\n";
	echo 
	"<p>Change BTC Threshold:</p>
	<form method='post' action=''>
		<input type='text' name='amt' placeholder='20000.0'/>
		<input type='hidden' name='type' value='BTC'/>
		<input type='submit' value='Change'/>
	</form></br>
	<p>Change XRP Threshold:</p>
	<form method='post' action=''>
		<input type='text' name='amt' placeholder='2.0'/>
		<input type='hidden' name='type' value='XRP'/>
		<input type='submit' value='Change'/>
	</form>
	</br>
	";
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
	return;
}

begin($api, $bitcoin_threshold, $ripple_threshold);
echo "Complete.";

?>