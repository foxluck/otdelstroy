<?php
	/*
	* WebAsyst dbkey instance class
	*	contains functions working with DBKEY and common installation settings
	*/
	class Dbkey {
		private $sqlServerName = null;
		private $dbkey = null;
		private $info;
		private $xml = null;
		/**
		 * @var DbkeyFiles
		 */
		private $fsObj; // wbs-filesystem obj
		private $dateFormat;
		
		
		private $wbsSystem;
		
		/**
		 * @param WbsSystem $systemObj
		 * @param string $dbkey
		 */
		public function __construct($systemObj, $dbkey) 
		{
			$this->wbsSystem = $systemObj;			
			$this->dbkey = strtoupper($dbkey);
		}
		
		public function getDbkey() 
		{
			return $this->dbkey;
		}
		
		
		public function loadDBKey() 
		{
			$filePath = sprintf( "%sdblist/%s.xml", WBS_DIR, $this->dbkey);
			if (!file_exists($filePath)) {
				error_log("Invalid DB_KEY: file ".$filePath." not found.");
				return false;
			}
			
			$this->xml = simplexml_load_file($filePath);
			
			$this->info = array();
			
			$dbsettings = $this->xml->DBSETTINGS[0];
			foreach ($dbsettings->attributes() as $cKey => $cValue) {
				$this->info["DBSETTINGS"][(string)$cKey] = (string)$cValue;
			}
			
			// Find actual sqlserver
			$serverName = (string)$dbsettings["SQLSERVER"];
			$sqlServers = $this->wbsSystem->getSqlServers();
			if ( !array_key_exists($serverName, $sqlServers) ) {
				$serverName = "DEFAULT";
			}
			$sqlServerFound = array_key_exists($serverName, $sqlServers);
			if ( !$sqlServerFound ) {
				$serverNames = array_keys($sqlServers);

				if ( count($serverNames) )
					$serverName = $serverNames[0];
			}
			$this->sqlServerName = $serverName;
			
			if (empty($sqlServers[$serverName])) 
			{
				throw new RuntimeException("Wrong sql server name: $serverName");
			}
			
			// Load languages list for this dbkey
			$this->info["LANGUAGES"] = isset($sqlServers[$serverName]["LANGUAGES"]) ? $sqlServers[$serverName]["LANGUAGES"] : array();
			
			// Load and setup default date format
			if (class_exists("CDate"))
				CDate::setDefaultDisplayFormat((string)$dbsettings["DATE_FORMAT"]);
			
			$this->dateFormat = (string)$dbsettings["DATE_FORMAT"];
			
			$this->fsObj = new DbkeyFiles($this, $this->wbsSystem->files());
			
			return true;
		}
		
		public function getDateFormat() 
		{
			return $this->dateFormat;
		}
		
		public function getCreationDate() {
			return CDate::fromStr($this->info["DBSETTINGS"]["CREATE_DATE"]);
		}
		
		public function existsModule($class)
		{
			if (!isset($this->xml->MODULES)) {
				return false;
			}			
			//print_r($this->xml->MODULES->ASSIGN);
			foreach ($this->xml->MODULES->ASSIGN as $module) {
				if ((string)$module['CLASS'] == $class && !(int)$module['DISABLED']) {
					return true;
				}
			}
			return false;
		}
		
		public function getLanguage() {
			if ($this->xml->ADMINISTRATOR && $this->xml->ADMINISTRATOR["LANGUAGE"])
				return (string)$this->xml->ADMINISTRATOR["LANGUAGE"];
			if ($this->xml->FIRSTLOGIN && $this->xml->FIRSTLOGIN["LANGUAGE"])
				return (string)$this->xml->FIRSTLOGIN["LANGUAGE"];
			return "eng";
		}
		
		public function getVersion($appId) 
		{
			return isset($this->xml->VERSIONS[$appId]) ? $this->xml->VERSIONS[$appId] : 0;
		}
		
		public function getXmlParam($xmlPath) 
		{
			return $this->xml->xpath($xmlPath);;
		}
		
		public function getLanguages() 
		{
		    $langs = $this->loadDBKeyLanguages();
			$result = array();
			foreach ($langs as $key => $lang) {
			    if ($key == 'gem') {
			        $key = 'deu';
			    }
			    $result[$key] = $lang['NAME'];
			}
			return $result;				
		}
		
		/**
			Languages list setted in the SQL-server languages section in wbs.xml
			this function load languages for current dbkey server
		**/
		public function loadDBKeyLanguages() 
		{
			$filePath = WBS_DIR."kernel/languages.csv";
			if ( !file_exists( $filePath ) )
				return PEAR::raiseError( "Error loading file: $filePath" );

			$result = array();

			$handle = fopen( $filePath, "r" );

			$wbs_languages = array();
			while ( ($data = fgetcsv($handle, 100, "\t") ) !== FALSE ) {
				if (Wbs::isHosted() || array_key_exists( $data[0], $this->info["LANGUAGES"] ) || $data[0] == 'eng' )
					$wbs_languages[$data[0]] = array( 'ID' => $data[0],  'NAME' => $data[1], 'ENCODING' => $data[2] );
			}

			fclose($handle);
			
			return $wbs_languages;
		}
		
		/**
		 * 
		 * @return DbkeyFiles
		 */
		public function files() 
		{
			if (!$this->fsObj)
				throw new RuntimeException ("DBKey File System not initialized yet");
			return $this->fsObj;
		}
		
		
		/**
			Returns db-connection config
			@return array
		**/
		public function getDbConfig () 
		{
			$dbconf = $this->info["DBSETTINGS"];
			$sqlServers = $this->wbsSystem->getSqlServers();
			$serverConf = $sqlServers[$this->sqlServerName];
			
			$dbconf["HOST"] = $serverConf["HOST"];
			$dbconf["PORT"] = $serverConf["PORT"];
			
			return $dbconf;			
		}
		
		public function getSetting($name) 
		{
			if (isset($this->info['DBSETTINGS'][$name])) {
				return $this->info['DBSETTINGS'][$name];
			} else {
				return false;
			}
		}
		
		
		public function getApplicationsList() {
			$appList = array ();
			foreach ($this->xml->APPLICATIONS[0]->APPLICATION as $cApp) {
				$appId = (string)$cApp["APP_ID"];
				$appList[] = $appId;
			}
			if (Wbs::isHosted()) {
				$appListData = $this->loadHostedAppsData();
				$appList = array_keys($appListData);
			}
			$appList = array_merge(array("MW", "AA", "UG", "WG"), $appList);
			$resAppList = array ();
			foreach ($appList as $cKey => $cId) {
				if ($this->wbsSystem->isValidApplication($cId))
					$resAppList[] = $cId;
			}
			
			return $resAppList;
		}
		
		public function appExists($app_id) 
		{
		    $apps = $this->getApplicationsList();
		    return in_array($app_id, $apps);
		}
		
		public function loadHostedAppsData() 
		{
			$dbsettings = $this->xml->DBSETTINGS[0];
				
			$result = array ();
			
			if ((string)$dbsettings["FREE_APPS"]) {
				$appListKeys = split(",", (string)$dbsettings["FREE_APPS"]);
				foreach ($appListKeys as $cKey) {
					$result[$cKey] = array ("plan" => "FREE");
				}
			}
					
			if ((string)$dbsettings["CUSTOM_APPS"]) {
				$appListKeys = split(",", (string)$dbsettings["CUSTOM_APPS"]);
				foreach ($appListKeys as $cKey) {
					$result[$cKey] = array ("plan" => "CUSTOM");
				}
			}
			
			$plan = (string)$dbsettings["PLAN"];
			include(WBS_ROOT_PATH . "/kernel/hosting_plans.php");
			if ($plan && $plan != "FREE" && $plan != "CUSTOM" && $plan != "DEFAULT" && isset($mt_hosting_plan_settings[$plan])) {
				foreach ($mt_hosting_plan_settings[$plan] as $appId => $value) {
					$result[$appId] = array ("plan" => $plan);
				}
			}
			
			if ($this->xml->APPLICATIONS[0]) {
				foreach ($this->xml->APPLICATIONS[0]->APPLICATION as $cApp) {
					$appId = (string)$cApp["APP_ID"];
					if (isset($cApp->SETTINGS) && $cApp->SETTINGS[0]->OPTION) {
						foreach ($cApp->SETTINGS[0]->OPTION as $optionNode) {
							$result[$appId]['settings'][(string)$optionNode["NAME"]] = (string)$optionNode;
						}
					}
					if (isset($result[$appId]['settings']['PLAN'])) {
						$result[$appId]["plan"] = $result[$appId]['settings']['PLAN'];
						$result[$appId]["type"] = 'APP';
					}
				}
			}
			if (isset($this->xml->DBSETTINGS['PLAN'])) {
				$result['AA']['plan'] = $plan;
			}
			return $result;
		}
		
		public function getAdvancedParam($paramName) 
		{
			$advSettingsXml = $this->xml->ADVSETTINGS[0];
			if (!$advSettingsXml)
				return null;
			foreach ($advSettingsXml->PARAM as $cParam) {
				if ($cParam["name"] == $paramName)
					return stripslashes($cParam["value"]);
			}
		}
		
		public function needBillingAlert() 
		{
			if (!Wbs::isHosted()) {
				return false;
			}
			$days = $this->getDaysToSuspend();
			if ($days === null) {
				return false;
			}
			
			$dbsettings = $this->xml->DBSETTINGS[0];
			if (!empty($dbsettings["AUTORENEW_MTO_ID"]))
				return null;
			
			if ($days < 5) {
				$message = ($days <= 0) ?
					_s('Your account has been expired.') :
					sprintf(_s('Your account will expire in %s day(s).'), $days);
				return array ("days" => $days, "message" => $message);
			}
		}	
		
		public function getDaysToSuspend() 
		{
			$billingDate = (string)$this->xml->DBSETTINGS["BILLING_DATE"];
			if (!$billingDate) {
				return null;
			}
			$timestamp = strtotime($billingDate);
			$days = ceil(($timestamp - mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))) / (60 * 60 * 24));
			return $days;
		}
	
		public function getModule($class)
		{
			if (!isset($this->xml->MODULES)) {
				return false;
			}			
			//print_r($this->xml->MODULES->ASSIGN);
			foreach ($this->xml->MODULES->ASSIGN as $module) {
				if ((string)$module['CLASS'] == $class && !(int)$module['DISABLED']) {
					return $module;
				}
			}
			return false;
		}

	}
?>