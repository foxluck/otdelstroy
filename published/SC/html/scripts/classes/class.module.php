<?php
/**
 * define types of interfaces
 */
define('INTCALLER', 100);
define('INTDIVAVAILABLE', 101);
define('INTHIDDEN', 102);
define('INTCOMPONENT', 104);
define('SETTING_NUMBER', 1001);
define('SETTING_CUSTOM', 1002);
define('INIT_GLOBAL', 1001);
define('INIT_LOCAL', 1002);
/**
 * @package Modules
 */
class Module{
	var $Interfaces = array();
	var $Settings = array();
	var $Version = 1.00;
	var $ConfigID = 0;
	var $ID = 0;
	var $SingleInstallation = false;
	var $GenerateConstants = false;
	var $ConfigKey = '';
	var $ConfigTitle = '';
	var $ConfigDescr = '';
	var $InitType = '';
	/*@var $dbHandler DataBase */
	var $dbHandler;
	var $ModuleDir='';
	
	var $__instarface_stack = array();
	
	/**
	 * @var Cache
	 */
	protected static $cache = null;

	function __clearInterfaceStack(){

		$this->__instarface_stack = array();
	}

	function __pushToStack($key, $data){

		$this->__instarface_stack[$key] = $data;
	}

	function __getFromStack($key){

		return $this->__instarface_stack[$key];
	}

	function __construct( $_ConfigID = 0 ){
		$this_class_name = get_class($this);
		$this->dbHandler = &Core::getdbHandler();
		$cache_file_name = sprintf('%s/.cache.modules',DIR_TEMP);
		if(!self::$cache){
			self::$cache = Cache::getInstance(__CLASS__,Cache::FILE);
		}

		$this->ConfigID = $_ConfigID;
		
		if($module_info = self::$cache->get($this->ConfigID.'_module')){
			list($this->ID,
				$this->ConfigKey,
				$this->ConfigTitle,
				$this->ConfigDescr,
				$this->InitType,
				$this->ModuleDir) = $module_info;
		}else{
		
			$sql = '
				SELECT ModuleID, ConfigKey, ConfigTitle, ConfigDescr, ConfigInit FROM ?#TBL_MODULE_CONFIGS
				WHERE ModuleConfigID=?
			';
			$Result = $this->dbHandler->ph_query($sql, $this->ConfigID);
			if(!$Result->getNumRows()){
					
				$sql = '
					SELECT ModuleID FROM ?#TBL_MODULES WHERE 
					ModuleVersion=? AND ModuleClassName=?
				';
					
				$Result = $this->dbHandler->ph_query($sql, $this->Version, $this_class_name);
				if($Result->getNumRows()){
					list($this->ID) = $Result->fetchRow();
				}elseif($this_class_name == 'sc_Abstract'){
	
					$this_class_name = 'Abstract';
				}
			}else {
					
				list($this->ID, $this->ConfigKey, $this->ConfigTitle, $this->ConfigDescr, $this->InitType) = $Result->fetchRow();
			}
			$sql = '
				SELECT ModuleClassFile FROM ?#TBL_MODULES WHERE LOWER(ModuleClassName)=?
			';
			$Result = $this->dbHandler->ph_query($sql, strtolower($this_class_name));
			if(!$Result->getNumRows() && $this_class_name == 'sc_Abstract'){
					
				$this_class_name = 'Abstract';
				$Result = $this->dbHandler->ph_query($sql, strtolower($this_class_name));
			}
			list($this->ModuleDir) = $Result->fetchRow();
			$this->ModuleDir = dirname($this->ModuleDir);
			
			self::$cache->set($this->ConfigID.'_module', array(
				$this->ID,
				$this->ConfigKey,
				$this->ConfigTitle,
				$this->ConfigDescr,
				$this->InitType,
				$this->ModuleDir,
			));
		}

		$this->initInterfaces();

		foreach ($this->Interfaces as $_Key=>$_Val){
				
			$this->Interfaces[$_Key]['key'] = $_Key;
			if(!isset($this->Interfaces[$_Key]['type']))$this->Interfaces[$_Key]['type'] = INTDIVAVAILABLE;
		}
		$this->initSettings();
	}

	/*
	 * abstract methods
	 */

	function initInterfaces()
	{
		;
	}

	function __registerInterface($key, $name, $type = INTCALLER, $method = ''){

		$this->Interfaces[$key] = array(
		'name' => $name,
		'type' => $type,
		'method' => $method,
		);
	}

