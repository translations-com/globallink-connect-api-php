<?php
require_once 'WordCount.inc.php';
class PDTarget {
        /**
         * Unique document identifier. 
         */
	public $clientIdentifier;
         /**
         * Name of the document 
         */
	public $documentName;
        /**
         * Document ticket
         */
	public $documentTicket;
        /**
         *  Target's source locale 
         */
	public $sourceLocale;
        /**
         * Target's target locale
         */
	public $targetLocale;
        /**
         * Array of target metadata
         */
	public $metadata;
        /**
         * Target ticket 
         */
	public $ticket;
        /**
         * PDWordCount of target 
         */
	public $wordCount;
	function PDTarget($externalTarget) {
		$this->documentName = $externalTarget->document->documentInfo->name;
		$this->sourceLocale = $externalTarget->sourceLanguage->locale;
		$this->targetLocale = $externalTarget->targetLanguage->locale;
		$this->ticket = $externalTarget->ticket;
		$this->documentTicket = $externalTarget->document->ticket;
		$this->wordCount = $externalTarget->document->documentInfo->wordCount;
		$this->clientIdentifier = $externalTarget->document->documentInfo->clientIdentifier;
		
		if (isset ( $externalTarget->tmStatistics )) {
			$this->wordCount = new WordCount ( $externalTarget->tmStatistics->goldWordCount, $externalTarget->tmStatistics->oneHundredMatchWordCount, $externalTarget->tmStatistics->repetitionWordCount, $externalTarget->tmStatistics->noMatchWordCount, $externalTarget->tmStatistics->totalWordCount );
		}
		
		if (isset ( $externalTarget->targetInfo->metadata ) && is_array ( $externalTarget->targetInfo->metadata )) {
			foreach ( $externalTarget->targetInfo->metadata as $k => $v ) {
				$this->metadata [$k] = $v;
			}
		}
	}
}

?>