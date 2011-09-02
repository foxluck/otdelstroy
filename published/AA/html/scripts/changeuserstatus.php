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

	if ( !isset($searchString) )
		$searchString = null;

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
				if ( !strlen($selectedStatus) ) {
					$invalidField = 'STATUS';
					break;
				}

				$contacts = unserialize( base64_decode($contactlist) );

				foreach ( $contacts as $C_ID ) {
					$targetUser = getContactUser( $C_ID, $kernelStrings );
					if ( !strlen($targetUser) )
						continue;

					$res = setUserActivityStatus( $targetUser, $selectedStatus, $kernelStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();

						break;
					}
				}

				redirectBrowser( PAGE_USERSANDGROUPS, array() );

				break;
		case 1 : 
				redirectBrowser( PAGE_USERSANDGROUPS, array() );
	}


	switch (true) {
		case true :
					$contacts = unserialize( base64_decode($contactlist) );

					$usernumLabel = sprintf( $kernelStrings['cus_usernum_label'], count($contacts) );

					// Prepare status list
					//
					$statusNames = array();
					$statusIDs = array_merge( array(null), array_keys( $commonStatusNames ) );

					$statusNames[] = $kernelStrings['app_select_item'];

					foreach( $commonStatusNames as $key=>$value )
						$statusNames[] = $kernelStrings[$value];

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['cus_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_CHANGEUSERSTATUS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );

	if ( !$fatalError ) {
		$preproc->assign( "contactlist", $contactlist );
		$preproc->assign( "usernumLabel", $usernumLabel );

		$preproc->assign( "statusNames", $statusNames );
		$preproc->assign( "statusIDs", $statusIDs );

		if ( isset($selectedStatus) )
			$preproc->assign( "selectedStatus", $selectedStatus );
	}

	$preproc->display( "changeuserstatus.htm" );
?>