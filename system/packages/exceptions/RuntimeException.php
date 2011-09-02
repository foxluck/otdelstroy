<?php
	/*
		Implements runtime exception
		can be throw from low-level functions like constructing sql-queries or division by zero
	*/
	class RuntimeException extends Exception {
		
		public function __construct($message, $code = 0) {
        	parent::__construct($message, $code);
    	}		
	}
?>