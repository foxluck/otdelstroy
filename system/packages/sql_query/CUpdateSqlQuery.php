<?php
	class CUpdateSqlQuery extends CChangeSqlQuery {
		
		/***********************************************************
		* Конструктор
		* Параметры: 
		*	1) имя основной таблицы
		* ********************************************************/
		function CUpdateSqlQuery ($f_main_table) {
			parent::CChangeSqlQuery ("update", $f_main_table);
		}

		/***********************************************************
		* Возвращает результат строительства
		* ********************************************************/
		function getQueryText () {
			$result = "";

			$this->_filterFieldsH ();

			$result = "UPDATE " . $this->m_main_table . " ";
			if (!$this->m_fields_h && !$this->m_fields_strings)
				throw new RuntimeException ("Not set update fields in CUpdateSqlQuery");
			
			$set_fields_a = array ();
			// Проходимся по всем полям и формируем $set_fields_a
			foreach ($this->m_fields_h as $c_name => $c_value) {
				$fields_names_a[] = $c_name;
				if ($c_value === null)
					$set_fields_a[] = $c_name . "=NULL";
				else {
					$c_value = str_replace ("\\", "\\\\", $c_value);
					$c_value = str_replace ("'", "\'", $c_value);
					$c_value = "'" . $c_value . "'";
					$set_fields_a[] = $c_name . "=" . $c_value;
				}
			}
			foreach ($this->m_fields_strings as $c_str)
				$set_fields_a[] = $c_str;

			$set_fields_str = join (", ", $set_fields_a);

			$result .= " SET " . $set_fields_str;

			$conditions_str = $this->_getConditionsStr ();
			if (!$conditions_str)
				throw new RuntimeException ("Update query without conditions");
			$result .= $conditions_str;


			return $result;		
			
		}



	/***********************************************************
	* Приватные функции класса
	* ********************************************************/

		

	};
?>