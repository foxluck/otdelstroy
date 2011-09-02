<?php
/**
 * @author Gumenuk Aleksey
 * @internal managing modules
 * @package Modules
 */
class ModulesFabric{
	protected static $cache;

	function getConfigsInfo(){

		/* @var $dbHandler DataBase */
		/* @var $Result DBResource */
		$dbHandler = &Core::getdbHandler();
		$ConfigsInfo = array();
		$sql = '
			SELECT * FROM ?#TBL_MODULE_CONFIGS ORDER BY ModuleID, ConfigTitle ASC
		';
		$Result = $dbHandler->ph_query($sql);
		while ($_Row = $Result->fetchAssoc()) {

			if(!isset($ConfigsInfo[$_Row['ModuleID']]))$ConfigsInfo[$_Row['ModuleID']] = array();
			$ConfigsInfo[$_Row['ModuleID']][] = array(
			'id'=>$_Row['ModuleConfigID'],
			'module_id'=>$_Row['ModuleID'],
			'key'=>$_Row['ConfigKey'],
			'title'=>$_Row['ConfigTitle'],
			'description'=>$_Row['ConfigDescr'],
			'init_type'=>$_Row['ConfigInit'],
			'enabled'=>$_Row['ConfigEnabled'],
			);
		}
		return $ConfigsInfo;
	}

	function getModulesInfo($_ModDir = '',$recursive = false){
		$_ModDir = str_replace('//','/',$_ModDir);
		$Dir = dir(DIR_MODULES.'/'.$_ModDir);

		$ModulesInfo = array();

		while ($FileName = $Dir->read()){
			if(preg_match('/(^[\._]{1,})|(^scripts)|(^payment)|(^shipping)/',$FileName))continue;
			$module_info = array();
			$related_modules = array();
			$path = $_ModDir?"{$_ModDir}/{$FileName}":"{$FileName}";
			if($recursive&&is_dir(DIR_MODULES.'/'.$path)){
				if($related = self::getModulesInfo($path,$recursive)){
					$related_modules = array_merge($related_modules,$related);
				}else{
				}
			}
			if(file_exists(DIR_MODULES."/{$path}/connector.{$FileName}.xml")){
				$module_info = ModulesFabric::getModuleConnectorInfo(DIR_MODULES."/{$path}/connector.{$FileName}.xml");
			}
			if(count($related_modules)||count($module_info)){
				$module_info['related'] = count($related_modules)?$related_modules:array();
				$ModulesInfo[] = $module_info;
			}else{
			}
		}
		return $ModulesInfo;
	}

