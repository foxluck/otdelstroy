<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "CP";

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$currencyList = array();

	$btnIndex = getButtonIndex( array( "savebtn" ), $_POST );

	switch ( $btnIndex ) {
		case 0 : {

			break;
		}
	}

	switch( true ) {
			case true: {


			}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['sms_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_MODULES );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "");


	if ( !$fatalError ) {
	}

	$preproc->display("sms.htm" );
?>
