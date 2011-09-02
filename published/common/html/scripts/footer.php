<?php

	$commonAppScript = true;
	require_once( "../includes/httpinit.php" );

	//	
	// Authorization
	//

	$SCR_ID = "";
	$fatalError = false;
	$errorStr = null;

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, true );

	if ( $fatalError )
		die();

	// 
	// Page variables setup
	//
	$locStrings = $loc_str[$language];

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, strtolower($AA_APP_ID) );
	$preproc->display( "footer.htm" );
?>