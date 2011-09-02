<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//	
	// Authorization
	//

	$SCR_ID = null;
	
	pageUserAuthorization( $SCR_ID, $AA_APP_ID, true );


	// Page variables setup
	//
	$kernelStrings = $loc_str[$language];

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['app_pagewelcome_title'] );
	$preproc->assign( "adminStr", sprintf($kernelStrings['app_adminstart_text'], "<b>", "</b>") );
	$preproc->display( "adminstartup.htm" );
?>