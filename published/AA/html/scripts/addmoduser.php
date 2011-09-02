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
	$contactCount = 0;
	$userNotEdited = false;

	$initUserIndRights = false;
	$included_groups_names = array();
	$notincluded_groups_names = array();
	$isWarning = false;

	if ( !isset($activeTab) || !strlen($activeTab) )
		$activeTab = null;

	if ( isset($curCF_ID) && $curCF_ID != -3 )
		$currentFolder = base64_decode($curCF_ID);
	else
		$currentFolder = null;

	define( 'CM_DISPLAY_TABS', 'TABS' );

	define( 'TAB_CONTACT', 'CONTACT' );
	define( 'TAB_USER', 'USER' );
	define( 'TAB_GROUPS', 'GROUPS' );
	define( 'TAB_ACCESS', 'ACCESS' );
	define( 'TAB_QUOTA', 'QUOTA' );

	$nonContactTab = array( TAB_USER, TAB_GROUPS, TAB_ACCESS, TAB_QUOTA );

	$rightMasks = array( TREE_ONLYREAD => array(1,0,0),
						 TREE_WRITEREAD => array(1,1,0),
						 TREE_READWRITEFOLDER => array(1,1,1) );


	// Load the common page variables
	//
	switch ( true ) {
		case true :
				$typeDesc = getContactTypeDescription( CONTACT_BASIC_TYPE, $language, $kernelStrings, false );
				if ( PEAR::isError($typeDesc) ) {
					$fatalError = true;
					$errorStr = $typeDesc->getMessage();

					break;
				}

				$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDesc, $kernelStrings, true );
	}

	if ( isset($lastTab) && strlen($lastTab) && $lastTab != 'null' )
		$activeTab = $lastTab;

	// Process form buttons
	//
	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL, 'uploadimgbtn', 'clearimgbtn', 'saveaddbtn' ), $_POST );

	switch ($btnIndex) {
		case 4 :
		case 0 :
				// Save
				//
				if ( $curCF_ID == -3 ) {
					$errorStr = $kernelStrings['amc_selectfolder_message'];
					$invalidField = 'FOLDER';
					break;
				}

				$newUser = $action == ACTION_NEW;
				$createUnsorted = ((isset($unsortedContacts) && $unsortedContacts) || $currentFolder == UNSORTED_FOLDER_NAME) && $newUser;

				// Check user rights
				//
				if ( !isAdministratorID( $currentUser ) && $newUser && !$createUnsorted ) {
					$rights = $cm_groupClass->getIdentityFolderRights( $currentUser, $currentFolder, $kernelStrings );
					if ( PEAR::isError($rights) ) {
						$errorStr = $rights->getMessage();

						break;
					}
				}

				if ( $createUnsorted )
					$currentFolder = aa_getUnsortedContactsFolder( $kernelString, $currentUser );

				$Contact = new Contact( $kernelStrings, $language, $typeDesc, $fieldsPlainDesc );

				if ( $action == ACTION_EDIT ) {
					$contactData['C_ID'] = base64_decode($C_ID);
					$contactData['CF_ID'] = $currentFolder;
				}

				$contactData['C_MODIFYDATETIME'] = convertToSqlDateTime( time() );
				$contactData['C_MODIFYUSERNAME'] = getUserName( $currentUser );

				if ( $action == ACTION_NEW ) {
					$contactData['C_CREATEDATETIME'] = convertToSqlDateTime( time() );
					$contactData['C_CREATEUSERNAME'] = getUserName( $currentUser );
				}

				$preparedData = prepareArrayToStore( $contactData );

				if ( !$newUser )
					$contactData['U_ID'] = $targetUser;

				if ( !isset($diskquotas) )
					$diskquotas = array();

				$contactData = rescueElement( $contactData, ALLOW_DRACCESS, 0 );
				$contactData = rescueElement( $contactData, U_RECEIVESMESSAGES, 0 );
				$contactData = rescueElement( $contactData, U_CHANGEPASSWORD_RIGHT, 0 );
				$contactData = rescueElement( $contactData, U_CHANGETEMPLATE_RIGHT, 0 );
				$contactData = rescueElement( $contactData, U_CHANGENAME_RIGHT, 0 );
				$contactData = rescueElement( $contactData, U_SWITCHEMAIL_RIGHT, 0 );

				$lang = $contactData[LANGUAGE];
				$contactData[WBS_ENCODING] = $wbs_languages[$lang][WBS_ENCODING];

				$preparedData = prepareArrayToStore( $contactData );
				$preparedData = nullSQLFields( $preparedData );

				$saveSettings = true;

				$res = $Contact->addModUser( $currentUser, $action, $diskquotas, $language, $currentFolder, $preparedData, $newUser, $kernelStrings );

				if ( !PEAR::isError($res) )
				{
					if ( !isset($included_groups) )
						$included_groups = array();

					if ( !isset($notincluded_groups) )
						$notincluded_groups = array();

					$Contact->applyUserSettings( $kernelStrings, $language, $included_groups, $notincluded_groups );

					$userAccessRights[UR_REAL_ID] = $Contact->U_ID;
					$saveResult =  $UR_Manager->SavePath( $userAccessRights );
					if ( PEAR::isError( $saveResult ) )
					{
						$errorStr = $saveResult->getMessage();
						break;
					}

					if ( SERVER_TZ )
					{
						$rs = writeUserCommonSetting( $Contact->U_ID, 'TIME_ZONE_ID', $userdata['timezone'], $kernelStrings );
						if ( PEAR::isError($rs) )
						{
							$errorStr = $kernelStrings[ERR_SAVINGUSERSETTINGS];
							break;
						}

						$rs = writeUserCommonSetting( $Contact->U_ID, 'TIME_ZONE_DST', intval( $userdata['tzdst']), $kernelStrings );
						if ( PEAR::isError($rs) )
						{
							$errorStr = $kernelStrings[ERR_SAVINGUSERSETTINGS];
							break;
						}
					}
				}

				if ( PEAR::isError( $res ) ) {
					$errorStr = $res->getMessage();

					// Parse error message
					//
					$invFieldData = $res->getUserInfo();
					if ( strlen($invFieldData) ) {
						$invFieldData = explode( '|', $invFieldData );
						$invalidField = $invFieldData[0];

						// Extract tab identifier
						//
						if ( isset($invFieldData[1]) ) {
							$activeTab = $invFieldData[1];

							if ( !in_array($activeTab, $nonContactTab) ) {
								if ( $activeTab != TAB_CONTACT )
									$ShowExtendedInfo = true;

								$activeTab = TAB_CONTACT;
							}
						}
					}

					break;
				}

				// Send login instructions
				//
				if ( isset($sendinstructions) && $sendinstructions )
					sendUserNotification( $kernelStrings, $preparedData["U_ID"], $preparedData["U_PASSWORD1"], $loginURL, $currentUser );

				$params = array();

				if ( $btnIndex != 4 ) {
					if ( !$newUser && $targetUser == $currentUser )
						$params["updateAll"] = 1;

					redirectBrowser( PAGE_USERSANDGROUPS, $params );
				} else {
					redirectBrowser( PAGE_ADDMODUSER, array( ACTION=>ACTION_NEW, 'curCF_ID'=>$curCF_ID ) );
				}

				break;

		case 1 :
				// Cancel
				//
				redirectBrowser( PAGE_USERSANDGROUPS, array() );
		case 2 :
				// Upload image
				//
				if ( strlen($imgfiles['name'][$imgFieldEdited]) ) {
					// Move image file to the temporary directory
					//
					$fileName = uniqid( TMP_FILES_PREFIX );
					$destPath = WBS_TEMP_DIR."/".$fileName;
					$srcPath =  $imgfiles['tmp_name'][$imgFieldEdited];
					if ( !move_uploaded_file($srcPath, $destPath) ) {
						$errorStr = $kernelStrings['amc_erroruploadingfile_message'];
						break;
					}

					// Process image
					//
					$originalName = $imgfiles['name'][$imgFieldEdited];
					$fileType = $imgfiles['type'][$imgFieldEdited];

					$fieldDescription = $contactData[$imgFieldEdited];

					$thumbnailError = null;
					$res = processImageFieldFile( $destPath, $originalName, $fileType, $thumbnailError, $kernelStrings, $fieldDescription );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						break;
					}

					if ( PEAR::isError($thumbnailError) ) {
						$errorStr = $thumbnailError->getMessage();
						$isWarning = 1;
					}

					$contactData[$imgFieldEdited] = $fieldDescription;
				}

				break;
		case 3 :
				// Clean image
				//
				$fieldDescription = clearContactImageField( $contactData[$imgFieldEdited] );
				$contactData[$imgFieldEdited] = $fieldDescription;

				break;
	}

	switch (true) {
		case true :
					if ( $fatalError )
						break;

					$timeZones = array();
					foreach( $GLOBALS['_DATE_TIMEZONE_DATA']  as $key=>$val )
						$timeZones[] = array(
										'ID' => $key,
										'NAME' => sprintf("%s %s", $val['shortname'], $val['longname'] ),
										'DST' => $val['hasdst']
						 );

					$newUser = $action == ACTION_NEW;
					if ( !isset($userIsDeleted) )
						$userIsDeleted = false;

					if ( !isset($edited) ) {
						$Contact = new Contact( $kernelStrings, $language, $typeDesc, $fieldsPlainDesc );

						if ( $action == ACTION_NEW ) {
							$contactData = array();

							$contactName = $kernelStrings['amu_newuser_label'];
							$targetUser = null;
							$subscriber = null;

							$userdata["timezone"] = SERVER_TIME_ZONE_ID;
							$userdata["tzdst"] = SERVER_TIME_ZONE_DST;

						} else {
							$res = $Contact->loadEntry( base64_decode($C_ID), $kernelStrings );
							if ( PEAR::isError($res) ) {
								$errorStr = $res->getMessage();

								break;
							}

							$userIsDeleted = $Contact->U_STATUS == RS_DELETED;
							$contactData = (array)$Contact;

							$contactData = applyContactTypeDescription( $contactData, array(), $fieldsPlainDesc, $kernelStrings, UL_LIST_VIEW );
							$contactName = df_contactname( $contactData, false );
							$curCF_ID = base64_encode( $Contact->CF_ID );

							$modifyDateTime = convertToDisplayDateTime($Contact->C_MODIFYDATETIME, false, true, true );
							$modifyUserName = $Contact->C_MODIFYUSERNAME;

							$createDateTime = convertToDisplayDateTime($Contact->C_CREATEDATETIME, false, true, true );
							$createUserName = $Contact->C_CREATEUSERNAME;

							$subscriber = $Contact->C_SUBSCRIBER;

							$contactData = readIdentityCommonSysSettings( $contactData, $Contact->U_ID, IDT_USER, $kernelStrings, false );

							$included_groups = findGroupsContaningUser( $Contact->U_ID, $kernelStrings, true );

							$targetUser = $Contact->U_ID;

							$userdata["timezone"] = readUserCommonSetting( $targetUser, 'TIME_ZONE_ID' );
							$userdata["timezone"] = strlen( $userdata["timezone"] ) == 0 ? SERVER_TIME_ZONE_ID : $userdata["timezone"];

							$userdata["tzdst"] = readUserCommonSetting( $targetUser, 'TIME_ZONE_DST' );
							$userdata["tzdst"] = strlen( $userdata["tzdst"] )  == 0? SERVER_TIME_ZONE_DST : $userdata["tzdst"];
						}
					}

					// Prepare folder list
					//
					$userId = isAdministratorID($currentUser) ? null : $currentUser;

					$foldersFound = aa_canManageContacts($currentUser);

					if ( $action == ACTION_NEW ) {
						// Load the folder list
						//
						$visibleFolders = array();

						if ( $foldersFound ) {
							$access = null;
							$hierarchy = null;
							$deletable = null;
							$folders = $cm_groupClass->listFolders( $userId, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
																	$access, $hierarchy, $deletable, TREE_WRITEREAD );
							if ( PEAR::isError($folders) ) {
								$fatalError = true;
								$errorStr = $folders->getMessage();

								break;
							}

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

								$encodedID = base64_encode($fCF_ID);
								$folderData->curID = $encodedID;
								$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

								if ( !UR_RightsObject::CheckMask( $folderData->RIGHT, TREE_WRITEREAD ) )
									$folderData->RIGHT = -2;

								$visibleFolders[$fCF_ID] = $folderData;
							}

							if ( !is_null($UnsortedFolderData) ) {
								if ( !isset($currentFolder) || !strlen($currentFolder) )
									$currentFolder = $UnsortedFolderData->CF_ID;
							} else {
								$folderData = array();
								$folderData['CF_ID'] = UNSORTED_FOLDER_NAME;
								$folderData['NAME'] = UNSORTED_FOLDER_NAME;
								$folderData['RIGHT'] = 2;
								$folderData['curID'] = base64_encode(UNSORTED_FOLDER_NAME);

								$visibleFolders[UNSORTED_FOLDER_NAME] = (object)$folderData;
								if ( !isset($currentFolder) || !strlen($currentFolder) )
									$currentFolder = UNSORTED_FOLDER_NAME;
							}
						}

						$folders = $visibleFolders;
					} else {
						if ( !$userIsDeleted ) {
							$curFolderData = $cm_groupClass->getFolderInfo( base64_decode($curCF_ID), $kernelStrings );
							if ( PEAR::isError($curFolderData) ) {
								$fatalError = true;
								$errorStr = $curFolderData->getMessage();

								break;
							}

							$folderName = $curFolderData['CF_NAME'];
						}
					}

					// Prepare contact tabs
					//
					$contactTabs = array();
					$firstTabId = null;

					foreach ( $typeDesc as $index=>$group ) {
						if ( $group[CONTACT_GROUPID] == CONTACT_CONTACTGROUP_ID )
							continue;

						if ( is_null($firstTabId) )
							$firstTabId = $group[CONTACT_GROUPID];

						$tabData[PT_NAME] = $group[CONTACT_FIELDGROUP_SHORTNAME];
						$tabData[PT_PAGE_ID] = $group[CONTACT_GROUPID];
						$tabData[PT_FILE] = 'amu_customtab.htm';
						$tabData[PT_CUSTOM_ID] = $index;

						$contactTabs[] = $tabData;
					}

					if ( is_null($activeTab) )
						$activeTab = CONTACT_CONTACTGROUP_ID;

					// Add user tabs
					//
					$UserTabs = array();

					$userControl = ($newUser) ? 'contactData[U_ID]' : 'contactData[U_PASSWORD1]';

					$UserTabs[] = array( PT_NAME=>$kernelStrings['amu_contact_title'],
										PT_PAGE_ID=>TAB_CONTACT,
										PT_FILE=>'amu_conttab.htm',
										PT_CONTROL=>'contactData[C_FIRSTNAME]',
										PT_CUSTOM_ID=>TAB_CONTACT,
									);

					$UserTabs[] = array( PT_NAME=>$kernelStrings['amu_user_title'],
										PT_PAGE_ID=>TAB_USER,
										PT_FILE=>'amu_usertab.htm',
										PT_CONTROL=>$userControl,
										PT_CUSTOM_ID=>TAB_USER,
									);

					$UserTabs[] = array( PT_NAME=>$kernelStrings['amu_groups_title'],
										PT_PAGE_ID=>TAB_GROUPS,
										PT_FILE=>'amu_groupstab.htm',
										PT_CUSTOM_ID=>TAB_GROUPS,
									);
					$UserTabs[] = array( PT_NAME=>$kernelStrings['amu_access_title'],
										PT_PAGE_ID=>TAB_ACCESS,
										PT_FILE=>'amu_accesstab.htm',
										PT_CUSTOM_ID=>TAB_ACCESS,
										PT_ON_OPEN=>'onActivateAccess()'
									);
					$UserTabs[] = array( PT_NAME=>$kernelStrings['amu_quota_title'],
										PT_PAGE_ID=>TAB_QUOTA,
										PT_FILE=>'amu_quotatab.htm',
										PT_CUSTOM_ID=>TAB_QUOTA
									);

					// Prepare user-related data
					//
					$formatNames = array();
					$formatIDs = array();

					foreach( $mail_formats as $format_id=>$format_name ) {
						$formatIDs[] = $format_id;
						$formatNames[] = $kernelStrings[$format_name];
					}

					// Prepare language list
					//
					foreach( $wbs_languages as $key=>$value ) {
						$language_names[] = $value["NAME"];
						$language_ids[] = $value["ID"];
					}

					// Prepare status list
					//
					$statusNames = array();
					$statusIDs = array_keys( $commonStatusNames );

					foreach( $commonStatusNames as $key=>$value )
						$statusNames[] = $kernelStrings[$value];

					// Prepare page list
					//
					$pages = listUserStartScreens( $currentUser, $language, $kernelStrings );
					$page_ids = array_keys($pages);
					$page_names = array_values($pages);

					if ( !$userNotEdited )
						if ( !isset($included_groups) )
							$included_groups = array();

					// Prepare user group list
					//
					$userGroups = listUserGroups( $kernelStrings, false );
					if ( PEAR::isError($userGroups) ) {
						$fatalError = true;
						$errorStr = $userGroups->getMessage();
						break;
					}

					$fullGroupListIDs = array_keys($userGroups);

					$notincluded_groups = array();

					if ( $newUser && $userNotEdited )
						$included_groups = array();
					else
						if ( $userNotEdited && !isset($included_groups) )
							$included_groups = array();

					$notincluded_groups = array_diff( $fullGroupListIDs, $included_groups );

					foreach( $included_groups as $key )
						$included_groups_names[] = prepareStrToDisplay($userGroups[$key][UG_NAME], true, true);

					foreach( $notincluded_groups as $key )
						$notincluded_groups_names[] = prepareStrToDisplay($userGroups[$key][UG_NAME], true, true);

					if ( !isset( $edited ) )
						$userAccessRights = array( UR_PATH=>'/ROOT', UR_ACTION=>UR_ACTION_EDITUSER, UR_ID=> ( ( $action == ACTION_EDIT) ? $targetUser : null ), UR_FIELD=>"userAccessRights", UR_INCLUDED_GROUPS=>$included_groups );
					else
					{
						if ( $userAccessRights[UR_ID] == UR_SYS_ID && !isset( $userAccessRights[UR_REAL_ID] ) )
							$userAccessRights[UR_ID] = null;

						$userAccessRights[UR_INCLUDED_GROUPS]=$included_groups;
					}

					$userAccessRightsHtml =  $UR_Manager->RenderPath( $userAccessRights );

					if ( PEAR::isError( $userAccessRightsHtml ) )
					{
						$fatalError = true;
						$errorStr = $userAccessRightsHtml->getMessage();
						break;
					}

					if ( $userNotEdited ) {
						// Save previous language, access settings and user name to track changes
						//
						$prevLanguage = $groupData[LANGUAGE];
						$prevName = base64_encode( getUserName($targetUser, false) );
					}

					// Prepare the quotable applications list
					//
					$QuotableApplications = DiskQuotaManager::ListQuotableApplications();

					// Prepare the quota values array
					//
					if ( !isset($edited) )
						$diskquotas = DiskQuotaManager::ListUserApplicationsQuotes( $targetUser, $KernelStrings );

					// Prepare text for the access tab
					//
					$groupCnt = count($included_groups);
					if ( $groupCnt )
						$accessTabNote = sprintf( $kernelStrings['amu_combinationrights_text'], $groupCnt );
					else
						$accessTabNote = $kernelStrings['amu_personalrights_text'];

					// Prepare image field thumbnail URLs
					//
					$thumbPerms = array();
					$cmFilesPath = getContactsAttachmentsDir();

					foreach ( $fieldsPlainDesc as $fieldId=>$fieldData ) {
						if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_IMAGE && isset($contactData[$fieldId]) ) {
							$contactFieldData = $contactData[$fieldId];

							if ( $contactFieldData[CONTACT_IMGF_FILENAME] ) {
								if ( $contactFieldData[CONTACT_IMGF_MODIFIED] )
									$thumbPath = base64_decode($contactFieldData[CONTACT_IMGF_DISKFILENAME]);
								else
									$thumbPath = $cmFilesPath."/".base64_decode($contactFieldData[CONTACT_IMGF_DISKFILENAME]);

								$thumbPerms[] = $thumbPath;

								$thumbParams = array();
								$srcExt = null;
								$thumbParams['nocache'] = getThumbnailModifyDate( $thumbPath, 'win', $srcExt );

								if ( $contactFieldData[CONTACT_IMGF_MODIFIED] )
									$thumbParams['basefile'] = $contactFieldData[CONTACT_IMGF_DISKFILENAME];
								else
									$thumbParams['basefile'] = base64_encode($cmFilesPath."/".base64_decode($contactFieldData[CONTACT_IMGF_DISKFILENAME]));

								$thumbParams['ext'] = base64_encode( $contactFieldData[CONTACT_IMGF_TYPE] );

								$contactFieldData['THUMB_URL'] = prepareURLStr( PAGE_GETFILETHUMB, $thumbParams );
								$contactData[$fieldId] = $contactFieldData;
							}
						}
					}

					$_SESSION['THUMBPERMS'] = $thumbPerms;
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$title = ($action == ACTION_NEW) ? $kernelStrings['amu_addpage_title'] : $kernelStrings['amu_modpage_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_ADDMODUSER );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "action", $action );

	$preproc->assign( "timeZones", $timeZones );

	if ( !$fatalError )
	{
		$preproc->assign( "enableTZ",  SERVER_TZ ? true : false );
		$preproc->assign( "userdata", $userdata );

		$preproc->assign( "contactTabs", $contactTabs );
		$preproc->assign( "UserTabs", $UserTabs );
		$preproc->assign( "typeDesc", $typeDesc );
		$preproc->assign( "activeTab", $activeTab );
		$preproc->assign( "contactData", $contactData );
		$preproc->assign( "currentFolder", $currentFolder );
		$preproc->assign( "contactName", $contactName );
		$preproc->assign( "targetUser", $targetUser );
		$preproc->assign( "subscriber", $subscriber );
		$preproc->assign( "foldersFound", $foldersFound );

		if ( isset($imgFieldEdited) )
			$preproc->assign( "imgFieldEdited", $imgFieldEdited );

		$preproc->assign( "userAccessRightsHtml", $userAccessRightsHtml );

		$preproc->assign( "loginURL", getLoginURL() );

		if ( strlen($subscriber) )
			$preproc->assign( "subscrStatusName", $kernelStrings[$subscruberStatusNames[$subscriber]] );

		if ( $action == ACTION_EDIT ) {
			$preproc->assign( "modifyDateTime", $modifyDateTime );
			$preproc->assign( "modifyUserName", $modifyUserName );
			$preproc->assign( "createDateTime", $createDateTime );
			$preproc->assign( "createUserName", $createUserName );
		}

		$preproc->assign( "formatIDs", $formatIDs );
		$preproc->assign( "formatNames", $formatNames );
		$preproc->assign( "language_names", $language_names );
		$preproc->assign( "language_ids", $language_ids );
		$preproc->assign( "statusNames", $statusNames );
		$preproc->assign( "statusIDs", $statusIDs );
		$preproc->assign( "page_ids", $page_ids );
		$preproc->assign( "page_names", $page_names );
		$preproc->assign( "userIsDeleted", $userIsDeleted );

		if ( !isset($ShowExtendedInfo) )
			$ShowExtendedInfo = 0;

		$preproc->assign( "ShowExtendedInfo", $ShowExtendedInfo );

		if ( isset($diskquotas) )
			$preproc->assign( "diskquotas", $diskquotas );

		$preproc->assign( "global_applications", $global_applications );
		$preproc->assign( "lng", $language );

		$preproc->assign( "included_groups", $included_groups );
		$preproc->assign( "included_groups_names", $included_groups_names );
		$preproc->assign( "notincluded_groups", $notincluded_groups );
		$preproc->assign( "notincluded_groups_names", $notincluded_groups_names );
		$preproc->assign( "quotableApplications", $QuotableApplications );

		if ( isset($groupData) )
			$preproc->assign( "groupData", $groupData );

		if ( isset($sendinstructions) )
			$preproc->assign( "sendinstructions", $sendinstructions );

		$preproc->assign( "tree_access_mode_names", $tree_access_mode_names );
		$preproc->assign( "accessTabNote", $accessTabNote );

		if ( isset($auxrights_cb) )
			$preproc->assign( "auxrights_cb", $auxrights_cb );

		$preproc->assign( "displayFieldMode", CM_DISPLAY_TABS );
		$preproc->assign( "firstTabId", $firstTabId );

		if ( isset($newUser) )
			$preproc->assign( "newUser", $newUser );

		if ( isset($edited) )
			$preproc->assign( "edited", $edited );

		if ( $action == ACTION_NEW ) {
			if ( $foldersFound ) {
				$preproc->assign( "folders", $folders );
				$preproc->assign( "folderCount", count($folders) );
				$preproc->assign( "hierarchy", $hierarchy );
			}
		} else {
			$preproc->assign( "curCF_ID", $curCF_ID );
			$preproc->assign( "C_ID", $C_ID );

			if ( !$userIsDeleted )
				$preproc->assign( "folderName", $folderName );
		}
	}

	$preproc->display( "addmoduser.htm" );
?>