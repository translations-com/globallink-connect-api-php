<?php
class PDDocument {
        /*
         * Document data for translation
         */
	public $data;
        /*
         * Document name that will show up in Project Director
         */
	public $name;
        /*
         * Source Language of the document in xx-XX format.
         */
	public $sourceLanguage;
        /*
         * Target languages into which this document will be translated
         */
	public $targetLanguages;
        /*
         * [Optional] Specify a unique identifier for this document (if one exists)
         * in the content management system
         */
	public $clientIdentifier;
        /*
         * [Optional] Encoding of the content if the data is textual. Defaults to UTF-8
         */
	public $encoding = "UTF-8";
        /**
         * File Format profile that defines the parsing rules for the document
         */
	public $fileformat;
        /**
         * [Optional] Translation instructions
         */
	public $instructions;
        /*
         * [Optional] Key-value array of additional metadata that you may want to attach to your document
         */
	public $metadata;
	public function getDocumentInfo($submission) {
		$documentInfo = new DocumentInfo ();
		$documentInfo->projectTicket = $submission->project->ticket;
		$documentInfo->name = $this->name;
		$documentInfo->sourceLocale = $this->sourceLanguage;
		
		if ($submission->ticket != "") {
			$documentInfo->submissionTicket = $submission->ticket;
		}
		
		$i = 0;
		if (isset ( $this->metadata )) {
			$metadatas = array ();
			foreach ( $this->metadata as $k => $v ) {
				$metadata = new Metadata ();
				$metadata->key = substr($k, 0, 255);
				$metadata->value = substr($v, 0, 1024);
				$metadatas [$i ++] = $metadata;
			}
			$documentInfo->metadata = $metadatas;
		}
		
		if (isset ( $this->clientIdentifier )) {
			$documentInfo->clientIdentifier = $this->clientIdentifier;
		}
		
		if (isset ( $this->instructions )) {
			$documentInfo->instructions = $this->instructions;
		} else {
			$documentInfo->instructions = $submission->instructions;
		}
		
		$documentInfo->targetInfos = $this->getTargetInfos ();
		
		return $documentInfo;
	}
	
	/**
	 * Internal method used by the UCF API
	 *
	 * @return TargetInfo array
	 */
	private function getTargetInfos() {
		$targetInfos = array ();
		foreach ( $this->targetLanguages as $language ) {
			$targetInfo = new TargetInfo ();
			$targetInfo->targetLocale = $language;
			
			if (isset ( $submission->dueDate )) {
				$targetInfo->requestedDueDate = $submission->dueDate;
			} else {
				$targetInfo->requestedDueDate = 0;
			}
			
			if (isset ( $this->encoding )) {
				$targetInfo->encoding = $this->encoding;
			} else {
				$targetInfo->encoding = "UTF-8";
			}
			$targetInfos [] = $targetInfo;
		}
		return $targetInfos;
	}
	
	/**
	 * Internal method used by the UCF API
	 *
	 * @return ResourceInfo object
	 * @throws IOException
	 */
	public function getResourceInfo() {
		$resourceInfo = new ResourceInfo ();
		if (isset ( $this->encoding )) {
			$resourceInfo->encoding = $this->encoding;
		} else {
			$resourceInfo->encoding = "UTF-8";
		}
		$resourceInfo->size = strlen ( $this->data );
		$resourceInfo->classifier = $this->fileformat;
		$resourceInfo->name = $this->name;
		
		if (isset ( $this->clientIdentifier )) {
			$resourceInfo->clientIdentifier = $this->clientIdentifier;
		}

		return $resourceInfo;
	}
}

?>
