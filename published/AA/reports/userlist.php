<?php

	require_once( "../../common/reports/reportsinit.php" );
	require_once( WBS_DIR."/published/AA/aa.php" );

	//
	// System information report
	//

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "UNG";
	$contactList = array();
	$groupData = array();
	$viewMode = 0;

	define( "PRINT_PAGE_GETFILETHUMB", "../../common/html/scripts/getfilethumb.php" );

	reportUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	function getFileModDateTime( $filePath, $ext )
	{
		$thumbPath = findThumbnailFile( $filePath, $ext );

		if ( !is_null($thumbPath) )
			return filemtime($thumbPath);
		else
			return 0;
	}

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$visibleColumns = array();

	switch ( true ) {
		case true :
						// Load user view settings
						//
						if ( isset($curFolderID) )
							$folderID = base64_decode($curFolderID);
						else
							$folderID = null;

						$visibleColumns = null;
						$viewMode = null;
						$sorting = null;
						$recordsPerPage = null;
						$showSharedPanel = null;
						$folderViewMode = null;
						$listViewImage = null;

						$selectedObjectId = $_SESSION['AA_SELECTED_OBJ'];

						aa_getViewOptions( $currentUser, $selectedObjectId, $visibleColumns, $viewMode, $sorting, $recordsPerPage, $showSharedPanel,
											$imgFieldsViewMode, $folderViewMode, $listViewImage, $kernelStrings, $readOnly, false );

						if ( $viewMode == UL_GRID_VIEW && !in_array(CONTACT_NAMEFIELD , $visibleColumns) )
							$visibleColumns = array_merge( array( CONTACT_NAMEFIELD ), $visibleColumns );

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
						$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, true, true );

						if ( $printMode == 2 ) {
							$sortStr = df_contactname_sort("ASC");
						} else {
							$sortData = parseSortStr($sorting);
							$searchString = $_SESSION['AA_SEARCHSTRING'];

							$sortData = parseSortStr($sorting);

							if ( ( ($sortData['field'] == 'CF_NAME') && !strlen($searchString) ) 
								|| ( ($sortData['field'] == 'U_ID') && (strlen($searchString)) ) ) {
								$sorting = sprintf( "%s asc", CONTACT_NAMEFIELD );
								$sortData = parseSortStr($sorting);
							}

							if ( !(($sortData['field'] == 'CF_NAME') && strlen($searchString)) && $sortData['field'] != 'U_ID' )
								$sortStr = getColumnSortString( $sorting, $fieldsPlainDesc );
							else
								$sortStr = $sorting;
						}

						$cmFilesPath = getContactsAttachmentsDir();

						// Load contact list depending on selected print option
						//
						switch ($printMode) {
							case 0 :
									// Selected contacts
									//
									if ( !isset($doclist) )
										break 2;

									$contactList = array();

									$contacts = unserialize( base64_decode( $doclist ) );
									$contacts = sprintf( "'%s'", implode( "','", $contacts ) );

									$qr = db_query( sprintf( $qr_selectSpecificContacts, $contacts, $sortStr ), array() );
									while ( $row = db_fetch_array($qr) ) {
										$row = applyContactTypeDescription( $row, array(), $fieldsPlainDesc, $kernelStrings, UL_LIST_VIEW );
										$contactList[] = $row;
									}

									db_free_result($qr);

									break;
							case 1 :
									// Selected object
									//
									$contactList = array();
			
									$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc, $language );
									$callbackParams = array();

									$objectType = base64_decode( $objType ); 

									if ( $objectType != AA_SEARCH_RESULT ) {
										$ContactCollection->loadFromUserGroup( $currentSrcId, $sortStr, null, null, $callbackParams, null, $kernelStrings );
									} else {
										$searchString = $_SESSION['AA_SEARCHSTRING'];
										$res = $ContactCollection->findContacts( $searchString, $currentUser, $sortStr, $callbackParams, null, $kernelStrings, false, null, true );
										if ( PEAR::isError($res) ) {
											$errorStr = $res->getMessage();

											break 2;
										}
									}

									$contactList = $ContactCollection->items;

									foreach( $contactList as $key=>$data ) {
										$data = (array)$data;
										$row = applyContactTypeDescription( $data, array(), $fieldsPlainDesc, $kernelStrings, UL_LIST_VIEW );
										$contactList[$key] = $row;
									}

									break;
							case 2 :
									// Entire user list
									//
									$contactList = array();

									$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc, $language );
									$callbackParams = array();

									$userId = isAdministratorID($currentUser) ? null : $currentUser;
									$ContactCollection->loadUserAvailableContacts( $userId, $sortStr, null, null, $callbackParams, null, $kernelStrings );

									$contactList = $ContactCollection->items;

									foreach( $contactList as $key=>$data ) {
										$data = (array)$data;
										$row = applyContactTypeDescription( $data, array(), $fieldsPlainDesc, $kernelStrings, UL_LIST_VIEW );
										$contactList[$key] = $row;
									}
						}

						$thumbPerms = array();

						$userRecordsFound = true;
						foreach ( $contactList as $key=>$contactData ) {

							foreach ( $fieldsPlainDesc as $fieldId=>$fieldData ) {
								$contactFieldData = $contactData[$fieldId];

								if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_IMAGE && isset($contactData[$fieldId]) ) {
									$thumbPath = $cmFilesPath."/".base64_decode($contactFieldData[CONTACT_IMGF_DISKFILENAME]);

									$thumbPerms[] = $thumbPath;

									$thumbParams = array();
									$srcExt = null;
									$thumbParams['nocache'] = getFileModDateTime( $thumbPath, 'win', $srcExt );
									$thumbParams['basefile'] = base64_encode($cmFilesPath."/".base64_decode($contactFieldData[CONTACT_IMGF_DISKFILENAME]));
									$thumbParams['ext'] = base64_encode( $contactFieldData[CONTACT_IMGF_TYPE] );

									$contactFieldData['THUMB_URL'] = prepareURLStr( PRINT_PAGE_GETFILETHUMB, $thumbParams );
									$contactData[$fieldId] = $contactFieldData;
								}
							}

							$contactList[$key] = $contactData;
						}

						$_SESSION['THUMBPERMS'] = $thumbPerms;

						// Prepare status names
						//
						$statusNames = array();
						foreach ( $commonStatusNames as $key=>$value )
							$statusNames[$key] = $kernelStrings[$value];
	}

	$preprocessor = new print_preprocessor( $AA_APP_ID, $kernelStrings, $language );

	$preprocessor->assign( REPORT_TITLE, $kernelStrings['cm_screen_long_name'] );
	$preprocessor->assign( ERROR_STR, $errorStr );
	$preprocessor->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preprocessor->assign( "contactList", $contactList );
		$preprocessor->assign( "printMode", $printMode );
		$preprocessor->assign( "viewMode", $viewMode );
		$preprocessor->assign( "printingMode", 1 );
		$preprocessor->assign( "statusNames", $statusNames );
		$preprocessor->assign( "userRecordsFound", $userRecordsFound );

		$preprocessor->assign( "imgFieldsViewMode", $imgFieldsViewMode );
		$preprocessor->assign( "listViewImage", $listViewImage );
		$preprocessor->assign( 'numUsers', count($contactList) );
		$preprocessor->assign( 'visibleColumns', $visibleColumns );
		$preprocessor->assign( 'numColumns', count($visibleColumns) );
		$preprocessor->assign( 'typeDescription', $typeDescription );
		$preprocessor->assign( 'fieldsPlainDesc', $fieldsPlainDesc );
		$preprocessor->assign( 'groupData', $groupData );
	}

	$preprocessor->display( "userlist.htm" );
?>