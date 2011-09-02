<?
	/***********************************************************
	* Строит SQL-запрос
	* ********************************************************/

	define ("CSQL_QUERY_CONDITION_NO_VALUE", "CSQL_QUERY_CONDITION_NO_VALUE");

	class CSqlQuery {

	/***********************************************************
	* Константы класса
	* ********************************************************/
		
	/***********************************************************
	* Члены класса
	* ********************************************************/
		
		var $m_type;
		var $m_main_table;
		var $m_conditions_a;

		


	/***********************************************************
	* Функции интерфейса
	* ********************************************************/

		/***********************************************************
		* Конструктор
		* Параметры: 
		*	1) тип запроса
		*	2) основная таблица запроса
		* ********************************************************/
		function CSqlQuery ($f_type, $f_main_table) {
			$this->m_type = $f_type;
			$this->m_main_table = $f_main_table;
		}




		/***********************************************************
		* Добавляет WHERE-условия
		* ********************************************************/
		function addConditions ($f_conditions, $f_value = CSQL_QUERY_CONDITION_NO_VALUE) {
			if ($f_value != CSQL_QUERY_CONDITION_NO_VALUE)
				$f_conditions = $f_conditions . "='" . $f_value . "'";
			$this->m_conditions_a[] = "(" . $f_conditions . ")";
		}
		
		function addInCondition ($fieldName, $array) {
			if (empty($array)) {
				return $this->addConditions ("FALSE");
			}
			$inValues = join(",", array_map ("_QuoteArray", $array));
			return $this->addConditions ($fieldName . " IN ("  . $inValues . ")");
		}
		
		function SetFilter ($filter) {
			$conditions = $filter->GetConditions ();
			foreach ($conditions as $c_condition) {
				$this->addConditions ($c_condition);
			}
			
		}


		
		/***********************************************************
		* Возвращает результат строительства
		* ********************************************************/
		function getQueryText () {
		}

		/***********************************************************
		* Возвращает результат строительства
		* ********************************************************/
		function getQuery () {
			$sql = $this->getQueryText ();
			$sql = str_replace ("!", "\\!", $sql);
			$sql = str_replace ("?", "\\?", $sql);
			$sql = str_replace ("&", "\\&", $sql);
			return $sql;
		}

		

	/***********************************************************
	* Приватные функции класса
	* ********************************************************/

		function _getConditionsStr () {
			if ($this->m_conditions_a)
				return " WHERE " . join (" AND ", $this->m_conditions_a) . " ";
			else
				return "";
		}
	};
	
	function _QuoteArray ($item) {
		return "'" . $item . "'";
	}
?>