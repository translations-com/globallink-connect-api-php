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
	function PDWorkflow($externalWorkflow) {
		$this->name = $externalWorkflow->name;
		$this->ticket = $externalWorkflow->ticket;
	}
}