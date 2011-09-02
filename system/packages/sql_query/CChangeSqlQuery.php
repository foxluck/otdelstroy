<?php
	/***********************************************************
	* ������ SQL-������ ������� ���-���� ��������
	* ********************************************************/

	class CChangeSqlQuery extends CSqlQuery {

	/***********************************************************
	* ��������� ������
	* ********************************************************/
		
	/***********************************************************
	* ����� ������
	* ********************************************************/
		
		var $m_fields_h;
		var $m_need_fields_a;


	/***********************************************************
	* ������� ����������
	* ********************************************************/

		/***********************************************************
		* �����������
		* ���������: 
		*	1) ��� �������� �������
		* ********************************************************/
		function CChangeSqlQuery ($f_type, $f_main_table) {
			$this->m_fields_h = array ();
			$this->m_need_fields_a = array ();
			$this->m_fields_strings = array ();
			parent::CSqlQuery ($f_type, $f_main_table);
		}


		/***********************************************************
		* ������ m_fields - ���� � �������
		* ********************************************************/
		function addFields ($f_fields_h, $f_need_fields_a = array ()) {
			if (is_array($f_fields_h)) {
				$this->m_fields_h = array_merge ($this->m_fields_h, $f_fields_h);
				$this->addNeedFields ($f_need_fields_a);
			}
			elseif (is_string($f_fields_h)) {
				$this->m_fields_strings[] = $f_fields_h;
			}			
		}


		/***********************************************************
		* ������ m_need_fields - ���� ��� �������� ���� ���������� ���������
		* ���� �� �������
		* ********************************************************/
		function addNeedFields ($f_need_fields_a) {
			if (!$f_need_fields_a) return;
			if (!is_array($f_need_fields_a)) {
				$f_need_fields_a = str_replace (",", ";", $f_need_fields_a);
				$f_need_fields_a = str_replace ("|", ";", $f_need_fields_a);
				$f_need_fields_a = split (";", $f_need_fields_a);
			}
			$this->m_need_fields_a = array_merge ($this->m_need_fields_a, $f_need_fields_a);
		}

		

	/***********************************************************
	* ��������� ������� ������
	* ********************************************************/

		/***********************************************************
		* ������� m_fields_h �� ��� ����� ������� �� ���������
		* ��� ������� (���� $f_need_fields_a ������ - ������� ���)
		* ********************************************************/
		function _filterFieldsH () {
			
			if (!$this->m_need_fields_a) return;
			foreach ($this->m_fields_h as $c_field => $c_value) {
				if (!in_array ($c_field, $this->m_need_fields_a) || ($c_field === 0)) {
					unset ($this->m_fields_h [$c_field]);
				}
			}
		}


		function hasFields () {
			if (!$this->m_need_fields_a) return false;
			$fields_h = $this->m_fields_h;
			foreach ($fields_h as $c_field => $c_value) {
				if (!in_array ($c_field, $this->m_need_fields_a) || ($c_field === 0)) 
					unset ($fields_h [$c_field]);
			}
			return sizeof ($fields_h);
		}

		

	};
?>