<?php

class Quote {
	public $upDown = null;
	public $pair = null;
	public $bid = null;
	public $ask = null;
	public $spread = null;

	function __construct($vals = null) {
		if (is_array($vals)) {
      if (isset($vals["upDown"])) {
        $this->upDown = $vals["upDown"];
      }
      if (isset($vals["pair"])) {
        $this->pair = $vals["pair"];
      }
      if (isset($vals["bid"])) {
        $this->bid = $vals["bid"];
      }
      if (isset($vals["ask"])) {
        $this->ask = $vals["ask"];
      }
      if (isset($vals["spread"])) {
        $this->spread = $vals["spread"];
      }
    }
	}
}