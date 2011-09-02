<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//
	
	$errorStr = null;
	$fatalError = false;

	pageUserAuthorization( null, $AA_APP_ID, true );
	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['app_pagewelcome_title'] );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "curAPP_ID", "common" );
	$preproc->assign( HELP_TOPIC, "whatiswbs.htm");

	$preproc->display( "blank.htm" );
?>