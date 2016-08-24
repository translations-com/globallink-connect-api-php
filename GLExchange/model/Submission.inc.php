<?php

require_once 'Project.inc.php';
class PDSubmission {
         /**
         * [Optional] Custom attributes. Array of PDCustomAttribute. PDProject->customAttributes gets you the list of configured attributes for the project. 
         */
	public $customAttributes;
         /**
         * [Optional] Due date by when the submission should be completed in Project Director.
         * This should always be greater than current date. If due date is not specified, Project Director 
         * will rely on the configured language model to calculate a due date. strtotime*1000
         */
	public $dueDate;
         /**
         * [Optional] Instructions for the translators    
         */
	public $instructions;
         /**
         * [Optional] Submission priority. Boolean. Defaults to false(Normal)
         */
	public $isUrgent;
         /**
         * [Optional] Key-value array of metadata
         */
	public $metadata;
         /**
         * Name of the submission that will show up in Project Director
         */
	public $name;
         /**
         * Set the PDProject for which this submission will be created
         */
	public $project;
         /**
         * [Optional] Notes for the project managers 
         */
	public $pmNotes;
         /**
         * [Optional]  Set the submitter to a user other than the logged in user 
         */
	public $submitter;
        /**
        * Submission ticket
        */
	public $ticket;
         /**
         * [Optional]  Set the PDWorkflow to use 
         */
	public $workflow;
}

?>