<?php
class DBObject extends Object {
	
	var $__db_table = '';
	var $__primary_key = '';
	var $__primary_key_autoincrement = true;
	var $__db_fields = null;
	
	function __construct(){
		
		$this->__db_fields = $this->__getDBVars();
	}
	
	function DBObject(){

		$this->__construct();
	}
	/**
	 * Load object by id
	 *
	 * @param mixed $id
	 * @return bool
	 */
	function loadByID($id){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/*@var $DBHandler DataBase*/

		$DBRes = $DBHandler->ph_query('SELECT * FROM '.$this->__db_table.' WHERE `'.$this->__primary_key.'`=?', $id);
		if(!$DBRes->getNumRows())return false;
		
		$row = $DBRes->fetchAssoc();
				
		$this->loadFromArray($row);
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	function save($force_insert = false){

		if($this->{$this->__primary_key} && !$force_insert){
			
			$dbq = "
				UPDATE `{$this->__db_table}` SET ".$this->__makeFieldsSet()." WHERE `{$this->__primary_key}` = '".xEscapeSQLstring($this->{$this->__primary_key})."' 
			";
		}else{
			
			$fields = $this->__getDBVars();
			if($this->__primary_key_autoincrement)unset($fields[$this->__primary_key]);
			$dbq = "
				INSERT `{$this->__db_table}` (`".implode('`, `', array_keys($fields))."`) 
				VALUES('".implode("', '", xEscapeSQLstring($fields))."')
			";
		}
		
		$dbres = db_query($dbq);
		if(!$this->{$this->__primary_key}){
			
			$this->{$this->__primary_key} = db_insert_id(); 
		}
		
		return true;
	}
	
	function delete(){
		
		$dbq = "
			DELETE FROM `{$this->__db_table}` WHERE `{$this->__primary_key}` = '".xEscapeSQLstring($this->{$this->__primary_key})."' 
		";
		db_query($dbq);
	}
	
	function __getDBVars(){
		
		$check = is_null($this->__db_fields);
		
		$fields = $this->getVars();
		$_fields = array();
		foreach ($fields as $k=>$v){

			if(substr($k, 0, 1)=='_')continue;
			if(!$check && !array_key_exists($k, $this->__db_fields))continue;
			
			$_fields[$k] = $v;
		}
		
		return $_fields;
	}
	
	function __makeFieldsSet(){
		
		$fields_set = '';
		$fields = $this->__getDBVars();
		foreach($fields as $k=>$v){
			
			if($k == $this->__primary_key)continue;
			$v = xEscapeSQLstring($v);
			$fields_set .= ", `{$k}`='{$v}'";
		}
		
		return substr($fields_set, 2);
	}
	
	/**
	 * Correct all wrong data
	 */
	function correctData(){
		
		$dbvars = $this->__getDBVars();
		foreach ($dbvars as $field=>$data)
			$this->{$field} = $this->correctFieldData($field, $data);
	}
	
	function correctFieldData($field, $data){
		
		return $data;
	}
}

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
}
?>