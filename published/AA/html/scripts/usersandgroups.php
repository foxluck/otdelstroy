<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/AA/aa.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$popupStr = null;
	$errorStr = null;
	$SCR_ID = "UNG";

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$invalidField = null;
	$statisticsMode = false;
	$folderChanged = false;

	$showPageSelector = false;
	$pages = array();
	$pageCount = 0;
	$startIndex = 0;
	$count = 0;

	$visibleColumns = null;
	$viewMode = null;
	$recordsPerPage = null;
	$showSharedPanel = null;
	$imgFieldsViewMode = null;
	$folderViewMode = null;
	$listViewImage = null;

	define( 'EXPAND', 'expand' );
	define( 'COLLAPSE', 'collapse' );
	define( 'HIDE_FOLDER', 'hidefolders' );
	define( 'ACTION_DELETEFOLDER', 'DELETE' );
	define( 'ACTION_SHOWALLUSERS', 'SHOWALLUSERS' );
	define( 'ACTION_SHOWALLGROUPS', 'SHOWALLGROUPS' );
	define( "SELECTED_FOLDER_ID", 'curCF_ID' );
	define( "SWITCH_OT", 'switchObjectType' );
	define( "SELECTED_GROUP_ID", 'selectedGroup' );
	define( "SELECTED_LIST_ID", 'selectedList' );
	define( "SELECTED_LIST", 'SELECTEDLIST' );

	// Contact menu matrix constants
	//
	define( 'OT_GROUP', 'GROUP' );

	define( 'AA_MTX_ACTIVEUSERS', 'ACTIVEUSERS' );
	define( 'AA_MTX_INACTIVEUSERS', 'INACTIVEUSERS' );
	define( 'AA_MTX_DELETEDUSERS', 'DELETEDUSERS' );
	define( 'AA_MTX_GROUP', 'GROUP' );

	define( 'AA_MTX_SEPARATOR', 'SEPARATOR' );

	$contactMenuMatrix = array();
	$contactMenuMatrix[] = array( AA_MTX_SEPARATOR );

	$contactMenuMatrix[] = array( OT_GROUP, $kernelStrings['ul_addtogroup_menu'], sprintf( $processButtonTemplate, 'addtogroupbtn' )."||confirmUserGroupAdding()", array( AA_MTX_GROUP, AA_MTX_ACTIVEUSERS ) );
	$contactMenuMatrix[] = array( OT_GROUP, $kernelStrings['ul_removefromgroup_menu'], sprintf( $processButtonTemplate, 'removefromgroupbtn' )."||confirmUserGroupRemoving()", array( AA_MTX_GROUP ) );
	$contactMenuMatrix[] = array( OT_GROUP, AA_MTX_SEPARATOR );
	$contactMenuMatrix[] = array( OT_GROUP, $kernelStrings['cman_changeuserstatus_menu'], sprintf( $processButtonTemplate, 'changeuserstatus' )."||confirmChageStatus()", "*" );
	$contactMenuMatrix[] = array( OT_GROUP, AA_MTX_SEPARATOR );
	$contactMenuMatrix[] = array( OT_GROUP, $kernelStrings['ul_deleteusers_menu'], sprintf( $processButtonTemplate, 'deletecontactbtn' )."||confirmContactDeletion()", "*" );
	$contactMenuMatrix[] = array( OT_GROUP, AA_MTX_SEPARATOR );
	$contactMenuMatrix[] = array( OT_GROUP, $kernelStrings['cman_revokeprivs_menu'], sprintf( $processButtonTemplate, 'revokebtn' )."||confirmRevokePrivs()", "*" );

	// Page function
	//
	function prepareContactEntry( &$params, &$data )
	{
		global $thumbPerms;
		global $language;
		global $userRecordsFound;

		extract($params);

		$data = (array)$data;
		$data = applyContactTypeDescription( $data, $visibleColumns, $fieldsPlainDesc, $kernelStrings, $viewMode );

		$urlParams = array();
		$urlParams[ACTION] = ACTION_EDIT;
		$urlParams['C_ID'] = base64_encode( $data['C_ID'] );

		$URL = prepareURLStr( PAGE_ADDMODUSER, $urlParams );

		$data['ROW_URL'] = $URL;

		if ( !$userRecordsFound )
			$userRecordsFound = isset($data['U_ID']) && strlen($data['U_ID']);

		if ( $searchString != "" ) {
			$urlParams = array();
			$urlParams['curCF_ID'] = base64_encode( $data['CF_ID'] );
			$urlParams['searchString'] = null;

			$data['FOLDER_URL'] = prepareURLStr( PAGE_USERSANDGROUPS, $urlParams );
		}

		$cmFilesPath = getContactsAttachmentsDir();

		foreach ( $fieldsPlainDesc as $fieldId=>$fieldData ) {
			$contactFieldData = $data[$fieldId];

			if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_IMAGE && isset($data[$fieldId]) ) {
				if ($imgFieldsViewMode == AA_IMAGESVIEW_THUMBNAILS || $listViewImage != AA_LISTVIEW_NOIMG) {
					$thumbPath = $cmFilesPath."/".base64_decode($contactFieldData[CONTACT_IMGF_DISKFILENAME]);

					$thumbPerms[] = $thumbPath;

					$thumbParams = array();
					$srcExt = null;
					$thumbParams['nocache'] = getThumbnailModifyDate( $thumbPath, 'win', $srcExt );
					$thumbParams['basefile'] = base64_encode($cmFilesPath."/".base64_decode($contactFieldData[CONTACT_IMGF_DISKFILENAME]));
					$thumbParams['ext'] = base64_encode( $contactFieldData[CONTACT_IMGF_TYPE] );

					$contactFieldData['THUMB_URL'] = prepareURLStr( PAGE_GETFILETHUMB, $thumbParams );
				}

				$fileParams = array();
				$fileParams['field'] = base64_encode($fieldId);
				$fileParams['contact'] = base64_encode( $data['C_ID'] );

				$contactFieldData['FILE_URL'] = prepareURLStr( PAGE_GETIMGFIELDFILE, $fileParams );
			}

			$data[$fieldId] = $contactFieldData;
		}

		return $data;
	}

	function aa_getUserOptionValue( $APP_ID, $user, $name )
	{
		if ( !isAdministratorID($user) )
			return getAppUserCommonValue( $APP_ID, $user, $name, null );
		else {
			if ( isset($_SESSION[$name]) )
				return $_SESSION[$name];
			else
				return null;
		}
	}

	function aa_setUserOptionValue( $APP_ID, $user, $name, $value, &$kernelStrings )
	{
		if ( !isAdministratorID($user) )
			setAppUserCommonValue( $APP_ID, $user, $name, $value, $kernelStrings );
		else
			$_SESSION[$name] = $value;
	}

	// Load user rights and check if Contact Lists functionality is supported
	//
	$listsIsSupported = contactListsIsSupported();

	if ( isset( $curObjectId ) ) {
		if ( substr( $curObjectId, 0, strlen(OT_GROUP)) == OT_GROUP ) {
			$selectedGroup = substr($curObjectId, strlen(OT_GROUP));
		}
	}

	// Define the current object type
	//
	$currentObjectType = OT_GROUP;

	// Process the search string
	//
	if ( !isset($searchString) ) {
		if ( isset($_SESSION['AA_SEARCHSTRING']) )
			$searchString = $_SESSION['AA_SEARCHSTRING'];
		else
			$searchString = null;

		$prevSearchString = $searchString;
	}

	if ( $searchString == "" )
		$searchString = null;

	if ( !isset( $prevSearchString ) )
		$prevSearchString = null;

	$_SESSION['AA_SEARCHSTRING'] = $searchString;

	// Determine active group
	//
	if ( isset($selectedGroup) ) {
		$selectedGroup = base64_decode($selectedGroup);

		aa_setUserOptionValue( $AA_APP_ID, $currentUser, SELECTED_GROUP, $selectedGroup, $kernelStrings );

		$folderChanged = true;
	} else {
		$selectedGroup = aa_getUserOptionValue( $AA_APP_ID, $currentUser, SELECTED_GROUP );
	}

	if ( !strlen($selectedGroup) )
		$selectedGroup = TREE_AVAILABLE_FOLDERS;

	if ( !isSystemGroup($selectedGroup) && !userGroupExists($selectedGroup) )
		$selectedGroup = TREE_AVAILABLE_FOLDERS;

	if ( $prevSearchString != $searchString )
		$folderChanged = true;

	$selectedObjectId = $selectedGroup;

	// Process page actions
	//
	if ( isset($action) )
		switch ($action) {
			case HIDE_FOLDER :
							$foldersHidden = true;
							aa_setUserOptionValue( $AA_APP_ID, $currentUser, 'AA_FOLDERSHIDDEN', $foldersHidden, $kernelStrings );

							break;
		}

	include( 'ung_formhandler.php' );

	switch (true) {
		case true :
			// Prepare sorting string
			//
			if ( isset($sorting) ) {
				$sorting = base64_decode( $sorting );

				aa_setUserOptionValue( $AA_APP_ID, $currentUser, 'AA_SORTING', $sorting, $kernelStrings );
			}

			// Load type description
			//
			$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, $language, $kernelStrings, false );
			if ( PEAR::isError($typeDescription) ) {
				$fatalError = true;
				$errorStr = $typeDescription->getMessage();

				break;
			}

			$contactSection = $typeDescription[0];

			// Obtain columns descriptions as a plain array
			//
			$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, true, true );

			// Load the view settings
			//
			$visibleColumns = $ul_defaultColumnSet;
			$viewMode = UL_GRID_VIEW;
			$sorting = null;
			$recordsPerPage = 30;
			$showSharedPanel = true;
			$imgFieldsViewMode = AA_IMAGESVIEW_THUMBNAILS;
			$folderViewMode = AA_FLDVIEW_LOCAL;
			$listViewImage = AA_LISTVIEW_NOIMG;

			$_SESSION['AA_SELECTED_OBJ'] = $selectedObjectId;
			$_SESSION['AA_OBJ_TYPE'] = $currentObjectType;

			aa_getViewOptions( $currentUser, $selectedObjectId, $visibleColumns, $viewMode, $sorting, $recordsPerPage, $showSharedPanel,
								$imgFieldsViewMode, $folderViewMode, $listViewImage, $kernelStrings, $readOnly, false, $currentObjectType );

			if ( $viewMode == UL_LIST_VIEW )
				$imgFieldsViewMode = AA_IMAGESVIEW_LINKS;

			$groupsHidden = aa_getUserOptionValue( $AA_APP_ID, $currentUser, 'AA_GROUPSHIDDEN' );

			$hideLeftPanel = $groupsHidden || !is_null( $searchString );

			if ( $viewMode == UL_LIST_VIEW ) {
				$imageFieldNames = array();

				foreach ( $fieldsPlainDesc as $fieldId=>$fieldData )
					if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_IMAGE)
						$imageFieldNames[$fieldId] = $fieldData[CONTACT_FIELDGROUP_SHORTNAME];

				if ( !array_key_exists($listViewImage, $imageFieldNames) )
					$listViewImage = AA_LISTVIEW_NOIMG;
			}

			if ( !isset($currentPage) || !strlen($currentPage) ) {
				if ( !$folderChanged ) {
					$currentPage = aa_getUserOptionValue( $AA_APP_ID, $currentUser, 'AA_CURRENTPAGE' );
				} else
					$currentPage = 1;

				if ( !strlen($currentPage) )
					$currentPage = 1;
			}

			// Load data for the current object type
			//
			$userRecordsFound = false;

			$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc, $language );

			$sortData = parseSortStr($sorting);

			if ( ( ($sortData['field'] == 'CF_NAME') && !strlen($searchString) ) 
				|| ( ($sortData['field'] == 'U_ID') && ($currentObjectType != OT_GROUP || strlen($searchString)) ) ) {
				$sorting = sprintf( "%s asc", CONTACT_NAMEFIELD );
				$sortData = parseSortStr($sorting);
			}

			if ( !(($sortData['field'] == 'CF_NAME') && strlen($searchString)) && $sortData['field'] != 'U_ID' )
				$sortStr = getColumnSortString( $sorting, $fieldsPlainDesc );
			else
				$sortStr = $sorting;

			$callbackParams = array();
			$callbackParams['fieldsPlainDesc'] = $fieldsPlainDesc;
			$callbackParams['typeDescription'] = $typeDescription;
			$callbackParams['kernelStrings'] = $kernelStrings;
			$callbackParams['language'] = $language;
			$callbackParams['visibleColumns'] = $visibleColumns;
			$callbackParams['viewMode'] = $viewMode;
			$callbackParams['searchString'] = $searchString;
			$callbackParams['cmFilesPath'] = getContactsAttachmentsDir();
			$callbackParams['imgFieldsViewMode'] = $imgFieldsViewMode;
			$callbackParams['listViewImage'] = $listViewImage;

			$thumbPerms = array();

			if ( is_null($searchString) ) {
				include( 'ung_groups.php' );
			} else {
				$res = $ContactCollection->findContacts( $searchString, $currentUser, $sortStr, $callbackParams, 'prepareContactEntry', $kernelStrings, false, null, true );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					break;
				}

				$totalObjects = count($ContactCollection->items);
				$showPageSelector = false;
				$pages = array();
				$pageCount = 0;

				$ContactCollection->items = addPagesSupport( $ContactCollection->items, $recordsPerPage, $showPageSelector, $currentPage, $pages, $pageCount );
			}

			// Prepare the Contact menu
			//
			$contactMenu = array();

			if ( is_null($searchString) ) {
				$contactMenu[$kernelStrings['ul_adduser_menu']] = sprintf( $processButtonTemplate, 'addcontactbtn' );

				$matrixObj = null;
				if ( !$selectedIsSystem )
					$matrixObj = AA_MTX_GROUP;
				else {
					if ( $selectedGroup == UGR_ACTIVE )
						$matrixObj = AA_MTX_ACTIVEUSERS;
					elseif ( $selectedGroup == UGR_INACTIVE )
						$matrixObj = AA_MTX_INACTIVEUSERS;
					elseif ( $selectedGroup == UGR_DELETED )
						$matrixObj = AA_MTX_DELETEDUSERS;
					else
						$matrixObj = AA_MTX_GROUP;
				}

				foreach ( $contactMenuMatrix as $itemData ) {
					if ( $itemData[0] != AA_MTX_SEPARATOR ) {
						if ( $itemData[0] == $currentObjectType ) {
							if ( $itemData[1] != AA_MTX_SEPARATOR ) {
								if ( (is_array($itemData[3]) && in_array( $matrixObj, $itemData[3] )) || $itemData[3] == "*" )
									$contactMenu[$itemData[1]] = $itemData[2];
								else
									$contactMenu[$itemData[1]] = null;
							} else
								$contactMenu[] = '-';
						}
					} else 
						$contactMenu[] = '-';
				}

				$cnt = count($contactMenu);
				$keys = array_keys($contactMenu);
				$lastIsSeparator = $contactMenu[$keys[$cnt-1]] == '-';

				$contactMenu[] = '-';

				$contactMenu[$kernelStrings['cm_import_menu']] = sprintf( $processButtonTemplate, 'importbtn' );
				$contactMenu[$kernelStrings['cm_export_menu']] = sprintf( $processButtonTemplate, 'exportbtn' );
				$contactMenu[] = '-';
				$contactMenu[$kernelStrings['cm_print_menu']] = sprintf( $processButtonTemplate, 'printbtn' );
			} else {
				$contactMenu[$kernelStrings['ul_deleteusers_menu']] = sprintf( $processButtonTemplate, 'deletecontactbtn' )."||confirmContactDeletion()";
				$contactMenu[] = '-';

				$contactMenu[$kernelStrings['cm_export_menu']] = sprintf( $processButtonTemplate, 'exportbtn' );
				$contactMenu[] = '-';
				$contactMenu[$kernelStrings['cm_print_menu']] = sprintf( $processButtonTemplate, 'printbtn' );
			}

			aa_setUserOptionValue( $AA_APP_ID, $currentUser, 'AA_CURRENTPAGE', $currentPage, $kernelStrings );
			$_SESSION['THUMBPERMS'] = $thumbPerms;

			// Read initial folder tree panel width
			//
			if ( isset($_COOKIE['splitterView'.$AA_APP_ID.$currentUser]) )
				$treePanelWidth = (int)$_COOKIE['splitterView'.$AA_APP_ID.$currentUser];
			else
				$treePanelWidth = 200;

			// Link for hide group list icon
			//
			$params = array();
			$params[ACTION] = HIDE_FOLDER;
			$hideGroupsLink = prepareURLStr( PAGE_USERSANDGROUPS, $params ); 

			// Prepare pages links
			//
			foreach( $pages as $key => $value ) {
				$params = array();
				$params[PAGES_CURRENT] = $value;

				$URL = prepareURLStr( PAGE_USERSANDGROUPS, $params );
				$pages[$key] = array( $value, $URL );
			}

			// Base link for sorting columns
			//
			if ( is_null($searchString) ) {
				$params = array( SELECTED_FOLDER_ID=>base64_encode($currentObjectId), PAGES_CURRENT=>$currentPage );
				$baseSortLink = prepareURLStr( PAGE_USERSANDGROUPS, $params );
			} else {
				$params = array( PAGES_CURRENT=>$currentPage );
				$baseSortLink = prepareURLStr( PAGE_USERSANDGROUPS, $params );
			}

			// Prepare view settings
			//
			$foldersHidden = aa_getUserOptionValue( $AA_APP_ID, $currentUser, 'AA_FOLDERSHIDDEN' );

			$hideLeftPanel = $foldersHidden || !is_null( $searchString );
			$showFolderSelector = $foldersHidden && is_null( $searchString );

			// Prepare the object combobox content
			//
			if ( $foldersHidden ) {
				$fullObjectList = array();

				if ( !isset($groups) ) {
					$groups = listUserGroups( $kernelStrings );
					if ( PEAR::isError($groups) ) {
						$fatalError = true;
						$errorStr = $groups->getMessage();

						break;
					}
				}

				foreach ( $groups as $UG_ID=>$groupData ) {
					$groupData = (object)$groupData;

					$encodedID = base64_encode($UG_ID);
					$groupData->ID = OT_GROUP.$encodedID;
					$groupData->NAME = $groupData->UG_NAME;
					$groupData->OFFSET_STR = "&nbsp;&nbsp;";

					$fullObjectList[] = $groupData;
				}

				if ( !isset($ContactListCollection) ) {
					$ContactListCollection = new ContactListCollection();

					$callbackParams = null;
					$res = $ContactListCollection->loadContactLists( 'CL_NAME ASC', null, null, $currentUser, $listCallbackParams, null, $kernelStrings );
				}
			}

			// Prepare the View menu
			//
			$checked = ($viewMode == UL_GRID_VIEW) ? "checked" : "unchecked";
			$viewMenu[$kernelStrings['cm_gridview_menu']] = sprintf( $processButtonTemplate, 'gridviewbtn' )."||null||$checked";

			$checked = ($viewMode == UL_LIST_VIEW) ? "checked" : "unchecked";
			$viewMenu[$kernelStrings['cm_listview_menu']] = sprintf( $processButtonTemplate, 'listviewbtn' )."||null||$checked";

			$viewMenu[$kernelStrings['cm_custview_menu']] = sprintf( $processButtonTemplate, 'custviewbtn' );

			// Prepare status names
			//
			$statusNames = array();
			foreach ( $commonStatusNames as $key=>$value )
				$statusNames[$key] = $kernelStrings[$value];
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['ung_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_USERSANDGROUPS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "popupStr", addSlashes($popupStr) );

	if ( !$fatalError ) {
		if ( isset($searchString) )
			$preproc->assign( "searchString", $searchString );

		$preproc->assign( PAGES_SHOW, $showPageSelector );
		$preproc->assign( PAGES_PAGELIST, $pages );
		$preproc->assign( PAGES_CURRENT, $currentPage );
		$preproc->assign( PAGES_NUM, $pageCount );
		$preproc->assign( SORTING_COL, $sorting );

		$preproc->assign( "contactMenu", $contactMenu );
		$preproc->assign( "statusNames", $statusNames );

		$preproc->assign( "currentObjectType", $currentObjectType );
		$preproc->assign( "groupsMode", $currentObjectType == OT_GROUP );
		
		$preproc->assign( "treePanelWidth", $treePanelWidth );
		$preproc->assign( 'hideGroupsLink', $hideGroupsLink );

		$preproc->assign( 'userRecordsFound', $userRecordsFound );

		$preproc->assign( 'genericLinkUnsorted', $baseSortLink );
		$preproc->assign( 'hideLeftPanel', $hideLeftPanel );
		$preproc->assign( 'numColumns', count($visibleColumns) );
		$preproc->assign( 'visibleColumns', $visibleColumns );
		$preproc->assign( 'typeDescription', $typeDescription );

		if (isset($totalObjects)) {
			$preproc->assign( "numDocuments", $totalObjects );
			$preproc->assign( 'numUsers', $totalObjects );
		}

		$preproc->assign( "contacts", $ContactCollection->items );
		$preproc->assign( "statisticsMode", $statisticsMode );
		$preproc->assign( "contactSection", $contactSection );
		$preproc->assign( "fieldsPlainDesc", $fieldsPlainDesc );

		$preproc->assign( 'viewMode', $viewMode );
		$preproc->assign( 'listViewImage', $listViewImage );

		$preproc->assign( 'imgFieldsViewMode', $imgFieldsViewMode );
		$preproc->assign( "showSharedPanel", $showSharedPanel );

		if ( $foldersHidden ) {
			$preproc->assign( "fullObjectList", $fullObjectList );
			$preproc->assign( "curObjectId", $currentObjectType.base64_encode($selectedObjectId) );
			$currentObjectType.base64_encode($selectedObjectId);
		}

		if ( isset( $updateAll ) )
			$preproc->assign( "updateAll", $updateAll );

		if ( is_null($searchString) ) {
			$preproc->assign( 'groups', $groups );
			$preproc->assign( 'rightPanelHeader', prepareStrToDisplay($curGroupName) );
			$preproc->assign( 'selectedGroup', $selectedGroup );
			$preproc->assign( 'groupsSummaryUrl', $groupsSummaryUrl );

			if ( $selectedGroup == TREE_AVAILABLE_FOLDERS ) {
				$preproc->assign( 'summaryStr', $summaryStr );
			}
		} else
			$preproc->assign( 'rightPanelHeader', $kernelStrings['cm_searchresult_title'] );

		$preproc->assign( 'contactMenu', $contactMenu );

		if ( is_null($searchString) ) {
			$preproc->assign( 'groupMenu', $groupMenu );
		}

		$preproc->assign( 'viewMenu', $viewMenu );
	}

	$preproc->display( "usersandgroups.htm" );
?>