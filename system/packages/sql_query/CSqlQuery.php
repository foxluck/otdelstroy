<?php
	/***********************************************************
	* ������ SQL-������
	* ********************************************************/

	define ("CSQL_QUERY_CONDITION_NO_VALUE", "CSQL_QUERY_CONDITION_NO_VALUE");

	class CSqlQuery {

	/***********************************************************
	* ��������� ������
	* ********************************************************/
		
	/***********************************************************
	* ����� ������
	* ********************************************************/
		
		var $m_type;
		var $m_main_table;
		var $m_conditions_a;

		


	/***********************************************************
	* ������� ����������
	* ********************************************************/

		/***********************************************************
		* �����������
		* ���������: 
		*	1) ��� �������
		*	2) �������� ������� �������
		* ********************************************************/
		function CSqlQuery ($f_type, $f_main_table) {
			$this->m_type = $f_type;
			$this->m_main_table = $f_main_table;
		}
		
		function applyFilter($filter) {
			$conditions = $filter->getConditions();
			foreach ($conditions as $cCond) {
				$this->addConditions($cCond);
			}			
		}




		/***********************************************************
		* ��������� WHERE-�������
		* ********************************************************/
		function addConditions ($f_conditions, $f_value = CSQL_QUERY_CONDITION_NO_VALUE) {
			if ($f_value !== CSQL_QUERY_CONDITION_NO_VALUE) {
				$f_conditions = $f_conditions . "='" . $f_value . "'";
			} 
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
		* ���������� ��������� �������������
		* ********************************************************/
		function getQueryText () {
		}

		/***********************************************************
		* ���������� ��������� �������������
		* ********************************************************/
		function getQuery () {
			$sql = $this->getQueryText ();
			$sql = str_replace ("!", "\\!", $sql);
			$sql = str_replace ("?", "\\?", $sql);
			$sql = str_replace ("&", "\\&", $sql);
			return $sql;
		}

		

	/***********************************************************
	* ��������� ������� ������
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