<?php

	//
	// WebAsyst Contact classes and constants
	//

	require_once "csvfiles.php";

	class Contact extends arrayAdaptedClass
	//
	// Represents the WebAsyst Contact entity
	//
	{
		var $C_ID;
		var $CF_ID;
		var $C_SUBSCRIBER;

		var $NAME;
		var $U_ID;
		var $U_STATUS;
		var $U_SETTINGS;
		var $U_SENDMAIL;
		var $U_ACCESSTYPE;

		var $C_MODIFYDATETIME;
		var $C_MODIFYUSERNAME;
		var $C_CREATEDATETIME;
		var $C_CREATEUSERNAME;

		var $ContactType = CONTACT_BASIC_TYPE;

		var $ContactIsUser = false;

		// Contact type description
		//
		var $typeDescription = null;

		// Contact type description as a plain array
		//
		var $fieldsPlainDesc = null;

		var $language;

		function Contact( &$kernelStrings, $language = LANG_ENG, $typeDescription = null, $fieldsPlainDesc = null, $initDescription = true )
		//
		// Creates a new instance of Contact
		//
		//		Parameters:
		//			&$kernelStrings - Kernel localization strings
		//			$language - user language
		//			$typeDescription - preloaded contact type description
		//			$fieldsPlainDesc - preloaded fields descrption
		//			$initDescription - indicats that internal object description must be initialized
		//
		//
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'U_ID', t_string, false );
			$this->dataDescrition->addFieldDescription( 'U_STATUS', t_string, false );
			$this->dataDescrition->addFieldDescription( 'U_SETTINGS', t_string, false );
			$this->dataDescrition->addFieldDescription( 'U_SENDMAIL', t_string, false );
			$this->dataDescrition->addFieldDescription( 'U_ACCESSTYPE', t_string, false );
			$this->dataDescrition->addFieldDescription( 'C_SUBSCRIBER', t_integer, false );

			$this->dataDescrition->addFieldDescription( 'C_MODIFYDATETIME', t_string, false );
			$this->dataDescrition->addFieldDescription( 'C_MODIFYUSERNAME', t_string, false );
			$this->dataDescrition->addFieldDescription( 'C_CREATEDATETIME', t_string, false );
			$this->dataDescrition->addFieldDescription( 'C_CREATEUSERNAME', t_string, false );

			$this->language = $language;

			if ( !$initDescription )
				return;

			// Load the contact type description
			//
			if ( is_null($typeDescription) )
				$this->typeDescription = getContactTypeDescription( $this->ContactType, $language, $kernelStrings, false );
			else
				$this->typeDescription = $typeDescription;

			if ( is_null($fieldsPlainDesc) )
				$this->fieldsPlainDesc = getContactTypeFieldsSummary( $this->typeDescription, $kernelStrings, true );
			else
				$this->fieldsPlainDesc = $fieldsPlainDesc;

			// Prepare object fields
			//

			foreach ( $this->fieldsPlainDesc as $fieldID=>$fieldData ) {
				$this->$fieldID = null;

				$fieldType = null;
				$fieldRequired = false;
				$fieldLength = null;

				switch ( $fieldData[CONTACT_FIELD_TYPE] ) {
					case CONTACT_FT_NUMERIC : ;
							$fieldType = t_float;
							break;
					case CONTACT_FT_DATE : ;
							$fieldType = t_date;
							break;
					case CONTACT_FT_TEXT :
							$fieldType = t_string;

							if ( isset($fieldData[CONTACT_MAXLEN]) )
								$fieldLength = $fieldData[CONTACT_MAXLEN];
							break;
					default :
							$fieldType = t_string;
							break;
				}

				$fieldRequired = isset($fieldData[CONTACT_REQUIRED]) && $fieldData[CONTACT_REQUIRED];

				$this->dataDescrition->addFieldDescription( $fieldID, $fieldType, $fieldRequired, $fieldLength );
			}
		}

		function loadEntry( $C_ID, &$kernelStrings )
		//
		// Loads contact from the database
		//
		//		Parameters:
		//			$C_ID - Contact identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_selectcontactanduser;

			$this->C_ID = $C_ID;

			$data = db_query_result( $qr_selectcontactanduser, DB_ARRAY, $this );
			if ( PEAR::isError($data) )
				return $data;

			$params = array( s_datasource=>s_database );

			if ( is_array($data) )
				$this->loadFromArray( $data, $kernelStrings, false, $params );

			$userID = $this->findContactUser( $kernelStrings );
			if ( PEAR::isError($userID) )
				return $userID;

			$this->NAME = df_contactname($data);

			$this->ContactIsUser = !is_null($userID);
		}

		function isUnsubscribed( &$kernelStrings )
		//
		// Returns true if contact email appears in the unsubscribers list
		//
		//		Parameters:
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns boolean identifier or PEAR_Error
		//
		{
			global $qr_select_unsubscriber_cnt;

			$cnt = db_query_result( $qr_select_unsubscriber_cnt, DB_FIRST, array() );
			if ( PEAR::isError($cnt) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return $cnt;
		}

		function addModContact( $action, $CF_ID, $contactData, &$kernelStrings, $validateData = true )
		//
		// Adds or modifies contact
		//
		//		Parameters:
		//			$action - action type (new/edit)
		//			$CF_ID - contact folder
		//			$contactData - source array
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns a contact identifier or PEAR_Error
		//
		{
			if ($validateData) {
				$res = validateContactData( $this->ContactType, $contactData, $this->language, $kernelStrings, false, $this->typeDescription, true );
				if ( PEAR::isError($res) )
					return $res;
			}

			$res = addmodContact( $contactData, $CF_ID, $action, $kernelStrings, false, false, $language = LANG_ENG, $this->typeDescription );
			if ( PEAR::isError($res) )
				return $res;

			$this->C_ID = $res;

			return $res;
		}

		function addModUser( $operatorU_ID, $action, $quotas, $language, $CF_ID, $contactData, $newUser, &$kernelStrings, $importMode = false, $savePart = false)
		//
		// Adds or modifies user
		//
		//		Parameters:
		//			$operatorU_ID - identifier of the operator who adds the user
		//			$action - action type (new/edit)
		//			$quotas - user quotas information
		//			$language - current user language
		//			$CF_ID - contact folder
		//			$contactData - source array
		//			$newUser - indicates that contact was converted to user
		//			$kernelStrings - Kernel localization strings
		//			$importMode - indicates that user adding occurs in the import mode
		//			$savePart - save only setted params (only for edit mode - especialy for My Account)
		//
		//		Returns null or PEAR_Error
		//
		{
			$userAction = $newUser ? ACTION_NEW : ACTION_EDIT;

			$res = addmodUser( $userAction, $contactData, $quotas, $kernelStrings, $language, $CF_ID, $importMode, $this->typeDescription, true, $action, $savePart );
			if ( PEAR::isError($res) )
				return $res;

			$this->U_ID = trim( strtoupper($contactData['U_ID']) );

			// Copy some user preferences
			//
			if ( $newUser ) {
				$styleSet = readUserCommonSetting( $operatorU_ID, HTML_STYLESET );
				if ( strlen($styleSet) )
					writeUserCommonSetting( $this->U_ID, HTML_STYLESET, $styleSet, $kernelStrings );
			}
		}

		function applyUserSettings( &$kernelStrings, $language, $included_groups, $notincluded_groups )
		//
		// Applies user settings to the contact
		//
		//		Parameters:
		//			$kernelStrings - Kernel localization strings
		//			$language - user language
		//			... - user settings
		//
		//		Returns null or PEAR_Error
		//
		{
			foreach( $included_groups as $UG_ID )
				registerUserInGroup( $this->U_ID, $UG_ID, $kernelStrings );

			foreach( $notincluded_groups as $UG_ID )
				$res = registerUserInGroup( $this->U_ID, $UG_ID, $kernelStrings, true );
		}

		function findContactUser( &$kernelStrings )
		//
		// Finds user ID assigned to the contact
		//
		//		Parameters:
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer, or null or PEAR_Error
		//
		{
			global $qr_selectContactUser;

			$res = db_query_result( $qr_selectContactUser, DB_FIRST, $this );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( !strlen($res) )
				$res = null;

			return $res;
		}

		function setSubscriberStatus( $active, &$kernelStrings )
		//
		// Sets the contact subscriber status
		//
		//		Parameters:
		//			$active - indicates if contact is a pending or active subscriber
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_set_contact_subscriber;
			global $qr_delete_unsubscriber;

			$this->C_SUBSCRIBER = $active ? CM_SBST_ACTIVE : CM_SBST_PENDING;

			$res = db_query($qr_set_contact_subscriber, $this);
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( $this->C_SUBSCRIBER == CM_SBST_ACTIVE ) {
				$res = $this->loadEntry( $this->C_ID, $kernelStrings );
				if ( PEAR::isError($res) )
					return $res;

				$params = array();
				$params['ENS_EMAIL'] = trim(strtolower($this->C_EMAILADDRESS));

				db_query( $qr_delete_unsubscriber, $params );
			}

			return null;
		}

		function revokeUserPrivileges( $C_ID, &$kernelStrings, $operator_U_ID )
		//
		// Revokes user privileges for the contact
		//
		//		Parameters:
		//			$C_ID - contact identifier
		//			$kernelStrings - Kernel localization strings
		//			$operator_U_ID - operator user identifier
		//
		//		Returns null or PEAR_Error
		//
		//
		{
			global $AA_APP_ID;

			$this->C_ID = $C_ID;

			$U_ID = $this->findContactUser( $kernelStrings );
			if ( PEAR::isError($U_ID) )
				return $U_ID;

			if ( is_null($U_ID) )
				return;

			if ( $U_ID == $operator_U_ID )
				return PEAR::raiseError( $kernelStrings['app_cantremoveself_message'], ERRCODE_APPLICATION_ERR );

			$userdata = array();
			$userdata['U_ID'] = $U_ID;

			// Remove user from the database
			//
			$res = deleteUser( $userdata, $kernelStrings, $this->language, false );
			if ( PEAR::isError($res) )
				return $res;

			return null;
		}
	}

	//
	// contactCollection class
	//

	class contactCollection extends generic_collection
	//
	// Represents a collection of Contact objects
	//
	{
		var $typeDescription = null;
		var $fieldsPlainDesc = null;
		var $language = null;

		function contactCollection( &$typeDescription, &$fieldsPlainDesc, $language = LANG_ENG )
		//
		// Creates a new instance of contactCollection
		//
		//		Parameters:
		//			$typeDescription - contact type description
		//			$fieldsPlainDesc - fields descrption
		//			$language - user language
		//
		//		Returns null or PEAR_Error
		//
		{
			$this->typeDescription = $typeDescription;
			$this->fieldsPlainDesc = $fieldsPlainDesc;

			$this->language = $language;
		}

		function _contactFolderListSQLProvider( &$parameters )
		//
		// Internal function
		//
		{
			global $qr_selectFolderContacts;
			global $qr_selectFolderContactsWithEmails;
			global $qr_contact_confirmed_filter;

			extract($parameters);

			$subscribersFilter = null;

			if ( isset($activeSubscribersOnly) && $activeSubscribersOnly )
				$subscribersFilter = $qr_contact_confirmed_filter;

			if ( !isset($withEmailsOnly) || !$withEmailsOnly )
				return sprintf( $qr_selectFolderContacts, $CF_ID, $subscribersFilter, $sorting );
			else
				return sprintf( $qr_selectFolderContactsWithEmails, $CF_ID, $subscribersFilter, $sorting );
		}

		function _contactGroupListSQLProvider( &$parameters )
		//
		// Internal function
		//
		{
			global $qr_selectugcontent;
			global $qr_selectSystemGroupContacts;
			global $qr_selectGroupContacts;
			global $user_group_status_link;

			extract($parameters);

			if ( isSystemGroup($UG_ID) ) {
				$status = $user_group_status_link[$UG_ID];

				return sprintf( $qr_selectSystemGroupContacts, $status, $sorting );
			} else {

				return sprintf( $qr_selectGroupContacts, $UG_ID, RS_DELETED, $sorting );
			}
		}

		function _contactAvailableListSQLProvider( &$parameters )
		//
		// Internal function
		//
		{
			global $qr_select_avlbCnt_folders_chunk;
			global $qr_select_avlbCnt_users_chunk;
			global $qr_select_avlbCnt_lists_chunk;
			global $qr_select_avlbCnt_global_folders_chunk;
			global $qr_select_avlbCnt_global_lists_chunk;
			global $UR_Manager;

			extract($parameters);

			$canManageLists = checkUserFunctionsRights( $U_ID, 'CM', CM_MANAGELISTS_RIGHTS, $kernelStrings );

			$queries = array();

			$globalAdmin = $UR_Manager->IsGlobalAdministrator( $U_ID );

			$queries[] = $globalAdmin ? $qr_select_avlbCnt_global_folders_chunk : $qr_select_avlbCnt_folders_chunk;

			if ( $canManageLists )
				$queries[] = $globalAdmin ? $qr_select_avlbCnt_global_lists_chunk : $qr_select_avlbCnt_lists_chunk;

			if ( is_null($filter) || !strlen($filter) )
				$filter = '1=1';

			$query = implode( ' UNION ', $queries );
			return sprintf( $query, $filter, $U_ID)." ORDER BY $sorting";
		}

		function _contactContactListSQLProvider( &$parameters )
		//
		// Internal function
		//
		{
			$ContactList = new ContactList();

			extract($parameters);

			$query = $ContactList->generateListQuery();
			$query = $query." ORDER BY %2\$s";

			return sprintf( $query, $CL_ID, $sorting );
		}

		function loadFromDatabase( $queryProvider, &$providerParams, &$kernelStrings, &$callbackParams, $itemCallBack = null, $firstIndex = null, $count = null )
		//
		// Loads collection from database
		//
		//		Parameters:
		//			$queryProvider - method name for providing SQL query
		//			$providerParams - parameters for query provider
		//			$kernelStrings - Kernel localization strings
		//			$callbackParams - item callback parameters
		//			$itemCallBack - item callback function name
		//			$firstIndex - fetch items from this index
		//			$count - fetch this count of items
		//
		//		Returns null or PEAR_Error
		//
		{
			$this->items = array();

			$sql = $this->$queryProvider( $providerParams );

			if ( !is_null($firstIndex) && !is_null($count) )
				$sql .= " LIMIT $firstIndex, $count";

			$res = db_query( $sql, $providerParams );

			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], SOAPROBOT_ERR_QUERYEXECUTING );

			while ( $row = db_fetch_array($res) ) {
				if ( !$this->loadAsArrays ) {
					$item = new Contact( $kernelStrings, $this->language, $this->typeDescription, $this->fieldsPlainDesc );

					$item->loadFromArray( $row, $kernelStrings, false, array( s_datasource=>s_database ) );

					$itemId = $item->C_ID;

				} else {
					$item = $row;
					$itemId = $row['C_ID'];
				}

				if ( !is_null($itemCallBack) )
					$item = eval( "return $itemCallBack( \$callbackParams, \$item );" );

				$this->items[$itemId] = $item;
			}

			db_free_result( $res );

			return null;
		}

		function exportToCSV( $queryProvider, &$providerParams, &$kernelStrings, &$importScheme )
		//
		// Exports contacts to a CSV file
		//
		//		Parameters:
		//			$queryProvider - method name for providing SQL query
		//			$providerParams - parameters for query provider
		//			$kernelStrings - Kernel localization strings
		//			$importScheme - CSV file import scheme
		//
		//		Returns path to the file created or PEAR_Error
		//
		{
			$sql = $this->$queryProvider( $providerParams );

			$File = new CSVFile();

			$filePath = $File->CreateCSVFile( $importScheme, $kernelStrings, false );
			if ( PEAR::isError($filePath) )
				return $filePath;

			$res = db_query( $sql, $providerParams );

			if ( PEAR::isError($res) ) {
				$File->CloseFile();
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], SOAPROBOT_ERR_QUERYEXECUTING );
			}

			while ( $row = db_fetch_array($res) ) {
				$row = applyContactTypeDescription( $row, array(), $this->fieldsPlainDesc, $kernelStrings, UL_LIST_VIEW );
				$File->WriteLine($row);
			}

			db_free_result( $res );

			$File->CloseFile();

			return $filePath;
		}

		function findContacts( $searchString, $U_ID, $sorting, &$callbackParams, $itemCallBack, &$kernelStrings, $export = false, $importScheme = null, $findUsers = false )
		//
		// Finds contacts and populates the collection
		//
		//		Parameters:
		//			$searchString - text to find
		//			$U_ID - user identifier
		//			$sorting - sorting string
		//			$callbackParams - item callback parameters
		//			$itemCallBack - item callback function name
		//			$kernelStrings - Kernel localization strings
		//			$export - export contacts to a CSV file
		//			$importScheme - CSV file import scheme
		//			$findUsers - indicates that must be found instead of contacts
		//
		//		Returns null, or path to a CSV file, or PEAR_Error
		//
		{
			global $qr_find_users;

			$result = array();

			$pattern = "/\"(?P<groups>[^\"]*)\"/iu";
			preg_match_all( $pattern, $searchString, $matches );

			$quotedWords = $matches['groups'];

			foreach ( $quotedWords as $group )
				$searchString = str_replace( '"'.$group.'"', "", $searchString );

			$totalWords = array_merge( $quotedWords, explode( " ", $searchString ) );

			$groupsToFind = array();

			foreach ( $totalWords as $value )
				if ( strlen(trim($value)) )
					$groupsToFind[] = $value;

			$sqlFields = array();
			$valueList = array();

			$valueList['U_ID'] = $U_ID;

			$index = 0;

			$fieldGroups = array();

			foreach( $groupsToFind as $index=>$groupValue ) {
				$fieldGroup = array();

				foreach ( $this->fieldsPlainDesc as $fieldData ) {
					if ( isset($fieldData[CONTACT_DBFIELD]) ) {
						$fieldPath = $fieldData[CONTACT_DBFIELD] != 'U_ID' ? 'CN.'.$fieldData[CONTACT_DBFIELD] : 'U.'.$fieldData[CONTACT_DBFIELD];

						$fieldGroup[] = sprintf( "LOWER(%s) LIKE '!group%s!'", $fieldPath, $index );
					}
				}

				if ( count($fieldGroup) ) {
					$fieldGroup = "(".implode( ' OR ', $fieldGroup ).")";
					$fieldGroups[] = $fieldGroup;
				}
			}

			$fieldGroups = implode( " AND ", $fieldGroups );

			foreach ( $groupsToFind as $index=>$group )
				$valueList[sprintf('group%s', $index)] = "%".strtolower($group)."%";

			$providerParams = array( "sorting"=>$sorting, "filter"=>$fieldGroups, "U_ID"=>$U_ID );

			if ( !$findUsers )
				$query = $this->_contactAvailableListSQLProvider( $providerParams );
			else
				$query = sprintf($qr_find_users, $fieldGroups)." ORDER BY $sorting";

			$qr = db_query( $query, $valueList );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( $export ) {
				$File = new CSVFile();

				$filePath = $File->CreateCSVFile( $importScheme, $kernelStrings, false );
				if ( PEAR::isError($filePath) )
					return $filePath;
			}

			while ( $row = db_fetch_array($qr ) ) {
				if ( !$export ) {
					$item = new Contact( $kernelStrings, $this->language, $this->typeDescription, $this->fieldsPlainDesc );

					$item->loadFromArray( $row, $kernelStrings, false, array( s_datasource=>s_database ) );

					if ( !is_null($itemCallBack) )
						$item = eval( "return $itemCallBack( \$callbackParams, \$item );" );

					$this->items[$row['C_ID']] = $item;
				} else {
					$row = applyContactTypeDescription( $row, array(), $this->fieldsPlainDesc, $kernelStrings, UL_LIST_VIEW );
					$File->WriteLine($row);
				}
			}

			if ( $export ) {
				$File->CloseFile();
			}

			db_free_result( $qr );

			if ( $export )
				return $filePath;

			return null;
		}

		function loadUserAvailableContacts( $U_ID, $sorting, $startIndex, $count, &$callbackParams, $itemCallBack, &$kernelStrings, $filter = null )
		//
		// Populates the collection with contacts available for a specified user
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$sorting - sorting string
		//			$startIndex - start record index
		//			$count - number of records to load
		//			$callbackParams - item callback parameters
		//			$itemCallBack - item callback function name
		//			$kernelStrings - Kernel localization strings
		//			$filter - optional SQL filter string
		//
		//		Returns null or PEAR_Error
		//
		{
			$providerParams = array( "sorting"=>$sorting, "filter"=>$filter, "U_ID"=>$U_ID );

			return $this->loadFromDatabase( "_contactAvailableListSQLProvider", $providerParams, $kernelStrings, $callbackParams, $itemCallBack, $startIndex, $count );
		}

		function exportCsvUserAvailableContacts( $U_ID, $sorting, &$kernelStrings, &$importScheme )
		//
		// Exports contacts available for a specified user to a CSV file
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$sorting - sorting string
		//			$kernelStrings - Kernel localization strings
		//			$importScheme - CSV file import scheme
		//
		//		Returns null or PEAR_Error
		//
		{
			$providerParams = array( "sorting"=>$sorting, "filter"=>null, "U_ID"=>$U_ID );

			return $this->exportToCSV( "_contactAvailableListSQLProvider", $providerParams, $kernelStrings, $importScheme );
		}

		function loadFromContactFolder( $CF_ID, $sorting, $startIndex, $count, &$callbackParams, $itemCallBack, &$kernelStrings, $withEmailsOnly = false, $activeSubscribersOnly = false )
		//
		// Loads collection content from the contact folder
		//
		//		Parameters:
		//			$CF_ID - folder identifier
		//			$sorting - sorting string
		//			$startIndex - start record index
		//			$count - number of records to load
		//			$callbackParams - item callback parameters
		//			$itemCallBack - item callback function name
		//			$kernelStrings - Kernel localization strings
		//			$withEmailsOnly - if true, skips contacts which has no email address
		//			$activeSubscribersOnly - if true, skips pending and unsubscribed subscribers
		//
		//		Returns null or PEAR_Error
		//
		{
			$providerParams = array( "sorting"=>$sorting, 'CF_ID'=>$CF_ID, 'withEmailsOnly'=>$withEmailsOnly, 'activeSubscribersOnly'=>$activeSubscribersOnly );

			return $this->loadFromDatabase( "_contactFolderListSQLProvider", $providerParams, $kernelStrings, $callbackParams, $itemCallBack, $startIndex, $count );
		}

		function exportCsvFromContactFolder( $CF_ID, $sorting, &$kernelStrings, &$importScheme )
		//
		// Exports contacts from the contact folder to a CVS File
		//
		//		Parameters:
		//			$CF_ID - folder identifier
		//			$sorting - sorting string
		//			$kernelStrings - Kernel localization strings
		//			$importScheme - CSV file import scheme
		//
		//		Returns null or PEAR_Error
		//
		{
			$providerParams = array( "sorting"=>$sorting, 'CF_ID'=>$CF_ID, 'withEmailsOnly'=>0, 'activeSubscribersOnly'=>0 );

			return $this->exportToCSV( "_contactFolderListSQLProvider", $providerParams, $kernelStrings, $importScheme );
		}

		function loadFromUserGroup( $UG_ID, $sorting, $startIndex, $count, &$callbackParams, $itemCallBack, &$kernelStrings )
		//
		// Loads collection content from the contact folder
		//
		//		Parameters:
		//			$UG_ID - group identifier
		//			$sorting - sorting string
		//			$startIndex - start record index
		//			$count - number of records to load
		//			$callbackParams - item callback parameters
		//			$itemCallBack - item callback function name
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			$providerParams = array( "sorting"=>$sorting, 'UG_ID'=>$UG_ID );

			return $this->loadFromDatabase( "_contactGroupListSQLProvider", $providerParams, $kernelStrings, $callbackParams, $itemCallBack, $startIndex, $count );
		}

		function exportCsvFromUserGroup( $UG_ID, $sorting, &$kernelStrings, &$importScheme )
		//
		// Loads collection content from the contact folder
		//
		//		Parameters:
		//			$UG_ID - group identifier
		//			$sorting - sorting string
		//			$kernelStrings - Kernel localization strings
		//			$importScheme - CSV file import scheme
		//
		//		Returns null or PEAR_Error
		//
		{
			$providerParams = array( "sorting"=>$sorting, 'UG_ID'=>$UG_ID );

			return $this->exportToCSV( "_contactGroupListSQLProvider", $providerParams, $kernelStrings, $importScheme );
		}

		function loadFromContactList( $CL_ID, $sorting, $startIndex, $count, &$callbackParams, $itemCallBack, &$kernelStrings )
		//
		// Loads collection content from the contact list
		//
		//		Parameters:
		//			$CL_ID - contact list identifier
		//			$sorting - sorting string
		//			$startIndex - start record index
		//			$count - number of records to load
		//			$callbackParams - item callback parameters
		//			$itemCallBack - item callback function name
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			$providerParams = array( "sorting"=>$sorting, 'CL_ID'=>$CL_ID );

			return $this->loadFromDatabase( "_contactContactListSQLProvider", $providerParams, $kernelStrings, $callbackParams, $itemCallBack, $startIndex, $count );
		}

		function exportCsvFromContactList( $CL_ID, $sorting, &$kernelStrings, &$importScheme )
		//
		// Loads collection content from the contact list
		//
		//		Parameters:
		//			$CL_ID - contact list identifier
		//			$sorting - sorting string
		//			$kernelStrings - Kernel localization strings
		//			$importScheme - CSV file import scheme
		//
		//		Returns null or PEAR_Error
		//
		{
			$providerParams = array( "sorting"=>$sorting, 'CL_ID'=>$CL_ID );

			return $this->exportToCSV( "_contactContactListSQLProvider", $providerParams, $kernelStrings, $importScheme );
		}

		function loadMixedEntityContactWithEmails( $folders, $groups, $lists, $contacts, $sorting, &$kernelStrings, $suppressUnsubscribed = false )
		//
		// Populates the collection with contacts from the specified entities: folders, user groups, lists or individual contacts
		// Collection includes only the contacts with non-empty email address
		//
		//		Parameters:
		//			$folders - array of folder identifiers, or null
		//			$groups - array of group identifiers, or null
		//			$lists - array of list identifiers, or null
		//			$contacts - array of contact identifiers, or null
		//			$sorting - sorting string
		//			$kernelStrings - Kernel localization strings
		//			$suppressUnsubscribed - indicates that unsubscribed contacts must not appear in the result collection
		//
		//		Returns null, or PEAR_Error
		//
		{
			global $qr_select_foldersContactEmails;
			global $qr_select_userGroupsContactEmails;
			global $qr_select_activeContactsEmails;
			global $qr_select_inactiveContactsEmails;
			global $qr_select_contactListsEmails;
			global $qr_select_indContactsListEmails;
			global $qr_contact_confirmed_filter;

			$queryParts = array();

			if ( $suppressUnsubscribed )
				$extraFilter = $qr_contact_confirmed_filter;
			else
				$extraFilter = null;

			if ( is_array($folders) && count($folders) )
				$queryParts[] = sprintf( $qr_select_foldersContactEmails, "'".implode( "','", $folders )."'", $extraFilter );

			if ( is_array($groups) && count($groups) ) {
				if ( in_array(UGR_ACTIVE, $groups) ) {
					unset($groups[UGR_ACTIVE]);
					$queryParts[] = sprintf( $qr_select_activeContactsEmails, $extraFilter );
				}

				if ( in_array(UGR_INACTIVE, $groups) ) {
					unset($groups[UGR_INACTIVE]);
					$queryParts[] = sprintf( $qr_select_inactiveContactsEmails, $extraFilter );
				}

				if ( count($groups) )
					$queryParts[] = sprintf( $qr_select_userGroupsContactEmails, "'".implode( "','", $groups )."'", $extraFilter );
			}

			if ( is_array($lists) && count($lists) )
				$queryParts[] = sprintf( $qr_select_contactListsEmails, "'".implode( "','", $lists )."'", $extraFilter );

			if ( is_array($contacts) && count($contacts) )
				$queryParts[] = sprintf( $qr_select_indContactsListEmails, "'".implode( "','", $contacts )."'", $extraFilter );

			$result = array();

			if ( !count($queryParts) )
				return $result;

			$query = implode( ' UNION ', $queryParts );
			$query .= ' ORDER BY '.$sorting;

			$qr = db_query( $query, array() );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			while ( $row = db_fetch_array($qr ) ) {
				if ( !$this->loadAsArrays ) {
					$item = new Contact( $kernelStrings, $this->language, $this->typeDescription, $this->fieldsPlainDesc );

					$item->loadFromArray( $row, $kernelStrings, false, array( s_datasource=>s_database ) );
				} else {
					$item = $row;
					$item['NAME'] = df_contactname($row);
				}

				$this->items[$row['C_ID']] = $item;
			}

			db_free_result( $qr );

			return null;
		}

		function getFolderContactNum( $CF_ID, &$kernelStrings, $withEmailsOnly = false )
		//
		// Returns a number of contacts in the folder
		//
		//		Parameters:
		//			$CF_ID - folder identifier
		//			$kernelStrings - Kernel localization strings
		//			$withEmailsOnly - if true, skips contacts which has no email address
		//
		//		Returns integer or PEAR_Error
		//
		{
			global $qr_selectFolderContactNum;
			global $qr_selectFolderContactWithEmailsNum;

			if ( !$withEmailsOnly )
				$res = db_query_result( sprintf($qr_selectFolderContactNum, $CF_ID), DB_FIRST, array() );
			else
				$res = db_query_result( sprintf($qr_selectFolderContactWithEmailsNum, $CF_ID), DB_FIRST, array() );

			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return $res;
		}

		function getGroupContactNum( $UG_ID, &$kernelStrings )
		//
		// Returns a number of contacts in the group
		//
		//		Parameters:
		//			$UG_ID - group identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			global $qr_selectUserGroupContactCount;
			global $qr_selectSystemGroupContactCount;
			global $user_group_status_link;

			if ( !isSystemGroup($UG_ID) ) {
				$res = db_query_result( $qr_selectUserGroupContactCount, DB_FIRST, array('UG_ID'=>$UG_ID, 'STATUS'=>RS_DELETED) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			} else {
				$status = $user_group_status_link[$UG_ID];

				$res = db_query_result( $qr_selectSystemGroupContactCount, DB_FIRST, array('STATUS'=>$status) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			return $res;
		}

		function getListContactContactNum( $CL_ID, &$kernelStrings )
		//
		// Returns a number of contacts in the Contact List
		//
		//		Parameters:
		//			$CL_ID - contact list identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			$ContactList = new ContactList();

			return $ContactList->getContactsNum( $CL_ID, $kernelStrings );
		}
	}

	class ContactList extends arrayAdaptedClass
	{
		var $CL_ID;
		var $CL_NAME;
		var $CL_SHARED;
		var $CL_OWNER_U_ID;
		var $CL_MODIFYDATETIME;
		var $CL_MODIFYUSERNAME;

		var $folders = array();
		var $contacts = array();
		var $groups = array();

		function ContactList()
		//
		// Creates a new instance of ContactList
		//
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'CL_ID', t_integer, true );
			$this->dataDescrition->addFieldDescription( 'CL_NAME', t_string, true, 50 );
			$this->dataDescrition->addFieldDescription( 'CL_OWNER_U_ID', t_string, false, 20 );
			$this->dataDescrition->addFieldDescription( 'CL_SHARED', t_string, false );
			$this->dataDescrition->addFieldDescription( 'CL_MODIFYUSERNAME', t_string, true, 50 );
		}

		function getCollectionID()
		{
			return $this->CL_ID;
		}

		function saveEntry( $action, &$kernelStrings )
		//
		// Saves list to the database
		//
		//		Parameters:
		//			$action - form action (new, edit)
		//			$kernelStrings - kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_select_max_clist_id;
			global $qr_insert_clist;
			global $qr_update_clist;

			if ( !$this->CL_SHARED )
				$this->CL_SHARED = 0;

			if ( $action == ACTION_NEW ) {
				$CL_ID = db_query_result( $qr_select_max_clist_id, DB_FIRST, array() );
				if ( PEAR::isError($CL_ID) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$this->CL_ID = incID( $CL_ID );

				$res = db_query( $qr_insert_clist, $this );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				return $this->CL_ID;
			} else {
				$res = db_query( $qr_update_clist, $this );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}
		}

		function loadEntry( $CL_ID, &$kernelStrings, $loadContent = true )
		//
		// Loads the ContactList from the database
		//
		//		Parameters:
		//			$CL_ID - contact list identifier
		//			$kernelStrings - kernel localization strings
		//			$loadContent - populate the folders, contacts and groups filelds
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_select_clist;
			global $qr_select_clist_folders;
			global $qr_select_clist_contacts;
			global $qr_select_clist_groups;

			// Load the Contact List properties
			//
			$this->CL_ID = $CL_ID;

			$data = db_query_result( $qr_select_clist, DB_ARRAY, $this );
			if ( PEAR::isError($data) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$params = array( s_datasource=>s_database );
			if ( is_array($data) )
				$this->loadFromArray( $data, $kernelStrings, false, $params );

			if ( $loadContent ) {
				// Load folders
				//
				$qr = db_query( $qr_select_clist_folders, $this );
				if ( PEAR::isError($data) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				while ( $row = db_fetch_array($qr) )
					$this->folders[$row['CF_ID']] = 1;

				db_free_result( $qr );

				// Load contacts
				//
				$qr = db_query( $qr_select_clist_contacts, $this );
				if ( PEAR::isError($data) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				while ( $row = db_fetch_array($qr) )
					$this->contacts[$row['C_ID']] = 1;

				db_free_result( $qr );

				// Load groups
				//
				$qr = db_query( $qr_select_clist_groups, $this );
				if ( PEAR::isError($data) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				while ( $row = db_fetch_array($qr) )
					$this->groups[$row['UG_ID']] = 1;

				db_free_result( $qr );
			}

			return null;
		}

		function generateListQuery()
		//
		// Returns the SQL query which corresponds this Contact List
		//
		//		Returns string
		//
		{
			global $qr_select_basic_clist_contacts;

			return $qr_select_basic_clist_contacts;
		}

		function getContactsNum( $CL_ID, &$kernelStrings )
		//
		// Returns a number of contacts in the Contact List
		//
		//		Parameters:
		//			$CL_ID - contact list identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			global $qr_select_basic_clist_contacts;

			$qr = db_query( sprintf($qr_select_basic_clist_contacts, $CL_ID) );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result = mysql_num_rows( $qr->result );

			db_free_result($qr);

			return $result;
		}

		function setFolders( $folderList, &$kernelStrings )
		//
		// Sets contact list folders
		//
		//		Parameters:
		//			$folderList - list of folders
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_delete_clist_folders;
			global $qr_insert_clist_folder;

			$res = db_query( $qr_delete_clist_folders, $this );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			foreach ( $folderList as $CF_ID=>$value ) {
				$params = array( 'CL_ID'=>$this->CL_ID, 'CF_ID'=>$CF_ID );

				$res = db_query( $qr_insert_clist_folder, $params );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			return null;
		}

		function setContacts( $U_ID, $contactList, &$kernelStrings )
		//
		// Sets contact list contacts
		//
		//		Parameters:
		//			$U_ID - user added the contacts
		//			$contactList - list of contacts
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_select_clist_contacts;
			global $qr_delete_clist_contact;
			global $qr_insert_clist_contact;

			// Load current contact list
			//
			$qr = db_query( $qr_select_clist_contacts, $this );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$currentContacts = array();
			while ( $row = db_fetch_array($qr) )
				$currentContacts[] = $row['C_ID'];

			db_free_result($qr);

			// Delete contacts
			//
			$deleteList = array_diff( $currentContacts, $contactList );

			foreach ( $deleteList as $C_ID ) {
				$res = db_query( $qr_delete_clist_contact, array('C_ID'=>$C_ID, 'CL_ID'=>$this->CL_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			// Insert contacts
			//
			$modifyUserName = getUserName( $U_ID, true );
			$insertList = array_diff( $contactList, $currentContacts );

			foreach ( $insertList as $C_ID ) {
				$res = db_query( $qr_insert_clist_contact, array('C_ID'=>$C_ID, 'CL_ID'=>$this->CL_ID, 'CLC_MODIFYUSERNAME'=>$modifyUserName) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			return null;
		}

		function setGroups( $groupList, &$kernelStrings )
		//
		// Sets contact list folders
		//
		//		Parameters:
		//			$groupList - list of groups
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_delete_clist_groups;
			global $qr_insert_clist_group;

			$res = db_query( $qr_delete_clist_groups, $this );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			foreach ( $groupList as $UG_ID ) {
				$params = array( 'CL_ID'=>$this->CL_ID, 'UG_ID'=>$UG_ID );

				$res = db_query( $qr_insert_clist_group, $params );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			return null;
		}

		function addContact( $CL_ID, $C_ID, $modifyUserName, &$kernelStrings )
		//
		// Adds contact to the list
		//
		//		Parameres:
		//			$CL_ID - contact list identifier
		//			$C_ID - contact identifier
		//			$modifyUserName - name of the user who adds the contact
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns true of contact was added, false if contact already belongs
		//		to the list, and PEAR_Error in case of error
		//
		{
			global $qr_select_clist_contact_num;
			global $qr_insert_clist_contact;

			$params = array();
			$params['CL_ID'] = $CL_ID;
			$params['C_ID'] = $C_ID;
			$params['CLC_MODIFYUSERNAME'] = $modifyUserName;

			$cnt = db_query_result( $qr_select_clist_contact_num, DB_FIRST, $params );
			if ( PEAR::isError($cnt) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( $cnt )
				return false;

			$res = db_query( $qr_insert_clist_contact, $params );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return true;
		}

		function removeContact( $CL_ID, $C_ID, &$kernelStrings )
		//
		// Removes contact from the list
		//
		//		Parameters:
		//			$CL_ID - contact list identifier
		//			$C_ID - contact identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_delete_clist_contact;

			$res = db_query( $qr_delete_clist_contact, array('C_ID'=>$C_ID, 'CL_ID'=>$CL_ID) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return null;
		}

		function isListAccessable( $CL_ID, $U_ID, &$kernelStrings )
		//
		// Returns true if user has access to a specified list
		//
		//		Parameters:
		//			$CL_ID - contact list identifier
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns boolean or PEAR_Error
		//
		{
			global $qr_select_user_list_access;

			if ( isAdministratorID($U_ID) )
				return true;

			$res = db_query_result( $qr_select_user_list_access, DB_FIRST, array('CL_ID'=>$CL_ID, 'U_ID'=>$U_ID) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return $res;
		}

		function deleteList( $CL_ID, &$kernelStrings )
		//
		// Deletes contact list
		//
		//		Parameters:
		//			$CL_ID - contact list identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_delete_clist_groups;
			global $qr_delete_clist_folders;
			global $qr_delete_clist_contacts;
			global $qr_delete_clist;

			$params = array( 'CL_ID'=>$CL_ID );

			db_query( $qr_delete_clist, $params );
			db_query( $qr_delete_clist_groups, $params );
			db_query( $qr_delete_clist_folders, $params );
			db_query( $qr_delete_clist_contacts, $params );
		}
	}

	class ContactListCollection extends generic_collection
	//
	// Represents a collection of ContactList objects
	//
	{
		function ContactListCollection()
		{
			$this->itemClass = "ContactList";
		}

		function _contactListsSQLProvider( &$parameters )
		//
		// Internal function
		//
		{
			global $qr_select_contactlists;
			global $qr_select_accessablecontactlists;

			extract($parameters);

			if ( !isset($CL_OWNER_U_ID) || !strlen($CL_OWNER_U_ID) ) {
				return sprintf( $qr_select_contactlists, $sorting );
			} else
				return sprintf( $qr_select_accessablecontactlists, $CL_OWNER_U_ID, $sorting );
		}

		function _contactListsContactSQLProvider( &$parameters )
		//
		// Internal function
		//
		{
			global $qr_select_contactlistsforcontact;

			extract($parameters);

			return sprintf( $qr_select_contactlistsforcontact, $C_ID, $sorting );
		}

		function loadContactLists( $sorting, $startIndex, $count, $U_ID, &$callbackParams, $itemCallBack, &$kernelStrings )
		//
		// Populates the collection
		//
		//		Parameters:
		//			$sorting - sorting string
		//			$startIndex - start record index
		//			$count - number of records to load
		//			$U_ID - user identifier. If this parameter is not null, the non shared or shared by another users lists will be ignored.
		//			$callbackParams - item callback parameters
		//			$itemCallBack - item callback function name
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			$providerParams = array( "sorting"=>$sorting, 'CL_OWNER_U_ID'=>$U_ID );

			return $this->loadFromDatabase( "_contactListsSQLProvider", $providerParams, $kernelStrings, $callbackParams, $itemCallBack, $startIndex, $count );
		}

		function loadContactListsContainingContact( $sorting, $startIndex, $count, $C_ID, &$callbackParams, $itemCallBack, &$kernelStrings )
		//
		// Populates the collection with contact lists which contains a contact specified
		//
		//		Parameters:
		//			$sorting - sorting string
		//			$startIndex - start record index
		//			$count - number of records to load
		//			$C_ID - contact identifier
		//			$callbackParams - item callback parameters
		//			$itemCallBack - item callback function name
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			$providerParams = array( "sorting"=>$sorting, 'C_ID'=>$C_ID );

			return $this->loadFromDatabase( "_contactListsContactSQLProvider", $providerParams, $kernelStrings, $callbackParams, $itemCallBack, $startIndex, $count );
		}

		function getContactListsNum( &$kernelStrings )
		//
		// Returns a number of contact lists
		//
		//		Parameters:
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			global $qr_select_contactlistnum;

			$res = db_query_result( $qr_select_contactlistnum, DB_FIRST, array() );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return $res;
		}
	}

	class ContactsTextService
	//
	// Provides text processing functionality
	//
	{
		var $ContactType = CONTACT_BASIC_TYPE;

		var $typeDescription = null;
		var $fieldsPlainDesc = null;
		var $language = null;

		var $cache = array();

		//
		// Public methods
		//

		function ContactsTextService( &$kernelStrings, $language, $typeDescription = null, $fieldsPlainDesc = null )
		//
		// Creates a new ContactsTextService instance
		//
		//		Parameters
		//			$kernelStrings - Kernel localization strings
		//			$language - user language
		//			$typeDescription - preloaded contact type description
		//			$fieldsPlainDesc - preloaded fields descrption
		//
		{
			$this->language = $language;

			// Load the contact type description
			//
			if ( is_null($typeDescription) )
				$this->typeDescription = getContactTypeDescription( $this->ContactType, $language, $kernelStrings, false );
			else
				$this->typeDescription = $typeDescription;

			if ( is_null($fieldsPlainDesc) )
				$this->fieldsPlainDesc = getContactTypeFieldsSummary( $this->typeDescription, $kernelStrings, true );
			else
				$this->fieldsPlainDesc = $fieldsPlainDesc;
		}

		function ListAvailableVariables( &$kernelStrings, $variableSets )
		//
		// Returns a list of available text variables
		//
		//		Parameters:
		//			$kernelStrings - Kernel localization strings
		//			$variableSets - a list of variable sets (see contact text variables constants)
		//
		//		Returns array
		//
		{
			$result = array();

			foreach ( $variableSets as $set )
				$result[$set] = $this->_listSetVariables( $kernelStrings, $set );

			return $result;
		}

		function ProcessText( $text, $contact, $U_ID, &$kernelStrings )
		//
		// Replaces text variables with a database values
		//
		//		Parameters:
		//			$text - text to process
		//			$contact - contact identifier, or contact data array (for the VS_CONTACT section)
		//			$U_ID - user identifier (for the VS_CURRENT_USER section)
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns processed text or PEAR_Error
		//
		{
			$text = $this->_replaceContactFields( $text, $kernelStrings, $contact );
			$text = $this->_replaceContactFields( $text, $kernelStrings, $U_ID, true );
			$text = $this->_replaceCompanyFields( $text, $kernelStrings );

			return $text;
		}

		function ReplaceContactFields( $text, $contact, &$kernelStrings )
		//
		// Replaces contact variables with a database values
		//
		//		Parameters:
		//			$text - text to process
		//			$contact - contact identifier, or contact data array (for the VS_CONTACT section)
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns processed text or PEAR_Error
		//
		{
			return $this->_replaceContactFields( $text, $kernelStrings, $contact );
		}

		function ReplaceUserFields( $text, $U_ID, &$kernelStrings )
		//
		// Replaces user contact variables with a database values
		//
		//		Parameters:
		//			$text - text to process
		//			$U_ID - user identifier (for the VS_CURRENT_USER section)
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns processed text or PEAR_Error
		//
		{
			return $this->_replaceContactFields( $text, $kernelStrings, $U_ID, true );
		}

		function ReplaceCompanytFields( $text, &$kernelStrings )
		//
		// Replaces company variables with a database values
		//
		//		Parameters:
		//			$text - text to process
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns processed text or PEAR_Error
		//
		{
			return $this->_replaceCompanyFields( $text, $kernelStrings );
		}

		//
		// Private methods
		//

		function _listSetVariables( &$kernelStrings, $set )
		//
		// Internal function
		//
		{
			switch ( $set )
			{
				case VS_CONTACT : return $this->_listContactFields();
				case VS_CURRENT_USER : return $this->_listContactFields( true );
				case VS_COMPANY : return $this->_listCompanyFields($kernelStrings);
			}
		}

		function _genMyContactFieldPrefix( $field )
		{
			if ( preg_match( "/^C_/u", $field ) )
				return sprintf( '{%s}', 'MY_'.substr($field, 2) );
			else
				return sprintf( '{%s}', 'MY_'.$field );
		}

		function _listContactFields( $my = false )
		//
		// Internal function
		//
		{
			global $contactTextFieldTypes;

			$result = array();

			foreach ( $this->fieldsPlainDesc as $key=>$value ) {
				if ( in_array( $value[CONTACT_FIELD_TYPE], $contactTextFieldTypes ) ) {

					if ( !$my )
						$fieldKey = sprintf( '{%s}', $key );
					else
						$fieldKey = $this->_genMyContactFieldPrefix( $key );

					$result[$fieldKey] = $value[CONTACT_FIELDGROUP_LONGNAME];
				}
			}

			return $result;
		}

		function _listCompanyFields( &$kernelStrings )
		//
		// Internal function
		//
		{
			global $companyTextVariableNames;

			$result = array();

			foreach ( $companyTextVariableNames as $key=>$value ) {
				$fieldKey = sprintf( '{%s}', $key );
				$result[$fieldKey] = $kernelStrings[$value];
			}

			return $result;
		}

		function _replaceContactFields( $text, &$kernelStrings, $ID, $my = false )
		//
		// Internal function
		//
		{
			global $qr_selectUserContact;

			if ( !is_null($ID) ) {
				if ( $my ) {
					if ( !isset($this->cache['MY_CONTACT']) ) {
						$ID = db_query_result( $qr_selectUserContact, DB_FIRST, array('U_ID'=>$ID) );

						$contact = new Contact( $kernelStrings, $this->language, $this->typeDescription, $this->fieldsPlainDesc );

						$res = $contact->loadEntry( $ID, $kernelStrings );
						if ( PEAR::isError($res) )
							return $res;

						$this->cache['MY_CONTACT'] = (array)$contact;
					}

					$contactData = $this->cache['MY_CONTACT'];
				} else {
					if ( is_array($ID) )
						$contactData = $ID;
					else {
						if ( !isset($this->cache[$ID]) ) {
							$contact = new Contact( $kernelStrings, $this->language, $this->typeDescription, $this->fieldsPlainDesc );

							$res = $contact->loadEntry( $ID, $kernelStrings );
							if ( PEAR::isError($res) )
								return $res;

							$this->cache[$ID] = (array)$contact;
						}

						$contactData = $this->cache[$ID];
					}
				}

				foreach ( $this->fieldsPlainDesc as $key=>$value ) {
					if ( !$my )
						$fieldKey = sprintf( '{%s}', $key );
					else
						$fieldKey = $this->_genMyContactFieldPrefix( $key );

					$text = str_replace( $fieldKey, $contactData[$key], $text );
				}
			}

			foreach ( $this->fieldsPlainDesc as $key=>$value ) {
				if ( !$my )
					$fieldKey = sprintf( '{%s}', $key );
				else
					$fieldKey = $this->_genMyContactFieldPrefix( $key );

				$text = str_replace( $fieldKey, ' ', $text );
			}

			return $text;
		}

		function _replaceCompanyFields( $text )
		//
		// Internal function
		//
		{
			global $companyTextVariableNames;
			global $companyTextVariableMap;
			global $qr_selectCompanyInfo;

			if ( !isset($this->cache['COMPANY_DATA']) ) {
				$companyData = db_query_result( $qr_selectCompanyInfo, DB_ARRAY );
				$this->cache['COMPANY_DATA'] = $companyData;
			}

			$companyData = $this->cache['COMPANY_DATA'];

			foreach ( $companyTextVariableNames as $key=>$value ) {
				$fieldKey = sprintf( '{%s}', $key );
				$index = $companyTextVariableMap[$key];

				$text = str_replace( $fieldKey, $companyData[$index], $text );
			}

			return $text;
		}
	}
?>