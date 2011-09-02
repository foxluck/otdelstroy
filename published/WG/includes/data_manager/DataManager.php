<?php
	class DataManager {
		var $tableName;
		var $keyField;
		var $fieldsList;
		
		var $kernelStrings;
		var $appStrings;
		
		function DataManager($tableName, $keyField, $fieldsList, &$kernelStrings, &$appStrings) {
			$this->tableName = $tableName;
			$this->keyField = $keyField;
			$this->fieldsList = $fieldsList;
			$this->kernelStrings = &$kernelStrings;
			$this->appStrings = &$appStrings;
		}
		
		
		function add ($params) {
			if (!$this->tableName)
				return PEAR::raiseError("empty table name for DataManager");
			
			$sql = new CInsertSqlQuery ($this->tableName);
			$sql->addFields ($params, $this->fieldsList);			
			
			$res = db_query( $sql->getQuery());
			if ( PEAR::isError($res) )
				return $res;
			return db_insert_id(null);
		}
		
		function update ($key, $params) {
			
			if (!$this->tableName)
				return PEAR::raiseError("empty table name for DataManager");
			
			$sql = new CUpdateSqlQuery ($this->tableName);
			$sql->addFields ($params, $this->fieldsList);
			$sql->addConditions ($this->keyField, $key);
			
			//PEAR::raiseError ($sql->getQuery());
			
			$res = db_query( $sql->getQuery());
			if ( PEAR::isError($res) )
				return $res;
			return true;
		}
		
		function delete ($key) {
			$sql = new CDeleteSqlQuery ($this->tableName);
			$sql->addConditions ($this->keyField, $key);
			$res = db_query( $sql->getQuery());
			if ( PEAR::isError($res) )
				return $res;
		}
		
		function get ($key) {
				$sql = new CSelectSqlQuery ($this->tableName);
				$sql->addConditions($this->keyField, $key);
				$row = db_query_result ($sql->getQuery(), DB_ARRAY);
				if ( PEAR::isError($row) )
					return $row;
				return $row;
		}
		
		function getList ($orderBy = "") {
			$sql = new CSelectSqlQuery ($this->tableName);
			if ($orderBy)
				$sql->setOrderBy ($orderBy);
			$result = $this->getListFromQuery ($sql->getQuery(), $this->keyField);
			
			return $result;
		}
		
		function getListFromQuery ($sqlStr, $key = "") {
			
			$qr = db_query( $sqlStr);
			if ( PEAR::isError($qr)) 
				return $qr;
			
			$result = array ();
			while ($row = db_fetch_array($qr)) {
				if ($key && isset($row[$key]))
					$result[$row[$key]] = $row;
				else
					$result[] = $row;
			}
			
			
			db_free_result($qr);
			return $result;
		}
		
	}
?>