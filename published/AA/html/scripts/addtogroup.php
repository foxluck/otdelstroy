<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/AA/aa.php" );

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
	$groupIDs = array();
	$groupNames = array();

	// Decode group identifier
	//
	if ( isset($UG_ID) )
		$activeGroup = base64_decode( $UG_ID );

	//
	// Form handling
	//

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	switch ( $btnIndex ) {
			case 0 :
					// Check if any group is selected
					//
					if ( !strlen($groupID) ) {
						$invalidField = "groupID";
						break;
					}

					// Loop through users and add they to group
					//
					$usersList = unserialize( base64_decode( $USERS ) );
					$updateUI = false;

					foreach ( $usersList as $user ) {
						$user = getContactUser( $user, $kernelStrings );
						if ( !strlen($user) )
							continue;

						if ( $user == $currentUser )
							$updateUI = true;

						$res = registerUserInGroup( $user, $groupID, $kernelStrings );
						if ( PEAR::isError($res) ) {
							$errorStr = $res->getMessage();

							break 2;
						}
					}

					$params = array();
					if ( $updateUI )
						$params["updateAll"] = 1;

					redirectBrowser( PAGE_USERSANDGROUPS, $params );
			case 1 :
					redirectBrowser( PAGE_USERSANDGROUPS, array() );
	}

	//
	// Prepare page data
	//

	switch (true) {
			case true :
				if ( !isset($edited) )
					$groupID = null;

				// Load group list
				//
				$userGroups = listUserGroups( $kernelStrings, false );
				if ( PEAR::isError($userGroups) ) {
					$fatalError = true;
					$errorStr = $userGroups->getMessage();

					break;
				}

				foreach( $userGroups as $key=>$value ) {
					if ( $key == $activeGroup ) 
						continue;

					$groupIDs[] = $key;
					$groupNames[] = $value['UG_NAME'];
				}

	}

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['adg_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_ADDTOGROUP );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preproc->assign( "UG_ID", $UG_ID );
		$preproc->assign( "USERS", $USERS );
		$preproc->assign( "groupID", $groupID );

		$preproc->assign( "groupIDs", $groupIDs );
		$preproc->assign( "groupNames", $groupNames );
	}

	$preproc->display( "addtogroup.htm" );
?>