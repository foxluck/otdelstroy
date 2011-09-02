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

	$btnIndex = getButtonIndex( array( BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
				redirectBrowser( PAGE_USERSANDGROUPS, array() );
	}


	switch (true) {
		case true :
					$contacts = unserialize( base64_decode($contactlist) );

					$typeDesc = getContactTypeDescription( CONTACT_BASIC_TYPE, $language, $kernelStrings, false );
					if ( PEAR::isError($typeDesc) ) {
						$fatalError = true;
						$errorStr = $typeDesc->getMessage();

						break;
					}

					$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDesc, $kernelStrings, true );

					$result = array();

					foreach ( $contacts as $C_ID ) {
						$Contact = new Contact( $kernelStrings, $language, $typeDesc, $fieldsPlainDesc );

						$res = $Contact->loadEntry( $C_ID, $kernelStrings );
						if ( PEAR::isError($res) ) {
							$errorStr = $res->getMessage();

							break;
						}

						$contactName = sprintf( "%s (%s)", getArrUserName( (array)$Contact, true ), $Contact->U_ID );

						$res = $Contact->revokeUserPrivileges( $C_ID, $kernelStrings, $currentUser );
						if ( PEAR::isError($res) )
							$result[$contactName] = $res->getMessage();
						else
							$result[$contactName] = $kernelStrings['rup_success_message'];
					}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['rup_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_REVOKEUSERPRIVS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );

	if ( !$fatalError ) {
		$preproc->assign( "contactlist", $contactlist );
		$preproc->assign( "result", $result );
	}

	$preproc->display( "revokeprivs.htm" );
?>