	function installDB(){

		$dbHandler = &Core::getdbHandler();
		$sql = '
			create table ?#TBL_MODULES (
				ModuleID INT unsigned not null auto_increment, ModuleVersion FLOAT not null,
				ModuleClassName varchar(30) not null, ModuleClassFile varchar(255) not null,
				primary key(ModuleID));
		';
		$dbHandler->ph_query($sql);
		$sql = '
			create table ?#TBL_MODULE_CONFIGS (
				ModuleConfigID INT unsigned not null auto_increment, ModuleID INT unsigned not null,
				ConfigInit SMALLINT(1) unsigned not null,
				ConfigKey VARCHAR(30) not null,
				ConfigDescr VARCHAR(100) not null,
				primary key(ModuleConfigID), key (ModuleID));
		';
		$dbHandler->ph_query($sql);
		$sql = '
			create table ?#TBL_CONFIG_SETTINGS (
				ModuleConfigID INT unsigned not null, SettingName varchar(30),
				SettingValue varchar(255), SettingType int unsigned not null,
				key (ModuleConfigID));
		';
		$dbHandler->ph_query($sql);
	}

	/**
	 * Getting module object by ConfigID at database
	 *
	 * @param int $_ConfigID
	 * @return Module
	 */
	static function getModuleObj($_ConfigID){
		$cache = Cache::getInstance(__CLASS__,Cache::FILE);
		$ModuleInfo = $cache->get($_ConfigID);
		if(!is_array($ModuleInfo)){
			$dbHandler = &Core::getdbHandler();
			$sql = '
				SELECT ?#TBL_MODULES.* FROM ?#TBL_MODULES
				LEFT JOIN ?#TBL_MODULE_CONFIGS ON ?#TBL_MODULES.ModuleID=?#TBL_MODULE_CONFIGS.ModuleID
				WHERE ?#TBL_MODULE_CONFIGS.ModuleConfigID=?
			';
			$Result = $dbHandler->ph_query($sql, $_ConfigID);
			if(!$Result->getNumRows())return null;
	
			$ModuleInfo = $Result->fetchAssoc();
			$cache->set($_ConfigID,$ModuleInfo);
		}
		if(!file_exists(DIR_MODULES.'/'.$ModuleInfo['ModuleClassFile']))return null;
		if(!class_exists($ModuleInfo['ModuleClassName'],false))require_once(DIR_MODULES.'/'.$ModuleInfo['ModuleClassFile']);
		if(!class_exists($ModuleInfo['ModuleClassName'],false)){
			if(class_exists('sc_'.$ModuleInfo['ModuleClassName'],false))$ModuleInfo['ModuleClassName'] = 'sc_'.$ModuleInfo['ModuleClassName'];
			else return  null;
		}
		$ModuleObj = new $ModuleInfo['ModuleClassName']($_ConfigID);
		return $ModuleObj;
	}

	/**
	 * Get module object by module config key
	 *
	 * @param string $_ConfigKey
	 * @return Module | null
	 */
	static function &getModuleObjByKey($_ConfigKey){

		$ConnectedModulesAssoc = &ModulesFabric::getConnectedModulesAssoc();

		if(isset($ConnectedModulesAssoc[$_ConfigKey]))return $ConnectedModulesAssoc[$_ConfigKey];

		$dbHandler = &Core::getdbHandler();
		$sql = '
			SELECT ?#TBL_MODULES.*,?#TBL_MODULE_CONFIGS.ModuleConfigID FROM ?#TBL_MODULES
			LEFT JOIN ?#TBL_MODULE_CONFIGS ON ?#TBL_MODULES.ModuleID=?#TBL_MODULE_CONFIGS.ModuleID
			WHERE ?#TBL_MODULE_CONFIGS.ConfigKey=?
		';
		$Result = $dbHandler->ph_query($sql, $_ConfigKey);
		if(!$Result->getNumRows()){
			$ModuleObj = null;
			return $ModuleObj;
		}

		$ModuleInfo = $Result->fetchAssoc();
		$ModuleInfo['ModuleClassFile'] = str_replace(array('///','//'),'/',$ModuleInfo['ModuleClassFile']);

		if(!file_exists(DIR_MODULES.'/'.$ModuleInfo['ModuleClassFile']))return null;
		if(!class_exists($ModuleInfo['ModuleClassName'],false))require_once(DIR_MODULES.'/'.$ModuleInfo['ModuleClassFile']);
		if(!class_exists($ModuleInfo['ModuleClassName'])){
			if(class_exists('sc_'.$ModuleInfo['ModuleClassName']))$ModuleInfo['ModuleClassName'] = 'sc_'.$ModuleInfo['ModuleClassName'];
			else return  null;
		}
		$ModuleObj = new $ModuleInfo['ModuleClassName']($ModuleInfo['ModuleConfigID']);

		$ConnectedModulesAssoc[$_ConfigKey] = &$ModuleObj;

		return $ModuleObj;
	}

	function getModuleConfigsNum($_ClassName){

		/* @var $dbHandler DataBase */
		$dbHandler = &Core::getdbHandler();

		$sql = '
			SELECT COUNT(*) as cnt FROM ?#TBL_MODULES
			LEFT JOIN ?#TBL_MODULE_CONFIGS ON ?#TBL_MODULES.ModuleID=?#TBL_MODULE_CONFIGS.ModuleID
			WHERE ?#TBL_MODULES.ModuleClassName=?
		';
		$Result = $dbHandler->ph_query($sql,$_ClassName);
		$Row =$Result->fetchAssoc();
		return $Row['cnt'];
	}

	static function &getModuleObjs($_InitType = null){

		$dbHandler = &Core::getdbHandler();
		$ModObjs = array();
		$sql = '
			SELECT * FROM ?#TBL_MODULES
			LEFT JOIN ?#TBL_MODULE_CONFIGS ON ?#TBL_MODULES.ModuleID=?#TBL_MODULE_CONFIGS.ModuleID
			'.(!is_null($_InitType)?'WHERE ?#TBL_MODULE_CONFIGS.ConfigInit=?':'').'
		';
		$Result = $dbHandler->ph_query($sql, $_InitType);
		while (	$ModuleInfo = $Result->fetchAssoc()){

			if(!file_exists(DIR_MODULES.'/'.$ModuleInfo['ModuleClassFile']))continue;
			if(!class_exists($ModuleInfo['ModuleClassName'],false))require_once(DIR_MODULES.'/'.$ModuleInfo['ModuleClassFile']);
			if(!class_exists($ModuleInfo['ModuleClassName']))continue;
			$ModObjs[$ModuleInfo['ConfigKey']] = new $ModuleInfo['ModuleClassName']($ModuleInfo['ModuleConfigID']);
		};
		return $ModObjs;
	}

	function getModuleConnectorInfo($_ConnectorPath){

		$ConnectorXML = new xmlNodeX();
		$ConnectorXML->renderTreeFromFile($_ConnectorPath);

		@list($xnClass) = $ConnectorXML->xPath('/Connector/Class');
		/*@var $xnClass xmlNodeX*/

		$ModuleInfo = array(
		'class_name' => $xnClass->getChildData('Name'),
		'class_file' => $xnClass->getChildData('File'),
		'description' => $xnClass->getChildData('Description'),
		'title' => $xnClass->getChildData('Title'),
		'single_installation' => $xnClass->getChildData('SingleInstallation'),
		'class_dir' => str_replace(array(DIR_MODULES,'//'),array('','/'),preg_replace('|connector\.[a-z_0-9]+\.xml$|i','',$_ConnectorPath))
		);

		return $ModuleInfo;
	}

	function getSettingHTML($_Setting){

		switch ($_Setting['type']){
			case SETTING_NUMBER:
				return '
				<input type="text" name="_SETTINGS['.xHtmlSpecialChars($_Setting['name']).']" value="'.xHtmlSpecialChars($_Setting['value']).'" />
			';
				break;
		}
	}

	function saveSetting($_Setting, $_ConfigID){

		switch ($_Setting['type']){
			case SETTING_NUMBER:

				/* @var $dbHandler DataBase */
				/* @var $Result DBResource */
				$dbHandler = &Core::getdbHandler();
				$sql = 'SELECT 1 FROM ?#TBL_CONFIG_SETTINGS WHERE ModuleConfigID=? AND SettingName=?';
				$Result = $dbHandler->ph_query($sql,$_ConfigID,$_Setting['name']);
				if(!$Result->getNumRows()){

					$sql = '
						INSERT ?#TBL_CONFIG_SETTINGS (SettingValue,ModuleConfigID,SettingName) VALUES(?,?,?)
					';
				}else {

					$sql = '
						UPDATE ?#TBL_CONFIG_SETTINGS SET SettingValue=? WHERE ModuleConfigID=? AND SettingName=?
					';
				}
				$dbHandler->ph_query($sql, isset($_POST['_SETTINGS'][$_Setting['name']])?$_POST['_SETTINGS'][$_Setting['name']]:'',$_ConfigID,$_Setting['name']);
				break;
		}
	}

	static function initGlobalModules(){
		class_exists('Module');
		$ConnectedModules = &ModulesFabric::getConnectedModules();
		$GlobalModules = &ModulesFabric::getModuleObjs(INIT_GLOBAL);
		foreach ($GlobalModules as $_Key=>$_Module){

			$ConnectedModules[$_Key] = &$GlobalModules[$_Key];
		}
	}

	/**
	 * @return &array connected modules objects
	 */
	static function &getConnectedModules(){

		static $ConnectedModules;
		if(!is_array($ConnectedModules))$ConnectedModules = array();
		return $ConnectedModules;
	}

	static function &getConnectedModulesAssoc(){

		static $ConnectedModulesAssoc;
		if(!is_array($ConnectedModulesAssoc))$ConnectedModulesAssoc = array();
		return $ConnectedModulesAssoc;
	}

	/**
	 * Call interface
	 *
	 * @param mixed $interface_params - it could be array of interface params
	 */
	static function callInterface($interface_params){

		$Args = func_get_args();
		array_shift($Args);


		$ConnectedModules = &ModulesFabric::getConnectedModules();

		if(isset($interface_params['module_config_id'])){
			$interface_params['ModConfigID'] = $interface_params['module_config_id'];
		}
		if(!isset($ConnectedModules[$interface_params['ModConfigID']])){
			$ConnectedModules[$interface_params['ModConfigID']] = ModulesFabric::getModuleObj($interface_params['ModConfigID']);
		}
		if(!isset($ConnectedModules[$interface_params['ModConfigID']])){
			return null;
		}
		$ConnectedModules[$interface_params['ModConfigID']]->getInterface($interface_params['key'], isset($Args[0])?$Args[0]:null);
	}

	static function callModuleInterface($modcfg_key, $interface_key){
		$moduleEntry = &ModulesFabric::getModuleObjByKey($modcfg_key);
		if(is_null($moduleEntry))return null;

		$params = func_get_args();
		array_shift($params);
		array_shift($params);

		$eval_params = array_merge(array($interface_key),$params);
		/*
		 for($i=2, $cnt=count($params); $i<=$cnt; $i++){

			$eval_params[] = $params[$i];
			}*/

		//PHP 4.10	$results = call_user_method_array('getInterface', $moduleEntry, $eval_params);
		$results = call_user_func_array(array(&$moduleEntry,'getInterface'), $eval_params);
		return $results;
	}
}
?>