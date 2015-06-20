#!/usr/bin/php

<?php

	$options = getopt("hf:d:e:",array("help","file:","dice:","error:"));
	
	if (array_key_exists("f",$options)) {
		
		$fh = fopen($options["f"],"r");
		if (!$fh) {
			die("Could not read file " . $options["f"] . "\n");
		}
		$lfreq = array();		
		$rolls = array();
		$err = .0001;
		if (array_key_exists("e",$options)) {
			if (floatval($options["e"]) < .5 && floatval($options["e"]) > 0) {
				$pcterr = $options["e"];
			}
		}
		$dicesides = 6;
		if (array_key_exists("d",$options)) {
			if (intval($options["d"]) > 0) {
				$dicesides = $options["d"];
			}
		}
		echo "Inputs:\n";
		$sum = 0;
		while ($line = fgets($fh)) {
			$labelpct = explode(",",$line);
			$lfreq[$labelpct[0]] = floatval(trim($labelpct[1])) * (strpos(trim($labelpct[1]),"%") > 0 ? .01 : 1);
			$rolls[$labelpct[0]] = array();
			echo $labelpct[0] . " " . ($lfreq[$labelpct[0]] * 100) . "%\n";
			$sum += $lfreq[$labelpct[0]] * 100;
 		}
		echo "Sum: $sum%\n";
		$exp = -1;
		$counters = array();
		$counters[0] = 1;
		$depth = 0;
		$done = false;
		while (!$done) {			
			arsort($lfreq);
			reset($lfreq);
			$fraction = pow($dicesides,$exp);
			if ((current($lfreq) >= $fraction)) {
				foreach($lfreq as $key => $value) {
					if ($value >= ($fraction-$err) && !$done) {
						if (array_key_exists($depth, $rolls[$key]))
							$rolls[$key][$depth]++;
						else 
							$rolls[$key][$depth] = 1;
						$counters[$depth]++;
						if ($counters[$depth] > $dicesides && $depth > 0) {
							$par_depth = $depth - 1;
							while ($par_depth >= 0 && ++$counters[$par_depth] > $dicesides) {
								$counters[$par_depth] = 1;
								$par_depth--;
							}
							if ($par_depth == -1) {
								$done = true;
							}							
							$counters[$depth] = 1;
						}
						$lfreq[$key] -= $fraction;
					}
				}
			}
			else {
				$exp--;
				$depth = abs($exp)-1;
				$counters[$depth] = 1;
			}
		}	
		$sum = 0;
		echo "Estimates\n";
		foreach($rolls as $key => $value) {
			$est = 0;
			foreach ($rolls[$key] as $depth => $num) {
				$est += $num * pow($dicesides,-1 - $depth);
			}
			$est *= 100;
			$sum += $est;
			echo "$key $est%\n";
		}
		echo "Sum: $sum%\n";
		$tree = array();
		$counters = array();
		$num = 0;
		for ($depth = 0; $depth < abs($exp); $depth++) {
			$counters[$depth] = 1;
			foreach($rolls as $key => $value) {
				if (array_key_exists($depth, $rolls[$key])) {
					while ($rolls[$key][$depth] > 0) {
						$rolls[$key][$depth]--;
						echo implode("-", $counters) . " $key\n";
						$counters[$depth]++;
						$num++;
						if ($counters[$depth] > $dicesides && $depth > 0) {
							$par_depth = $depth - 1;
							while ($par_depth >= 0 && ++$counters[$par_depth] > $dicesides) {
								$counters[$par_depth] = 1;
								$par_depth--;
							}							
							$counters[$depth] = 1;
						}
					}
				}
			}
		}
		echo "Total Combinations: $num\n"; 
	}
	else {
		die("Please input the path to a csv file that contains label and probability data\n");
	}
	exit;
?>