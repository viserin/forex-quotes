<?php
require_once("iwrapper.php");
require_once("quote.php");

class MT4 implements IWrapper {
	private $ip;
	private $pairs;

	function __construct($ip = null, $pairs = null) {
		if (!$ip || !$pairs || !is_array($pairs)) {
        throw new Exception(1);
    }

		$this->ip = $ip;
		$this->pairs = $pairs;
  }

	public function GetQuotes() {
		/*header("Cache-Control: must-revalidate");
		$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT";
		header($ExpStr);*/
		define("T_HOST", $this->ip);
		define("T_PORT", 443);
		define("T_TIMEOUT", 5);

		$q = "QUOTES-" . implode(":,", $this->pairs).":,";
		$ptr = @fsockopen(T_HOST, T_PORT, $errno, $errstr, T_TIMEOUT);
		$ret = "";

		if ($ptr) {
			if (fputs($ptr, "W$q\nQUIT\n") != FALSE) {
				while (!feof($ptr)) {
					if (($line = fgets($ptr, 128)) == "end\r\n") break;
						$ret .= $line;
				}
			}
		}

		if (!empty($ret)) {
			$quotes = array();

			foreach (explode("\n", $ret) as $line) {
				if (isset($line[0]) && ($line[0] == 'u' || $line[0] == 'd')) {
        	$tmp = explode(' ', $line);
        	$tmp[1] = strtoupper(substr($tmp[1], 0, -1));

					$spread = substr(str_pad(sprintf("%f", round(abs($tmp[2] - $tmp[3]), 5)), 5, "0", STR_PAD_RIGHT), 0, 7);
					$spread = (substr($tmp[1], -3) == "JPY") ? substr($spread, 0, -1) : $spread;

					if (substr($tmp[1], -3) == "JPY") {
						$spread = $spread * 100;
					} else if ($tmp[1] == "XAUUSD" || $tmp[1] == "XAGUSD") {
						$spread = $spread * 100;
					} else {
						$spread = $spread * 10000;
					}

					if (empty($spread)) {
						$spread = 0;
					}

					$q = new Quote(
        		array(
        			"upDown" 		=> $tmp[0],
        			"pair"			=> $tmp[1],
        			"bid" 			=> $tmp[2],
        			"ask" 			=> $tmp[3],
        			"spread" 		=> $spread
        		)
        	);

        	array_push($quotes, $q);
				}
			}

			return $quotes;
		} else {
			throw new Exception(2);
		}
	}
}