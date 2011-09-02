<?php
/**
 * Parent for all modules
 *
 */

define('SHIPPING_RATE_MODULE', 1);
define('PAYMENT_MODULE', 2);
define('SMSMAIL_MODULE', 3);

define('MODULE_LOG_CURL', 1);
define('MODULE_LOG_FEDEX', 2);

define('LOGTYPE_ERROR',1);
define('LOGTYPE_MSG',2);
define('LOGTYPE_DEBUG',4);

define('LOGMODE_ERROR',1);
define('LOGMODE_DEBUG',7);
define('LOGMODE_MSG',2);
define('LOGMODE_NONE',0);

@ini_set('max_execution_time', 0);

class virtualModule{
	
	var $id;
	var $title;
	var $description;
	var $sort_order;
	var $ModuleType;
	var $ModuleConfigID;
	var $MethodsTable;
	var $DebugMode;
	var $ModuleVersion = 1;

	var $Settings = array();
	var $SettingsFields = array();
	var $LanguageDir;
	var $TemplatesDir;
	var $SingleInstall = false;
	var $LogFile;
	var $log_mode = LOGMODE_NONE;

	/**
	 * Constructor for modules
	 *
	 * @param integer $_ModuleConfigID - if more then zero work with given config id
	 * @return virtualModule
	 */
	function virtualModule($_ModuleConfigID = 0){
		
		return $this->load($_ModuleConfigID);
	}
	
	function load($_ModuleConfigID = 0){
		
		$this->Settings = array();
		$this->SettingsFields = array();
		$this->LogFile = DIR_TEMP.'/ss.'.date('Y.m.d').'.log';
		
		$this->_initDebugMode();
		
		$this->_connectLanguageFile();
		
		$this->_initVars();
		
		$this->ModuleConfigID = $_ModuleConfigID;
		
		if($_ModuleConfigID && !$this->SingleInstall){
			
//			$this->title .= ' ('.$_ModuleConfigID.')';
			$_TC = count($this->Settings)-1;
			for ( ;$_TC>=0; $_TC-- ){
				
				$this->Settings[$_TC] .= '_'.$_ModuleConfigID;
			}
		}
		
		$this->update();
	}
	
	function update(){
		
		$this->_initSettingFields();
		
		if(!$this->ModuleConfigID)return 0;
		
		foreach ($this->Settings as $_SettingName){
			
			if(defined($_SettingName)) continue;
			$orName = preg_replace('/\_[0-9]*$/','', $_SettingName);
			$sql = "
				INSERT INTO ".SETTINGS_TABLE."
				(
					settings_groupID, settings_constant_name, 
					settings_value, 
					settings_title, 
					settings_description, 
					settings_html_function, 
					sort_order
				)
				VALUES (
					".settingGetFreeGroupId().", '".$_SettingName."',
					'".(isset($this->SettingsFields[$orName]['settings_value'])?$this->SettingsFields[$orName]['settings_value']:'')."',
					'".(isset($this->SettingsFields[$orName]['settings_title'])?$this->SettingsFields[$orName]['settings_title']:'')."',
					'".(isset($this->SettingsFields[$orName]['settings_description'])?$this->SettingsFields[$orName]['settings_description']:'')."',
					'".(isset($this->SettingsFields[$orName]['settings_html_function'])?$this->SettingsFields[$orName]['settings_html_function']:'')."',
					'".(isset($this->SettingsFields[$orName]['sort_order'])?$this->SettingsFields[$orName]['sort_order']:'')."'
				)";
			db_query($sql)	or die (db_error());
		}
	}
	
	function getModuleConfigID(){
		
		return $this->ModuleConfigID;
	}
		
	/**
	 * Returns module settings list
	 *
	 * @return array
	 */
	function settings_list(){
		
		return $this->Settings;
	}
	
	/**
	 * Return module id 
	 *
	 * @return integer
	 */
	function get_id(){
		
		if($this->ModuleConfigID)return $this->ModuleConfigID;
		
		$sql = '
			SELECT module_id FROM ?#MODULES_TABLE
			WHERE module_name=? 
		';
		$q = db_phquery($sql, $this->title);
		$row = db_fetch_row($q);
		return (int)$row["module_id"];
	}
	
