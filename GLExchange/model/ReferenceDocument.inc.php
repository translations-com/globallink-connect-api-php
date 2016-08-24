<?php 

class PDReferenceDocument {

         /*
         * Document data
         */       
	public $data;
         /*
         * Document name that will show up in Project Director
         */
	public $name;
	
	public function getResourceInfo() {
		$resourceInfo = new ResourceInfo ();
		$resourceInfo->size = strlen ( $this->data );
		$resourceInfo->name = $this->name;
		return $resourceInfo;
	}
}

?>