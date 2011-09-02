<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "ARD";

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$invalidField = null;

	switch ( true ) {
		case true :

				$users = implode( ",", $included_users );
				$data = array( UR_PATH=>'/ROOT', UR_ACTION=>UR_ACTION_VIEWUSER, UR_ID=>$users, UR_FIELD=>"data" );

				$ret = $UR_Manager->RenderPath( $data );
	}


	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['rp_accessrightsusers_title'] );
	$preproc->assign( FORM_LINK, PAGE_ACCESSRIGHTS_REP_USERS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );

	if ( !$fatalError ) {
		$preproc->assign( "renderData", $ret );

	}

	$preproc->display( "rep_accessrightsusrrep.htm" );
?>