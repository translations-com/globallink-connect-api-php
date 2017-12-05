<?php
class PDWorkflow {
         /**
         * Workflow name
         */
	public $name;
        /**
         * Workflow ticket 
         */
	public $ticket;
	function __construct($externalWorkflow) {
		$this->name = $externalWorkflow->name;
		$this->ticket = $externalWorkflow->ticket;
	}
}