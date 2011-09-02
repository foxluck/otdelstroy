<?php

	//
	// Robot script initialization script
	//
	define( 'WEB_CLIENT', 0 );

	$scriptPath = getcwd();

	$WBSPath = $scriptPath."/../../../";

	$rp = realpath($WBSPath);

	define( "WBS_DIR", $rp."/" );

	require_once( WBS_DIR."kernel/wbsinit.php" );

	if ( !loadWBSSettings() )
		die( "Unable to load WBS settings" );

	if ( $argc % 2 == 0 )
		die( "Wrong parameters count." );

	$_ARGS = array();		
		
	for ( $i=1; $i<$argc; $i+=2 )
		$_ARGS[$argv[$i]] = $argv[$i+1];

	if ( !isset( $init_required ) || $init_required != false )
	{
		$DB_KEY = isset( $_ARGS["DB_KEY"] ) ? trim( $_ARGS["DB_KEY"] ) : "";

		if ( !strlen(trim($DB_KEY)) )
			die( "DB_KEY is not set." );

		if ( isset($DB_KEY) )
			loadDatabaseLanguageList($DB_KEY);
	}

	require_once( WBS_DIR."kernel/kernel.php" );

	if ( $hostDataFileError )
		die( "Error loading database profile file or database key is not found" );

?>