<?php

	//
	// This script loads the Groups information for the Contact Manager screen
	//

	// Load the user groups list
	//
	$groups = listUserGroups( $kernelStrings );
	if ( PEAR::isError($groups) ) {
		$fatalError = true;
		$errorStr = $groups->getMessage();

		break;
	}

	// Check if selected group exists
	//
	if ( !array_key_exists( $selectedGroup, $groups ) )
		$selectedGroup = TREE_AVAILABLE_FOLDERS;

	// Check if selected group is a system group
	//
	$selectedIsSystem = isSystemGroup($selectedGroup) || $selectedGroup == TREE_AVAILABLE_FOLDERS;

	// Setup groups navigation attributes
	//
	foreach( $groups as $key=>$data ) {
		$params = array( SELECTED_GROUP_ID => base64_encode($key), 'currentObjectType'=>$currentObjectType );
		$data['groupURL'] = prepareURLStr( PAGE_USERSANDGROUPS, $params );
		$data['PAGE_ID'] = $params[SELECTED_GROUP_ID];
		$data['SYSTEM'] = isSystemGroup($key);

		$groups[$key] = $data;
	}

	// Link for the Available Groups item
	//
	$params = array( SELECTED_GROUP_ID => base64_encode(TREE_AVAILABLE_FOLDERS), 'currentObjectType'=>$currentObjectType );
	$groupsSummaryUrl = prepareURLStr( PAGE_USERSANDGROUPS, $params );

	// Set current group name
	//
	if ( $selectedGroup != TREE_AVAILABLE_FOLDERS )
		$curGroupName = $groups[$selectedGroup][UG_NAME];
	else
		$curGroupName = $kernelStrings['ul_availablegroups_title']; 

	// Load group content
	//
	if ( $selectedGroup != TREE_AVAILABLE_FOLDERS ) {
		if ( is_null($searchString) ) {
			$docCount = $ContactCollection->getGroupContactNum( $selectedGroup, $kernelStrings );
			$totalObjects = $docCount;

			getQueryLimitValues( $docCount, $recordsPerPage, $showPageSelector, $currentPage, $pages, $pageCount, $startIndex, $count );
			$ContactCollection->loadFromUserGroup( $selectedGroup, $sortStr, $startIndex, $count, $callbackParams, 'prepareContactEntry', $kernelStrings );
		}
	} else {
		$statisticsMode = true;

		// Find out user counts
		//
		foreach( $groups as $key=>$data ) {
			$count = getGroupUserCount( $key, $kernelStrings );
			$data['USERCOUNT'] = ($count > 0) ? $count : null;

			$groups[$key] = $data;
		}

		// Fetch total user count
		//
		$totalUserCount = db_query_result( $qr_selectglobalusercount, DB_FIRST, array() );

		// Format summary string
		//
		$summaryStr = $kernelStrings['ul_summary_note'];
		$summaryStr = sprintf( $summaryStr, $totalUserCount, count($groups) );
	}

	$currentObjectId = $selectedGroup;

	// Create the Group menu content
	//
	$groupMenu = array();

	$groupMenu[$kernelStrings['ul_addgroup_menu']] = sprintf( $processButtonTemplate, 'addgroupbtn' );
	if ( !$selectedIsSystem ) {
		$groupMenu[$kernelStrings['ul_modifygroup_menu']] = sprintf( $processButtonTemplate, 'modgroupbtn' );
		$groupMenu[$kernelStrings['ul_deletegroup_menu']] = sprintf( $processButtonTemplate, 'delgroupbtn' )."||confirmGroupDeletion()";
	} else {
		$groupMenu[$kernelStrings['ul_modifygroup_menu']] = null;
		$groupMenu[$kernelStrings['ul_deletegroup_menu']] = null;
	}
?>