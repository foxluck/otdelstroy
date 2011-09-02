<?php
	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = null;

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, true );
	$kernelStrings = $loc_str[$language];

	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, strtolower("common") );
	$preproc->assign( PAGE_TITLE, $kernelStrings["help_page_name"] );
	
	$domain = ($language == LANG_RUS) ? "webasyst.ru" : "webasyst.net";
	
	$preproc->assign( "domain", $domain );
	$preproc->display( "helpmain.htm" );

?>