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
	$shownFieldsNames = array();
	$hiddenFieldsNames = array();

	if ( !isset($listViewImage) )
		$listViewImage = AA_LISTVIEW_NOIMG;

	//
	// Helper functions
	//

	function fillFieldNames( $fields, $fieldsPlainDesc ) 
	{
		$result = array();

		foreach( $fields as $key ) {
			$fieldData = $fieldsPlainDesc[$key];
			if ( is_null($fieldData[CONTACT_FIELDGROUPNAME]) )
				$result[] = sprintf( "%s", $fieldsPlainDesc[$key][CONTACT_FIELDGROUP_LONGNAME] );
			else
				$result[] = sprintf( "%s - %s", $fieldData[CONTACT_FIELDGROUPNAME], $fieldsPlainDesc[$key][CONTACT_FIELDGROUP_LONGNAME] );
		}

		return $result;
	}

	//
	// Form handling
	//

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	switch ( $btnIndex ) {
			case 0 :
					if ( !isset($shownFieldsIDs) )
						$shownFieldsIDs = array();

					if ( !count($shownFieldsIDs) ) {
						$errorStr = $kernelStrings['cv_nofields_message'];
						break;
					}

					$columns = implode( ",", $shownFieldsIDs );
					if ( !strlen($columns) )
						$columns = UL_NOCOLUMNS;

					setAppUserCommonValue( $AA_APP_ID, $currentUser, 'AA_VISIBLECOLUMNS', $columns, $kernelStrings, $readOnly );
					setAppUserCommonValue( $AA_APP_ID, $currentUser, 'AA_RECORDPERPAGE', $recordsPerPage, $kernelStrings, $readOnly );

					setAppUserCommonValue( $AA_APP_ID, $currentUser, 'AA_FOLDEVIEWMODE', $folderViewMode, $kernelStrings, $readOnly );
					setAppUserCommonValue( $AA_APP_ID, $currentUser, 'AA_IMAGEVIEWMODE', $imgFieldsViewMode, $kernelStrings, $readOnly );

					setAppUserCommonValue( $AA_APP_ID, $currentUser, 'AA_LISTVIEWIMG', $listViewImage, $kernelStrings, $readOnly );

					redirectBrowser( PAGE_USERSANDGROUPS, array() );
			case 1 :
					redirectBrowser( PAGE_USERSANDGROUPS, array() );
	}

	//
	// Prepare page data
	//

	switch (true) {
			case true :
						// Load type description
						//
						$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, $language, $kernelStrings, false, true );
						if ( PEAR::isError($typeDescription) ) {
							$fatalError = true;
							$errorMessage = $typeDescription->getMessage();

							break;
						}

						// Obtain columns descriptions as a plain array
						//
						$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, true, true );

						// Load image field names
						//
						$imageFieldNames = array();

						foreach ( $fieldsPlainDesc as $fieldId=>$fieldData )
							if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_IMAGE) {
								$imageFieldNames[$fieldId] = $fieldData[CONTACT_FIELDGROUP_SHORTNAME];
							}

						// Load view settings
						//
						if ( !isset($edited) ) {
							$recordsPerPage = null;
							$showSharedPanel = null;
							$imgFieldsViewMode = null;
							$folderViewMode = null;
							$listViewImage = null;
							$res = aa_getViewOptions( $currentUser, null, $shownFieldsIDs, $viewMode, $sorting, $recordsPerPage, 
														$showSharedPanel, $imgFieldsViewMode, $folderViewMode, $listViewImage,
														$kernelStrings, $readOnly, true );
							if ( PEAR::isError($res) ) {
								$errorStr = $res->getMessate();

								$fatalError = true;
								break;
							}

							if ( !array_key_exists($listViewImage, $imageFieldNames) )
								$listViewImage = AA_LISTVIEW_NOIMG;
						}

						// Prepare lists
						//
						$fullFieldIDs = array_keys( $fieldsPlainDesc );

						$hiddenFieldsIDs = array();

						if ( isset($edited) && !isset($shownFieldsIDs) )
							$shownFieldsIDs = array();

						$hiddenFieldsIDs = array_diff( $fullFieldIDs, $shownFieldsIDs );
	
						$shownFieldsNames = fillFieldNames( $shownFieldsIDs, $fieldsPlainDesc ) ;
						$hiddenFieldsNames = fillFieldNames( $hiddenFieldsIDs, $fieldsPlainDesc ) ;

					// Prepare form tabs
					//
					$tabs = array();

					$tabs[] = array( PT_NAME=>$kernelStrings['cv_general_tab'], 
										PT_PAGE_ID=>'GENERAL', 
										PT_FILE=>'cv_generaltab.htm',
										);
					$tabs[] = array( PT_NAME=>$kernelStrings['cv_grid_tab'], 
										PT_PAGE_ID=>'GRID',
										PT_FILE=>'cv_gridtab.htm' );
					$tabs[] = array( PT_NAME=>$kernelStrings['cv_list_tab'], 
										PT_PAGE_ID=>'LIST', 
										PT_FILE=>'cv_listtab.htm' );
	}

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['cv_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_CUSTOMIZEVIEW );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preproc->assign( "hiddenFieldsIDs", $hiddenFieldsIDs );
		$preproc->assign( "shownFieldsIDs", $shownFieldsIDs );
		$preproc->assign( "shownFieldsNames", $shownFieldsNames );
		$preproc->assign( "hiddenFieldsNames", $hiddenFieldsNames );
		$preproc->assign( "imgFieldsViewMode", $imgFieldsViewMode );
		$preproc->assign( "folderViewMode", $folderViewMode );
		$preproc->assign( "imageFieldNames", $imageFieldNames );
		$preproc->assign( "listViewImage", $listViewImage );

		$preproc->assign( "tabs", $tabs );

		$preproc->assign( "recordsPerPage", $recordsPerPage );
		$preproc->assign( "recordsPerPageIDs", array( 10, 20, 30, 40, 50 ) );
	}

	$preproc->display( "customizeview.htm" );
?>