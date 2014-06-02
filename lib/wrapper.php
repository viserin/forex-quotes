<?php
require_once("iwrapper.php");

class Wrapper {
	private $connector;

	function __construct($connector) {
      $this->connector = $connector;
  }

  public function GetQuotes() {
	  return $this->connector->GetQuotes();
  }
}