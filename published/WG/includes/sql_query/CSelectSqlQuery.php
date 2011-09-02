<?
	/***********************************************************
	* Строит SQL-запрос
	* ********************************************************/

	class CSelectSqlQuery extends CSqlQuery {

	/***********************************************************
	* Константы класса
	* ********************************************************/
		
	/***********************************************************
	* Члены класса
	* ********************************************************/
		
		var $m_select_fields;
		var $m_start;
		var $m_count;
		var $m_order_by;
		var $m_group_by;

		var $m_main_table_alias;
		var $m_joined_tables_ha;
		


	/***********************************************************
	* Функции интерфейса
	* ********************************************************/

		/***********************************************************
		* Конструктор
		* Параметры: 
		*	1) имя основной таблицы
		* ********************************************************/
		function CSelectSqlQuery ($f_main_table, $f_main_table_alias = "") {
			$this->m_select_fields = "*";
			$this->m_main_table_alias = $f_main_table_alias;
			$this->m_joined_tables_ha = array ();
			parent::CSqlQuery ("select", $f_main_table);
		}


		/***********************************************************
		* Ставит select_fields (поля которые надо выбрать
		* ********************************************************/
		function setSelectFields ($f_select_fields) {
			if (is_array($f_select_fields)) {
				$this->m_select_fields = join (", ", $f_select_fields);				
			} else {
				$this->m_select_fields = $f_select_fields;
			}
		}

		
		/***********************************************************
		* Устанавливает LIMIT
		* Если $f_count = 0 - LIMIT не действует
		* ********************************************************/
		function setLimit ($f_start, $f_count) {
			$this->m_start = $f_start;
			$this->m_count = $f_count;
		}

		/***********************************************************
		* Устанавливает LIMIT через page
		* Если $f_count = 0 - LIMIT не действует
		* ********************************************************/
		function setPage ($f_page, $f_items_on_page) {
			$this->setLimit ($f_page * $f_items_on_page, $f_items_on_page);
		}


		/***********************************************************
		* Устанавливает GROUP_BY
		* Если $f_group_by = '' - GROUP_BY не действует
		* ********************************************************/
		function setGroupBy ($f_group_by, $f_default_group_by = "") {
			$f_group_by = trim ($f_group_by);
			$this->m_group_by = ($f_group_by) ? $f_group_by : $f_default_group_by;
		}

		/***********************************************************
		* Устанавливает ORDER_BY
		* Если $f_order_by = '' - ORDER_BY не действует
		* ********************************************************/
		function setOrderBy ($f_order_by, $f_default_order_by = "") {
			$f_order_by = trim ($f_order_by);
			$this->m_order_by = ($f_order_by) ? $f_order_by : $f_default_order_by;
		}

		/***********************************************************
		* Join'ит таблицу слева
		* ********************************************************/
		function leftJoin ($f_table_name, $f_alias, $f_on) {
			$this->_addJoinTable ("LEFT", $f_table_name, $f_alias, $f_on);
		}

		/***********************************************************
		* Join'ит таблицу inner
		* ********************************************************/
		function innerJoin ($f_table_name, $f_alias, $f_on) {
			$this->_addJoinTable ("INNER", $f_table_name, $f_alias, $f_on);
		}





		
		/***********************************************************
		* Возвращает результат строительства
		* ********************************************************/
		function getQueryText () {
			$result = "";

			$result = "SELECT " . $this->m_select_fields;
			$result .= " FROM " . $this->_getFromText ();
			$result .= $this->_getConditionsStr ();

			if (trim($this->m_group_by)) {
				$result .= " GROUP BY " . $this->m_group_by;
			}

			if (trim($this->m_order_by)) {
				$result .= " ORDER BY " . $this->m_order_by;
			}

			if (intval($this->m_count)) {
				$limit_str = intval($this->m_start) . ", " . intval($this->m_count);
				$result .= " LIMIT " . $limit_str ;
			}

			return $result;		
		}

		


	/***********************************************************
	* Приватные функции класса
	* ********************************************************/
		
		function _getFromText () {
			$result = "";

			$tables = array ();
			$tables_aliases = array ();
			$tables_join = array ();

			if (!$this->m_main_table) {
				trigger_error ("Не указано имя основной таблицы", E_USER_WARNING);
				return "";
			}
			
			$result = $this->m_main_table;
			if ($this->m_main_table_alias) 
				$result .= " AS " . $this->m_main_table_alias;

			foreach ($this->m_joined_tables_ha as $c_table_h) {
				if (!$c_table_h["name"] || !$c_table_h["on"] || !$c_table_h["join_type"]) {
					trigger_error ("Не указано имя таблицы, join type или значение JOIN ON в объединении", E_USER_WARNING);
					return "";
				}
				$c_table_val = $c_table_h["name"];
				if ($c_table_h["alias"]) $c_table_val .= " AS " . $c_table_h["alias"];
				$result = " (" . $result . ") " . $c_table_h["join_type"] . " JOIN " . $c_table_val . " ON " .  $c_table_h["on"];
			}

			return $result;
		}

		/***********************************************************
		* Возвращает запрос без лимита и сортировки 
		* Limit'а. Если указано поле - считается по нему
		* ********************************************************/
		function getTotalQuery ($f_field = "") {
			if (!$f_field) {
				$result = "SELECT COUNT(*) as rows_count";
			} else {
				$result = "SELECT COUNT($f_field) AS rows_count";
			}			
			$result .= " FROM " . $this->_getFromText ();
			$result .= $this->_getConditionsStr ();
			return $result;
		}
		
		


		
		/***********************************************************
		* Добавляет JOIN-таблицу
		* ********************************************************/
		function _addJoinTable ($f_join_type, $f_table_name, $f_table_alias, $f_on) {
			if (!$f_table_name || !$f_on) {
				trigger_error ("Не указано имя таблицы которую JOIN'им или её ON-параметры");
			}
			$table_h["join_type"] = $f_join_type;
			$table_h["name"] = $f_table_name;
			$table_h["alias"] = $f_table_alias;
			$table_h["on"] = $f_on;
			$this->m_joined_tables_ha[] = $table_h;
		}
		

		

	};
?>