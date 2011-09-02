<?php

	header('Content-Type: text/html; charset=UTF-8;');

	//
	// HTML client initialization script
	//
	define( 'WEB_CLIENT', 1 );
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

	if ( !( isset( $loginScript ) && $loginScript ) )
		$WBSPath = $scriptPath."/../../../../";
	else
		$WBSPath = $scriptPath."/../";

	if ( array_key_exists( "appListToLoad", $_POST) ||
		array_key_exists( "appListToLoad", $_GET) ||
		(isset($_SESSION ) && array_key_exists( "appListToLoad", $_SESSION) ) )
		die( "Invalid script parameters" );

	if ( !isset( $appListToLoad ) )
		$appListToLoad = null;

	if ( !ini_get('magic_quotes_gpc') )
		$WBSPath = addSlashes( $WBSPath );

	$rp = realpath($WBSPath);

	if (!empty($_GET["ajaxAccess"]))
		define ("EXPIRED_PAGE_HEADER", 'Location: ../../../common/html/scripts/expired.php?redirect=1&ajaxAccess=1');
	elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))
		define ("EXPIRED_PAGE_HEADER", 'Location: ../../../common/html/scripts/expired.php?redirect=1&ajaxRequest=1');
	else
		define ("EXPIRED_PAGE_HEADER", 'Location: ../../../common/html/scripts/expired.php?redirect=1');

    if (!$rp) {
        $rp = realpath(dirname(__FILE__)."/../../../../");
    }
	define( "WBS_DIR", $rp."/" );
	require_once( WBS_DIR."kernel/wbsinit.php" );
	

	require_once( WBS_DIR."system/init.update.php" );
	$update = false;

	if ( !loadWBSSettings() )
		die( "Unable to load WBS settings" );

	function debugSessionLog( $str )
	{
		$fh = @fopen( WBS_DIR."published/common/html/session.log", "a" );
		@fwrite( $fh, $str."\n" );
		@fclose($fh);
	}

	if ( array_key_exists( "get_key_from_url", $_POST) ||
		array_key_exists( "get_key_from_url", $_GET) ||
		(isset($_SESSION ) && array_key_exists( "get_key_from_url", $_SESSION) ) )
		die( "Invalid script parameters" );

	if ( !isset( $get_key_from_url ) )
		$get_key_from_url = false;

	if ( !$get_key_from_url) {

		if (!empty($HTTP_COOKIE_VARS["onbrowsercloseexpire"])) {
			ini_set( 'session.cookie_lifetime', 0);
			session_set_cookie_params( 0 );
		} else {
			ini_set( 'session.cookie_lifetime', 2592000 );
			session_set_cookie_params( 2592000 );
		}
		ini_set( 'session.use_only_cookies', 0 );

		if ( !(isset($allow_page_caching) && !$allow_page_caching) )
			ini_set( 'session.cache_limiter', 'nocache' );
		else
			ini_set( 'session.cache_limiter', 'public' );

		if ( !( isset( $init_required ) && !$init_required ) ) {
			$session_started = ini_get( 'session.auto_start' );

			if ( !$session_started && empty($loginScript)) {
				@session_start();
			}

			if ( !isset($_SESSION['timestamp']) ) {
				header( EXPIRED_PAGE_HEADER );
				die();
			}

			$lastStamp = $_SESSION['timestamp'];

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

			if ( !$noExpire && strlen($db_timeout) && (time() - $lastStamp) > $db_timeout ) {
				@session_unset();
				@session_destroy();
				header( EXPIRED_PAGE_HEADER );
				die();
			} else {
				$_SESSION['timestamp'] = time();
			}

			$DB_KEY = strtoupper($_SESSION["wbs_dbkey"]);

			if ( !isset($DB_NAME) || !strlen($DB_NAME) ) {
				if ( !strlen(trim($DB_KEY)) ) {
					header( EXPIRED_PAGE_HEADER );
					die();
				}

				$DB_NAME = "DB".strtoupper($DB_KEY);
			}
		}
	} else {

		if ( isset($_GET['DB_KEY']) )
		    $DB_KEY = base64_decode($_GET['DB_KEY']);
		else
		if ( isset($_POST['DB_KEY']) )
			$DB_KEY = base64_decode($_POST['DB_KEY']);

		if ( !strlen(trim($DB_KEY)) ) {
			header( EXPIRED_PAGE_HEADER );
			die();
		}

		$DB_NAME = "DB".strtoupper($DB_KEY);
	}
	if (Wbs::loadCurrentDBKey()) {
		$update = true;
	}

	if ((Wbs::isHosted()||true) && $update) {
	    $updater = new WbsUpdater("SYSTEM");
		$updater->check();
	}
	
	if ( isset($DB_KEY) )
		loadDatabaseLanguageList($DB_KEY);	
		
	require_once( WBS_DIR."kernel/kernel.php" );

	if (Wbs::isHosted()) {
		$expire_date=strtotime($databaseInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE]);
		if ($expire_date > 0 && $expire_date < time() && file_exists(WBS_DIR."kernel/includes/expired.htm")) {
			header("HTTP/1.0 200 OK");
     die(file_get_contents(WBS_DIR."kernel/includes/expired.htm"));
		}
	}
	if ( $hostDataFileError )
		die( "Error loading database profile file or database key is not found" );

	require_once( "preproc.php" );
	require_once( "httpcommon.php" );

	// Free accounts shouldn't use https
	if(isset($_SERVER['HTTPS']) && isset($databaseInfo[HOST_DBSETTINGS]['PLAN']) && $databaseInfo[HOST_DBSETTINGS]['PLAN'] == HOST_DEFAULT_PLAN) {
		if (onWebAsystServer() && basename($_SERVER["SCRIPT_NAME"]) != 'proceed_pay.php' && basename($_SERVER["SCRIPT_NAME"]) != 'proceed_pay_domains.php') {
			$q = explode("&", $_SERVER["QUERY_STRING"]); // Manually create GET params, because widgets & links mix up _GET array
			$params = array();
			if (count($q)) {
				foreach ($q as $val) {
					list ($a, $b) = explode("=", $val, 2);
					if (!empty($a)) $params[$a] = $b;
				}
			}
			redirectBrowser('http://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], $params, '', true, true, false, true);
		}
	}

	$ccUrl = getCCURL();
	define('URL_MYWEBASYST', $ccUrl );

	define('URL_UPGRADE', $ccUrl.'?ukey=wahost_update&DBKEY=%s');
	define('URL_REGISTER', $ccUrl.'?ukey=wahost_signup&DBKEY=%s&LOGIN=%s&LANGUAGE=%s');
	define('URL_CONFIRMINFO', '../../../AA/html/scripts/confirm_info.php');

	define('URL_EXTEND', $ccUrl.'?ukey=wahost_update&DBKEY=%s');
	define('URL_CANCEL', $ccUrl.'?ukey=wahost_cancel&wa=1&DBKEY=%s');


?>