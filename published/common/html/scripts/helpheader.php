<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//	
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = null;

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, true );

	$kernelStrings = $loc_str[$language];

	$appList = sortAppScreenList( listUserScreens( $currentUser ) );

	$appLinks = array();

	/*
	$helpLinkParams = array();

	$basicsLang = $language;
	$helpFilePath = WBS_DIR."published/common/help/$language/basics.html";
	if ( !file_exists($helpFilePath) )
		$basicsLang = "eng";

	$helpLinkParams['APP_NAME'] = $kernelStrings['hlp_basics_title'];
	$helpLinkParams['LINK'] = "../../../common/help/$basicsLang/basics.html";

	$appLinks[] = $helpLinkParams;
	*/

	$selectedItem = 0;
	foreach ( $appList as $key=>$value ) {

		//if ( $key == "MW" )
		//	continue;

		$lowerID = strtolower($key);
		$helpFilePath = WBS_DIR."published/$key/help/$language/$lowerID.html";
		$appLang = $language;
		if ( !file_exists($helpFilePath) ) {
			$helpFilePath = WBS_DIR."published/$key/help/eng/$lowerID.html";
			$appLang = "eng";

			if ( !file_exists($helpFilePath) )
				continue;
		}

		$helpLinkParams = array();
		
		if ( isset($global_applications[$key][APP_NAME][$language]) )
			$helpLinkParams['APP_NAME'] = $global_applications[$key][APP_NAME][$language];
		else
			$helpLinkParams['APP_NAME'] = $global_applications[$key][APP_NAME][LANG_ENG];

		$helpLinkParams['LINK'] = "../../../$key/help/$appLang/$lowerID.html";
		
		if (!empty($selectedApp) && $key == $selectedApp) {
			$selectedItem = sizeof($appLinks) ;
		}		
		
		$appLinks[] = $helpLinkParams;
	}
	
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, strtolower("common") );

	$preproc->assign( "appLinks", $appLinks );
	$preproc->assign( "selectedItem", $selectedItem );
	if (!empty($section))
		$preproc->assign ( "section", $section); 
	
	$preproc->display( "helpheader.htm" );

?>