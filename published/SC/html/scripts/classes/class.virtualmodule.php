<?php
define('VIRTUAL_MODULE', 0);
define('SHIPPING_RATE_MODULE', 1);
define('PAYMENT_MODULE', 2);
define('SMSMAIL_MODULE', 3);
define('FORM_MODULE', 4);

define('MODULE_LOG_CURL', 1);
define('MODULE_LOG_FEDEX', 2);

define('LOGTYPE_ERROR',1);
define('LOGTYPE_MSG',2);
define('LOGTYPE_DEBUG',4);

define('LOGMODE_ERROR',1);
define('LOGMODE_DEBUG',7);
define('LOGMODE_MSG',3);
define('LOGMODE_NONE',0);

@ini_set('max_execution_time', 0);
/**
 * Parent for all modules
 * @package DynamicModules
 */
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
	var $className;

	/**
	 * Constructor for modules
	 *
	 * @param integer $_ModuleConfigID - if more then zero work with given config id
	 * @return virtualModule
	 */

	function __construct($_ModuleConfigID = 0)
	{
		return $this->load($_ModuleConfigID);
	}

	static function getClassName( $fileName )
	{
		$strContent = file_get_contents($fileName);
		$_match = array();
		$strContent = substr($strContent, strpos($strContent, '@connect_module_class_name'), 100);
		if(preg_match("|\@connect_module_class_name[\t ]+([0-9a-z_]*)|i", $strContent, $_match)){

			return $_match[1];
		}else {

			return false;
		}
	}

	static function listModules()
	{
		//view index file
		//scan directory
		//get class instances and properties
		//update index file
		//return properties array
	}

	static function getModuleDirectory($_ModuleType)
	{
		$IncludeDir = '';
		switch ($_ModuleType){
			case SHIPPING_RATE_MODULE:
				$IncludeDir = DIR_MODULES."/shipping";
				break;
			case PAYMENT_MODULE:
				$IncludeDir = DIR_MODULES."/payment";
				break;
			case SMSMAIL_MODULE:
				$IncludeDir = DIR_MODULES.'/ordering/smsmail';
				break;
			case FORM_MODULE:
				$IncludeDir = DIR_MODULES.'/printforms';
				break;
		}
		return $IncludeDir;
	}

	static function &getModulesInDirectory($moduleFiles, $exludeClasses = array())
	{
		$modules	= array();
		foreach( $moduleFiles as $fileName ){

			$className	= self::GetClassName( $fileName );
			if(!$className) continue;
			if(in_array($className,$exludeClasses))continue;
			if(!class_exists($className,false))include_once($fileName);
			if(!class_exists($className,false))continue;
			$objectModule = new $className();
			/*@var $objectModule virtualModule*/
			$objectModule->className = get_class($objectModule);
			$modules[] = $objectModule;
		}
		return $modules;
	}

	static function &getClassInstance($class = null,$_ModuleConfigID = 0,$IncludeDir = null)
	{
		$objectModule = null;
		$class = strtolower($class);
		if($class){

			$class_path = $IncludeDir.'/class.'.$class.'.php';
			if(!class_exists($class,false)&&file_exists($class_path)){
				include_once($class_path);
			}
			if(!class_exists($class,false)){
				$moduleFiles = GetFilesInDirectory( $IncludeDir, "php" );
				foreach( $moduleFiles as $fileName )
				{
					$file_class = self::getClassName($fileName );
					if($class	== strtolower($file_class)){
						$class = $file_class;
						require_once($fileName);
						break;
					}
				}
			}
			if(class_exists($class,false)){
				$objectModule = new $class($_ModuleConfigID);
				/*@var $objectModule virtualModule*/
				$objectModule->className = $class;
			}
		}
		return $objectModule;
	}

	static function &getInstance($_ModuleConfigID, $_ModuleType = 0)
	{
		$ModuleConfig = modGetModuleConfig($_ModuleConfigID);
		$objectModule = null;
		if(!$_ModuleConfigID) return $objectModule;

		if ($ModuleConfig['ModuleClassName']) {
			$class_path = self::getModuleDirectory($_ModuleType);
			$objectModule = self::getClassInstance($ModuleConfig['ModuleClassName'],$_ModuleConfigID,$class_path);
			if($objectModule&&$_ModuleType && $objectModule->getModuleType()!=$_ModuleType){
				$objectModule = null;
			}elseif($objectModule&&($objectModule->getModuleType()!=$ModuleConfig['module_type'])){
				$sql = 'UPDATE `?#MODULES_TABLE` SET `module_type`=? WHERE `module_id`=?';
				db_phquery($sql,$objectModule->getModuleType(),$_ModuleConfigID);
			}
		}else {//deprecated case (used when module class is missed)
			$moduleFiles = array();
			$IncludeDir = self::getModuleDirectory($_ModuleType);
			$moduleFiles = GetFilesInDirectory( $IncludeDir, "php" );
			foreach( $moduleFiles as $fileName )
			{
				if(!$className = self::getClassName($fileName )) continue;
				if(!class_exists($className,false))require_once($fileName);
				$objectModule = new $className($_ModuleConfigID);

				if ( $objectModule->get_id() == $_ModuleConfigID
				&& $objectModule->title==$ModuleConfig['module_name']){
					break;
				}else{
					$objectModule = null;
				}
			}
		}
		return $objectModule;
	}

	function load($_ModuleConfigID = 0)
	{

		$this->Settings = array();
		$this->SettingsFields = array();
		$this->LogFile = DIR_LOG.'/SC.'.get_class($this).'.'.date('Y.m.d').'.log';

		$this->_initDebugMode();
		$this->_connectLanguageFile();
		$this->_initVars();
		$this->ModuleConfigID = $_ModuleConfigID;
		if($_ModuleConfigID && !$this->SingleInstall){
			//		$this->title .= ' ('.$_ModuleConfigID.')';
			foreach($this->Settings as &$settings){
				$settings .= '_'.$_ModuleConfigID;
			}
		}

		$this->update();
	}

	function update()
	{
		$this->_initSettingFields();
		//$this->Settings = array_keys($this->SettingsFields);
		if(!$this->ModuleConfigID)return 0;

		foreach ($this->Settings as $_SettingName){

			if(defined($_SettingName)) continue;
			$orName = preg_replace('/\_[0-9]*$/','', $_SettingName);
			$sql = "
				INSERT INTO ".SETTINGS_TABLE."
				(
					settings_groupID, settings_constant_name, 
					settings_value,	settings_title,	settings_description, 
					settings_html_function, sort_order
				)
				VALUES (?,?,
					?,?,?,
					?,?)";
			$settings_groupID =settingGetFreeGroupId();
			$settings_constant_name =$_SettingName; 
			$settings_value = isset($this->SettingsFields[$orName]['settings_value'])?$this->SettingsFields[$orName]['settings_value']:'';
			$settings_title = isset($this->SettingsFields[$orName]['settings_title'])?$this->SettingsFields[$orName]['settings_title']:'';
			$settings_description = isset($this->SettingsFields[$orName]['settings_description'])?$this->SettingsFields[$orName]['settings_description']:'';
			$settings_html_function = isset($this->SettingsFields[$orName]['settings_html_function'])?$this->SettingsFields[$orName]['settings_html_function']:'';
			$sort_order = isset($this->SettingsFields[$orName]['sort_order'])?$this->SettingsFields[$orName]['sort_order']:'';
			db_phquery($sql,	$settings_groupID,$settings_constant_name,
						$settings_value, $settings_title, $settings_description,
						$settings_html_function, $sort_order)	or die (db_error());
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
		if(!count($this->settings_list())){
			return false;
		}
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

		$constants = "'".implode("', '",$this->settings_list()).($_ConfigID&&!$this->SingleInstall?'_'.$_ConfigID:'')."'";

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
			INSERT INTO ?#MODULES_TABLE (module_type,module_name,ModuleClassName) 
			VALUES(?,?,?)
		';
		db_phquery($sql,$this->ModuleType, $this->title, get_class($this));

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

		//OLD localization, based on define string constants via php file
		static $connectedFiles = array();
		if($language = LanguagesManager::getCurrentLanguage()){
			$iso2 = $language->iso2;
		}else{
			$iso2 = DEF_LANG_ID;
		}
		//$language = isset($lang_list[$_SESSION["current_language"]])?$lang_list[$_SESSION["current_language"]]->iso2:DEF_LANG_ID;
		$LanguageFile = $this->LanguageDir.$iso2.'.'.strtolower(get_class($this)).'.php';

		if(!file_exists($LanguageFile)){
			$LanguageFile = $this->LanguageDir.DEF_LANG_ID.'.'.strtolower(get_class($this)).'.php';
		}
		if(file_exists($LanguageFile)&&!in_array($LanguageFile,$connectedFiles)){
			require_once($LanguageFile);
			$connectedFiles[] = $LanguageFile;
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

		if(!strlen($this->title)){
			$this->title = translate('mdls_'.strtolower(get_class($this)).'_title', false);
		}
		if(!strlen($this->description)){
			$this->description = translate('mdls_'.strtolower(get_class($this)).'_description', false);
		}
		if(!$this->method_title){
			$this->method_title = translate('mdlc_'.strtolower(get_class($this)).'_title', false);
		}
		if(!$this->method_description ){
			$this->method_description = translate('mdlc_'.strtolower(get_class($this)).'_description', false);
		}

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
				$this->LogFile = DIR_LOG.'/curl_msg.log';
				break;
			case MODULE_LOG_FEDEX:
				$this->LogFile = DIR_LOG.'/fedex_msg.log';
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

/**
 * Base class for dynamic connected modules like payment modules, shipping modules,
 * print forms modules and etc..
 * @todo set it parent for shipping and payment modules
 * @package DynamicModules
 */
class VirtualModule2
{
	/**
	 * path to modules files
	 *
	 * @var string
	 */
	static $path = false;
	/**
	 * module type
	 *
	 * @var integer
	 */
	static $type = VIRTUAL_MODULE;
	/**
	 * aviable class names of modules
	 *
	 * @var array
	 */
	static $classes = false;

	public $title = '';
	public $description = '';
	public $version = '2.0';

	/**
	 * allow multiple installation for module
	 *
	 * @var bool
	 */
	protected $single_install = true;
	/**
	 * module id at database
	 *
	 * @var int
	 */
	protected $id = 0;
	protected $Settings = array();
	/**
	 * module settings
	 * @example array('setting_name'=>'value')
	 *
	 * @var array(mixed)
	 */
	protected $SettingsFields = array();

	static function listModules($list_installed = true)
	{
		self::$classes = array();
		//all
		$module_files = GetFilesInDirectory( self::$path, 'php','class\..+');
		foreach($module_files as $module_file){
			if(preg_match('|/class\.([\w]+)\.php|',$module_file,$matches)){
				$class_name = $matches[1];
				self::$classes[$class_name] = array_merge(
					array('class'=>$class_name,
					'name'=>$class_name,
					'description'=>'',
					'installed'=>0,
					'installed_id'=>array()),
					self::getModuleProperties($module_file)					
				);
			}
		}
		//installed
		if($list_installed){
			$dbq = 'SELECT * FROM ?#MODULES_TABLE WHERE module_type=?';
			$installed_modules = db_phquery_fetch(DBRFETCH_ASSOC_ALL,$dbq,self::$type);
			foreach($installed_modules as $installed_module){
				$id = $installed_module['module_id'];
				$class_name = strtolower($installed_module['ModuleClassName']);
				if(isset(self::$classes[$class_name])){
					self::$classes[$class_name]['installed']++;
					self::$classes[$class_name]['installed_id'][] = $id;
				}
			}
		}
		//allowed to install
		return self::$classes;
	}

	/**
	 * Attempt to icnlude module file with class $class
	 *
	 * @param string $class
	 * @return bool
	 */
	static function includeModuleClass($class)
	{
		if(class_exists($class,false))return true;
		$class_file = self::$path.'/class.'.strtolower($class).'.php';
		if(file_exists($class_file)){
			include_once($class_file);
		}

		return class_exists($class,false);
	}

	static function getModuleProperties($module_file,$connect_language = true)
	{
		$properties = array();
		if(file_exists($module_file)&&($fp = fopen($module_file,'r'))){
			$file_content = fread($fp,4096);
			if(preg_match_all('|\s*\*\s+@(\w+)\s+(.+)|i',$file_content,$matches)){
				foreach($matches[2] as &$match){
					$match = trim(str_replace("\n",'',$match));
				}
				$properties = array_combine($matches[1],$matches[2]);
			}
			fclose($fp);
			if(isset($properties['connect_module_class_name'])){
				$class = strtolower($properties['connect_module_class_name']);
				$curr_language = LanguagesManager::getCurrentLanguage();
				/*@var $curr_language Language*/ 
				$lang_iso2 = $curr_language->iso2;//sc_getSessionData('LANGUAGE_ISO3');
				//$lang_iso3 = isset($lang_list[$_SESSION["current_language"]])?$lang_list[$_SESSION["current_language"]]->iso2:DEF_LANG_ID;
				$file_path = self::$path.'/languages/'.$lang_iso2.'.'.$class.'.php';
				$file_path_def = self::$path.'/languages/en.'.$class.'.php';
				if(file_exists($file_path)){
					require_once($file_path);
				}elseif(file_exists($file_path_def)){
					require_once($file_path_def);
				}
			}
		}
		$translated_properties = array('name','description');
		foreach($translated_properties as $translated_property){
			$_translated_property = '_'.$translated_property;
			if(isset($properties[$_translated_property])){
				$properties[$translated_property] = defined($properties[$_translated_property])?constant($properties[$_translated_property]):$properties[$_translated_property];
			}
		}
		return $properties;
	}

	/**
	 * Attempt to get module instance by the module_id in database or by class
	 *
	 * @param int $module_id
	 * @param string $module_class
	 * @return VirtualModule
	 */
	static function &getInstance($module_id = 0,$module_class = false,$search = false)
	{
		$instance = null;
		$class = false;
		if(!$module_id&&$search&&$module_class){//attempt find installed module by classname
				
			$dbq = 'SELECT module_id FROM ?#MODULES_TABLE WHERE module_type=? AND ModuleClassName=? LIMIT 1';
			$module_id = db_phquery_fetch(DBRFETCH_FIRST,$dbq,self::$type,$module_class);
			$class = $module_class;
		}elseif($module_id){//atempt to load module by id
			$dbq = 'SELECT * FROM ?#MODULES_TABLE WHERE module_type=? AND module_id=?';
			$module_info = db_phquery_fetch(DBRFETCH_ASSOC,$dbq,self::$type,$module_id);
			$class = $module_info['ModuleClassName'];
			if($module_class&&($module_class != $class)){
				$class = false;
			}
		}else{
			$class = $module_class;
		}

		if($class&&self::includeModuleClass($class)){
			$instance = new $class($module_id);
		}
		return $instance;
	}

	function __construct($module_id = 0)
	{
		$this->id = $module_id;
		$this->_initVars();
		$this->_connectLanguageFile();
		$this->_initSettingFields();
		$this->load();

	}

	function __set($setting,$val)
	{
		if(isset($this->SettingsFields[$setting])){
			$this->SettingsFields[$setting]['settings_value'] = $val;
		}
	}

	function __get($setting)
	{
		if(isset($this->SettingsFields[$setting])){
			return $this->SettingsFields[$setting]['settings_value'];
		}else{
			return null;
		}
	}

	function load()
	{
		if($this->id){//load settings from database
			$dbq = 'SELECT * FROM ?#MODULES_SETTINGS_TABLE WHERE module_id=?';
			$settings = db_phquery_fetch(DBRFETCH_ASSOC_ALL,$dbq,$this->id);
			foreach($settings as $setting){
				$this->$setting['field'] = $setting['value'];
			}
		}
	}



	/**
	 * @example
	 * $this->SettingsFields['CONF_PAYMENTMODULE_EGOLD_MERCHANT_ACCOUNT'] = array(
	 * 'settings_value' 		=> string,//default value
	 * 'settings_title' 		=> string,//
	 * 'settings_description' 	=>  string,
	 * 'settings_html_function' 	=> string,// 'setting_TEXT_BOX(0,',
	 * 'sort_order' 			=> 1,
		);
	 *
	 */
	protected function _initSettingFields(){
		$this->Settings = array_keys($this->SettingsFields);
	}
	protected function _initVars(){;}
	protected function _connectLanguageFile(){
		$curr_language = LanguagesManager::getCurrentLanguage();
		/*@var $curr_language Language*/ 
		$lang_iso2 = $curr_language->iso2;//sc_getSessionData('LANGUAGE_ISO3');
		$file_path = self::$path.'/languages/'.$lang_iso2.'.'.strtolower(get_class($this)).'.php';
		$file_path_def = self::$path.'/languages/en.'.strtolower(get_class($this)).'.php';
		if(file_exists($file_path)){
			require_once($file_path);
		}elseif(file_exists($file_path_def)){
			require_once($file_path_def);
		}
	}

	function save()
	{
		if(!$this->id){
			$this->install();
		}
		if($this->id){
			foreach($this->SettingsFields as $field=>$settings){
				$dbq = 'INSERT INTO ?#MODULES_SETTINGS_TABLE (`module_id`,`field`,`value`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)';
				db_phquery($dbq,$this->id,$field,$settings['settings_value']);
			}
		}

		return $this->id;
	}

	function setup($params)
	{
		foreach($this->SettingsFields as $field=>&$settings){
			$settings['settings_value'] = isset($_POST[$field])?$_POST[$field]:false;
		}
	}

	function install()
	{
		$allow_install = true;
		if($this->single_install){
			$dbq = 'SELECT COUNT(*) FROM ?#MODULES_TABLE WHERE `module_type`=? AND `ModuleClassName` = ?';
			if(db_phquery_fetch(DBRFETCH_FIRST,$dbq,self::$type,get_class($this))){
				$allow_install = false;
			}
		}else{
			$allow_install = true;
		}
		if($allow_install){
			$dbq = 'INSERT INTO ?#MODULES_TABLE (`module_type`,`module_name`,`ModuleClassName`) VALUES (?,?,?)';
			db_phquery($dbq,self::$type,$this->title,get_class($this));
			$this->id = db_insert_id();
		}
		return $this->id;
	}

	function isInstalled()
	{
		$installed = false;
		$dbq = 'SELECT COUNT(*) FROM ?#MODULES_TABLE WHERE `module_type`=? AND `ModuleClassName` = ?';
		if(db_phquery_fetch(DBRFETCH_FIRST,$dbq,self::$type,get_class($this))){
			$installed = true;
		}
		return $installed;
	}

	function uninstall()
	{
		if($this->id){
			$dbq = 'DELETE FROM ?#MODULES_TABLE WHERE `module_id`=?';
			db_phquery($dbq,$this->id);
			$dbq = 'DELETE FROM ?#MODULES_SETTINGS_TABLE WHERE `module_id`=?';
			db_phquery($dbq,$this->id);
			$this->id = 0;
		}
		return $this->id;
	}

	function getControls()
	{
		$controls = array();
		foreach( $this->SettingsFields as $field=>$properties )
		{
			$control = array('control'=>self::getHtmlControl($properties['settings_html_function'],$field,$properties['settings_value']));
			$controls[] = array_merge($properties,$control);
		}
		return $controls;
	}

	static function getHtmlControl($html_function, $field,$value)
	{
		$matches = false;
		$control = '';
		if(preg_match('/([\w]+)(.*)$/',$html_function,$matches)){
			$function	= $matches[1];
			$params		= trim($matches[2]);
			//
			switch($function){
				case ('text'):
					$control = '<input type="text" value="'
						.xHtmlSpecialChars($value)
					.'" name="'
						.$field.'" size="50" />';
					break;
				case ('textarea'):
					$control = '<textarea cols="50" name="'
							.$field
							.'">'
							.xHtmlSpecialChars($value)
							.'</textarea>';
					break;
				case ('checkbox'):
					$control = '<input type="checkbox" value="1"'
						.($value?' checked="checked"':'')
					.' name="'
						.$field.'" size="50" />';
					break;
				case ('options'):
					break;
				case ('select'):
					$control = "<select name=\"{$field}\">\n";
					if(is_callable($params)){
						if(preg_match('/(\w+)::(\w+)/',$params,$matches)){
							$params = array(&$matches[1],$matches[2]);
						}
						//$params = array('VirtualModule2','select_customer_fields');
						$options = call_user_func($params);
						if(is_array($options)){
							foreach($options as $option){
								$option['value'] = xHtmlSpecialChars($option['value']);
								$option['title'] = xHtmlSpecialChars($option['title']);
								$selected = ($option['value']==$value)?' selected':'';
								$control .= "<option value=\"{$option['value']}\"{$selected}>{$option['title']}</option>\n";
							}
						}
					}else{
						$matches = '';
						if(preg_match_all('/((.+):(.+))(,|$)/',$params,$matches)){
							var_dump($matches);
						}
					}
					$control .= "</select>\n";
						
					break;
			}
		}
		return $control."\n";
	}


	static public function select_customer_fields()
	{
		$result = array();
		$result[] = array('title'=>translate('regform_not_requested'),'value'=>0);
		$fields= GetRegFields();
		foreach($fields as $field){
			$result[] = array('title'=>$field['reg_field_name'],'value'=>$field['reg_field_ID']);
		}
		return $result;
	}

	static public function select_currency()
	{
		$result = array();
		$result[] = array('title'=>translate('regform_not_requested'),'value'=>0);
		$currencies = currGetAllCurrencies();
		foreach($currencies as $currency){
			$result[] = array('title'=>$currency['Name'],'value'=>$currency['CID']);
		}
		return $result;

	}
}
?>