	function __prepend_interface($interface_key, &$params){

	}

	function initSettings(){
		
		$this->Settings = self::$cache->get($this->ConfigID.'settings');
		if(!is_array($this->Settings)){
			$this->Settings = array();
			$sql = '
				SELECT SettingName, SettingValue FROM ?#TBL_CONFIG_SETTINGS
				WHERE ModuleConfigID=?
			';
			$Result = $this->dbHandler->ph_query($sql, $this->getConfigID());
			while ($_Row = $Result->fetchAssoc()){
					
				$this->Settings[$_Row['SettingName']]['value'] = $_Row['SettingValue'];
			}
			self::$cache->set($this->ConfigID.'settings',$this->Settings);
		}
	}

	function callFromInstallConfig(){;}

	function callFromUninstallConfig(){;}
	/*
	 * general methods
	 */

	function installConfig( $FilePath, $_Key,$_Title, $_Descr, $_InitType ){

		if($this->InitType)$_InitType = $this->InitType;
		$ModuleID = 0;
		/* @var $Result DBResource */

		if(!$this->ID){
				
			$sql = '
				INSERT INTO ?#TBL_MODULES
				(ModuleVersion, ModuleClassName, ModuleClassFile)
				VALUES(?,?,?)
			';
				
			$this->dbHandler->ph_query($sql, $this->Version, get_class($this), $FilePath);
			$this->ID = $this->dbHandler->getInsertedID();
		}

		$sql = '
			INSERT INTO ?#TBL_MODULE_CONFIGS
			(ModuleID, ConfigInit, ConfigKey, ConfigTitle, ConfigDescr)
			VALUES(?,?,?,?,?)
		';
		$this->dbHandler->ph_query($sql, $this->ID, $_InitType, $_Key, $_Title, $_Descr);

		$this->ConfigID = $this->dbHandler->getInsertedID();

		foreach ($this->Settings as $_Setting){
				
			$sql = '
				INSERT INTO ?#TBL_CONFIG_SETTINGS
				(ModuleConfigID, SettingName, SettingValue, SettingType)
				VALUES(?,?,?,?)
			';
			$this->dbHandler->ph_query($sql, $this->ConfigID, $_Setting['name'], $_Setting['value'], $_Setting['type']);
		}

		$this->callFromInstallConfig();
	}

	function uninstallConfig(){

		$this->callFromUninstallConfig();

		$sql = 'DELETE FROM ?#TBL_MODULE_CONFIGS WHERE ModuleConfigID=?';
		$this->dbHandler->ph_query($sql, $this->ConfigID);

		$sql = 'DELETE FROM ?#TBL_CONFIG_SETTINGS WHERE ModuleConfigID=?';
		$this->dbHandler->ph_query($sql, $this->ConfigID);

		DivisionModule::disconnectInterfaces(array($this->getConfigID()=>array_keys($this->getInterfacesParams())));
	}

