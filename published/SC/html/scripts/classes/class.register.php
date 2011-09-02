<?php
class Register extends Singleton {
	
	var $Variables;
	
	function Register(){
		
		$this->Variables = array();
	}
	
	function set($Variable, &$Value){
		
		$this->Variables[$Variable] = &$Value;
	}
	
	function assign($Variable, $Value){
		
		$this->Variables[$Variable] = $Value;
	}
	
	function &get($Variable){
		
		if(!isset($this->Variables[$Variable])){
			
			$this->Variables[$Variable] = null;
			return $this->Variables[$Variable];
			print_r(debug_backtrace());
			die('Variable ('.$Variable.') doesnt set!');
		}
		return $this->Variables[$Variable];
	}
	
	function is_set($Variable){
		
		return isset($this->Variables[$Variable]);
	}
}
?>