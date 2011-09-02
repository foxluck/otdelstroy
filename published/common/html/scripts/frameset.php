<?php

	$commonAppScript = true;
	require_once( "../../../common/html/includes/httpinit.php" );

	//	
	// Authorization
	//

	$SCR_ID = "";
	$fatalError = false;
	$errorStr = null;

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, true );

	if ( $fatalError )
		redirectBrowser( PAGE_LOGIN, array() ); 

	// 
	// Page variables setup
	//
	$locStrings = $loc_str[$language];

	$firstPage = getUserFirstPage( $currentUser );

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, "aa" );
	$preproc->assign( "firstPage", $firstPage );
	$preproc->display("frameset.htm");
?>