	/**
	 * returns TRUE if module is installed (if number of settings in the database equals number of settings from settings_list()), and FALSE if not
	 *
	 * @return bool
	 */
	function is_installed(){
		
		$sql = '
			SELECT COUNT(*) FROM ?#SETTINGS_TABLE
			WHERE settings_constant_name IN (?@)
		';
		$q = db_phquery($sql, $this->settings_list());
		list($cnt) = db_fetch_row($q);
		
		return ($cnt != 0 );
	}

	/**
	 * Uninstall module
	 *
	 * @param int $_ConfigID: if zero will taken from $this->ModuleConfigID
	 */
	function uninstall($_ConfigID = 0){
		
		$_ConfigID = (int)$_ConfigID?(int)$_ConfigID:$this->ModuleConfigID;
		
		$constants = "'".implode(($_ConfigID&&!$this->SingleInstall?'_'.$_ConfigID:'')."', '",$this->settings_list()).($_ConfigID&&!$this->SingleInstall?'_'.$_ConfigID:'')."'";

		if($this->MethodsTable){
			
			$sql = '
				UPDATE '.$this->MethodsTable.'
				SET module_id=NULL WHERE module_id=?
			';
			db_phquery( $sql, $_ConfigID );
		}

		$sql = '
			DELETE FROM ?#SETTINGS_TABLE
			WHERE settings_constant_name IN ('.$constants.')
		';
		db_phquery( $sql );
		
		$sql = '
			DELETE FROM ?#MODULES_TABLE
			WHERE module_id=?
		';
		db_phquery($sql, $_ConfigID);
	}

	/**
	 * Install module
	 * Should be redefined
	 * In redefinition before call to parent method should be init SettingsFields
	 *
	 */
	function install(){
		
		if($this->SingleInstall && $this->is_installed()){
			
			return false;
		}
		
		$sql = '
			INSERT INTO ?#MODULES_TABLE (module_name,ModuleClassName) 
			VALUES(?,?)
		';
		db_phquery($sql, $this->title, get_class($this));
		
		$NewModuleConfigID = db_insert_id();
		
		$this->ModuleConfigID = $NewModuleConfigID;
		
		$sql = '
			UPDATE ?#MODULES_TABLE
			SET module_name=?	WHERE module_id=?
		';
		db_phquery($sql,$this->title, $NewModuleConfigID);

		$this->_initSettingFields();
		
		foreach ($this->Settings as $_SettingName){
			
			$sql = '
				INSERT INTO ?#SETTINGS_TABLE
				(
					settings_groupID, settings_constant_name, 
					settings_value, settings_title, 
					settings_description, settings_html_function, 
					sort_order
				)
				VALUES (?,?,?,?,?,?,?)
				';
			
			db_phquery($sql,settingGetFreeGroupId(), $_SettingName.($this->SingleInstall?'':'_'.$NewModuleConfigID),
				(isset($this->SettingsFields[$_SettingName]['settings_value'])?$this->SettingsFields[$_SettingName]['settings_value']:''),
				(isset($this->SettingsFields[$_SettingName]['settings_title'])?$this->SettingsFields[$_SettingName]['settings_title']:''),
				(isset($this->SettingsFields[$_SettingName]['settings_description'])?$this->SettingsFields[$_SettingName]['settings_description']:''),
				(isset($this->SettingsFields[$_SettingName]['settings_html_function'])?$this->SettingsFields[$_SettingName]['settings_html_function']:''),
				(isset($this->SettingsFields[$_SettingName]['sort_order'])?$this->SettingsFields[$_SettingName]['sort_order']:'')
				);
		}
	}

	/**
	 * Return value for setting constant
	 *
	 * @param string $_SettingName - setting constant name
	 * @return unknown
	 */
	function _getSettingValue($_SettingName){
		
		$const_real_name = $_SettingName.(($this->ModuleConfigID&&!$this->SingleInstall)?'_'.$this->ModuleConfigID:'');
		return defined($const_real_name)?constant($const_real_name):'';
	}

	function _getSettingRealName($_SettingName){
		
		return $_SettingName.(($this->ModuleConfigID&&!$this->SingleInstall)?'_'.$this->ModuleConfigID:'');
	}
	
