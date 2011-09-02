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

	@set_time_limit( 3600 );

	$kernelStrings = $loc_str[$language];
	$invalidField = null;
	$done = false;
	$groupValues = array();
	$groupNames = array();

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 : 
				if ( $fileFormat != CSV_CUSTOM_FORMAT ) {
					// Load import scheme from database
					//
					$importScheme = loadFileFormat( USERLIST_FILEFORMATS, $fileFormat, $kernelStrings );
					if ( PEAR::isError($importScheme) ) {
						$errorStr = $importScheme->getMessage();
						$fatalError =  true;

						break;
					}
				} else {
					$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, $language, $kernelStrings, false );
					if ( PEAR::isError($typeDescription) ) {
						$fatalError = true;
						$errorStr = $typeDescription->getMessage();

						break;
					}

					// Obtain columns descriptions as a plain array
					//
					$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, false, true );

					// Assemble scheme
					//
					$importScheme = array();

					$importScheme[CSV_DELIMITER] = base64_decode($separator);
					$importScheme[CSV_IMPORTFIRSLN] = false;

					if ( !isset($includedFields) )
						$includedFields = array();

					$links = array();
					foreach( $includedFields as $fieldId ) {

						$fieldData = $fieldsPlainDesc[$fieldId];

						// Add field data to the link list
						//
						$linkData = array();

						$linkData[CSV_FILEFIELD] = $fieldData[CONTACT_FIELDGROUP_SHORTNAME];
						$linkData[CSV_DBFIELD] = $fieldId;

						$links[$fieldId] = $linkData;
					}

					$importScheme[CSV_LINKS] = $links;
				}

				// Load user list
				//
				switch ( $exportMode ) {
					case 0 :
							// Selected contacts
							//
							if ( !isset($doclist) )
								break 2;

							$userList = array();

							$contacts = unserialize( base64_decode( $doclist ) );
							$contacts = sprintf( "'%s'", implode( "','", $contacts ) );

							$sortStr = "C_ID asc";

							$qr = db_query( sprintf( $qr_selectSpecificContacts, $contacts, $sortStr ), array() );
							while ( $row = db_fetch_array($qr) ) {
								$row = applyContactTypeDescription( $row, array(), $fieldsPlainDesc, $kernelStrings, UL_LIST_VIEW );
								$userList[] = $row;
							}

							db_free_result($qr);

							$filePath = exportCSVFile( $importScheme, $userList, $kernelStrings, false );
							$link = prepareURLStr( PAGE_GETULFILE, array( 'file'=>base64_encode($filePath) ) );
							$done = true;

							break 2;
					case 1 :
							// Selected object
							//
							$userList = array();
	
							$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc, $language );
							$callbackParams = array();

							$objectType = base64_decode( $objType ); 

							if ( $objectType == AA_SEARCH_RESULT ) {
								$searchString = $_SESSION['AA_SEARCHSTRING'];
								$filePath = $ContactCollection->findContacts( $searchString, $currentUser, "C_ID asc", $callbackParams, null, $kernelStrings, true, $importScheme, true );
								if ( PEAR::isError($filePath) ) {
									$errorStr = $filePath->getMessage();

									break 2;
								}
							} else {
								$filePath = $ContactCollection->exportCsvFromUserGroup( $currentSrcId, "C.C_ID asc", $kernelStrings, $importScheme );
							}

							break;
					case 2 :
							// Entire contact list
							//
							$userList = array();

							$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc, $language );
							$callbackParams = array();
							$filePath = $ContactCollection->exportCsvUserAvailableContacts( $currentUser, $qr_namesortclause, $kernelStrings, $importScheme );
				}

				$link = prepareURLStr( PAGE_GETULFILE, array( 'file'=>base64_encode($filePath) ) );
				$done = true;

				break;

		case 1 : redirectBrowser( PAGE_USERSANDGROUPS, array() );
	}

	switch (true) {
		case true : 

					if ( !isset($edited) ) {
						$separator = base64_encode( ";" );
					}

					// Fill file formats list
					//
					$formats = listFileFormats( USERLIST_FILEFORMATS, $kernelStrings );
					if ( PEAR::isError($formats) ) {
						$errorMessage = $formats->getMessage();
						$fatalError = true;

						break;
					}

					$formatIDs = array();
					$formatNames = array();

					foreach( $formats as $key=>$value ) {
						$formatIDs[] = $key;
						$formatNames[] = prepareStrToDisplay($value);
					}

					// Prepare the object combobox content
					//
					$documents = unserialize( base64_decode( $doclist ) );
					$showSelected = count( $documents );

					$objectType = base64_decode( $objType );
					$currentObject = base64_decode( $selectedObj );

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

					if ( !isset($edited) ) {
						if ( $showSelected )
							$exportMode = 0;
						else
							$exportMode = 1;
					}

					// Fill fields list
					//
					$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, $language, $kernelStrings, false );
					if ( PEAR::isError($typeDescription) ) {
						$fatalError = true;
						$errorStr = $typeDescription->getMessage();

						break;
					}

					$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, false, true );

					if ( !isset($edited) ) {

						$includedFields = array();

						foreach ( $fieldsPlainDesc as $key=>$value )
							if ( in_array($value[CONTACT_FIELD_TYPE], $exportingFieldTypes) )
								$includedFields[] = $key; 
						
						$notincludedFields = array();
					} else {
						if ( !isset($notincludedFields) )
							$notincludedFields = array();

						if ( !isset($includedFields) )
							$includedFields = array();

						$notincludedFields = array_diff( array_keys($fieldsPlainDesc), $includedFields );
					}

					foreach( $includedFields as $key=>$value )
						$includedFieldNames[] = sprintf( "%s - %s", $fieldsPlainDesc[$value][CONTACT_FIELDGROUPNAME], $fieldsPlainDesc[$value][CONTACT_FIELDGROUP_LONGNAME] );

					foreach( $notincludedFields as $key=>$value )
						$notincludedFieldNames[] = sprintf( "%s - %s", $fieldsPlainDesc[$value][CONTACT_FIELDGROUPNAME], $fieldsPlainDesc[$value][CONTACT_FIELDGROUP_LONGNAME] );

					// Fill separator list
					//
					$separatorIDs = array();
					$separatorNames = array();

					foreach ( $cm_separators as $key=>$value ) {
						$separatorIDs[] = base64_encode($key);
						$separatorNames[] = $kernelStrings[$value];
					}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['eul_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_EXPORTUSERS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preproc->assign( "done", $done );

		if ( $done ) {
			$preproc->assign( "link", $link );
		}

		$preproc->assign( "formatIDs", $formatIDs );
		$preproc->assign( "formatNames", $formatNames );

		$preproc->assign( "separatorIDs", $separatorIDs );
		$preproc->assign( "separatorNames", $separatorNames );
		$preproc->assign( "separator", $separator );

		$preproc->assign( "doclist", $doclist );
		$preproc->assign( "showSelected", $showSelected );

		if ( $objectType != AA_SEARCH_RESULT ) {
			$preproc->assign( "srcIds", $srcIds );
			$preproc->assign( "srcNames", $srcNames );
			$preproc->assign( "currentSrcId", $currentSrcId );
		}

		$preproc->assign( "selectedObj", $selectedObj );
		$preproc->assign( "objType", $objType );
		$preproc->assign( "objectType", $objectType );

		$preproc->assign( "exportMode", $exportMode );

		$preproc->assign( "notincludedFields", $notincludedFields );
		$preproc->assign( "includedFields", $includedFields );

		if ( isset($includedFieldNames) )
			$preproc->assign( "includedFieldNames", $includedFieldNames );

		if ( isset($notincludedFieldNames) )
			$preproc->assign( "notincludedFieldNames", $notincludedFieldNames );

	}

	$preproc->display( "exportusers.htm" );
?>