#!/usr/bin/env php
<?php
///////////////////////////////////////////////////////////
// PHP FIRECE DNS Analysis by m0rtem.        	         //
// eh                                       	         //
// thanks to rsnake for the idea                         //
///////////////////////////////////////////////////////////

ini_set('max_execution_time', 0);

include("lib/rollingcurlx.class.php");

// Remove first argument
array_shift($argv);

// Defaults
$target = "";
$bruteforcefile = "data/subdomainscommon.txt";
$openSocketsAllowed = 20;

// If there are arguments
if ($argc > 1) 
{
  	// Variables
	$target = $argv[0];
	
	// If user gave us a brute force file to work with else use default
	if(isset($argv[1]))
	{	
		$bruteforcefile = "data/".$argv[1];
	}

	// If open sockets allowed setting set
	if(isset($argv[2]))
	{	
		$openSocketsAllowed = $argv[2];
	}
	
	// Run the scan
	initialize($target, $bruteforcefile, $openSocketsAllowed);

}
else
{
	showHelp();
}


function showHelp()
{
	echo "Usage: php fierce.php [<target>] <bruteforce file> <number of open sockets>".PHP_EOL;
}

function initialize($target,$bruteforce,$openSocketsAllowed)
{
	echo "     _       ___ _                 ".PHP_EOL;
	echo " ___| |_ ___|  _|_|___ ___ ___ ___ ".PHP_EOL;
	echo "| . |   | . |  _| | -_|  _|  _| -_|".PHP_EOL;
	echo "|  _|_|_|  _|_| |_|___|_| |___|___|".PHP_EOL;
	echo "|_|     |_|                        ".PHP_EOL;
	echo "  a dns analysis tool by m0rtem".PHP_EOL.PHP_EOL;

	// Start timer
	$time_start = microtime(true);
	
	echo "Starting phpfierce on target - ".$target."".PHP_EOL.PHP_EOL;
	echo "Attempting DNS lookup...".PHP_EOL.PHP_EOL;
	
	// Start DNS query
	$result = dns_get_record($target,DNS_ALL);
	
	foreach($result as $results)
	{
		if(isset($results['ip']))
		{
			$s = $results['ip'];
		}
		else if(isset($results['target']))
		{
			$s = $results['target'];
		}
		$ip = gethostbyname($s);
		echo "".$results['type']." - ".$s."".PHP_EOL;
		echo "    |".PHP_EOL;
		echo "    -> ".$ip."".PHP_EOL.PHP_EOL;
	}
	
	// Start bruteforce
	$numberOfLines = count(file($bruteforce));
	$contents = file($bruteforce);
	
	echo PHP_EOL."Starting " . $numberOfLines . " tests via bruteforce, please wait...".PHP_EOL.PHP_EOL;
	
	$RCX = new RollingCurlX($openSocketsAllowed);
	$RCX->setTimeout(10); //in milliseconds
	
	// Loop through file and handle queries
	foreach($contents as $line) {
		$url = trim($line).".".$target;
		$options = [CURLOPT_VERBOSE => false, CURLOPT_FOLLOWLOCATION => true, CURLOPT_RETURNTRANSFER => true,CURLOPT_TIMEOUT_MS => 1];
		$headers = ["Content-type: application/xml"];
		$post_data = null;
		$user_data = ['foo', 'bar'];
		$RCX->addRequest($url, $post_data, 'callback_functn', $user_data, $options, $headers);
	}
	
	$RCX->execute();
	
	// End timer
	$time_end = microtime(true);
	$execution_time = $time_end - $time_start;
	echo PHP_EOL.'Total Execution Time: '.number_format((float)$execution_time, 2, '.', '').' seconds.'.PHP_EOL;

}

function callback_functn($response, $url, $request_info, $user_data, $time) {

	if($request_info['primary_ip'])
	{
		$ip = $request_info['primary_ip'];
		$timeFormatted = floor($time % 1000);

		// If using proxychains i think this should fix, ha
		if($ip == "127.0.0.1")
		{
			$ip = gethostbyname($url);
		}

		echo $request_info['url'] . " - " . $ip . " (".$timeFormatted."ms)" . PHP_EOL;

	}
}


?>
