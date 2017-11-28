<?php
class PDCustomAttribute {
        /**
         * Is attribute required. Boolean
         */
	public $mandatory;
        /**
         * Attribute name
         */
	public $name;
        /**
         * Attribute type
         */
	public $type;
        /**
         * Attribute value
         */
	public $values;
	function __construct($customAttribute = NULL) {
		if (isset ( $customAttribute )) {
			$this->mandatory = $customAttribute->mandatory;
			$this->name = $customAttribute->name;
			$this->type = $customAttribute->type;
			$this->values = $customAttribute->values;
		}
	}
}
?>
