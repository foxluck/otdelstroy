<?php

	/*
	 *
	 * Common reports routines
	 *
	 */

	header ("Cache-Control: no-cache, must-revalidate"); 
	header ("Pragma: no-cache");

	/*
	 * Common report constants
	 */

	define( "REPORT_TITLE", "pageTitle" );
	define( "SCRIPT_NAME", "scriptName" );
 	define( "ERROR_STR", "errorStr" );
	define( "FATAL_ERROR", "fatalError" );

	/*
	 * Extracting variables
	 */

	extract( $_GET );
	extract( $_POST );

	function reportUserAuthorization( $SCR_ID, $APP_ID, $public )
	//
	// Perform authorization of the user in the report specified.
	//		Fill in $errorStr and $fatalError variables if access denied.
	//
	//		Parameters:
	//			$SCR_ID - page identifier
	//			$APP_ID - application identifier
	//			$public - common access report
	//
	//		Returns: null
	//
	{
		global $_GET;
		global $_SESSION;
		global $language;
		global $errorStr;
		global $fatalError;
		global $loc_str;

		$SID_VAR = ini_get( 'session.name' );
		$session_started = ini_get( 'session.auto_start' );

		if ( isset($_GET[$SID_VAR]) && strlen($_GET[$SID_VAR]) ) {
			if ( !$session_started ) {
				ini_set( "session.use_cookies", 0 );
				session_id( $_GET[$SID_VAR] );
				session_start();
			}
			$U_ID = @$_SESSION[WBS_USERNAME];
		} else
			$U_ID = $_SESSION[WBS_USERNAME];

		$code = initReport( $U_ID, $SCR_ID, $APP_ID, $public );
		if ( $code != 1 ) {
			if (!strlen($language))
				$language = LANG_ENG;

			if (!strlen($templateName))
				$templateName = HTML_DEFAULT_TEMPLATE;

			$errorStr = $loc_str[$language][ERR_GENERALACCESS];
			$fatalError = true;
		}

		return null;
	}

	function initReport( $U_ID, $SCR_ID, $APP_ID, $public )
	// 
	//  Checks if user logged in and hava an access to report specified.
	//		Initialization of variables $currentUser, $language, $templateName, $styleSet, $html_encoding
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$SCR_ID - page identifier
	//			$APP_ID - application identifier
	//			$public - common access page
	//
	// 	Returns
	//		0, if user is not logged in
	//		-1 - if user doesn't have access to page
	//		1 - if user logged in and have an access to page
	//	
	{ 
		global $currentUser;
		global $language;
		global $html_encoding;

		$U_ID = strtoupper( $U_ID );
		$currentUser = $U_ID;
		$html_encoding = getUserEncoding( $U_ID );
		
		$userGlobalSettings = array();
		$userGlobalSettings = readUserSummaryCommonSysSettings( $currentUser, $loc_str[$language] );

		if ( !strlen($U_ID) ) 
			return 0;

		if ( !checkUserAccessRights( $U_ID, $SCR_ID, $APP_ID, $public) ) 
			return -1;

		if ( $U_ID == ADMIN_USERNAME ) {
			$adminInfo = loadAdminInfo();

			$language =	$adminInfo[LANGUAGE];
		}	
		else {		
			$language = readUserCommonSetting( $U_ID, LANGUAGE );
		}

		$language = getApplicationLanguage( $APP_ID, $language );

		return 1;
	}

?>