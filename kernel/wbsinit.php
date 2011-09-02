<?php

	//
	// WBS Settings XML tags
	//

	define( "WBS_WBS", "WBS" );
	define( "WBS_LANGUAGES", "LANGUAGES" );
	define( "WBS_LANGUAGE", "LANGUAGE" );
	define( "WBS_NAME", "NAME" );
	define( "WBS_EMAIL", "EMAIL" );
	define( "WBS_ENABLED", "ENABLED" );
	define( "WBS_HTML_SETTINGS", "HTML_SETTINGS" );
	define( "WBS_HTML_HTTPS_PORT", "HTTPS_PORT" );
	define( "WBS_DIRECTORIES", "DIRECTORIES" );
	define( "WBS_DATA_DIRECTORY", "DATA_DIRECTORY" );
	define( "WBS_WEB_DIRECTORY", "WEB_DIRECTORY" );
	define( "WBS_PATH", "PATH" );
	define( "WBS_WBS_PATH", "[WBS_PATH]" );
	define( "WBS_ADMIN_PASSWORD", "ADMIN_PASSWORD" );
	define( "WBS_ADMIN_USERNAME", "ADMIN_USERNAME" );
	define( "WBS_HOST", "HOST" );
	define( "WBS_WEBASYSTHOST", "WEBASYST_HOST" );
	define( "WBS_SESSION_TIMEOUT", "SESSION_TIMEOUT" );
	define( "WBS_SQLSERVERS", "SQLSERVERS" );
	define( "WBS_SQLSERVER", "SQLSERVER" );
	define( "WBS_PORT", "PORT" );
	define( "WBS_DBCHARSET", "DBCHARSET" );
	define( "WBS_ENCODING", "ENCODING" );
	define( "WBS_ROBOTEMAIL", "ROBOTEMAIL" );
	define( "WBS_SYSTEM", "SYSTEM" );
	define( "WBS_MEMORYLIMIT", "MEMLIMIT" );
	define( "WBS_ADMINRIGHTS", "ADMIN_ADMINRIGHTS" );
	define( "WBS_DEBUGMODEVALUE", "DEBUG" );
	define( "WBS_SHOWTT", "TIPSANDTRICKS" );
	define( "WBS_ONDBCREATEHANDLER", "ONDBCREATEHANDLER" );
	define( "WBS_MAIL_MODE", "MAIL_MODE" );

	define( "WBS_LANGUAGE_ID", "ID" );
	define( "WBS_LANGUAGE_NAME", "NAME" );

	define( "WBS_SERVER_TIME_ZONE", "SERVER_TIME_ZONE" );
	define( "WBS_SERVER_TIME_ZONE_ID", "ID" );
	define( "WBS_SERVER_TIME_ZONE_ENABLE", "ENABLE" );
	define( "WBS_SERVER_TIME_ZONE_DST", "SERVER_TIME_ZONE_DST" );

	define( "DEF_LANG_ID", "eng" );
	define( "DEF_LANG_NAME", "English" );
	define( "DEF_LANG_ENCODING", "iso-8859-1" );

	define( "DEF_SQLSERVER", "DEFAULT" );

	define( "HOST_SESS_EXPIRE_PERIOD", "SESSION_EXPIRE_PERIOD" );
	define( "SESSION_USE_SYSTEM_TO", "SYSTEM" );

	define( "WBS_TRUEVAL", "TRUE" );
	define( "WBS_FALSEVAL", "FALSE" );

	define( "WBS_DEFMEMORYAVAILABLE", 32 );

	if ( version_compare(PHP_VERSION,'5','>=') )
		require_once( WBS_DIR."kernel/domxml-php4-to-php5.php" );

	// Localization support
	//
	$langListFileName = "languages.csv";

	function getXMLAttributes( &$node )
	//
	// Returns values of all node attributes as associative array
	//
	//		Parameters:
	//			$node - XML document node
	//
	//		Returns associative array
	//
	{
		$attrs = $node->attributes();

		$result = array();

		if ( !is_array( $attrs ) )
			return $result;

		for ( $i = 0; $i < count($attrs); $i++ ) {
			$attr = $attrs[$i];

			$result[$attr->name] = $attr->value;
		}

		return $result;
	}

	function getCCURL($lang = null){

		if(is_null($lang)){
			global $language;
			if($language){
				$lang = $language;
			}
		}

		if(!$lang){
			global $databaseInfo;

			if(isset($databaseInfo[HOST_DBSETTINGS])){

				if(isset($databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER]) && $databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER] == 'Russian'){
					$lang = LANG_RUS;
				}

				if(isset($databaseInfo[HOST_DBSETTINGS][HOST_TRIALDATASOURCE]) && $databaseInfo[HOST_DBSETTINGS][HOST_TRIALDATASOURCE] == 'TRIALDEMORUS'){
					$lang = LANG_RUS;
				}
			}
		}

		$lang = strtolower($lang);
		if($lang != LANG_RUS && $lang != LANG_ENG)$lang = LANG_ENG;

		$_wbs_host = getWBSHost();
		switch($_wbs_host){
			case 'dev.webasyst.net':
			case 'test.webasyst.net':
			case 'qa.webasyst.net':
				$ccUrl = 'https://'.$_wbs_host.'/cc/';
				break;
			case 'dev.yug.webasyst.net':
				$ccUrl = 'http://my.dev.yug.webasyst.net';
				break;
			default:
				$ccUrl = $lang==LANG_ENG?'https://my.webasyst.net':'https://my.articus.ru';
				break;
		}

		return $ccUrl;
	}

	function loadWBSSettings()
	//
	// Parses wbs.xml file
	//
	//		Returns false in case of error
	//
	{
		global $sendmail_enabled;
		global $init_required;
		global $wbsSettingsLoaded;
		global $wbs_sqlServers;
		global $wbs_dataPath;
		global $wbs_robotemailaddress;
		global $wbs_memoryLimit;
		global $wbs_settingsLimit;
		global $wbs_smtp_settings;
		global $wbs_accounts_db;

		$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );

		$dom = domxml_open_file( realpath($filePath) );
		if ( !$dom )
			return false;

		$xpath = xpath_new_context($dom);

		// Load email settings
		//
		if ( !( $email = xpath_eval($xpath, "/".WBS_WBS."/".WBS_EMAIL) ) )
			return false;

		$email = $email->nodeset[0];
		$sendmail_enabled = $email->get_attribute(WBS_ENABLED);
		$wbs_robotemailaddress = $email->get_attribute(WBS_ROBOTEMAIL);

		// Load Server TimeZone
		if ( !( $tz = xpath_eval($xpath, "/".WBS_WBS."/".WBS_SERVER_TIME_ZONE) ) )
			return false;

                if ( isset( $tz->nodeset[0] ) )
			$tz = $tz->nodeset[0];
		else
			$tz= null;

		if ( !is_null( $tz ) )
		{
			define( 'SERVER_TZ', $tz->get_attribute(WBS_SERVER_TIME_ZONE_ENABLE ) == 1 ? 1 : 0 );
			define( 'SERVER_TIME_ZONE_ID',  $tz->get_attribute(WBS_SERVER_TIME_ZONE_ID ) );
			define( 'SERVER_TIME_ZONE_DST', $tz->get_attribute(WBS_SERVER_TIME_ZONE_DST ) == 1 ? 1 : 0 );
		}
		else
		{
			define( 'SERVER_TZ', 0 );
			define( 'SERVER_TIME_ZONE_ID',  "" );
			define( 'SERVER_TIME_ZONE_DST', 0 );
		}
		// Load system settings
		//
		$defMemoryLimit = WBS_DEFMEMORYAVAILABLE;
		if ( $system = xpath_eval($xpath, "/".WBS_WBS."/".WBS_SYSTEM) ) {
			if ( count($system->nodeset) ) {
				$system = $system->nodeset[0];

				$wbs_settingsLimit = $wbs_memoryLimit = $system->get_attribute(WBS_MEMORYLIMIT);
				if ( !strlen($wbs_memoryLimit) )
					$wbs_memoryLimit = $defMemoryLimit;

				define( 'WBS_DEBUGMODE', $system->get_attribute(WBS_DEBUGMODEVALUE) );
				$handlerPath = str_replace( WBS_WBS_PATH, realpath(WBS_DIR), $system->get_attribute(WBS_ONDBCREATEHANDLER) );
				define( 'WBS_ONDBCREATE_HANDLER_PATH', $handlerPath );

				// Load URL to server
				//
				$host_url = $system->get_attribute('MT_HOST_SERVER');
				define( "MT_HOST_SERVER", $host_url );
				
				// Load mail sending mode
				//
				$mailmode = $system->get_attribute(WBS_MAIL_MODE);
				define( "MAIL_MODE", $mailmode );
			} else
				$wbs_memoryLimit = $defMemoryLimit;
		} else
			$wbs_memoryLimit = $defMemoryLimit;

		// Load HTTPS port
		//
		if ( !( $html_settings = xpath_eval($xpath, "/".WBS_WBS."/".WBS_HTML_SETTINGS) ) )
			return false;

		$html_settings = $html_settings->nodeset[0];

		$https_port = $html_settings->get_attribute(WBS_HTML_HTTPS_PORT);
		define( "HTTPS_PORT", $https_port );

		// Load Show Tips And Tricks valus
		//
		$show_tt = $html_settings->get_attribute(WBS_SHOWTT) == 1;
		define( "SHOW_TIPSANDTRICKS", $show_tt );

		// Load session timeout value
		//
		$session_timeout = $html_settings->get_attribute(WBS_SESSION_TIMEOUT);

		if ( strlen($session_timeout) )
			define( "SESSION_TIMEOUT", $session_timeout*60 );
		else
			define( "SESSION_TIMEOUT", 0 );

		// Load data directory path
		//
		if ( !( $dataDir = xpath_eval($xpath, "/".WBS_WBS."/".WBS_DIRECTORIES."/".WBS_DATA_DIRECTORY) ) )
			return false;

		$dataDir = $dataDir->nodeset[0];
		$wbs_dataPath = $dataDir->get_attribute(WBS_PATH);

		$dataDirPath = str_replace( WBS_WBS_PATH, realpath(WBS_DIR), $wbs_dataPath );
		define( "WBS_DATA_DIR", $dataDirPath );
		
		// Load web relative directory path
		if ( !( $installDir = xpath_eval($xpath, "/".WBS_WBS."/".WBS_DIRECTORIES."/".WBS_WEB_DIRECTORY)) ||!count($installDir->nodeset)){
			$wbs_installPath = '';
		}else{			
			$installDir = $installDir->nodeset[0];
			$wbs_installPath = $installDir->get_attribute(WBS_PATH);
		}
	
		define( "WBS_INSTALL_PATH", $wbs_installPath );

		// Load SQL servers
		//
		if ( !( $sqlservers = xpath_eval($xpath, "/".WBS_WBS."/".WBS_SQLSERVERS."/".WBS_SQLSERVER) ) )
			return false;

		$wbs_sqlServers = array();
		foreach( $sqlservers->nodeset as $sqlserver ) {
			$serverParams = getXMLAttributes($sqlserver);
			$serverName = $serverParams[WBS_NAME];

			if ( $serverLanguages = xpath_eval($xpath, WBS_LANGUAGES."/".WBS_LANGUAGE, $sqlserver) ) {
				$languages = array( DEF_LANG_ID=>DEF_LANG_NAME );
				foreach( $serverLanguages->nodeset as $serverLanguage ) {
						$languageParams = getXMLAttributes($serverLanguage);
						$languages[$languageParams[WBS_LANGUAGE_ID]] = $languageParams[WBS_LANGUAGE_NAME];
				}

				$serverParams[WBS_LANGUAGES] = $languages;
			} else
				$serverParams[WBS_LANGUAGES] = array( DEF_LANG_ID=>DEF_LANG_NAME );

			$wbs_sqlServers[$serverName] = $serverParams;
		}
		
		// Load SMTP settings
		//
		$wbs_smtp_settings = array();
		if ($wbs_smtp_settings_section = xpath_eval($xpath, "/".WBS_WBS."/SMTP_SERVER")){
			$nodes = $wbs_smtp_settings_section->nodeset;
			if($nodes && isset($nodes[0])){
				$wbs_smtp_settings = getXMLAttributes($nodes[0]);
			}
		}
		// Load accounts database info
		$wbs_accounts_db=array();
		if ($node = xpath_eval($xpath, "/".WBS_WBS."/ACCOUNTS_DB")){
			$nodes = $node->nodeset;
			if($nodes && isset($nodes[0])){
				$a=getXMLAttributes($nodes[0]);
				if (array_key_exists('sqlserver', $a) && array_key_exists('dbname', $a)) {
					$wbs_accounts_db['sqlserver'] = $a['sqlserver'];
					$wbs_accounts_db['dbname'] = $a['dbname'];
				}
			}
		}

		$wbsSettingsLoaded = true;
		return true;
	}

	function loadDatabaseLanguageList($DB_KEY)
	{
		global $wbs_sqlServers;
		global $wbs_languages;

		global $langListFileName;

		$wbs_languages = array();

		$filePath = sprintf( "%sdblist/%s.xml", WBS_DIR, strtoupper($DB_KEY) );

		if ( !file_exists($filePath) )
			return;

		$dom = domxml_open_file( realpath($filePath) );

		$xpath = xpath_new_context($dom);

		if ( !( $dbsettings = xpath_eval($xpath, "/DATABASE/DBSETTINGS") ) )
			return;

		$dbsettings = $dbsettings->nodeset[0];
		$serverName = $dbsettings->get_attribute( "SQLSERVER" );

		if ( !array_key_exists($serverName, $wbs_sqlServers) ) {
			$serverName = DEF_SQLSERVER;

			$sqlServerFound = false;
			foreach( $wbs_sqlServers as $curServerName => $serverParams ) {
				if ( $serverName == strtoupper($curServerName) ) {
					$serverName = $curServerName;
					$sqlServerFound = true;

					break;
				}
			}

			if ( !$sqlServerFound ) {
				$serverNames = array_keys($wbs_sqlServers);

				if ( count($serverNames) )
					$serverName = $serverNames[0];
			}
		}

		$serverData = $wbs_sqlServers[$serverName];
		$serverLanguages = $serverData[WBS_LANGUAGES];

		$filePath = WBS_DIR."kernel/$langListFileName";
		if ( !file_exists( $filePath ) )
			return PEAR::raiseError( "Error loading file: $langListFileName" );

		$result = array();

		$handle = fopen( $filePath, "r" );

		while ( ($data = fgetcsv($handle, 100, "\t") ) !== FALSE ) {
			if ( array_key_exists( $data[0], $serverLanguages ) )
				$wbs_languages[$data[0]] = array( WBS_LANGUAGE_ID=>$data[0],  WBS_LANGUAGE_NAME=>$data[1], WBS_ENCODING=>$data[2] );
		}

		fclose($handle);

		return null;
	}

?>
