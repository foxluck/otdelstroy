<?php

	//
	// Account Administrator application functions
	//

	function aa_getViewOptions( $U_ID, $folderID, &$visibleColumns, &$viewMode, &$sorting, &$recordsPerPage, &$showSharedPanel, &$imgFieldsViewMode,
								&$folderViewMode, &$listViewImage, $kernelStrings, $useCookies, $actualColumns )
	//
	//	Returns view options for specified user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$folderID - contact folder ID
	//			$visibleColumns - array of visible columns
	//			$viewMode - view mode (UL_LIST_VIEW, UL_GRID_VIEW)
	//			$sorting - sorting column
	//			$recordsPerPage - number of files on one page
	//			$showSharedPanel - show shared panel in Document Depot window
	//			$imgFieldsViewMode - image fields view mode
	//			$folderViewMode - folder view mode
	//			$listViewImage - list view image ID
	//			$kernelStrings - Kernel localization strings
	//			$useCookies - use cookies instead of database
	//			$actualColumns - return actual visible columns list stored in DB, regardless current view mode
	//
	//		Returns null
	//
	{
		global $AA_APP_ID;
		global $ul_defaultColumnSet;
		global $ul_listColumnSet;
		global $UR_Manager;

		$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, LANG_ENG, $kernelStrings, false );
		$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, true, true );

		$visibleColumns = getAppUserCommonValue( $AA_APP_ID, $U_ID, 'AA_VISIBLECOLUMNS', null, $useCookies );

		if ( $visibleColumns === 0 || !strlen($visibleColumns) && $visibleColumns != UL_NOCOLUMNS )
			$visibleColumns = $ul_defaultColumnSet;
		else
			if ( $visibleColumns != UL_NOCOLUMNS )
				$visibleColumns = explode( ",", $visibleColumns );
			else
				$visibleColumns = array();

		$folderViewMode = getAppUserCommonValue( $AA_APP_ID, $U_ID, 'AA_FOLDERVIEWMODE', null, $useCookies );
		if ( !strlen($folderViewMode) )
			$folderViewMode = AA_FLDVIEW_LOCAL;

		$viewMode = null;
		aa_getLocalViewSettings( $U_ID, $folderID, $viewMode );
		
		if ( !strlen($viewMode) )
			$viewMode = UL_GRID_VIEW;

		if ( $viewMode == UL_LIST_VIEW && !$actualColumns )
			$visibleColumns = $ul_listColumnSet;

		$existingColumns = array();
		foreach ( $visibleColumns as $col_id ) {
			if ( array_key_exists($col_id, $fieldsPlainDesc) )
				$existingColumns[] = $col_id;
		}

		$visibleColumns = $existingColumns;

		$listViewImage = getAppUserCommonValue( $AA_APP_ID, $U_ID, 'AA_LISTVIEWIMG', null, $useCookies );
		if ( !strlen($listViewImage) )
			$listViewImage = AA_LISTVIEW_NOIMG;

		$recordsPerPage = getAppUserCommonValue( $AA_APP_ID, $U_ID, 'AA_RECORDPERPAGE', null, $useCookies );
		if ( !strlen($recordsPerPage) )
			$recordsPerPage = 30;

		$imgFieldsViewMode = getAppUserCommonValue( $AA_APP_ID, $U_ID, 'AA_IMAGEVIEWMODE', null, $useCookies );
		if ( !strlen($imgFieldsViewMode) )
			$imgFieldsViewMode = AA_IMAGESVIEW_THUMBNAILS;

		$showSharedPanel = $UR_Manager->GetUserRightValue( $U_ID, "/ROOT/AA/FOLDERS/VIEWSHARES" ) == UR_BOOL_TRUE;

		if ( !strlen($showSharedPanel) )
			$showSharedPanel = false;

		$sorting = getAppUserCommonValue( $AA_APP_ID, $U_ID, 'AA_SORTING', null, $useCookies );
		if ( !strlen($sorting) )
			$sorting = sprintf( "%s asc", CONTACT_NAMEFIELD );
		else {
			$sortData = parseSortStr($sorting);

			if ( !in_array($sortData['field'], $visibleColumns) && $sortData['field'] != 'CF_NAME' && $sortData['field'] != 'U_ID' )
				$sorting = sprintf( "%s asc", CONTACT_NAMEFIELD );
		}

		return null;
	}

	function aa_getLocalViewSettings( $U_ID, $folderID, &$viewMode )
	//
	// Returns folder view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$folderID - folder identifier
	//			$viewMode - view mode (DD_GRID_VIEW, DD_LIST_VIEW, DD_THUMBLIST_VIEW)
	//
	//		Returns null or PEAR_Error
	//
	{
		global $AA_APP_ID;

		if ( is_null($folderID) )
			return null;

	
		$viewMode = getAppUserCommonValue($AA_APP_ID, $U_ID, "AA_FOLDERVIEWMODE_".$folderID);		

		return null;
	}

	function aa_setFolderViewSettings( $U_ID, $folderID, $viewMode, &$kernelStrings )
	//
	// Sets folder view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$folderID - folder identifier
	//			$viewMode - folder view mode (grid, list)
	//			$kernelStrings - Kernel localization strings
	//
	{
		global $AA_APP_ID;

		$folderViewMode = getAppUserCommonValue( $AA_APP_ID, $U_ID, 'AA_FOLDERVIEWMODE', null, false );
		if ( !strlen($folderViewMode) )
			$folderViewMode = AA_FLDVIEW_LOCAL;

		// Set local view settings
		//
		aa_applyLocalViewSettings( $U_ID, $folderID, $folderViewMode, $viewMode, $kernelStrings );
	}

	function aa_applyLocalViewSettings( $U_ID, $folderID, $folderViewMode, $viewMode, $kernelStrings )
	//
	// Applies local view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$folderID - folder identifier
	//			$folderViewMode - folder view mode (AA_FLDVIEW_GLOBAL, AA_FLDVIEW_LOCAL)
	//			$viewMode - view mode (UL_GRID_VIEW, UL_LIST_VIEW)
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $AA_APP_ID;
		
		setAppUserCommonValue( $AA_APP_ID, $U_ID, 'AA_FOLDERVIEWMODE_'.$folderID, $viewMode, $kernelStrings);
		return null;
	}

	function aa_setObjectTypeViewSettings( &$folders, &$xpath, &$dom, &$foldersViewNode, $viewMode )
	//
	// Saves the view settings for the Contact Manager object type. Helper function for the aa_applyLocalViewSettings function
	//
	{
		foreach ( $folders as $ID=>$data ) {
			$folderElement = &xpath_eval( $xpath, "FOLDER[@ID='$ID']", $foldersViewNode );

			// Create element for folder if it doesn't exists already
			//
			if ( !count($folderElement->nodeset) ) {
				$folderNode = @create_addElement( $dom, $foldersViewNode, 'FOLDER' );
				$folderNode->set_attribute( 'ID', $ID );
			} else
				$folderNode = $folderElement->nodeset[0];

			if ( !is_null($viewMode) )
				$folderNode->set_attribute( 'VIEWMODE', $viewMode );
		}
	}

	function aa_deleteUserFolderViewSettings( $U_ID, $folderID, $kernelStrings )
	//
	// Deletes user folder view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$folderID - folder identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $AA_APP_ID;

		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if ( !$settingsElement )
			return $kernelStrings[ERR_XML];

		$result = array();

		$appNode = getElementByTagname( $settingsElement, $aa_APP_ID );
		if ( !$appNode )
			return null;

		$xpath = xpath_new_context($dom);

		$foldersViewNode = getElementByTagname( $appNode, 'FOLDERSVIEW' );
		if ( !$foldersViewNode )
			return null;

		$folderElement = &xpath_eval( $xpath, "FOLDER[@ID='$folderID']", $foldersViewNode );
		if ( !$folderElement || !count($folderElement->nodeset)  )
			return null;

		$folder = $folderElement->nodeset[0];
		$folder->unlink_node();

		$res = saveUserSettingsDOM( $U_ID, $dom, $settingsElement, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		return null;
	}

	function aa_getUnsortedContactsFolder( &$kernelStrings, $U_ID )
	//
	// Returns the _Unsorted contacts folder identifier
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$U_ID - user identifier
	//
	//		Returns string or PEAR_Error
	//
	{
		global $qr_selectUnsortedFolder;
		global $cm_groupClass;

		// Check whether the _Unsorted folder exists
		//
		$FolderID = db_query_result( $qr_selectUnsortedFolder, DB_FIRST, array() );
		if ( PEAR::isError($FolderID) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( strlen($FolderID) )
		{
			$rights = $cm_groupClass->getIdentityFolderRights( $U_ID, $FolderID, $kernelStrings );
			if ( !UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER ) )
				$cm_groupClass->setIdentityRights( $U_ID, IDT_USER, array( $FolderID=>TREE_READWRITEFOLDER ), $kernelStrings );

			return $FolderID;
		}

		// Create new _Unsorted folder
		//
		$folderData = array();
		$folderData['CF_NAME'] = UNSORTED_FOLDER_NAME;

		$FolderID = $cm_groupClass->addmodFolder( ACTION_NEW, SYS_USER_ID, APP_REG_RIGHTS_ROOT, $folderData, $kernelStrings, true );
		if ( PEAR::isError( $FolderID ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$setrights_ret = $cm_groupClass->setIdentityRights( $U_ID, IDT_USER, array( $FolderID=>TREE_READWRITEFOLDER ), $kernelStrings );
		if ( PEAR::isError( $setrights_ret ) )
			return $setrights_ret;

		return $FolderID;
	}

	function aa_deleteContact( $C_ID, $operatorU_ID, $kernelStrings, $language, $fieldsPlainDesc = null, $ignoreRights = false )
	//
	//	Deletes contact
	//
	//		Parameters:
	//			$C_ID - contact identifier
	//			$operatorU_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$language - user language
	//			$fieldsPlainDesc - columns descriptions as a plain array
	//			$ignoreRights - ignore user folder rights
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_deletecontact;
		global $cm_groupClass;
		global $qr_cm_resetContactFolder;

		// Check if fields description provided
		//
		if ( !is_null($fieldsPlainDesc) ) {
			// Load type description
			//
			$typeDesc = getContactTypeDescription( CONTACT_BASIC_TYPE, LANG_ENG, $kernelStrings, false );
			if ( PEAR::isError($typeDesc) )
				return $typeDesc;

			// Obtain columns descriptions as a plain array
			//
			$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDesc, $kernelStrings, true );
		}

		// Check user rights
		//
		$contactInfo = $cm_groupClass->getDocumentInfo( $C_ID, $kernelStrings );
		if ( PEAR::isError($contactInfo) )
			return $contactInfo;

		if ( !is_null($operatorU_ID) && !$ignoreRights ) {
			$rights = $cm_groupClass->getIdentityFolderRights( $operatorU_ID, $contactInfo['CF_ID'], $kernelStrings );
			if ( PEAR::isError($rights) )
				return $rights;

			if ( !UR_RightsManager::CheckMask( $rights, TREE_WRITEREAD ) )
				return PEAR::raiseError( $kernelStrings['cm_nomodrights_message'], ERRCODE_APPLICATION_ERR );
		}

		// Check if it is not a user contact
		//
		$U_ID = getContactUser( $C_ID, $kernelStrings );
		if ( PEAR::isError($U_ID) )
			return $U_ID;

		if ( !is_null($U_ID) ) {
			$Contact = new Contact( $kernelStrings, $language, null, null, false );

			$canManageUsers = aa_canManageUsers( $operatorU_ID );
			if ( !$canManageUsers && !$ignoreRights ) {
				return PEAR::raiseError( $kernelStrings['cm_nouserrights_message'], ERRCODE_APPLICATION_ERR );
			}

			$res = $Contact->revokeUserPrivileges( $C_ID, $kernelStrings, $operatorU_ID );
			if ( PEAR::isError($res) ) {
				return $res;
			}
		}

		return deleteContact( $C_ID, $kernelStrings, $fieldsPlainDesc );
	}

?>