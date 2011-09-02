<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/AA/aa.php" );
	
	$url = "http://".getenv("HTTP_HOST").'/AA/html/scripts/change_plan.php'; 
	header("Location: ".$url);
	exit();
	
/*	$isSSL = isset($_SERVER['HTTPS']); 
	if (!$isSSL) {
		$currentUrl = getenv("HTTP_HOST") . getenv("REQUEST_URI");
		header("Location: https://" . $currentUrl);
		exit;
		//$currentUrl = 
	}
		
	
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
	
	//
	// Page implementation
	//
	
	$afterAction = (!empty($after_action)) ? $after_action : "";
	if ($action == "addapps") {
		include_once ("cc_after.php");
		$ccUrl = getCCLinkWithAuth( $DB_KEY, '?ukey=wahost_waupdate&step=add_apps&DBKEY='.base64_encode($DB_KEY) . "&direct=1&after_action=" . $afterAction . '&suri='.base64_encode(getenv("SCRIPT_URI"))) ;	
	}
	elseif($action == "cancelaccount")
		$ccUrl = getCCLinkWithAuth( $DB_KEY, '?ukey=wahost_cancel&noframe=1&wa=1&DBKEY='.base64_encode($DB_KEY));	
	else
		$ccUrl = getCCLinkWithAuth( $DB_KEY, '?ukey=wahost_waupdate&DBKEY='.base64_encode($DB_KEY));

	$pageTitle = $kernelStrings["cc_" . $action . "_page_title"];	
	if (!$pageTitle)
		$pageTitle = $kernelStrings['aa_screen_long_name'] ;
	
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );
	
	$preproc->assign( "ccUrl", $ccUrl );
	$preproc->assign( PAGE_TITLE, $pageTitle);
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	
	$preproc->display( "cc.htm" );*/
?>