<?php
	ClassManager::includeClass('Language');

	class LanguagesManager{

		/**
		 * @var array
		 */
		var $languages;
		/**
		 * @var Language
		 */
		var $curLanguage;
		/**
		 * @var Language
		 */
		var $defLanguage;
		/**
		 * Multi lingual table fields info
		 *
		 * @var array
		 */
		var $ml_tables;

		/**
		 * @return LanguagesManager
		 */
		static function &getInstance(){
				static $languagesManager;
			if(!is_object($languagesManager)){

				$languagesManager = new LanguagesManager();
			}

			return $languagesManager;
		}

		function LanguagesManager(){

			$this->languages = &$this->getLanguages();

			foreach ($this->languages as $languageEntry){
				/*@var $languageEntry Language*/

				if($languageEntry->isDefault()){
					$this->defLanguage = $languageEntry;
					$this->curLanguage = $languageEntry;
					break;
				}
			}
			if(!$this->defLanguage&&count($this->languages))
			{
				$this->defLanguage = $this->languages[0];
				$this->curLanguage = $this->languages[0];
			}

			if(isset($_SESSION['current_language']))
			{
			    foreach($this->languages as $le)
			    {
			        if($le->id == $_SESSION['current_language'])
			        {
			            $this->curLanguage = $le;
			            break;
			        };
			    }; 
			}
			$_SESSION['current_language'] = $this->curLanguage->id;

			$this->ml_tables = array(
				PRODUCTS_TABLE => array(
					'name', 'brief_description', 'description','meta_title', 'meta_description', 'meta_keywords'
				),
				PRODUCT_OPTIONS_TABLE => array(
					'name',
				),
				PRODUCT_OPTIONS_VALUES_TABLE => array(
					'option_value'
				),
				PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE => array(
					'option_value'
				),
				CATEGORIES_TABLE => array(
					'name', 'description', 'meta_title', 'meta_description', 'meta_keywords'
				),
				SHIPPING_METHODS_TABLE => array(
					'Name', 'description', 'email_comments_text'
				),
				PAYMENT_TYPES_TABLE => array(
					'Name', 'description', 'email_comments_text'
				),
				ZONES_TABLE => array(
					'zone_name'
				),
				COUNTRIES_TABLE => array(
					'country_name'
				),
				AUX_PAGES_TABLE => array(
					'aux_page_name', 'aux_page_text', 'meta_keywords', 'meta_description'
				),
				CUSTOMER_REG_FIELDS_TABLE => array(
					'reg_field_name'
				),
				CUSTGROUPS_TABLE => array(
					'custgroup_name'
				),
				ORDER_STATUSES_TABLE => array(
					'status_name'
				),
				CURRENCY_TYPES_TABLE => array(
					'Name', 'display_template'
				),
				NEWS_TABLE => array(
					'title','textToPublication'
				),
			);
		}

		/**
		 * @return Language
		 */
		static function getLanguageInstance($language_id){

			$r_languages = &LanguagesManager::getLanguages();
			for($j=0, $j_max=count($r_languages);$j<$j_max;$j++){

				if($r_languages[$j]->id == $language_id)return $r_languages[$j];
			}
			return null;
		}

		function &getLanguages($enabled = null){

			static $r_languageEntry;
			if(is_array($r_languageEntry))return $r_languageEntry;

			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */

			$dbq = '
				SELECT * FROM ?#LANGUAGE_TABLE'.(is_null($enabled)?'':(' WHERE enabled='.intval($enabled))).' ORDER BY `priority` ASC, `name` ASC
			';
			$DBRes = $DBHandler->ph_query($dbq);
			$r_languageEntry = array();
			while ($row = $DBRes->fetchAssoc()){

				$Language = new Language();
				$Language->loadFromArray($row);
				$r_languageEntry[] = &$Language;
				unset($Language);
			}

			return $r_languageEntry;
		}

		/**
		 * @return Language |PEAR_Error
		 */
		static function &getDefaultLanguage(){

			$langManager = &LanguagesManager::getInstance();
			return $langManager->defLanguage;
		}

		/**
		 * @return Language | null
		 */
		static function getLanguageByISO2($iso2){

			$r_languages = &LanguagesManager::getLanguages();
			for($j=0, $j_max=count($r_languages);$j<$j_max;$j++){

				if($r_languages[$j]->iso2 == $iso2)return $r_languages[$j];
			}
			return null;
		}

		/**
		 * @return Language
		 */
		static function &getCurrentLanguage(){

			$langManager = &LanguagesManager::getInstance();
			return $langManager->curLanguage;
		}

		static function setCurrentLanguage($lang_id, $session = true){

			$langManager = &LanguagesManager::getInstance();
			foreach ($langManager->languages as $languageEntry){
				/*@var $languageEntry Language*/
				if($languageEntry->id != $lang_id)continue;
				if(!$languageEntry->enabled)continue;
				if($session)$_SESSION['current_language'] = $lang_id;
				$langManager->curLanguage = $languageEntry;
				break;
			}
		}

		static function getMaxLanguagePriority(){

			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */

			$dbq = '
				SELECT MAX(`priority`) FROM ?#LANGUAGE_TABLE
			';
			$DBRes = $DBHandler->ph_query($dbq);

			list($priority) = $DBRes->fetchRow();
			return $priority;
		}

		static function sql_prepareTableUpdate($table, &$values, $name_map = null){

			$langManager = &LanguagesManager::getInstance();
			$ml_fields = LanguagesManager::getMLTablesInfo($table);
			$dbq = '';

			foreach ($ml_fields as $ml_field){

				$dbq .= ', '.LanguagesManager::sql_prepareFieldUpdate($ml_field, $values, $name_map);
			}
			$dbq = substr($dbq, 1);

			return $dbq;
		}

		static function sql_prepareFieldUpdate($field_name, &$field_values, $name_map=null){

			$langManager = &LanguagesManager::getInstance();
			if(!is_array($field_values)){

				$defLanguageEntry = &LanguagesManager::getDefaultLanguage();
				$field_values = array(
					$field_name.'_'.$defLanguageEntry->iso2 => $field_values
				);
			}
			$languages = &$langManager->languages;
			$dbq = '';

			foreach ($languages as $languageEntry){
				/*@var $languageEntry Language*/
				$lng_field_name = $field_name.'_'.$languageEntry->iso2;
				$key = $lng_field_name;
				if(is_array($name_map)){

					$key = preg_replace(array_keys($name_map), array_values($name_map), $key);
				}

				$dbq .= ', `'.xEscapeSQLstring($lng_field_name).'`="'.xEscapeSQLstring(array_key_exists($key, $field_values)?$field_values[$key]:'').'"';
			}
			$dbq = substr($dbq, 1);
			return $dbq;
		}

		static function sql_constractSortField($table, $field, $use_full_name = false){

			if(preg_match('@\.([^\.]+)$@', $field, $sp)){

				$orig_field = $sp[1];
			}else{

				$orig_field = $field;
			}
			
			$field_prefix = $use_full_name?($table.'.'):'';

			if(LanguagesManager::ml_isMLField($table, $orig_field)){

				$langManager = &LanguagesManager::getInstance();
				if($langManager->curLanguage->id == $langManager->defLanguage->id){
					return "{$field_prefix}{$field}_{$langManager->defLanguage->iso2} AS ".LanguagesManager::sql_getSortField($table, $field);
				}else{
					return "IF(LENGTH({$field_prefix}{$field}_{$langManager->curLanguage->iso2}), {$field_prefix}{$field}_{$langManager->curLanguage->iso2}, {$field_prefix}{$field}_{$langManager->defLanguage->iso2}) AS ".LanguagesManager::sql_getSortField($table, $field);
				}
			}else{

				return $field_prefix.$field;
			}
		}

		static function sql_getSortField($table, $field){

			if(preg_match('@\.([^\.]+)$@', $field, $sp)){

				$orig_field = $sp[1];
			}else{

				$orig_field = $field;
			}
			if(LanguagesManager::ml_isMLField($table, $orig_field)){

				return "_{$orig_field}_sort";
			}else{

				return $orig_field;
			}
		}

		static function sql_prepareField($field, $do_as = false){

			$curLanguage = &LanguagesManager::getCurrentLanguage();
			$defLanguage = &LanguagesManager::getDefaultLanguage();

			if(preg_match('@([^\.]+)\.([^\.]+)@', $field, $sp)){
				$field = "{$sp[1]}`.`{$sp[2]}";
				$do_as = false;
			}

			if($curLanguage->id == $defLanguage->id) return "`{$field}_{$defLanguage->iso2}`".($do_as?" AS `{$field}`":'');

			return "IF(LENGTH(`{$field}_{$curLanguage->iso2}`), `{$field}_{$curLanguage->iso2}`, `{$field}_{$defLanguage->iso2}`)".($do_as?" AS `{$field}`":'');
		}

		/**
		 * Prepare sql injections for inserting ml field
		 *
		 * @param string $field
		 * @param array $values ($field_%lang%=>string)
		 * @return array (fields=>string, values=>string)
		 */
		static function sql_prepareFieldInsert($field, &$values, $needClearValues = false){

			$langManager = &LanguagesManager::getInstance();

			$languages = &$langManager->languages;
			$dbq_fields = '';
			$dbq_values = '';
			$clearValues = array ();
			$clearFields = array ();

			if(!is_array($values)){

				$defLanguageEntry = &LanguagesManager::getDefaultLanguage();
				$values = array( $field.'_'.$defLanguageEntry->iso2 => $values );
			}

			foreach ($languages as $languageEntry){
				/*@var $languageEntry Language*/
				$lng_field = $field.'_'.$languageEntry->iso2;
				$key = $lng_field;
				$dbq_fields .= ', `'.xEscapeSQLstring($lng_field).'`';
				$dbq_values .= ', "'.xEscapeSQLstring(array_key_exists($key, $values)?$values[$key]:'').'"';
				if ($needClearValues){
					$clearValues[] = xEscapeSQLstring(array_key_exists($key, $values)?$values[$key]:'');
					$clearFields[] = '`'.xEscapeSQLstring($lng_field).'`';
				}
			}
			$dbq_fields = substr($dbq_fields, 1);
			$dbq_values = substr($dbq_values, 1);
			return array('fields' => $dbq_fields, 'values'=> $dbq_values, "clear_values" => $clearValues, 'clear_fields'=> $clearFields);
		}

		static function sql_prepareFields($field, &$values){
			$langManager = &LanguagesManager::getInstance();
			$languages = &$langManager->languages;
			$res_fields = array ();
			$res_values = array ();
			$fields_list = '';

			if(!is_array($values)){

				$defLanguageEntry = &LanguagesManager::getDefaultLanguage();
				$values = array( $field.'_'.$defLanguageEntry->iso2 => $values );
			}
			foreach ($languages as $languageEntry){
				/*@var $languageEntry Language*/
				$lng_field = $field.'_'.$languageEntry->iso2;
				$key = $lng_field;
				$res_fields[] = '`'.xEscapeSQLstring($lng_field).'`';
				$fields_list.=',`'.xEscapeSQLstring($lng_field).'`';
				$res_values[] =array_key_exists($key, $values)?$values[$key]:'';
			}
			return array('fields'=>$res_fields,'values'=>$res_values,'fields_list'=>substr($fields_list,1));
		}

		static function getMLTablesInfo($table_name = null){

			$langManager = &LanguagesManager::getInstance();
			if(!is_null($table_name) && array_key_exists($table_name, $langManager->ml_tables)){
				return $langManager->ml_tables[$table_name];
			}

			return $langManager->ml_tables;
		}

		static function ml_isMLField($table, $field){

			$langManager = &LanguagesManager::getInstance();
			return isset($langManager->ml_tables[$table]) && in_array($field, $langManager->ml_tables[$table]);
		}

		static function ml_getMLFields($table){

			$langManager = &LanguagesManager::getInstance();
			return isset($langManager->ml_tables[$table])?$langManager->ml_tables[$table]:array();
		}

		static function ml_getFieldValue($field, $data){

			$langManager = &LanguagesManager::getInstance();
			$field_current = $field.'_'.$langManager->curLanguage->iso2;
			$field_default = $field.'_'.$langManager->defLanguage->iso2;
			return (isset($data[$field_current])&&$data[$field_current])?$data[$field_current]:$data[$field_default];
		}

		static function ml_fillFields($table, &$data){

			$ml_fields = LanguagesManager::ml_getMLFields($table);
			foreach ($ml_fields as $ml_field){

				$data[$ml_field] = LanguagesManager::ml_getFieldValue($ml_field, $data);
			}
			return $data;
		}

		/**
		 * Return true if all languages values is empty
		 *
		 * @param string $field
		 * @param array $data : ($field_%lang%=>string)
		 * @return bool
		 */
		static function ml_isEmpty($field, &$data){

			$langManager = &LanguagesManager::getInstance();
			$languages = &$langManager->getLanguages();
			foreach ($languages as $languageEntry){
				/*@var $languageEntry Language*/
				$field_name = $field.'_'.$languageEntry->iso2;
				if(isset($data[$field_name]) && strlen($data[$field_name])){
					return false;
				}
			}

			return true;
		}

		/**
		 * @param string $field
		 * @return array ($field_%lang%=>string)
		 */
		static function ml_getLangFieldNames($field){

			$field_names = array();
			$langManager = LanguagesManager::getInstance();

			foreach ($langManager->languages as $langEntry){
				/*@var $langEntry Language*/
				$field_names[] = "{$field}_{$langEntry->iso2}";
			}

			return $field_names;
		}

		/**
		 * @param string - field name
		 * @param Language
		 * @return string
		 */
		static function ml_getLangFieldName($field, $languageEntry = null){

			if(is_null($languageEntry))$languageEntry = &LanguagesManager::getDefaultLanguage();
			return "{$field}_{$languageEntry->iso2}";
		}

	}
?>