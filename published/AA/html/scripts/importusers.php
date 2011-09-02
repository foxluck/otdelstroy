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

	ini_set( 'max_execution_time', 3600 );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$invalidField = null;
	$folderIDs = array();
	$folderNames = array();
	$saveFormatError = false;
	$grantuserprivs = true;

	$included_groups_names = array();
	$notincluded_groups_names = array();

	define( "CSV_SELECT_U_ID", "SELECT_U_ID" );

	//
	// Helper functions
	//

	function addStackMessage( $stack, $message, $user )
	{
		$item = array();

		if ( strlen($user) ) {
			$item['msg'] = $message;
			$item['user'] = $user;
		} else {
			$item['msg'] = $message;
			$item['user'] = null;
		}

		$stack[] = $item;

		return $stack;
	}

	function importContacts( $filePath, $importScheme, $kernelStrings, $folderID, &$imported, &$errorNum, $grantPrivs, $groups, $status )
	{
		global $language;
		global $currentUser;
		global $userCommonSysSettingsDefaults;

		$imported = 0;

		// Apply import scheme to the file content
		//
		$content = applyCSVImportScheme( $filePath, $importScheme, $kernelStrings );

		// Load type description
		//
		$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, $language, $kernelStrings, false );
		if ( PEAR::isError($typeDescription) ) {
			$fatalError = true;
			$errorStr = $typeDescription->getMessage();

			break;
		}

		// Obtain columns descriptions as a plain array
		//
		$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, false );

		// Obtain current user name
		//
		$thisUserName = getUserName( $currentUser );

		// Add contacts
		//
		$errorStack = array();

		foreach( $content as $contactData )
		{
			$preparedData = prepareArrayToStore($contactData);
			$preparedData['C_MODIFYDATETIME'] = convertToSqlDateTime( time() );
			$preparedData['C_MODIFYUSERNAME'] = $thisUserName;
			$preparedData['C_CREATEDATETIME'] = convertToSqlDateTime( time() );
			$preparedData['C_CREATEUSERNAME'] = $thisUserName;

			// Add missed contact fields
			//
			foreach( $fieldsPlainDesc as $key=>$value )
				if ( !isset($preparedData[$key]) )
					$preparedData[$key] = null;

			// Prepare contact fields to storing
			//
			fixContactInputValues( $preparedData, $fieldsPlainDesc, $kernelStrings );

			// Add contact
			//
			$Contact = new Contact( $kernelStrings, $language, $typeDescription, $fieldsPlainDesc );

			if ( !$grantPrivs )
				$res = $Contact->addModContact( ACTION_NEW, $folderID, $preparedData, $kernelStrings );
			else
			{
				$diskquotas = array();
				$preparedData['U_PASSWORD1'] = null;
				$preparedData['U_STATUS'] = $status;
				foreach ( $userCommonSysSettingsDefaults as $key=>$value )
					$preparedData[$key] = $value;

				$res = $Contact->addModUser( $currentUser, ACTION_NEW, $diskquotas, $language, $folderID, $preparedData, true, $kernelStrings, true );
				if ( !PEAR::isError($res) )
				{
					foreach( $groups as $UG_ID )
						registerUserInGroup( trim( strtoupper($preparedData['U_ID']) ), $UG_ID, $kernelStrings );
				}
			}

			if ( PEAR::isError($res) )
			{
				$errorNum++;

				// Extract contact name identifier
				//
				$contName = df_contactname($preparedData);

				// Extract invalid field
				//
				$invFieldData = $res->getUserInfo();
				if ( strlen($invFieldData) && $invFieldData != 'U_ID|USER' )
				{
					$invFieldData = explode( '|', $invFieldData );
					$invField = $invFieldData[0];

					// Process invalid field
					//

					if ( array_key_exists($invField, $fieldsPlainDesc) ) {
						if ( $res->getCode() == ERRCODE_INVALIDFIELD || $res->getCode() == ERRCODE_INVCONTACTFIELD ) {
							$fieldName = $fieldsPlainDesc[$invField][CONTACT_FIELDGROUP_LONGNAME];
							$err = sprintf($kernelStrings['icl_importinvfield_message'], $fieldName);
							$errorStack = addStackMessage( $errorStack, $err, $contName );
						} else
							$errorStack = addStackMessage( $errorStack, $res->getMessage(), $contName );
					}
				}
				else
				{

					if ( $invFieldData != 'U_ID|USER' || ($invFieldData == 'U_ID|USER' && $grantPrivs && strlen($preparedData['U_ID'])) )
						$errorStack = addStackMessage( $errorStack, $res->getMessage(), $contName );
					else {
						$err = sprintf($kernelStrings['iul_importinvfield_message'], $kernelStrings['icl_userid_item']);
						$errorStack = addStackMessage( $errorStack, $err, $contName );
					}
				}
			}
			else
			{
				$imported++;
			}
		}

		return $errorStack;
	}

	function getFileHeaders( $destPath, &$kernelStrings )
	{
		$csv_separator = getCSVSeparator( $destPath, $kernelStrings );
		if ( PEAR::isError($csv_separator) )
			return PEAR::raiseError( $csv_separator );

		return getCSVHeaders( $destPath, $csv_separator, $kernelStrings );
	}

	// Form handling
	//

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL, 'managetemplatesbtn'), $_POST );

	switch ($btnIndex) {
		case 0 :
					if ( $csv_step == CSV_STEP_LOADFILE ) {

						// Process file uploading form
						//
						if ( (!isset($unsortedContacts) || !$unsortedContacts) && $folderID != UNSORTED_FOLDER_NAME ) {
							if ( ($folderID == -3 || !strlen($folderID)) ) {
								$invalidField = 'folderID';
								$errorStr = $kernelStrings['app_requiredfields_message'];
								break;
							}

							// Check user rights
							//
							if ( !isAdministratorID($currentUser) ) {
								$rights = $cm_groupClass->getIdentityFolderRights( $currentUser, $folderID, $kernelStrings );
								if ( PEAR::isError($rights) ) {
									$errorStr = $rights->getMessage();

									break;
								}

								if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
								{
									$errorStr = $kernelStrings['amc_noaddrights_message'];
									$invalidField = 'folderID';

									break;
								}
							}
						}

						// Validate form data
						//
						if ( !isset($file['name']) || !strlen($file['name']) ) {
							$invalidField = 'FILE';
							$errorStr = $kernelStrings['icl_selectfile_message'];

							break;
						}

						// Upload file to the temporary dir
						//
						$tmpFileName = uniqid( TMP_FILES_PREFIX );
						$destPath = WBS_TEMP_DIR."/".$tmpFileName;

						if ( !@move_uploaded_file( $file['tmp_name'], $destPath ) ) {
							$errorStr = $kernelStrings['icl_erruploading_message'];

							break;
						}

						// Import custom file
						//
						if ( $fileFormat == CSV_CUSTOM_FORMAT ) {
							// Load file headers
							//
							$csv_separator = getCSVSeparator( $destPath, $kernelStrings );
							if ( PEAR::isError($csv_separator) ) {
								$errorStr = $csv_separator->getMessage();

								break;
							}

							$headers = getCSVHeaders( $destPath, $csv_separator, $kernelStrings );
							if ( PEAR::isError($headers) ) {
								$errorStr = $headers->getMessage();

								break;
							}

							//
							// Prepare database scheme
							//
							$dbScheme = array();

							// Load type description
							//
							$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, $language, $kernelStrings, false );
							if ( PEAR::isError($typeDescription) ) {
								$fatalError = true;
								$errorStr = $typeDescription->getMessage();

								break;
							}

							// Obtain columns descriptions as a plain array
							//
							$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, false );

							// Add the User Identifier to the scheme
							//
							if ( isset($grantuserprivs) && $grantuserprivs ) {
								$dbSchemeItem = array();
								$dbSchemeItem[CSV_DBNAME] = $kernelStrings['icl_userid_item'];
								$dbSchemeItem[CSV_DBREQUIRED] = true;
								$dbSchemeItem[CSV_DBREQUIREDGROUP] = null;

								$dbScheme['U_ID'] = $dbSchemeItem;
							}

							// Interpret fields for the database scheme
							//
							foreach( $fieldsPlainDesc as $fieldID=>$fieldData ) {
								if ( !in_array($fieldData[CONTACT_FIELD_TYPE], $exportingFieldTypes) )
									continue;

								$dbSchemeItem = array();
								$dbSchemeItem[CSV_DBNAME] = sprintf( "%s - %s", $fieldData[CONTACT_FIELDGROUPNAME], $fieldData[CONTACT_FIELDGROUP_LONGNAME] );
								$dbSchemeItem[CSV_DBREQUIRED] = $fieldData[CONTACT_REQUIRED];
								$dbSchemeItem[CSV_DBREQUIREDGROUP] = $fieldData[CONTACT_REQUIRED_GROUP];

								$dbScheme[$fieldID] = $dbSchemeItem;
							}

							// Init other custom file settings
							//
							$csv_headerdblink = array();
							$csv_importfirstline = 0;
							$csv_separator = base64_encode($csv_separator);
							$destPath = base64_encode($destPath);

							if ( !isset($included_groups) )
								$included_groups = array();

							$groups = base64_encode( serialize($included_groups) );

							// Change step value
							//
							$csv_step = CSV_STEP_SETSCHEME;

							break;
						} else {
							// Import predefined file format
							//
							$importScheme = loadFileFormat( CM_FILEFORMATS, $fileFormat, $kernelStrings );
							if ( PEAR::isError($importScheme) ) {
								$errorStr = $importScheme->getMessage();
								$fatalError =  true;

								break;
							}

							// Import contacts
							//
							$filePath = $destPath;
							$imported = 0;
							$errorNum = 0;

							if ( !isset($included_groups) )
								$included_groups = array();

							/*
							if ( $grantuserprivs ) {
								$csv_step = CSV_SELECT_U_ID;
								$groups = base64_encode( serialize($included_groups) );

								$selectedU_ID = null;
								$fieldIDs = array();

								$headers = getFileHeaders( $destPath, $kernelStrings );
								if ( PEAR::isError($headers) ) {
									$errorStr = $importScheme->getMessage();
									$fatalError =  true;

									break;
								}

								$fieldIDs = $headers;

								foreach ( $importScheme[CSV_LINKS] as $linkData )
									if ( $linkData[CSV_DBFIELD] == 'U_ID' )
										$selectedU_ID = $linkData[CSV_FILEFIELD];

								break;
							}
							*/

							if ( ( isset($unsortedContacts) && $unsortedContacts) || $folderID == UNSORTED_FOLDER_NAME )
								$folderID = aa_getUnsortedContactsFolder( $kernelStrings, $currentUser );

							$errorStack = importContacts( $filePath, $importScheme, $kernelStrings, $folderID, $imported, $errorNum, $grantuserprivs, $included_groups, $selectedStatus );

							$csv_step = CSV_STEP_FINISHED;
						}
					} elseif ( $csv_step == CSV_SELECT_U_ID ) {
						$importScheme = loadFileFormat( CM_FILEFORMATS, $fileFormat, $kernelStrings );
						if ( PEAR::isError($importScheme) ) {
							$errorStr = $importScheme->getMessage();
							$fatalError =  true;

							break;
						}

						if ( !strlen($selectedU_ID) ) {
							$headers = getFileHeaders( $destPath, $kernelStrings );
							if ( PEAR::isError($headers) ) {
								$errorStr = $importScheme->getMessage();
								$fatalError =  true;

								break;
							}

							$fieldIDs = $headers;
							$invalidField = 'selectedU_ID';
							$errorStr = $kernelStrings['app_requiredfields_message'];
							break;
						}

						if ( strlen($groups) )
							$included_groups = unserialize( base64_decode($groups) );
						else
							$included_groups = array();

						$filePath = $destPath;
						$imported = 0;
						$errorNum = 0;

						$importScheme[CSV_LINKS]['U_ID'] = array();
						$importScheme[CSV_LINKS]['U_ID'][CSV_FILEFIELD] = $selectedU_ID;
						$importScheme[CSV_LINKS]['U_ID'][CSV_DBFIELD] = 'U_ID';

						if ( (isset($unsortedContacts) && $unsortedContacts) || $folderID == UNSORTED_FOLDER_NAME )
							$folderID = aa_getUnsortedContactsFolder( $kernelStrings, $currentUser );

						$errorStack = importContacts( $filePath, $importScheme, $kernelStrings, $folderID, $imported, $errorNum, true, $included_groups, $selectedStatus );

						$csv_step = CSV_STEP_FINISHED;
					} elseif ( $csv_step == CSV_STEP_SETSCHEME ) {
						// Restore custom file settings
						//
						$dbScheme = unserialize( base64_decode($csv_dbscheme_packed) );
						$headers = unserialize( base64_decode($csv_headers_packed) );
						$separator = base64_decode($csv_separator);

						if (!isset($csv_importfirstline))
							$csv_importfirstline = 0;

						// Make file import scheme
						//
						$importScheme = makeCSVImportScheme( $headers, $dbScheme, $separator, $csv_headerdblink,
																$csv_importfirstline, $kernelStrings );
						if ( PEAR::isError($importScheme) ) {
							$errorStr = $importScheme->getMessage();
							$invalidField = $importScheme->getUserInfo();

							break;
						}

						// Import contacts
						//
						$filePath = base64_decode($destPath);
						$imported = 0;
						$errorNum = 0;
						$groups = unserialize( base64_decode($groups) );

						if ( (isset($unsortedContacts) && $unsortedContacts) || $folderID == UNSORTED_FOLDER_NAME )
							$folderID = aa_getUnsortedContactsFolder( $kernelStrings, $currentUser );

						$errorStack = importContacts( $filePath, $importScheme, $kernelStrings, $folderID, $imported, $errorNum, $grantuserprivs, $groups, $selectedStatus );

						$csv_step = CSV_STEP_FINISHED_SAVESCHEMA;
					} elseif ( $csv_step == CSV_STEP_FINISHED_SAVESCHEMA ) {

						// Restore custom file settings
						//
						$importScheme = unserialize( base64_decode($csv_importscheme_packed) );

						if ( isset($saveFormat) && $saveFormat ) {
							if ( !strlen($fileformatname) ) {
								$invalidField = "fileformatname";
								$errorStr = $kernelStrings[ERR_REQUIREDFIELDS];
								$saveFormatError = true;

								break;
							}

							$formatName = prepareStrToStore( $fileformatname );
							saveFileFormat( CM_FILEFORMATS, $importScheme, prepareStrToStore($formatName), $kernelStrings, $currentUser );
						}

						// Return to the contact list
						//
						$params = array();

						if ( strlen($folderID) )
							$params['curCF_ID'] = base64_encode($folderID);

						redirectBrowser( PAGE_USERSANDGROUPS, $params );
					} elseif ( $csv_step == CSV_STEP_FINISHED ) {
						// Return to the user list
						//
						$params = array();

						if ( strlen($folderID) )
							$params['curCF_ID'] = base64_encode($folderID);

						redirectBrowser( PAGE_USERSANDGROUPS, array() );
					}

					break;
		case 1 :
					redirectBrowser( PAGE_USERSANDGROUPS, array() );
		case 2 :
					redirectBrowser( PAGE_MANAGETEMPLATES, array(OPENER=>PAGE_IMPORTUSERS) );
	}

	//
	// Prepare page data
	//

	switch (true) {
		case true :
					if ( !isset($csv_step) )
						$csv_step = CSV_STEP_LOADFILE;

					if ( $csv_step == CSV_STEP_LOADFILE ) {
						// Fill file formats list
						//
						$formats = listFileFormats( CM_FILEFORMATS, $kernelStrings, $currentUser );
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

						// Load folder list
						//
						$foldersFound = false;
						$access = null;
						$hierarchy = null;
						$deletable = null;
						$userId = isAdministratorID($currentUser) ? null : $currentUser;
						$folders = $cm_groupClass->listFolders( $userId, TREE_ROOT_FOLDER, $kernelStrings, 0,
																false, $access, $hierarchy,
																$deletable, TREE_WRITEREAD );

						if ( PEAR::isError($folders) ) {
							$fatalError = true;
							$errorStr = $folders->getMessage();

							break;
						}

						$canManageContacts = aa_canManageContacts($currentUser);

						if ( $canManageContacts ) {
							$visibleFolders = array();
							$UnsortedFolderData = null;
							foreach ( $folders as $fCF_ID=>$folderData ) {
								if ( strcmp($folderData->CF_NAME, UNSORTED_FOLDER_NAME) == 0 && $folderData->CF_ID_PARENT == APP_REG_RIGHTS_ROOT ) {
									$folderData->TREE_ACCESS_RIGHTS = TREE_READWRITEFOLDER;
									$folderData->RIGHT = TREE_READWRITEFOLDER;
									$UnsortedFolderData = $folderData;
								}

								if ( isAdministratorID($currentUser) ) {
									$folderData->TREE_ACCESS_RIGHTS = TREE_READWRITEFOLDER;
									$folderData->RIGHT = TREE_READWRITEFOLDER;
								}
								$folderData->curID = $fCF_ID;
								$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

								if ( !UR_RightsObject::CheckMask( $folderData->RIGHT, TREE_WRITEREAD ) )
									$folderData->RIGHT = -2;

								$visibleFolders[$fCF_ID] = $folderData;
							}

							if ( !is_null($UnsortedFolderData) ) {
								if ( !isset($folderID) || !strlen($folderID) )
									$folderID = $UnsortedFolderData->CF_ID;
							} else {
								$folderData = array();
								$folderData['CF_ID'] = UNSORTED_FOLDER_NAME;
								$folderData['NAME'] = UNSORTED_FOLDER_NAME;
								$folderData['RIGHT'] = 2;
								$folderData['curID'] = UNSORTED_FOLDER_NAME;

								$visibleFolders[UNSORTED_FOLDER_NAME] = (object)$folderData;
								if ( !isset($folderID) || !strlen($folderID) )
									$folderID = UNSORTED_FOLDER_NAME;
							}

							$folders = $visibleFolders;
						}

						// Prepare status list
						//
						$statusNames = array();
						$statusIDs = array_keys( $commonStatusNames );

						foreach( $commonStatusNames as $key=>$value )
							$statusNames[] = $kernelStrings[$value];

						// Load groups for the Groups tab
						//
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
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['iul_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_IMPORTUSERS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preproc->assign( CSV_STEP, $csv_step );

		if ( isset($unsortedContacts) )
			$preproc->assign( "unsortedContacts", $unsortedContacts );

		if ( isset($CF_ID) )
			$preproc->assign( "CF_ID", $CF_ID );

		if ( $csv_step == CSV_STEP_LOADFILE ) {
			$preproc->assign( 'formatIDs', $formatIDs );
			$preproc->assign( 'formatNames', $formatNames );

			$preproc->assign( "canManageContacts", $canManageContacts );
			if ( $canManageContacts )
				$preproc->assign( "folders", $folders );

			if ( isset($folderID) )
				$preproc->assign( 'folderID', $folderID );

			$preproc->assign( 'canManageContacts', $canManageContacts );

			if ( isset($fileFormat) )
				$preproc->assign( 'fileFormat', $fileFormat );

			$preproc->assign( "statusNames", $statusNames );
			$preproc->assign( "statusIDs", $statusIDs );

			$preproc->assign( "included_groups", $included_groups );
			$preproc->assign( "notincluded_groups", $notincluded_groups );
			$preproc->assign( "included_groups_names", $included_groups_names );
			$preproc->assign( "notincluded_groups_names", $notincluded_groups_names );

			if ( isset($edited) ) {
				$preproc->assign( "selectedStatus", $selectedStatus );

				if ( isset($grantuserprivs) )
					$preproc->assign( "grantuserprivs", $grantuserprivs );
			}
		} elseif ( $csv_step == CSV_STEP_SETSCHEME ) {

			// Pass parameters required for the scheme editor
			//
			$preproc->assign( CSV_DBSCHEME, $dbScheme );
			$preproc->assign( CSV_FILEHEADERS, $headers );

			$preproc->assign( CSV_DBSCHEME_PACKED, base64_encode(serialize($dbScheme)) );
			$preproc->assign( CSV_FILEHEADERS_PACKED, base64_encode(serialize($headers)) );

			$preproc->assign( CSV_HEADERDBLINK, $csv_headerdblink );
			$preproc->assign( CSV_IMPORTFIRSTLINE, $csv_importfirstline );

			$preproc->assign( CSV_SEPARATOR, $csv_separator );
			$preproc->assign( 'destPath', $destPath );

			if ( isset($folderID) )
				$preproc->assign( 'folderID', $folderID );

			if ( isset($grantuserprivs) )
				$preproc->assign( "grantuserprivs", $grantuserprivs );
			$preproc->assign( "selectedStatus", $selectedStatus );

			$preproc->assign( "groups", $groups );

		} elseif ( $csv_step == CSV_STEP_FINISHED_SAVESCHEMA ) {
			if ( !$saveFormatError ) {
				$preproc->assign( CSV_DBSCHEME_PACKED, base64_encode(serialize($dbScheme)) );
				$preproc->assign( CSV_FILEHEADERS_PACKED, base64_encode(serialize($headers)) );

				$preproc->assign( 'errorStack', $errorStack );
				$preproc->assign( 'importStr', sprintf( $kernelStrings['iul_finished_message'], $imported ) );
				$preproc->assign( 'errorNum', $errorNum );
			}

			$preproc->assign( CSV_IMPORTSCHEME_PACKED, base64_encode(serialize($importScheme)) );
			$preproc->assign( 'saveFormatError', $saveFormatError );
			if ( isset($saveFormat) )
				$preproc->assign( 'saveFormat', $saveFormat );

			$preproc->assign( 'folderID', $folderID );
		} elseif ( $csv_step == CSV_SELECT_U_ID ) {
			$preproc->assign( 'selectedU_ID', $selectedU_ID );
			$preproc->assign( 'fileFormat', $fileFormat );
			$preproc->assign( 'fieldIDs', $fieldIDs );
			$preproc->assign( 'folderID', $folderID );
			$preproc->assign( 'selectedStatus', $selectedStatus );
			$preproc->assign( 'destPath', $destPath );
			$preproc->assign( 'groups', $groups );
		} elseif ( $csv_step == CSV_STEP_FINISHED ) {
			$preproc->assign( 'errorStack', $errorStack );
			$preproc->assign( 'importStr', sprintf( $kernelStrings['iul_finished_message'], $imported ) );
			$preproc->assign( 'errorNum', $errorNum );
			$preproc->assign( 'folderID', $folderID );
		}
	}

	$preproc->display( "importusers.htm" );
?>