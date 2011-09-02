<?php

	//
	// WBS initialization script
	//

	define( 'PEAR_PATH', realpath(WBS_DIR.'kernel/includes/pear') );
	$paths = array(realpath( PEAR_PATH ),realpath( PEAR_PATH.'/PEAR' ));
	set_include_path( implode(((DIRECTORY_SEPARATOR=='/')?':':';'),$paths) );

	require_once "DB.php";
	require_once "PEAR.php";
	require_once "Date.php";
	
	//
	//require_once "Archive/Tar.php";

	require_once "XML/HTMLSax3.php";
	require_once "includes/safehtml.php";
	require_once "includes/locloader.class.php";
	require_once( "sysconsts.php" );
	require_once( "functions.php" );
	require_once( "classes/class.metric.php" );
	
	if(onWebAsystServer())require_once("limit_functions.php");

	require_once( WBS_SMARTY_DIR.'/Smarty.class.php');

	if ( !( isset($wbsSettingsLoaded) && $wbsSettingsLoaded ) ) {
		require_once( WBS_DIR."kernel/wbsinit.php" );

		if ( !loadWBSSettings() )
			die( "Unable to load WBS settings" );
	}

	require_once( "classes.php" );
	require_once( "modules.php" );
	require_once( "quotas.php" );

	// added by timur-kar 
	if (!defined ("NOT_CACHE_MODULES") && $modules = getGlobalCacheValue ("WBSMODULES", "modules")) {
		$WBS_MODULES = $modules;
	} else {
		$WBS_MODULES = new wbsModules();

		$WBS_MODULES->load();
		if (!defined ("NOT_CACHE_MODULES")) {
			setGlobalCacheValue ("WBSMODULES", "modules", $WBS_MODULES);
		}
	}
	
	$hostDataFileError = false;

	if ( !( isset($init_required) && !$init_required ) )
		{
		if ( PEAR::isError($databaseInfo = loadHostDataFile($DB_KEY, null) ) )
			$hostDataFileError = true;
		}

	if ( !$hostDataFileError ) {
		require_once( "dbaccess.php" );
		require_once( "database.php" );
		require_once( "dbfunctions.php" );
		require_once( "classes.php" );
		require_once( "dbfunctions_cmn.php" );
		require_once( "queries.php" );
		require_once( "queries_cmn.php" );
		require_once( "sms.php" );
		if (!defined('NEW_CONTACT')) {
			require_once( "contacts.php" );
		}
		require_once( WBS_DIR."published/common/soap/includes/soaprobots.php" );

		systemInit();

		require_once( "ur_manager.php" );
		require_once( "ur_screens.php" );
	}

	set_magic_quotes_runtime( 0 );

?>