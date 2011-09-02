<?php

	$btnIndex = getButtonIndex( array( 'addcontactbtn', 'addgroupbtn', 'modgroupbtn', 
										'delgroupbtn', 'copyfolderbtn', 'movefolderbtn', 'foldersbtn', 
										'deletecontactbtn', 'copybtn', 'movebtn', 'addtogroupbtn', 'removefromgroupbtn', 
										'addlistbtn', 'modlistbtn', 'sendemailbtn', 'sendsmsbtn', 
										'gridviewbtn', 'listviewbtn', 'custviewbtn', 'showFoldersBtn',
										'revokebtn', 'changeuserstatus', 'addtolistbtn', 'importbtn', 'exportbtn', 'dellistbtn', 'printbtn'), $_POST );

	switch ($btnIndex) {

		case 0 :
				// Add contact
				//
				$targetFolder = null;

				redirectBrowser( PAGE_ADDMODUSER, array( ACTION=>ACTION_NEW, 'curCF_ID'=>$targetFolder ) );

		case 1 :
				// Add group
				//
				$params = array( ACTION=>ACTION_NEW );
				redirectBrowser( PAGE_ADDMODGROUP, $params );
		case 2 :
				// Modify group
				//
				$params = array( ACTION=>ACTION_EDIT, 'UG_ID'=>base64_encode($selectedGroup) );
				redirectBrowser( PAGE_ADDMODGROUP, $params );
		case 3 : 
				// Delete group
				//
				$res = deleteUserGroup( $selectedGroup, $kernelStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();
					break;
				}

				$selectedGroup = TREE_AVAILABLE_FOLDERS;
				break;
		case 4 :
				// Copy folder
				//
				$commonRedirParams['operation'] = TREE_COPYFOLDER;
				$commonRedirParams['CF_ID'] = base64_encode($curCF_ID);
				redirectBrowser( PAGE_COPYMOVE, $commonRedirParams );
		case 5 :
				// Move folder
				//
				$commonRedirParams['operation'] = TREE_MOVEFOLDER;
				$commonRedirParams['CF_ID'] = base64_encode($curCF_ID);
				redirectBrowser( PAGE_COPYMOVE, $commonRedirParams );
		case 6 :
				// Clear search results
				//
				$searchString = null;

				$_SESSION['AA_SEARCHSTRING'] = null;
				break;
		case 7 :
				// Delete contacts
				//

				if ( !isset($document) )
					break;

				if ( !isset($document) || !count($document) ) 
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['contactlist'] = $documentList;

				redirectBrowser( PAGE_DELETEUSERS, $commonRedirParams, "", false, false, true  );

				break;

		case 8 :
				// Copy contacts
				//
				if ( !isset($document) )
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['operation'] = TREE_COPYDOC;

				if ( !strlen($searchString) )
					$commonRedirParams['CF_ID'] = base64_encode($curCF_ID);
				else
					$commonRedirParams['CF_ID'] = null;

				redirectBrowser( PAGE_COPYMOVE, $commonRedirParams, "", false, false, true  );
		case 9 : 
				// Move contacts
				//
				if ( !isset($document) )
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['operation'] = TREE_MOVEDOC;

				if ( !strlen($searchString) )
					$commonRedirParams['CF_ID'] = base64_encode($curCF_ID);
				else
					$commonRedirParams['CF_ID'] = null;

				redirectBrowser( PAGE_COPYMOVE, $commonRedirParams, "", false, false, true );
		case 10 :
				// Add to group
				//
				if ( !isset($document) || !count($document) ) 
					break;

				$params = array();
				$params['USERS'] = base64_encode( serialize( array_keys($document) ) );
				$params['UG_ID'] = base64_encode( $selectedGroup );

				redirectBrowser( PAGE_ADDTOGROUP, $params, "", false, false, true  );
		case 11 :
				// Remove from group
				//
				if ( !isset($document) || !count($document) ) 
					break;

				foreach ( $document as $targetUser => $data ) {
					$targetUser = getContactUser( $targetUser, $kernelStrings );
					if ( !strlen($targetUser) )
						continue;

					if ( $targetUser == $currentUser )
						$updateAll = true;

					$res = registerUserInGroup( $targetUser, $selectedGroup, $kernelStrings, true );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();

						break;
					}
				}

				break;
		case 12 :
				// Create list
				//
				break;
		case 13 :
				// Modify list
				//
				break;
		case 14 :
				// Send email
				//
				break;
		case 15 :
				// Send SMS
				//
				break;
		case 16 : 
				// Grid view
				//
				aa_setFolderViewSettings( $currentUser, $selectedObjectId, UL_GRID_VIEW, $kernelStrings, $currentObjectType );
				break;
		case 17 :
				// List view
				//
				aa_setFolderViewSettings( $currentUser, $selectedObjectId, UL_LIST_VIEW, $kernelStrings, $currentObjectType );
				break;
		case 18 : 
				// Customize view
				//
				redirectBrowser( PAGE_CUSTOMIZEVIEW, array() );
		case 19 :
				// Show folders panel
				//
				$foldersHidden = false;
				aa_setUserOptionValue( $AA_APP_ID, $currentUser, 'AA_FOLDERSHIDDEN', $foldersHidden, $kernelStrings );

				break;
		case 20 :
				// Revoke user privileges
				//
				if ( !isset($document) || !count($document) ) 
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['contactlist'] = $documentList;

				redirectBrowser( PAGE_REVOKEUSERPRIVS, $commonRedirParams, "", false, false, true  );

				break;
		case 21 :
				// Change user status
				//
				if ( !isset($document) || !count($document) ) 
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['contactlist'] = $documentList;

				redirectBrowser( PAGE_CHANGEUSERSTATUS, $commonRedirParams, "", false, false, true  );

				break;
		case 22 :
				// Add to list
				//
				break;
		case 23 :
				// Import user list
				//
				$params = array();

				redirectBrowser( PAGE_IMPORTUSERS, $params );

		case 24 :
				// Export
				//
				if ( !isset($document) || !count($document) )
					$docList = array();
				else
					$docList = array_keys( $document );

				$params = array();
				$params['doclist'] = base64_encode( serialize($docList) );

				if ( is_null($searchString) )
					$params['objType'] = base64_encode( $currentObjectType );
				else
					$params['objType'] = base64_encode( AA_SEARCH_RESULT );

				$params['selectedObj'] = base64_encode( $selectedObjectId );

				redirectBrowser( PAGE_EXPORTUSERS, $params, "", false, false, true  );
		case 25 :
				// Delete list
				//
				$ContactList = new ContactList();

				$ContactList->deleteList( $selectedList, $kernelStrings );
				$selectedList = TREE_AVAILABLE_FOLDERS;
				setAppUserCommonValue( $AA_APP_ID, $currentUser, SELECTED_LIST, $selectedList, $kernelStrings, $readOnly );

				break;
		case 26 :
				// Print
				//
				if ( !isset($document) || !count($document) )
					$docList = array();
				else
					$docList = array_keys( $document );

				$params = array();
				$params['doclist'] = base64_encode( serialize($docList) );

				if ( is_null($searchString) )
					$params['objType'] = base64_encode( $currentObjectType );
				else
					$params['objType'] = base64_encode( AA_SEARCH_RESULT );

				$params['selectedObj'] = base64_encode( $selectedObjectId );

				redirectBrowser( PAGE_PRINT, $params, "", false, false, true  );
	}

?>