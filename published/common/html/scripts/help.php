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

	$helpFilePath = WBS_DIR."published/MW/help/$language/mw.html";
	$mwLang = $language;
	if ( !file_exists($helpFilePath) ) {
		$mwLang = "eng";
	}


	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, strtolower("common") );
	$preproc->assign( PAGE_TITLE, "WebAsyst Help" );
	if (!empty($selectedApp))
		$preproc->assign ( "selectedApp", $selectedApp); 
	if (!empty($section))
		$preproc->assign ( "section", $section); 
	$preproc->assign( "mwLang", $mwLang );

	$preproc->display( "help.htm" );

?>