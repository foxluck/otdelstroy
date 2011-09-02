<?php
	/*
		Implements runtime exception
		can be throw from low-level functions like constructing sql-queries or division by zero
	*/
	class NotImplementedException extends Exception {
		public function __construct() {
        parent::__construct("Not implemented");
    }		
	}
?>