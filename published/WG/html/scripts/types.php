<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/WG/wg.php" );
	require_once( "_WidgetsPageBuilder.php" );
	
	// Authorization
	//
	$SCR_ID = "WG";
	pageUserAuthorization( $SCR_ID, $WG_APP_ID, true, true);
	
	// Global wars
	//
	$kernelStrings = &$loc_str[$language];
	$wgStrings = &$wg_loc_str[$language];
	
	// Page bulding
	//	
	$dataManager = new WidgetManager ($kernelStrings, $wgStrings);
	$pageBuilder = new WidgetsPageBuilder($WG_APP_ID, $dataManager);
	
	$pageBuilder->prepareTypesPage ();
	$pageBuilder->displayPage("types.htm");
	
?>