	function getInstalledConfigsInfo(){

		/* @var $Result DBResource */
		/* @var $dbHandler DataBase */
		$ConfigsInfo = array();

		$sql = 'SELECT * FROM ?#TBL_MODULE_CONFIGS WHERE ModuleID=?';
		$Result = $this->dbHandler->ph_query($sql, $this->ID);
		while ($_Row = $Result->fetchAssoc()) {
			$ConfigsInfo[] = $_Row;
		}
		return $ConfigsInfo;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $interface_key
	 * @return mixed
	 */
	function getInterface(){
		global $ConnectedModules;
		/**
		 * @features My
		 */
		$Register = &Register::getInstance();
		$GetVars = &$Register->get(VAR_GET);
		$debug_mode = isset($GetVars['debug_interfaces']);
		/**
		 * @features
		 */
		$Args = func_get_args();
		$_InterfaceName = array_shift($Args);
		$Results = '';

		$this->__prepend_interface($_InterfaceName, $Args);
		if($debug_mode){
			
			$memoryUsed = function_exists('memory_get_usage')?memory_get_usage():0;
			$timeUsed = microtime(true);
		}

		if(isset($this->Interfaces[$_InterfaceName])){
				
			$EvalParams = $Args;
			$InterfaceParams = $this->getInterfaceParams($_InterfaceName);
			if(isset($InterfaceParams['type'])&& ($InterfaceParams['type']&INTCALLER)){
				$IInterfaces = $this->getInterfaceInterfaces($_InterfaceName);
				if($IInterfaces&&isset($IInterfaces['main'])&&is_array($IInterfaces['main'])){
					foreach ($IInterfaces['main'] as $IInterface){
						if(isset($ConnectedModules[$IInterface['module_config_id']])){
							$tModule = &$ConnectedModules[$IInterface['module_config_id']];
						}else{
							$tModule = ModulesFabric::getModuleObj($IInterface['module_config_id']);
						}

						if(!is_object($tModule))continue;

						if(isset($IInterface['key'])){
							$EvalParams = array_merge($EvalParams,is_array($IInterface['key'])?$IInterface['key']:array($IInterface['key']));
						}
						call_user_func_array(array(&$tModule,'getInterface'),$EvalParams);
					}
				}
			}

			/**
			 * @features My
			 */
			if($debug_mode){
				print "<p><strong>Class:</strong> ".get_class($this);
			}
			/**
			 * @features
			 */

			$this->__clearInterfaceStack();
			$this->__pushToStack('info', $this->Interfaces[$_InterfaceName]);
			$this->__pushToStack('call_params', $Args);
			if(isset($this->Interfaces[$_InterfaceName]['method']) && $this->Interfaces[$_InterfaceName]['method']){

				/**
				 * @features My
				 */
				if($debug_mode){
					print "<br /><strong>Method:</strong> ".$this->Interfaces[$_InterfaceName]['method'].'</p>';
				}
				/**
				 * @features
				 */
				array_shift($EvalParams);
				//PHP 4.10		$Results = call_user_method_array($this->Interfaces[$_InterfaceName]['method'],$this,$Args);
				$Results = call_user_func_array(array(&$this,$this->Interfaces[$_InterfaceName]['method']),$Args);

			}else {

				/**
				 * @features My
				 */
				$method_path = DIR_MODULES.'/'.$this->ModuleDir.'/'.'_methods/'.$_InterfaceName.'.php';

				if($debug_mode){
					print "<br /><strong>File:</strong> {$method_path}</p>";
					$path_count = count(get_included_files());
					print "<br /><strong>Files count:</strong> {$path_count}</p>";
					if(function_exists('memory_get_usage')){
						$method_memory = memory_get_usage();
						print "<br/><strong>Memory:</strong> {$method_memory} </p>";
					}
						
				}
				/**
				 * @features
				 */
				//or use include_once ?
				include($method_path);
			}
		}else {
				
			/**
			 * @features My
			 */
			if($debug_mode){
				print "<br /><strong>File:</strong> ".DIR_MODULES.'/'.$this->ModuleDir.'/'.'_methods/'.$_InterfaceName.'.php'.'</p>';
				$path_count = count(get_included_files());
				print "<br /><strong>Files count:</strong> {$path_count}</p>";
				if(function_exists('memory_get_usage')){
					$method_memory = memory_get_usage();
					print "<br/><strong>Memory:</strong> {$method_memory} </p>";
				}
			}
			/**
			 * @features
			 */
			if(file_exists(DIR_MODULES.'/'.$this->ModuleDir.'/'.'_methods/'.$_InterfaceName.'.php'))
			include(DIR_MODULES.'/'.$this->ModuleDir.'/'.'_methods/'.$_InterfaceName.'.php');
		}
		if($debug_mode){
			$currentMemoryUsed = function_exists('memory_get_usage')?memory_get_usage():0;
			$currentTimeUsed = microtime(true);
			print "<p><strong>Memory:</strong> ".sprintf('Total: %2.2fMb Function: %2.2fKb',$currentMemoryUsed/1048576,($currentMemoryUsed-$memoryUsed)/1024);
			print "<p><strong>Time:</strong> ".sprintf('Function: %2.3fus',($currentTimeUsed-$timeUsed)*1000);
			print "<br /><strong>Method executed:</strong> {$this->Interfaces[$_InterfaceName]['method']} <br>args:{$Args}/";
			print_r($Args);
			print "<br>Result:{$Results} /";print_r($Results);print "</p>";
		}
		return $Results;
	}

	function getInterfacesParams($_Type = 0){

		if(!$_Type)return $this->Interfaces;

		$Interfaces = array();
		foreach ($this->Interfaces as $_Key=>$_Interface){
				
			if($_Interface['type']&$_Type)$Interfaces[$_Key] = $_Interface;
		}
		return $Interfaces;
	}

	function getInterfaceParams($_int){

		if(isset($this->Interfaces[$_int]))return $this->Interfaces[$_int];
		return null;
	}

	function getSettings(){

		return $this->Settings;
	}

	function getSettingValue($_Key){

		if(isset($this->Settings[$_Key]))return $this->Settings[$_Key]['value'];
		else return '';
	}

	function getConfigID(){

		return $this->ConfigID;
	}

	function getConfigKey(){

		return $this->ConfigKey;
	}

	function getConfigTitle(){

		return $this->ConfigTitle;
	}

	function getConfigDescr(){

		return $this->ConfigDescr;
	}

	function getInitType(){

		return $this->InitType;
	}

	function saveConfigKey( $_ConfigKey){

		$this->ConfigKey = $_ConfigKey;

		$sql = '
			UPDATE ?#TBL_MODULE_CONFIGS
			SET ConfigKey=?
			WHERE ModuleConfigID=?
		';
		$this->dbHandler->ph_query($sql, $this->ConfigKey, $this->ConfigID);
	}

	function saveConfigDescr( $_ConfigDescr){

		$this->ConfigDescr = $_ConfigDescr;

		$sql = '
			UPDATE ?#TBL_MODULE_CONFIGS
			SET ConfigDescr=?
			WHERE ModuleConfigID=?
		';
		$this->dbHandler->ph_query($sql, $this->ConfigDescr, $this->ConfigID);
	}

	function saveInitType( $_InitType ){

		$this->InitType = $_InitType;

		$sql = '
			UPDATE ?#TBL_MODULE_CONFIGS
			SET ConfigInit=?
			WHERE ModuleConfigID=?
		';
		$this->dbHandler->ph_query($sql, $this->InitType, $this->ConfigID);
	}

	/**
	 * register another module interface for current module interface
	 *
	 * @param string $_InterfaceCaller
	 * @param integer $_InterfaceCalledModConfID - another module config id
	 * @param string $_InterfaceCalled
	 * @param integer $_Priority
	 */
	function registerInterface2Interface($_InterfaceCaller, $_InterfaceCalledModConfID, $_InterfaceCalled, $_Priority=0){

		$sql = '
			INSERT ?#TBL_INTERFACE_INTERFACES (xInterfaceCaller,xInterfaceCalled,xPriority)
			VALUES(?,?,?)
		';
		$this->dbHandler->ph_query($sql, $this->getConfigID().'_'.$_InterfaceCaller, $_InterfaceCalledModConfID.'_'.$_InterfaceCalled, $_Priority);
	}

	function getInterfaceInterfaces($_Interface){

		$cache_key_part = 'interfaces_interfaces_15112007::';
		$cache_key = $cache_key_part.$this->getConfigID().'_'.$_Interface;
		if($result = self::$cache->get($cache_key_part))
		if(self::$cache->get($cache_key_part)){
			return self::$cache->get($cache_key,array('main'=>array()));
		}
		$sql = '
			SELECT xInterfaceCaller,xInterfaceCalled FROM ?#TBL_INTERFACE_INTERFACES ORDER BY xPriority DESC
		';
		$Result = $this->dbHandler->ph_query($sql);
		$Interfaces = array();
		while ($_Row = $Result->fetchAssoc()) {
				
			$_T = explode('_', $_Row['xInterfaceCalled'], 2);
			if(!isset($_T[1]))continue;

			$Interfaces[$_Row['xInterfaceCaller']]['main'][] = array(
			'module_config_id' => $_T[0],
			'key' => $_T[1],
			);
		}

		foreach ($Interfaces as $_caller => $_interfaces){

			self::$cache->set($cache_key_part.$_caller, $_interfaces);
		}
		self::$cache->set($cache_key_part, true);

		return self::$cache->get($cache_key,array('main'=>array()));
	}

	function getTemplatePath($_Tpl){

		return DIR_TPLS.'/'.$_Tpl;
	}

	function assignSubTemplate($_SubTemplate){

		/* @var $smarty Smarty */
		$smarty = &Core::getSmarty();
		$smarty->assign('sub_template',$this->getTemplatePath($_SubTemplate));
	}

	function assign2template($_Var, $_Value){

		/* @var $smarty Smarty */
		$smarty = &Core::getSmarty();
		$smarty->assign($_Var, $_Value);
	}
}
?>