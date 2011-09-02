<?php

	//
	// HTML client initialization script
	//

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
	$WBSPath = $scriptPath."/../../../";

	define( "WBS_DIR", realpath($WBSPath)."/" );

	require_once( WBS_DIR."kernel/wbsinit.php" );

	if ( !loadWBSSettings() )
		die( "Unable to load WBS settings" );

	if ( isset( $_GET["DB_KEY"] ) )
		$DB_KEY = strtoupper($_GET["DB_KEY"]);
	else { 
		$session_started = ini_get( 'session.auto_start' );

		if ( !$session_started )
			@session_start();

		$lastStamp = @$_SESSION['timestamp'];
		
		$noExpire = false;
		if ( isset($_SESSION['NOEXPIRE']) )
			$noExpire = $_SESSION['NOEXPIRE'];
          
          $db_timeout = 0;
          if (array_key_exists(HOST_SESS_EXPIRE_PERIOD, $_SESSION)) {
		    $db_timeout = $_SESSION[HOST_SESS_EXPIRE_PERIOD];
	    }

		if ( $db_timeout == SESSION_USE_SYSTEM_TO ) {
			if (SESSION_TIMEOUT > 0) {
				$db_timeout = SESSION_TIMEOUT;
			} else {
				$db_timeout = 0;
				$noExpire = true;
			}
		}
			
		if (!$noExpire && (time() - $lastStamp) > SESSION_TIMEOUT ) {
			session_unset(); 
			session_destroy();
			die( "Session is expired" );
		}

		$DB_KEY = strtoupper($_SESSION["wbs_dbkey"]);
	}

	$DB_NAME = "DB".strtoupper($DB_KEY);

	if ( isset($DB_KEY) )
		loadDatabaseLanguageList($DB_KEY);

	require_once( WBS_DIR."kernel/kernel.php" );

	if ( !( isset( $include_preprocessor ) && !$include_preprocessor ) ) {
		require_once( "printpreproc.php" );
		require_once( "reportfunctions.php" );
	}
?>