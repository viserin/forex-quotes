<?php
require_once("iwrapper.php");
require_once("quote.php");

class FXCM implements IWrapper {
	private $pairs;
	private $url = "http://rates.fxcm.com/RatesXML";

	function __construct($ip = null, $pairs = null) {
		if (!$pairs || !is_array($pairs)) {
        throw new Exception(1);
    }

		$this->pairs = $pairs;
  }

	public function GetQuotes() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		//curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$ret = curl_exec($ch);
		curl_close($ch);

		if (!empty($ret)) {
			$quotes = array();
			$qtXML = new SimpleXMLElement($ret);

			if (isset($qtXML->Rate)) {
				foreach ($qtXML->Rate as $k => $v) {
					if (in_array((string)$v->attributes(), $this->pairs)) {
						$spread = substr(str_pad(sprintf("%f", round(abs((double)$v->Bid - (double)$v->Ask), 5)), 5, "0", STR_PAD_RIGHT), 0, 7);
						//$spread = (substr($tmp[1], -3) == "JPY") ? substr($spread, 0, -1) : $spread;

						if (strpos((string)$v->attributes(), "JPY") !== false) {
							$spread = $spread * 100;
						} else if ((string)$v->attributes() == "XAUUSD" || (string)$v->attributes() == "XAGUSD") {
							$spread = $spread * 100;
						} else {
							$spread = $spread * 10000;
						}

						if (empty($spread)) {
							$spread = 0;
						}

						$q = new Quote(
	        		array(
	        			"upDown" 		=> (((double)$v->Direction == 1 || (double)$v->Direction == 0) ? "up" : "down"),
	        			"pair"			=> (string)$v->attributes(),
	        			"bid" 			=> (double)$v->Bid,
	        			"ask" 			=> (double)$v->Ask,
	        			"spread" 		=> $spread
	        		)
	        	);
	        	array_push($quotes, $q);
					}
				}
			}

			return $quotes;
		} else {
			throw new Exception(2);
		}
	}
}