<?php
//////////////////////////////////////////////////
// PHP FIRECE DNS Analysis by m0rtem            //
// ........................................     //
// THIS IS A OLD AND DEFUNCT VERSION FOR WEB USE//
//////////////////////////////////////////////////

ini_set('max_execution_time', 0);

// Start timer
$time_start = microtime(true);

// Subdomains file
$filename = 'subdomains.txt';
$contents = file($filename);
$amt = count($contents);

echo "Starting " . $amt . " tests, please wait...<br><br>";

// Loop through file and handle queries
foreach($contents as $line) {

	$url = trim($line).".downornot.net";
	$wrapper = fopen('php://temp', 'r+');
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_STDERR, $wrapper);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1); // Allow a 1 ms timeout, very fast but not always accurate
	$result = curl_exec($ch);
	curl_close($ch);
	$ips = get_curl_remote_ips($wrapper);
	fclose($wrapper);
	if($ips)
	{
		echo $url . " - " . end($ips) . "<br>";
	}
	
}

// End timer
$time_end = microtime(true);
$execution_time = $time_end - $time_start;
echo '<br><b>Total Execution Time:</b> '.$execution_time.' seconds.';

function get_curl_remote_ips($fp) 
{
    rewind($fp);
    $str = fread($fp, 8192);
    $regex = '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/';
    if (preg_match_all($regex, $str, $matches)) {
        return array_unique($matches[0]);  // Array([0] => 74.125.45.100 [2] => 208.69.36.231)
    } else {
        return false;
    }
}
?>