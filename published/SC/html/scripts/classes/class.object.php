<?php
class Object{
	
	function checkInfo($scheme = null){
		
	}
	
	function getVars(){
		
		return get_object_vars($this);
	}
	
	function loadFromArray($Data, $trim = false){
		
		if(is_array($Data))
		foreach ($Data as $Key=>$Value){
			
			$this->{$Key} = $trim?xCall('trim', $Value):$Value;
		}
	}
	
	function loadFromObject($Object){
		if(is_object($Object)){
			foreach ($Object as $Key=>$Value){
				$this->{$Key} = $Value;
			}
		}
	}
}
?>