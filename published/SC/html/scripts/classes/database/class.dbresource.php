<?php
class DBResource{
	
	var $Result;
	var $Type = 'mysql';
	var $Link;
	var $SQL = '';
	
	function DBResource($_Result, $_Type, $_Link, $_SQL){
		
		$this->Result 	= $_Result;
		$this->Type 	= $_Type;
		$this->Link 	= $_Link;
		$this->SQL = $_SQL;
	}
	
	function fetchRow($i = null){
		
		switch ($this->Type){
			case 'mysql':
				if(is_null($i))
					return mysql_fetch_row($this->Result);
				else {
					$r = mysql_fetch_row($this->Result);
					return $r[$i];
				}
			break;
		}
	}

	function fetchAssoc(){
		
		switch ($this->Type){
			case 'mysql':
				return mysql_fetch_assoc($this->Result);
			break;
		}
	}

	/**
	 * Fetch results into array
	 *
	 * @param string|null $key_field - if not null that field value will be used as key
	 * @return array
	 */
	function fetchArrayAssoc($key_field = null){
		
		$array = array();
		while ($row = $this->fetchAssoc()){
			
			if(is_null($key_field))$array[] = $row;
			else $array[$row[$key_field]] = $row;
		}
		
		return $array;
	}
	
	function getInsertID(){
		
		switch ($this->Type){
			case 'mysql':
				return mysql_insert_id();
			break;
		}
	}
	
	function getNumRows(){
		
		switch ($this->Type){
			case 'mysql':
				return mysql_num_rows($this->Result);
			break;
		}
	}
}
?>