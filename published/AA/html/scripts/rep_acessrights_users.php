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

	$included_users_names = array();
	$notincluded_users_names = array();

	$btnIndex = getButtonIndex( array( BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
				redirectBrowser( PAGE_REPORTS, array() );

				break;
	}


	switch (true) {
		case true :
					$statusList = array( RS_ACTIVE, RS_LOCKED );
					$systemUsers = listSystemUsers( $statusList, $kernelStrings );
					if ( PEAR::isError($systemUsers) ) {
						$fatalError = true;
						$errorStr = $systemUsers->getMessage();

						break;
					}

					$fullUserListIDs = array_keys($systemUsers);

					$notincluded_users = array();

					if ( !isset($edited) )
						$included_users = array();
					else
						if ( isset($edited) && !isset($included_users) )
							$included_users = array();

					$notincluded_users = array_diff( $fullUserListIDs, $included_users );

					foreach( $included_users as $key )
						$included_users_names[] = getArrUserName( $systemUsers[$key] );

					foreach( $notincluded_users as $key )
						$notincluded_users_names[] = getArrUserName( $systemUsers[$key] );
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
		$preproc->assign( "included_users", $included_users );
		$preproc->assign( "notincluded_users", $notincluded_users );

		$preproc->assign( "included_users_names", $included_users_names );
		$preproc->assign( "notincluded_users_names", $notincluded_users_names );
	}

	$preproc->display( "rep_acessrights_users.htm" );
?>