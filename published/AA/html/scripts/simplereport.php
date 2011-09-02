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

	$preproc->assign( PAGE_TITLE, $kernelStrings['app_pagemessage_title'] );

	$preproc->assign( "infoStr", urldecode(base64_decode($infoStr)) );
	if ( isset( $updateAll ) )
		$preproc->assign( "updateAll", $updateAll );

	if ( isset( $setTitle ) ) {
		$preproc->assign( "setTitle", $setTitle );
		$preproc->assign( "title", base64_decode($title) );
	}

	if ( isset( $reportType ) )
		$preproc->assign( "reportType", $reportType );
	else
		$preproc->assign( "reportType", 1 );

	$preproc->display( "simplereport.htm" );
?>