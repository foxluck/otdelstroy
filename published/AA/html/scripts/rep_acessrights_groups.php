<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "UNG";

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$invalidField = null;

	$included_groups_names = array();
	$notincluded_groups_names = array();

	$btnIndex = getButtonIndex( array( BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
				redirectBrowser( PAGE_REPORTS, array() );

				break;
	}


	switch (true) {
		case true :
					$groups = listUserGroups( $kernelStrings, false );
					if ( PEAR::isError($groups) ) {
						$fatalError = true;
						$errorStr = $groups->getMessage();

						break;
					}

					foreach( $groups as $UG_ID=>$groupData )
						$groups[$UG_ID] = $groupData[UG_NAME];

					$fullGroupListIDs = array_keys($groups);

					$notincluded_groups = array();

					if ( !isset($included_groups) )
						$included_groups = array();

					$notincluded_groups = array_diff( $fullGroupListIDs, $included_groups );

					foreach( $included_groups as $key )
						$included_groups_names[] = $groups[$key];

					foreach( $notincluded_groups as $key )
						$notincluded_groups_names[] = $groups[$key];
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['rp_accessrightsgroups_title'] );
	$preproc->assign( FORM_LINK, PAGE_ACCESSRIGHTS_REP_GROUPS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );

	if ( !$fatalError ) {
		$preproc->assign( "included_groups", $included_groups );
		$preproc->assign( "notincluded_groups", $notincluded_groups );

		$preproc->assign( "included_groups_names", $included_groups_names );
		$preproc->assign( "notincluded_groups_names", $notincluded_groups_names );
	}

	$preproc->display( "rep_acessrights_groups.htm" );
?>