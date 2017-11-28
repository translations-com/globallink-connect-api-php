<?php
require_once 'LanguageDirection.inc.php';
require_once 'Workflow.inc.php';
require_once 'CustomAttribute.inc.php';
class PDProject {
       /**
        * Project short code
        */
	public $shortcode;
       /**
        * Project name
        */
	public $name;
       /**
         * Project ticket, an internal ID required for creating submissions
         */
	public $ticket;
       /**
         * List of supported language directions. Array of PDLanguageDirection 
         */
	public $languageDirections = array ();
        /**
         * List of file format profiles configured for this project. A file format
         * profile defines the parsing rules to identify translatable content and
         * inline non-translatable content (placeholders) within the submitted
         * document. Project Director supports a wide range of formats such as XML,
         * DOC, PPT, XLS, .properties. Array of String
         */
	public $fileFormats = array ();
       /**
         * List of available workflows configured for this project. Array or PDWorkflow objects
         */
	public $workflows = array ();
       /**
         * List of custom attributes configured for this project. Array of PDCustomAttribute
         */
	public $customAttributes = array ();
	function __construct($externalProject) {
		$this->name = $externalProject->projectInfo->name;
		$this->shortcode = $externalProject->projectInfo->shortCode;
		$this->ticket = $externalProject->ticket;
		$i = 0;
		if (isset ( $externalProject->projectLanguageDirections ) && is_array ( $externalProject->projectLanguageDirections )) {
			foreach ( $externalProject->projectLanguageDirections as $externalLanguageDirection ) {
				$this->languageDirections [$i ++] = new PDLanguageDirection ( $externalLanguageDirection );
			}
		} elseif (isset ( $externalProject->projectLanguageDirections )) {
			$this->languageDirections [$i ++] = new PDLanguageDirection ( $externalProject->projectLanguageDirections );
		}
		
		$i = 0;
		if (isset ( $externalProject->fileFormatProfiles ) && is_array ( $externalProject->fileFormatProfiles )) {
			foreach ( $externalProject->fileFormatProfiles as $fileFormatProfile ) {
				$this->fileFormats [$i ++] = $fileFormatProfile->profileName;
			}
		} elseif (isset ( $externalProject->fileFormatProfiles )) {
			$this->fileFormats [$i ++] = $externalProject->fileFormatProfiles->profileName;
		}
		
		$i = 0;
		if (isset ( $externalProject->workflowDefinitions ) && is_array ( $externalProject->workflowDefinitions )) {
			foreach ( $externalProject->workflowDefinitions as $externalWorkflowDefinition ) {
				$this->workflows [$i ++] = new PDWorkflow ( $externalWorkflowDefinition );
			}
		} elseif (isset ( $externalProject->workflowDefinitions )) {
			$this->workflows [$i ++] = new PDWorkflow ( $externalProject->workflowDefinitions );
		}
		
		$i = 0;
		if (isset ( $externalProject->projectCustomFieldConfiguration ) && is_array ( $externalProject->projectCustomFieldConfiguration )) {
			foreach ( $externalProject->projectCustomFieldConfiguration as $externalCustomField ) {
				$this->customAttributes [$i ++] = new PDCustomAttribute ( $externalCustomField );
			}
		} elseif (isset ( $externalProject->projectCustomFieldConfiguration )) {
			$this->customAttributes [$i ++] = new PDCustomAttribute ( $externalProject->projectCustomFieldConfiguration );
		}
	}
}

?>