	/**
	 * Check defined constant
	 *
	 */
	function _defined($_SettingName){
	
		return defined($_SettingName.(($this->ModuleConfigID&&!$this->SingleInstall)?'_'.$this->ModuleConfigID:''));
	}
	/**
	 * Return module type. Such as PAYMENT_MODULE,SHIPPING_RATE_MODULE
	 *
	 * @return integer
	 */
	function getModuleType(){
		
		return $this->ModuleType;
	}

	/**
	 * Connect language file for module
	 *
	 */
	function _connectLanguageFile(){
		
		global $lang_list;
		$language = isset($lang_list[$_SESSION["current_language"]])?$lang_list[$_SESSION["current_language"]]->iso2:DEF_LANG_ID;
		$LanguageFile = $this->LanguageDir.$language.'.'.strtolower(get_class($this)).'.php';

		if(file_exists($LanguageFile)){
			require_once($LanguageFile);
		}else{
			$LanguageFile = $this->LanguageDir.DEF_LANG_ID.'.'.strtolower(get_class($this)).'.php';
			if(file_exists($LanguageFile)){
				require_once($LanguageFile);
			}
		}
	}

	/**
	 * Convert from one currency type to another type
	 * @param float $_Value - currency value
	 * @param mixed $_FromType - could be currency ID or currency ISO3
	 * @param mixed $_ToType
	 */
	function _convertCurrency($_Value, $_FromType, $_ToType){
	
		if(!intval($_FromType)){
		
			if(strlen($_FromType)==3){
			
				$FromCurrency = currGetCurrencyByISO3($_FromType);
			}else{
			
				$FromCurrency = array('currency_value'=>1);
			}
		}else{

			$FromCurrency = currGetCurrencyByID($_FromType);
		}
		
		if(!intval($_ToType)){
		
			if(strlen($_ToType)==3){
			
				$ToCurrency = currGetCurrencyByISO3($_ToType);
			}else{
			
				$ToCurrency = array('currency_value'=>1);
			}
		}else{

			$ToCurrency = currGetCurrencyByID($_ToType);
		}

		return (($_Value/$FromCurrency['currency_value'])*$ToCurrency['currency_value']);
	}

	/**
	 * For redifinition in child classes. Called in constructor
	 *
	 */
	function _initVars(){
		
		if(!strlen($this->title))$this->title = translate('mdls_'.strtolower(get_class($this)).'_title', false);
		if(!strlen($this->description))$this->description = translate('mdls_'.strtolower(get_class($this)).'_description', false);
		if(!$this->method_title)$this->method_title = translate('mdlc_'.strtolower(get_class($this)).'_title', false);
		if(!$this->method_description )$this->method_description = translate('mdlc_'.strtolower(get_class($this)).'_description', false);
		
	}

	/**
	 * For redifinition in child classes. Called in function 'install'
	 *
	 */
	function _initSettingFields(){
		;
	}
	
	function getTitle(){
		
		return $this->title;
	}

	function _writeLogMessage($_LogType, $_Message){
		
		switch ($_LogType){
			case MODULE_LOG_CURL:
				$this->LogFile = DIR_TEMP.'/curl_msg.log';
				break;
			case MODULE_LOG_FEDEX:
				$this->LogFile = DIR_TEMP.'/fedex_msg.log';
				break;
		}
		if($this->LogFile){
			
			$fp = fopen($this->LogFile, 'a');
			fwrite($fp, "\r\n".date("Y-m-d H:i:s ")."\r\n".$_Message."\r\n");
			fclose($fp);
		}
	}

	function _initDebugMode(){
		
		global $DebugMode;
		$this->DebugMode = $DebugMode;
	}
	
	function debugMessage($_Title, $_Msg){
		
		if($this->DebugMode){
			
			print '<br /><b>'.$_Title.'</b><br />'.$_Msg;
		}
	}
	
	/**
	 * Log message
	 *
	 * @param int $msg_type: LOGTYPE_ERROR, LOGTYPE_MSG, LOGTYPE_DEBUG
	 * @param string $message
	 */
	function _log($msg_type, $message){
		
		if($this->log_mode&$msg_type && $msg_type){
			
			$this->_writeLogMessage('', $message);
		}
	}
}
?>