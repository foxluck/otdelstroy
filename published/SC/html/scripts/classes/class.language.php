<?php
	define('LOCALTYPE_HIDDEN', 'hidden');
	define('LOCALTYPE_FRONTEND', 'front');
	define('LOCALTYPE_BACKEND', 'back');
	define('LOCALTYPE_GENERAL', 'general');

	class Language extends Object{

		var $id = '';
		var $iso2 = '';
		var $name = '';
		var $enabled = '';
		var $priority = '';
		var $thumbnail = '';
		var $direction = '';

		function setName($name){

			$this->name = $name;
		}

		function setISO2($iso2){

			$this->iso2 = substr($iso2,0,2);
		}

		function enabled($enabled = null){

			if(!is_null($enabled)){

				$this->enabled = $enabled;
			}
			return $this->enabled;
		}

		function setPriority($priority){

			$this->priority = $priority;
		}

		function getPriority(){

			return $this->priority;
		}

		function setThumbnail($thumbnail){

			$this->thumbnail = $thumbnail;
		}

		function getName(){

			return $this->name;
		}

		function getThumbnail(){

			return $this->thumbnail;
		}

		function getThumbnailURL(){

			return file_exists(DIR_FLAGS.'/'.$this->getThumbnail())&&$this->getThumbnail()?(URL_FLAGS.'/'.$this->getThumbnail()):'';
		}

		/**
		 *
		 * @param int $id
		 * @return PEAR_Error | null
		 */
		function loadById($id){

			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */

			$dbq = '
				SELECT * FROM ?#LANGUAGE_TABLE WHERE id=?
			';
			$DBRes = $DBHandler->ph_query($dbq, $id);
			if(!$DBRes->getNumRows())return PEAR::raiseError('error_nolang');

			$this->loadFromArray($DBRes->fetchAssoc());
		}

		function save(){

			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */

			ClassManager::includeClass('LanguagesManager');

			$is_new = !($this->id>0);
			$update_mlfields = false;

			/**
			 * Check for iso2 is unique
			 */
			$DBRes = $DBHandler->ph_query("SELECT 1 FROM ?#LANGUAGE_TABLE WHERE `iso2`=? AND `id`<>?", $this->iso2, $this->id);
			if($DBRes->getNumRows())return PEAR::raiseError('loc_iso2_reserved');

			if(!$is_new){

				$origLanguageEntry = new Language();
				$origLanguageEntry->loadById($this->id);
				$update_mlfields = $origLanguageEntry->iso2 != $this->iso2;

				$dbq = '
					UPDATE ?#LANGUAGE_TABLE SET `name`=?name, `thumbnail`=?thumbnail, `enabled`=?enabled, `priority`=?priority, `iso2`=?iso2, `direction`=?direction 
					WHERE `id`=?id
				';
			}else{
				$dbq = '
					INSERT ?#LANGUAGE_TABLE (`name`,`thumbnail`, `enabled`, `priority`, `iso2`, `direction`)
					VALUES(?name, ?thumbnail, ?enabled, ?priority, ?iso2, ?direction)
				';
			}

			if($this->priority<=0){

				$this->priority = 1+LanguagesManager::getMaxLanguagePriority();
			}

			$DBRes = $DBHandler->ph_query($dbq, $this->getVars());
			if($is_new){

				$this->id = $DBRes->getInsertID();
				$ml_tables_info = LanguagesManager::getMLTablesInfo();

				if(count($ml_tables_info)){

					$dbstructure = new xmlNodeX();
					$dbstructure->renderTreeFromFile(DIR_CFG.'/database_structure.xml');
				}

				foreach ($ml_tables_info as $table_name=>$table_fields){

					if(defined('DBTABLE_PREFIX'))$table_name = str_replace(DBTABLE_PREFIX, 'SS_', $table_name);
					if(!count($table_fields))continue;
					$xpath = 'DataBaseStructure/tables/table[@name="'.$table_name.'"]/column[@ML=1]';
					$r_xnColumn = $dbstructure->xPath($xpath);
					$r_columns = array();
					foreach ($r_xnColumn as $xnColumn){
						/*@var $xnColumn xmlNodeX*/
						$r_columns[$xnColumn->getData()] = $xnColumn;
					}

					foreach ($table_fields as $i=>$field){

						$xmlColumn = new XmlNode();
						$xmlColumn->SetXmlNodeAttributes($r_columns[$field]->getAttributes());
						$xmlColumn->SetXmlNodeData($field.'_'.$this->iso2);
						$table_fields[$i] = GetColumnSQL($xmlColumn, false);
					}

					if(defined('DBTABLE_PREFIX'))$table_name = str_replace('SS_', DBTABLE_PREFIX, $table_name);
					$dbq = '
						ALTER TABLE `'.$table_name.'` ADD '.implode(', ADD ', $table_fields).'
	  				';
					$DBHandler->ph_query($dbq);
				}
			}

			if($update_mlfields){

				$ml_tables_info = LanguagesManager::getMLTablesInfo();

				if(count($ml_tables_info)){

					$dbstructure = new xmlNodeX();
					$dbstructure->renderTreeFromFile(DIR_CFG.'/database_structure.xml');
				}

				foreach ($ml_tables_info as $table_name=>$table_fields){

					if(!count($table_fields))continue;
					$xpath = 'DataBaseStructure/tables/table[@name="'.(defined('DBTABLE_PREFIX')?str_replace(DBTABLE_PREFIX, 'SS_', $table_name):$table_name).'"]/column[@ML=1]';
					$r_xnColumn = $dbstructure->xPath($xpath);
					$r_columns = array();
					foreach ($r_xnColumn as $xnColumn){
						/*@var $xnColumn xmlNodeX*/
						$r_columns[$xnColumn->getData()] = $xnColumn;
					}
					foreach ($table_fields as $i=>$field){

						$xmlColumn = new XmlNode();
						$xmlColumn->SetXmlNodeAttributes($r_columns[$field]->getAttributes());
						$xmlColumn->SetXmlNodeData($field.'_'.$this->iso2);
						$field_definition = GetColumnSQL($xmlColumn, false);
						$dbq = '
							ALTER TABLE `'.$table_name.'` CHANGE '.($field.'_'.$origLanguageEntry->iso2).' '.$field_definition.'
		  				';
						$DBHandler->ph_query($dbq);
					}
				}
			}
		}

		/**
		 * Return array of language locals
		 *
		 * @param string $type: LOCALTYPE_*
		 * @param bool $full_info
		 * @param bool $group_by_subgroups
		 * @return PEAR_Error | array
		 */
		function getLocals($group, $full_info, $group_by_subgroups){

			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */

			$cache_key = is_array($group)?implode('__',$group):$group.'::'.intval($full_info).'::'.intval($group_by_subgroups);

			if($this->_findCache($cache_key)){
				return $this->_getCache($cache_key);
			}

			$dbq = '
				SELECT `id`, `value`'.($full_info?', `group`, `subgroup`':'').' FROM ?#LOCAL_TABLE WHERE lang_id=? AND `group` IN (?@)
				ORDER BY `id` ASC
			';
			$DBRes = $DBHandler->ph_query($dbq, $this->id, is_array($group)?$group:array($group));
			if (PEAR::isError($DBRes))return $DBRes;

			$locals = array();
			while ($row = $DBRes->fetchAssoc()){

				if($full_info && $group_by_subgroups){

					$locals[$row['subgroup']][$row['id']] = $full_info?$row:$row['value'];
			}else{

					$locals[$row['id']] = $full_info?$row:$row['value'];
				}
			}

			$this->_makeCache($cache_key, $locals);
			return $locals;
		}

		/**
		 *
		 * @param string $local_key
		 * @param string $local_value
		 * @return PEAR_Error|null
		 */
		function updateLocal($local_key, $local_value){

			$this->_dropCache();
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */

			if(db_phquery_fetch(DBRFETCH_FIRST, 'SELECT 1 FROM ?#LOCAL_TABLE WHERE `id`=? AND `lang_id`=?', $local_key, $this->id)){

				$dbq = '
					UPDATE ?#LOCAL_TABLE SET `value`=? WHERE `id`=? AND `lang_id`=?
				';
				$DBRes = $DBHandler->ph_query($dbq, $local_value, $local_key, $this->id);
				if(PEAR::isError($DBRes))throwMessage($DBRes);
			}else{

				$DefLanguageEntry = &ClassManager::getInstance('Language');
				/*@var $DefLanguageEntry Language*/
				$res = $DefLanguageEntry->loadById(CONF_DEFAULT_LANG);
				if(PEAR::isError($res))throwMessage($res);

				$def_local = $DefLanguageEntry->getLocal($local_key);
				if(PEAR::isError($def_local))throwMessage($def_local);
				return $this->addLocal($local_key, $local_value, $def_local['group'], $def_local['subgroup']);
			}
		}

		function addLocal($local_key, $local_value, $local_group, $local_subgroup){

			$this->_dropCache();
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */

			$dbq = '
				INSERT ?#LOCAL_TABLE (`id`, `lang_id`, `value`, `group`, `subgroup`)
				VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)
			';
			$DBRes = $DBHandler->ph_query($dbq, $local_key, $this->id, $local_value, $local_group, $local_subgroup);
			if(PEAR::isError($DBRes))return $DBRes;
		}

		/**
		 * @return array
		 */
		function getLocal($local_key){

			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */

			$dbq = '
				SELECT * FROM ?#LOCAL_TABLE WHERE `id`=? AND `lang_id`=?
			';
			$DBRes = $DBHandler->ph_query($dbq, $local_key, $this->id);
			if(PEAR::isError($DBRes))return $DBRes;

			return $DBRes->fetchAssoc();
		}

		function deleteLocal($local_key){

			$this->_dropCache();
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */

			$dbq = '
				DELETE FROM ?#LOCAL_TABLE WHERE `id`=?
			';
			return $DBHandler->ph_query($dbq, $local_key);
		}

		function delete(){

			$this->_dropCache();
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */

			$dbq = '
				DELETE FROM ?#LOCAL_TABLE WHERE lang_id=?
			';
			$DBHandler->ph_query($dbq, $this->id);

			$dbq = '
				DELETE FROM ?#LANGUAGE_TABLE WHERE id=?
			';
			$DBHandler->ph_query($dbq, $this->id);

			$ml_tables_info = LanguagesManager::getMLTablesInfo();
			foreach ($ml_tables_info as $table_name=>$table_fields){

				if(!count($table_fields))continue;
				foreach ($table_fields as $i=>$field){

					$table_fields[$i] = $field.'_'.$this->iso2;
				}
				$dbq = '
					ALTER TABLE `'.$table_name.'` DROP `'.implode('`, DROP `', $table_fields).'`
  				';
				$DBHandler->ph_query($dbq);
			}
		}

		function isDefault(){
			return $this->id == CONF_DEFAULT_LANG;
		}

		/**
		 * Cache
		 */

		function _getCacheDir(){

			return DIR_TEMP.'/loc_cache';
		}

		function _getCachePath($cache_key){

			return $this->_getCacheDir().'/serlang'.$this->id.'_'.md5($cache_key).'.cch';
		}

		function _findCache($cache_key){

			if(!file_exists($this->_getCachePath($cache_key)))return false;

			return file_exists($this->_getCachePath($cache_key));
		}

		function _getCache($cache_key){

			return unserialize(file_get_contents($this->_getCachePath($cache_key)));
		}

		function _makeCache($cache_key, $data){

			checkPath($this->_getCacheDir());

			$fp = fopen($this->_getCachePath($cache_key), 'w');

			fwrite($fp, serialize($data));

			fclose($fp);
		}

		function _dumpArray($data){

			if(!is_array($data))return "'".str_replace("'", "\'", str_replace('\\', '\\\\', $data))."',";

			$_dump = '';
			foreach ($data as $k=>$v){

				$_dump .= "'".str_replace("'", "\'", str_replace('\\', '\\\\', $k))."' => ".
				(is_array($v)?'array('.$this->_dumpArray($v).'),':"'".str_replace("'", "\'", str_replace('\\', '\\\\', $v))."',");
			}

			return $_dump;
		}

		function _dropCache(){

			if(file_exists($this->_getCacheDir())){
				delete_file($this->_getCacheDir());
			}
			Theme::cleanUpCache();
			return true;
		}
	}
?>