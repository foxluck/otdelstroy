<?php
	class WbsSystem {
		private $sqlServers = array ();
		/**
		 * @var WbsSystemFiles
		 */
		private $fs;
		private $xml;
		private $timeZone;
		private $sessionTimeout;
		private $accountsDB = array();
		
		public function __construct() {
			$this->fs = new WbsSystemFiles();
			$this->loadSettings();
		}
		
		/**
			Load common settings for this Wbs-installation.  
		**/
		public function loadSettings() {
			$settingsFilePath = $this->fs->getWbsPath ("/kernel/wbs.xml");
			
			if (!file_exists($settingsFilePath))
				throw new RuntimeException ("File wbs.xml not found");
			
			$xml = simplexml_load_file($settingsFilePath);
			$this->xml = $xml;
			

			foreach ($xml->SQLSERVERS[0]->SQLSERVER as $serverXml) {
				$serverName = (string)$serverXml["NAME"];
				$serverRecord = array ();
				foreach ($serverXml->LANGUAGES[0]->LANGUAGE as $cLang)
					$serverRecord["LANGUAGES"][(string)$cLang["ID"]] = (string)$cLang["NAME"];
				foreach ($serverXml->attributes() as $cName => $cValue) {
					$serverRecord[$cName] = (string)$cValue;
				}
				$this->sqlServers[$serverName] = $serverRecord;
			}
			
			// Load server timezone
			$this->timeZone = null;
			if ($xml->SERVER_TIME_ZONE) {
				$tz = $xml->SERVER_TIME_ZONE;
				$timezoneEnabled = $tz["ENABLE"];
				$timezoneId = (string)$tz["ID"];
				$timezoneDst = ((string)$tz["SERVER_TIME_ZONE_DST"] == 1) ? 1 : 0;
				
				if ($timezoneEnabled) {
					$this->timeZone = new CTimeZone($timezoneId, $timezoneDst);
					CDateTime::setDefaultTimeZone($this->timeZone);
				}
			}
							
			// Initialize files system
			$dataDirectoryPath = (string)$xml->DIRECTORIES[0]->DATA_DIRECTORY["PATH"];
			$dataDirectoryPath = str_replace("/", DIRECTORY_SEPARATOR, $dataDirectoryPath);
			$this->fs->initialize($dataDirectoryPath);
			
			// Load accounts database info
			if ($xml->ACCOUNTS_DB) {
				$node=$xml->ACCOUNTS_DB;
				$this->accountsDB['sqlserver'] = (string)$node['sqlserver'];
				$this->accountsDB['dbname'] = (string)$node['dbname'];
			}
		}
		
		public function getWebUrl()	{
			return (string)$this->xml->DIRECTORIES[0]->WEB_DIRECTORY["PATH"] ? (string)$this->xml->DIRECTORIES[0]->WEB_DIRECTORY["PATH"] : "/";			
		}

		public function getVersion() {
			$attr = $this->xml->attributes();
			return $attr['VERSION']; 
		}

		public function getUrl() {
			return isset($this->xml->WBS_URL)? (string)$this->xml->WBS_URL : false;
		}

		public function setUrl($url) {
			$this->xml->WBS_URL = $url;
			$path = $this->fs->getWbsPath('kernel/');
			if(!file_put_contents(WBS_DIR.'temp/wbs.xml.tmp', $this->xml->asXML()) ||
				!copy(WBS_DIR.'temp/wbs.xml.tmp', $path.'wbs.xml') ||
				!unlink(WBS_DIR.'temp/wbs.xml.tmp'))
				return false;
			return true;
		}
		
		/**
		 * Returns Robot Email (from wbs.xml)
		 * 
		 * @return string
		 */
		public function getEmail()
		{
			if (isset($this->xml->EMAIL['ENABLED']) && $this->xml->EMAIL['ENABLED']) {
				$email = (string)$this->xml->EMAIL['ROBOTEMAIL'];				
			}
			if (!$email) {
				$email = 'robot@'.Env::Server('HTTP_HOST', 'webasyst.net'); 
			}
			return $email;
		}

		/**
		 * 
		 * @return CTimeZone
		 */
		public function getTimeZone() {
			return $this->timeZone;
		}

		public function getSessionTimeout() {
			$timeout = $this->xml->HTML_SETTINGS[0]["SESSION_TIMEOUT"];
			if ($timeout)
				$timeout = $timeout * 60;
			return $timeout;
		}
		
		public function getSqlServers() {
			return $this->sqlServers;
		}
		
		public function isValidApplication($appId) {
			if (strtoupper($appId) == "HR" || strtoupper($appId) == "NL")
				return false;
			return file_exists($this->fs->getAppPath ($appId, "wbs_application.xml")) && 
				file_exists($this->fs->getAppPath ($appId, "_screens.php"));
		}
		
		public function isModeRewrite()
		{
			if ($this->xml->FRONTEND) {
				$attrs = $this->xml->FRONTEND->attributes();
				return isset($attrs['mod_rewrite']) ? (int)$attrs['mod_rewrite'] : 0;
			}
			return 0;			
		}
		
		public function isDisablePoweredBy()
		{
		    if ( Wbs::isHosted() ) return 0;
		    if ($this->xml->FRONTEND) {
				$attrs = $this->xml->FRONTEND->attributes();
				return isset($attrs['disable_powered_by']) ? (int)$attrs['disable_powered_by'] : 0;
			}
			return 0;	
		}
		
		public function getFrontendType()
		{
			if ($this->xml->FRONTEND) {
				$attrs = $this->xml->FRONTEND->attributes();
				return isset($attrs['type']) ? (string)$attrs['type'] : '';
			}
			return '';			
		}
		
		public function getFrontendDbkey()
		{
			if ($this->xml->FRONTEND) {
				$attrs = $this->xml->FRONTEND->attributes();
				return isset($attrs['dbkey']) ? (string)$attrs['dbkey'] : null;
			}
			return null;			
		}		

		
		/**
		 * @return WbsSystemFiles
		 */
		public function files() {
			return $this->fs;
		}
		
		/**
		 * Load settings and get connection to CommonLogBase (for Event Dispatcher or Metrics)
		**/
		public function getCommonLogBase($dbname = 'DBNAME') {
			$settingsFilePath = $this->fs->getWbsPath ('/kernel/wbs.xml');

			if (!file_exists($settingsFilePath))
				throw new RuntimeException ('File wbs.xml not found');

			$xml = simplexml_load_file($settingsFilePath);

			if(!isset($xml->COMMONLOGBASE))
				return false;

			$this->CommonLogBase = new MysqlDb();
			$this->CommonLogBase->connect(
				$xml->COMMONLOGBASE->HOST,
				$xml->COMMONLOGBASE->PORT,
				$xml->COMMONLOGBASE->ADMIN_USERNAME,
				$xml->COMMONLOGBASE->ADMIN_PASSWORD,
				$xml->COMMONLOGBASE->$dbname
			);
			$this->CommonLogBase->setCharset('UTF8');
			return true;
		}
		
		public function getAccountsDBCredentials() {
			$result=array();
			if (count($this->accountsDB)) {
				$sqlserver=$this->sqlServers[$this->accountsDB['sqlserver']];
				$result['HOST']=$sqlserver['HOST'].':'.$sqlserver['PORT'];
				$result['USER']=$sqlserver['ADMIN_USERNAME'];
				$result['PASS']=$sqlserver['ADMIN_PASSWORD'];
				$result['DBNAME']=$this->accountsDB['dbname'];
			}
			return $result;
		}

	}
?>
