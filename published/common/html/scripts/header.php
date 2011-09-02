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

	$locStrings = $loc_str[$language];

	$logoPath = getKernelAttachmentsDir();
	$logoPath .= "/logo.gif";
	$showLogo = file_exists($logoPath);

	$firstPage = getUserFirstPage( $currentUser );
	$firstPage = str_replace( "\\", "/", $firstPage );
	$parts = explode( "/", $firstPage );
	$index = count($parts)-4;
	$firstAPP_ID = $parts[$index];

	if ( $firstAPP_ID == $AA_APP_ID && basename($firstPage) == "blank.php" )
		$menuName = null;
	else
		if ( array_key_exists($firstAPP_ID, $global_applications) ) {
			$app = $global_applications[$firstAPP_ID];
			$menuName = $app['APP_UI_NAME'][$language];
		} else
			$menuName = null;

	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $locStrings, $language, strtolower($AA_APP_ID) );
	$preproc->assign( PAGE_TITLE, null );	
	$preproc->assign( "showLogo", $showLogo );	
	$preproc->assign( "startMenuName", $menuName );	
	
	$preproc->display( "frameheader.htm" );
?>