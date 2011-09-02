<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/AA/aa.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "UNG";
	$activeTab = null;

	define( 'TAB_GROUP', 'IDGROUP' );
	define( 'TAB_USERS', 'IDUSERS' );
	define( 'TAB_ACCESS', 'ACCESS' );

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$invalidField = null;
	$included_users_names = array();
	$notincluded_users_names = array();

	$rightMasks = array( TREE_ONLYREAD => array(1,0,0),
						 TREE_WRITEREAD => array(1,1,0),
						 TREE_READWRITEFOLDER => array(1,1,1) );

	//
	// Helper functions
	//

	function formatAccessSettingsStr( $groupData, $kernelStrings )
	{
		global $currentUser;

		$buf = array();

		$groups = findGroupsContaningUser( $currentUser, $kernelStrings );
		$groups = serialize( $groups );
		$buf[] = $groups;

		return base64_encode( serialize( implode(',', $buf) ) );
	}

	// Decode group identifier
	//
	$targetGroup = isset($UG_ID) ? base64_decode( $UG_ID ) : null;

	if ( isset($lastTab) && strlen($lastTab) )
		$activeTab = $lastTab;

	//
	// Form handling
	//

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	switch ( $btnIndex ) {
			case 0 :
					if ( isset($edited) && !isset($included_users) )
						$included_users = array();

					if ( isset($edited) && !isset($notincluded_users) )
						$notincluded_users = array();

					$groupData['UG_ID'] = $targetGroup;

					// Prepare group data to save
					//
					$preparedData = prepareArrayToStore( $groupData );

					// Prepare checkbox parameters
					//
					$preparedData = rescueElement( $preparedData, ALLOW_DRACCESS, 0 );
					$preparedData = rescueElement( $preparedData, U_RECEIVESMESSAGES, 0 );
					$preparedData = rescueElement( $preparedData, U_CHANGEPASSWORD_RIGHT, 0 );
					$preparedData = rescueElement( $preparedData, U_CHANGETEMPLATE_RIGHT, 0 );
					$preparedData = rescueElement( $preparedData, U_CHANGENAME_RIGHT, 0 );
					$preparedData = rescueElement( $preparedData, U_SWITCHEMAIL_RIGHT, 0 );

					$res = addmodUserGroup( $action, $preparedData, $included_users, $kernelStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						$invalidField = $res->getUserInfo();

						break;
					}

					if ( $action == ACTION_NEW )
						$groupData['UG_ID'] = $res;

					$groupAccessRights[UR_REAL_ID] = $res;
					$saveResult =  $UR_Manager->SavePath( $groupAccessRights );
					if ( PEAR::isError( $saveResult ) )
					{
						$errorStr = $saveResult->getMessage();
						break;
					}

					$params = array();

					if ( $action == ACTION_NEW ) {
						$params['selectedGroup'] = base64_encode($res);
					}

					if ( $action == ACTION_EDIT ) {
						$newAccessSettings = formatAccessSettingsStr( $groupData, $kernelStrings );
						if ( $newAccessSettings != $prevAccessSettings )
							$params["updateAll"] = 1;
					}

					redirectBrowser( PAGE_USERSANDGROUPS, $params );
			case 1 :
					$params = array();

					redirectBrowser( PAGE_USERSANDGROUPS, $params );
	}

	//
	// Prepare page data
	//

	switch (true) {
			case true :

						// Prepare group data
						//
						if ( $action == ACTION_NEW ) {
							// Group name label
							//
							$groupName = $kernelStrings['amgr_newgroup_label'];

							// Init group data
							//
							if ( !isset($edited) )
								$groupData = array();
						} else {
							if ( !isset($edited) ) {
								// Load group data
								//
								$groupData = db_query_result( $qr_selectugroup, DB_ARRAY, array( 'UG_ID'=>$targetGroup ) );
								if ( PEAR::isError($groupData) ) {
									$errorStr = $groupData->getMessage();
									$fatalError = true;

									break;
								}

								// Load group content
								//
								$sortStr = df_contactname_sort( 'ASC' );
								$groupContent = loadUserGroupContent( $targetGroup, $sortStr, $kernelStrings );
								if ( PEAR::isError($groupContent) ) {
									$errorStr = $groupContent->getMessage();
									$fatalError = true;

									break;
								}

								$groupName = $groupData['UG_NAME'];
								$included_users = array_keys( $groupContent );

								$prevAccessSettings = formatAccessSettingsStr( $groupData, $kernelStrings );
							}
						}

						if ( !isset( $edited ) )
							$groupAccessRights = array( UR_PATH=>'/ROOT', UR_ACTION=>UR_ACTION_EDITGROUP, UR_ID=> ( ( $action == ACTION_EDIT) ? $targetGroup : null ),  UR_FIELD=>"groupAccessRights" );
						else
						{
							if ( $groupAccessRights[UR_ID] == UR_SYS_ID && !isset( $groupAccessRights[UR_REAL_ID] ) )
								$groupAccessRights[UR_ID] = null;
						}

						$groupAccessRightsHtml =  $UR_Manager->RenderPath( $groupAccessRights );
						if ( PEAR::isError( $groupAccessRightsHtml ) )
						{
							$fatalError = true;
							$errorStr = $groupAccessRightsHtml->getMessage();
							break;
						}						// Prepare user lists
						//
						$statusList = array( RS_ACTIVE, RS_LOCKED );
						$systemUsers = listSystemUsers( $statusList, $kernelStrings );
						if ( PEAR::isError($systemUsers) )
						{
							$fatalError = true;
							$errorStr = $systemUsers->getMessage();

							break;
						}

						$fullUserListIDs = array_keys($systemUsers);

						$notincluded_users = array();

						if ( $action == ACTION_NEW && !isset($edited) )
							$included_users = array();
						else
							if ( isset($edited) && !isset($included_users) )
								$included_users = array();

						$notincluded_users = array_diff( $fullUserListIDs, $included_users );

						foreach( $included_users as $key )
							$included_users_names[] = getArrUserName( $systemUsers[$key] );

						foreach( $notincluded_users as $key )
							$notincluded_users_names[] = getArrUserName( $systemUsers[$key] );

						// Prepare page tabs structure
						//
						$tabs = array();

						$tabs[] = array( PT_NAME=>$kernelStrings['amgr_group_title'],
											PT_PAGE_ID=>TAB_GROUP,
											PT_FILE=>'amg_grouptab.htm',
											PT_CONTROL=>'groupData[UG_NAME]' );
						$tabs[] = array( PT_NAME=>$kernelStrings['amgr_users_title'],
											PT_PAGE_ID=>TAB_USERS,
											PT_FILE=>'amg_userstab.htm' );
						$tabs[] = array( PT_NAME=>$kernelStrings['amgr_access_title'],
											PT_PAGE_ID=>TAB_ACCESS,
											PT_FILE=>'amg_accesstab.htm' );
	}

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, ( $action == ACTION_NEW ) ? $kernelStrings['amgr_pageadd_title'] : $kernelStrings['amgr_pagemod_title'] );
	$preproc->assign( FORM_LINK, PAGE_ADDMODGROUP );
	$preproc->assign( ACTION, $action );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preproc->assign( "groupName", $groupName );
		$preproc->assign( "tabs", $tabs );
		$preproc->assign( "activeTab", $activeTab );

		$preproc->assign( "included_users", $included_users );
		$preproc->assign( "notincluded_users", $notincluded_users );

		$preproc->assign( "included_users_names", $included_users_names );
		$preproc->assign( "notincluded_users_names", $notincluded_users_names );

		$preproc->assign( "groupAccessRightsHtml", $groupAccessRightsHtml );

		if ( isset($groupData) )
			$preproc->assign( "groupData", $groupData );

		if ( $action == ACTION_EDIT ) {
			$preproc->assign( "UG_ID", $UG_ID );
			$preproc->assign( "targetGroup", $targetGroup );
			$preproc->assign( "prevAccessSettings", $prevAccessSettings );
		}

		if ( isset($auxrights_cb) )
			$preproc->assign( "auxrights_cb", $auxrights_cb );

		if ( isset($opener) )
			$preproc->assign( OPENER, $opener );

		if ( isset($openerParams) )
			$preproc->assign( OPENER_PARAMS, $openerParams );
	}

	$preproc->display( "addmodgroup.htm" );
?>