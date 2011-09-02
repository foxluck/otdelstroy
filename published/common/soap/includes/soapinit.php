<?php

	//
	// SOAP client initialization script
	//

	global $_GET;
	$DB_KEY = strtoupper($_GET["DB_KEY"]);
	$DEBUG_MODE = 0;

	if ( !( isset($init_required) && !$init_required ) ) {
		if ( !isset($DB_NAME) || !strlen($DB_NAME) ) {
			$DB_NAME = "DB".strtoupper($DB_KEY);
			if ( !strlen(trim($DB_NAME)) )
				die( "Unknown database. Please use index.php script with your database key to login." );

		}
	}

	define('PATH_THISSCRIPT',
		str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi'
		||php_sapi_name()=='cgi-fcgi' ||php_sapi_name()=='cgix')&&(isset($_SERVER['ORIG_PATH_TRANSLATED']) ?
		$_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED'])?
		(isset($_SERVER['ORIG_PATH_TRANSLATED']) ? $_SERVER['ORIG_PATH_TRANSLATED'] :
		$_SERVER['PATH_TRANSLATED']) : (isset($_SERVER['ORIG_SCRIPT_FILENAME']) ? $_SERVER['ORIG_SCRIPT_FILENAME'] :
		$_SERVER['SCRIPT_FILENAME']))));

	$_SERVER['PATH_TRANSLATED'] = PATH_THISSCRIPT;

	if ( !ini_get('magic_quotes_gpc') )
		$_SERVER['PATH_TRANSLATED'] = addSlashes( $_SERVER['PATH_TRANSLATED'] );

	$scriptPath = dirname( stripSlashes($_SERVER['PATH_TRANSLATED']) );
	if ( !( isset( $loginScript ) && $loginScript ) ) {
		if ( !isset( $authScript ) )
			$WBSPath = $scriptPath."/../../../";
		else
			$WBSPath = $scriptPath."/../../../../";
	} else {
		$WBSPath = $scriptPath."/../";
	}

	define( "WBS_DIR", realpath($WBSPath)."/"  );

	require_once( WBS_DIR."kernel/wbsinit.php" );

	if ( !loadWBSSettings() )
		die( "Unable to load WBS settings" );

	require_once( WBS_DIR."kernel/kernel.php" );

	if ( $hostDataFileError ) {
		define( 'PEAR_PATH', realpath(WBS_DIR.'kernel/includes/pear') );
		$paths = realpath($WBSPath)."/kernel/";
		$paths .= ';'.realpath( PEAR_PATH );
		$paths .= ';'.realpath( PEAR_PATH.'/PEAR' );
		$paths .= ':'.realpath( PEAR_PATH );
		$paths .= ':'.realpath( PEAR_PATH.'/PEAR' );
		$paths .= ':'.realpath($WBSPath)."/kernel/";
		set_include_path( $paths );
		require_once "PEAR.php";
		require_once "functions.php";
		require_once ("SOAP/Value.php");
		require_once ("SOAP/Fault.php");
		$soap_login_fault = new SOAP_Fault("Database Key is not found", 'Server', 'PHP');
		header('Content-Type: text/xml');
		echo $soap_login_fault->message();
		exit();
	}

	loadDatabaseLanguageList($DB_KEY);
	global $html_encoding;

	$html_encoding = DEF_LANG_ENCODING;
	
	if (!function_exists("json_encode") ) {
		
		include (WBS_DIR . "kernel/classes/JSON.php");
		/**
		 * Returns the JSON representation of a value
		 * 
		 * @param mixed $string
		 * @return string
		 */
		function json_encode($value) 
		{
			$json = new Services_JSON();
			return $json->encode($value);
		}
		
		/**
		 * Decodes a JSON string
		 * 
		 * @param string $string
		 * @param bool $assoc
		 * @return object|array
		 */
		function json_decode($string, $assoc = false) 
		{
			$json = new Services_JSON();
			if ($assoc) {
				$json->use = SERVICES_JSON_LOOSE_TYPE;
			} 
			return $json->decode($string);
		}
	}
	
?>
