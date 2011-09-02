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
	$groupValues = array();
	$groupNames = array();

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 : ;
		case 1 : redirectBrowser( PAGE_USERSANDGROUPS, array() );
	}

	switch (true) {
		case true : 
					// Prepare the object combobox content
					//
					$documents = unserialize( base64_decode( $doclist ) );
					$showSelected = count( $documents );

					$objectType = base64_decode( $objType );
					$currentObject = base64_decode( $selectedObj );

					if ( $objectType != AA_SEARCH_RESULT ) {
						$groups = listUserGroups( $kernelStrings );
						if ( PEAR::isError($groups) ) {
							$fatalError = true;
							$errorStr = $groups->getMessage();

							break;
						}

						$srcIds = array_keys($groups);
						$srcNames = array();

						foreach ( $groups as $UG_ID=>$groupData )
							$srcNames[] = $groupData['UG_NAME'];

						if ( !isset($edited) )
							$currentSrcId = $currentObject;

					}


					if ( !isset($edited) ) {
						if ( $showSelected )
							$printMode = 0;
						else
							$printMode = 1;
					}

					$printURL = prepareURLStr( "../../reports/userlist.php", array() );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['pcl_print_title'] );
	$preproc->assign( FORM_LINK, $printURL );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preproc->assign( "doclist", $doclist );
		$preproc->assign( "showSelected", $showSelected );
		$preproc->assign( "printMode", $printMode );

		if ( $objectType != AA_SEARCH_RESULT ) {
			$preproc->assign( "srcIds", $srcIds );
			$preproc->assign( "srcNames", $srcNames );
			$preproc->assign( "currentSrcId", $currentSrcId );
		}

		$preproc->assign( "selectedObj", $selectedObj );
		$preproc->assign( "objType", $objType );
		$preproc->assign( "objectType", $objectType );
	}

	$preproc->display( "print.htm" );
?>