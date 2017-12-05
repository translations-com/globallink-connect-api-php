<?php 
class PDLanguageDirection {

	public $sourceLanguage;
	public $sourceLanguageName;
	public $targetLanguage;
	public $targetLanguageName;
	function __construct($externalLanguageDirection){
		$this->sourceLanguage = $externalLanguageDirection->sourceLanguage->locale;
		$this->sourceLanguageName = $externalLanguageDirection->sourceLanguage->value;
		$this->targetLanguage = $externalLanguageDirection->targetLanguage->locale;
		$this->targetLanguageName = $externalLanguageDirection->targetLanguage->value;
	}
	
}

?>