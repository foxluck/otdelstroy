<?php
	/***********************************************************
	* Insert SqlQuery 
	* ********************************************************/

	class CInsertSqlQuery extends CChangeSqlQuery {

		/***********************************************************
		* @param string table name
		* ********************************************************/
		function CInsertSqlQuery ($f_table_name) {
			parent::__construct ("insert", $f_table_name);
		}

		/***********************************************************
		* @return sql query text
		* ********************************************************/
		function getQueryText () {
			$result = "";

			$this->_filterFieldsH ();

			$result = "INSERT INTO " . $this->m_main_table . " ";
			if (!$this->m_fields_h)
				throw new RuntimeException("Not set insert fields in CInsertSqlQuery");
				

			//$fields_names_a = array ();
			$set_fields_a = array ();
			

			// Go for 
			foreach ($this->m_fields_h as $c_name => $c_value) {
				$fields_names_a[] = $c_name;
				if ($c_value === null) {
					$set_fields_a[] = $c_name . "=NULL";
				} elseif (is_numeric($c_value) && substr($c_value,-1,1) != ".") {
					$set_fields_a[] = $c_name . "=" . $c_value;
				} else {
					$c_value = str_replace ("\\", "\\\\", $c_value);
					$c_value = str_replace ("'", "\'", $c_value);
					//$fields_values_a[] = "'" . $c_value . "'";
					$c_value = "'" . $c_value . "'";
					$set_fields_a[] = $c_name . "=" . $c_value;
				}
			}
			foreach ($this->m_fields_strings as $c_str)
				$set_fields_a[] = $c_str;

			//$fields_names_str = join (", ", $fields_names_a);
			//$fields_values_str = join (", ", $fields_values_a);
			
			$set_fields_str = join (", ", $set_fields_a);
			$result .= " SET " . $set_fields_str;

			//$result .= "(" . $fields_names_str . ") VALUES (" . $fields_values_str . ") ";

			return $result;		
			
		}


	};
?>