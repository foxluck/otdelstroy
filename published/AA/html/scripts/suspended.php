<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//	
	// Authorization
	//

	$SCR_ID = PAGE_ACCOUNT_SUSPENDED;
	
	pageUserAuthorization( $SCR_ID, $AA_APP_ID, true );

	// Page variables setup
	//
	$kernelStrings = $loc_str[$language];

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );
	$preproc->assign( PAGE_TITLE, $kernelStrings['app_pagemessage_title'] );

	$preproc->assign( 'hasAccountInfoAccess', hasAccountInfoAccess( $currentUser ) );
	$preproc->assign( 'resolveLink', "<a class=\"activelink\" href=\"".getExtendLink()."\">".$kernelStrings['ai_extend_btn']."</a>" );

	$preproc->display( "suspended.htm" );
?>