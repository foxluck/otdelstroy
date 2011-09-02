<?php
class DBObject extends Object {
	
	var $__db_table = '';
	var $__primary_key = '';
	var $__primary_key_autoincrement = true;
	var $__db_fields = null;
	var $__className = '';
	
	var $__use_cache = true;
	
	function __construct(){
		
		$this->__db_fields = $this->__getDBVars();
	}
	
	function registerByID($id){
		if($this->__className&&$this->__use_cache){
			$Register = &Register::getInstance();
			/*@var $Register Register*/
			$Register->set('OBJECT_CACHE_'.$this->__className.'__'.$id,$this);
			//if(isset($_GET['debug'])) print 'OBJECT_CACHE_'.$this->__className.'__'.$id.' REGISTERED<hr>';
		}
	}
	
	function getRegisteredByID($id){
		if($this->__className&&$this->__use_cache){
			if(isset($_GET['debug'])) print 'GETTING OBJECT_CACHE_'.$this->__className.'__'.$id.'<hr>';
			
			$Register = &Register::getInstance();
			/*@var $Register Register*/
			$copy = &$Register->get('OBJECT_CACHE_'.$this->__className.'__'.$id);
			if(is_object($copy)){
				$this->loadFromObject($copy);
				//if(isset($_GET['debug'])) print 'SUCCESS<hr>';
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Load object by id
	 *
	 * @param mixed $id
	 * @return bool
	 */

	function loadByID($id){
		
		if($this->getRegisteredByID($id))return true;
			
		$Register = &Register::getInstance();
		/*@var $Register Register*/


		
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/*@var $DBHandler DataBase*/

		$DBRes = $DBHandler->ph_query('SELECT * FROM '.$this->__db_table.' WHERE `'.$this->__primary_key.'`=?', $id);
		if(!$DBRes->getNumRows())return false;
		
		$row = $DBRes->fetchAssoc();
		
		/*if(preg_match('@FROM\s+?#([a-z_]+)@i', $this->__query_loadbyid, $p_results)){
			LanguagesManager::ml_fillFields(constant($p_results[1]), $row);
		}*/
		LanguagesManager::ml_fillFields($this->__db_table, $row);
		
		$this->loadFromArray($row);
		
		$this->registerByID($id);
				
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
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/*@var $DBHandler DataBase*/
		$DBRes = $DBHandler->query($dbq);
		
		if(!$this->{$this->__primary_key}){
			
			$this->{$this->__primary_key} = $DBRes->getInsertID(); 
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
			if(!$check && LanguagesManager::ml_isMLField($this->__db_table, $k)){
				
				$r_languageEntry = LanguagesManager::getLanguages();
				for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++){
					/*@var $r_languageEntry Language*/
		
					$_fname = LanguagesManager::ml_getLangFieldName($k, $r_languageEntry[$_j]);
					$_fields[$_fname] = isset($fields[$_fname])?$fields[$_fname]:'';
				}
				continue;
			}elseif(!$check && !array_key_exists($k, $this->__db_fields))continue;
			
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
		/*$mlvars = LanguagesManager::ml_getMLFields($this->__db_table);
		foreach ($mlvars as $field){
			$this-> {$field} = $this->{LanguagesManager::ml_getLangFieldName($field)};
		}*/
	}
	
	function correctFieldData($field, $data){
		
		return $data;
	}
	
	/**
	 * @param DBObject $object
	 */
	static function is_inited_object($object){
		
		if(!is_object($object))return false;
		return $object->{$object->__primary_key}>0;
	}
}
?>