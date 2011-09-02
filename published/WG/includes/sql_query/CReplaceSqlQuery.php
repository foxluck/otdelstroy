<?
	/***********************************************************
	* ������ SQL-������
	* ********************************************************/

	class CReplaceSqlQuery extends CChangeSqlQuery {

	/***********************************************************
	* ��������� ������
	* ********************************************************/
		
	/***********************************************************
	* ����� ������
	* ********************************************************/
		
	/***********************************************************
	* ������� ����������
	* ********************************************************/

		/***********************************************************
		* �����������
		* ���������: 
		*	1) ��� �������� �������
		* ********************************************************/
		function CReplaceSqlQuery ($f_main_table) {
			parent::CChangeSqlQuery ("replace", $f_main_table);
		}

		/***********************************************************
		* ���������� ��������� �������������
		* ********************************************************/
		function getQueryText () {
			$result = "";

			$this->_filterFieldsH ();

			$result = "REPLACE INTO " . $this->m_main_table . " ";
			if (!$this->m_fields_h) {
				trigger_error ("Not set replace fields in CReplaceSqlQuery", E_USER_WARNING);
				return;
			}

			$fields_names_a = array ();

			// ���������� �� ���� ����� � ��������� $fields_names_a � $fields_values_a
			foreach ($this->m_fields_h as $c_name => $c_value) {
				$fields_names_a[] = $c_name;
				if (is_numeric($c_value)) {
					$fields_values_a[] = $c_value;
				} else {
					$c_value = str_replace ("'", "\'", $c_value);
					$fields_values_a[] = "'" . $c_value . "'";
				}
			}

			$fields_names_str = join (", ", $fields_names_a);
			$fields_values_str = join (", ", $fields_values_a);

			$result .= "(" . $fields_names_str . ") VALUES (" . $fields_values_str . ") ";

			return $result;		
			
		}



	/***********************************************************
	* ��������� ������� ������
	* ********************************************************/

		

	};
?>