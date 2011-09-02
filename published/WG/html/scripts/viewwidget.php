<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	
	require_once( WBS_DIR."/published/WG/wg.php" );
	require_once( "_WidgetsPageBuilder.php" );
	
	// Authorization
	//
	$SCR_ID = "WG";
	pageUserAuthorization( $SCR_ID, $WG_APP_ID, true, true);

	if (!$language) {
	    $language = 'eng';
	}
	// Global wars
	//
	$kernelStrings = &$loc_str[$language];
	$wgStrings = &$wg_loc_str[$language];
		
	// Page bulding
	//	
	$dataManager = new WidgetManager ($kernelStrings, $wgStrings);
	$pageBuilder = new WidgetsPageBuilder($WG_APP_ID, $dataManager);
	
	$pageBuilder->prepareWidgetPage ();


	$pageBuilder->setAjaxVersion (array("tpl" => "viewwidget.rightpanel.htm", "toolbar_content_tpl" => "empty_toolbar.htm"));
 
	if (!empty($interface_wrapper) || Env::Get('fapp') == 'CM') {
		$pageBuilder->preproc->assign ("interface_wrapper", true);
	}

	$pageBuilder->displayPage ("viewwidget.htm");
?>