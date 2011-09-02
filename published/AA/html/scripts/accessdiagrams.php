<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/AA/aa.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "ARD";

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];

	$invalidField = null;

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['rp_accessrighsdiags_title'] );
	$preproc->assign( FORM_LINK, PAGE_ACCESSDIAGRAMS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( "kernelStrings", $kernelStrings );

	if ( !$fatalError ) {

	}

	$preproc->display( "accessdiagrams.htm" );
?>