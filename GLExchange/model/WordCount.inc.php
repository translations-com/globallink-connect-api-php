<?php
class WordCount {
	public $golden; // int
	public $exact_100; // int
	public $fuzzy; // int
	public $repetitions; // int
	public $nomatch; // int
	public $total; // int
	function __construct($_golden, $_exact_100, $_repetitions, $_nomatch, $_total) {
		$this->golden = $_golden;
		$this->exact_100 = $_exact_100;
		$this->fuzzy = $_total - $_golden - $_exact_100 - $_repetitions - $_nomatch;
		$this->repetitions = $_repetitions;
		$this->nomatch = $_nomatch;
		$this->total = $_total;
	}
}
