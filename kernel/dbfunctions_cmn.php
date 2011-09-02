<?php

	//require_once "SOAP/Client.php";

	//
	// WBS Kernel DBSM-independent functions
	//

	//
	// Company functions
	//

	function updateCompanyInfo( $companyData, &$kernelStrings )
	//
	// Examines incoming data and modifies company information
	//
	//		Parameters:
	//			$companyData - an associative array containing fields of COMPANY record
	//			$kernelStrings - kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_deleteCompanyInfo;
		global $qr_insertCompanyInfo;

		$requiredFields = array( "COM_NAME" );

		if ( PEAR::isError( $invalidField = findEmptyField($companyData, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		if ( PEAR::isError( exec_sql( $qr_deleteCompanyInfo, $companyData, $outputList, false ) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( PEAR::isError( exec_sql( $qr_insertCompanyInfo, $companyData, $outputList, false ) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function getCompanyName()
	//
	// Returns company name
	//
	//
	//		Returns field COM_NAME from table COMPANY
	//
	{
		global $qr_selectCompanyInfo;

		$name = db_query_result( $qr_selectCompanyInfo, DB_FIRST );

		if ( PEAR::isError( $name ) )
			return null;

		return $name;
	}

	//
	// Users functions
	//

	function userExists( $U_ID )
	//
	// Checks if user with identifier U_ID exists in database
	//
	//		Parameters:
	//			$U_ID - user identifier
	//
	//		Returns 0 or 1, or PEAR_Error
	//
	{
		global $qr_selectUsersCount;

		return db_query_result( $qr_selectUsersCount, DB_FIRST, array("U_ID"=>$U_ID) );
	}

	function addmodUser( $action, $userdata, $quotas, &$kernelStrings, $language, $CF_ID, $importing = false, $typeDesc = null,
							$saveSettings = true, $contactAction = null, $savePart )
	//
	// Examines incoming parameters and adds (or modifies) user in database.
	//
	//		Parameters:
	//			$action - action type - addition ($action = ACTION_NEW) or modification ($action = ACTION_EDIT)
	//			$userdata - an associative array containing fields of WBS_USER record
	//			$quotas - user quotas information
	//			$kernelStrings - kernel localization strings
	//			$language - user language
	//			$CF_ID - contact folder
	//			$importing - import mode
	//			$typeDesc - contact type description
	//			$saveSettings - save custom user settings
	//			$contactAction - action for the Contact record
	//			$savePart - save only setted params (only for edit mode - especialy for My Account)
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_addUser;
		global $qr_modifyUser_pwd;
		global $qr_selectUser;
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;
		global $AA_APP_ID;
		global $qr_selectGlobalUserCount;
		global $qr_selectcontactfolder;
		global $qr_modifyUserStatus;
		global $qr_selectGlobalUserCountExcl;
		global $userContactMode;

		if ($action != ACTION_EDIT)
			$savePart = false;
		$saveOnlyFull = !$savePart;


		if ( is_null($contactAction) )
			$contactAction = $action;

		$userdata = trimArrayData( $userdata );

		$userdata['U_ACCESSTYPE'] = ACCESS_SUMMARY;

		// Load contact folder data
		//
		$folderData = db_query_result( $qr_selectcontactfolder, DB_ARRAY, array('CF_ID'=>$CF_ID) );
		if ( PEAR::isError($folderData) )
			return $folderData;

		// Load current user data
		//
		if ( $action == ACTION_EDIT ) {
			$res = exec_sql( $qr_selectUser, $userdata, $oldUserData, true );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$userdata['C_ID'] = $oldUserData['C_ID'];
		}

		// Validate contact data
		//
		$saveContact = false;
		if ($saveOnlyFull || isset($userdata["C_FIRSTNAME"])) {
			$res = validateContactData( $folderData['CT_ID'], $userdata, $language, $kernelStrings, false, $typeDesc, true );
			if ( PEAR::isError($res) )
				return $res;
			$saveContact = true;
		}

		// Validate user data
		//
		$requiredFields = array( "U_ID" );
		if ( $action == ACTION_NEW && !$importing )
			$requiredFields = array_merge( $requiredFields, array( "U_PASSWORD1", "U_PASSWORD2" ) );

		if ( PEAR::isError( $invalidField = findEmptyField($userdata, $requiredFields, null, 'USER') ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		// Check is valid user ID is selected
		//
		if ( isAdministratorID(strtoupper($userdata["U_ID"])) )
			return PEAR::raiseError( $kernelStrings['amu_reservedname_message'], ERRCODE_APPLICATION_ERR );

		// Check if no users with the same ID exists
		//
		if ( $action == ACTION_NEW && userExists( $userdata["U_ID"] ) ) {
			$errorStr = sprintf($kernelStrings['amu_usrexists_message'], $userdata["U_ID"] );
			$invalidField = "U_ID|USER";

			return PEAR::raiseError( $errorStr, ERRCODE_USEREXISTS, $_PEAR_default_error_mode,
												$_PEAR_default_error_options, $invalidField);

		}

		// Check for maximum user count exceeding
		//
		if ( $action == ACTION_NEW ) {
			$userCount = db_query_result( $qr_selectGlobalUserCount, DB_FIRST, array( 'U_STATUS'=>RS_DELETED ) );
			if ( PEAR::isError($userCount) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$res = userAdddingPermitted( $kernelStrings, $userCount, $action );
			if ( PEAR::isError($res) )
				return $res;


			if ( MAX_USER_COUNT != 0 && $userCount >= MAX_USER_COUNT )
				return PEAR::raiseError( sprintf($kernelStrings['amu_maxusers_message'], MAX_USER_COUNT), ERRCODE_APPLICATION_ERR );
		} else
		{
			if ( $action == ACTION_EDIT && ($userdata['U_STATUS'] == RS_ACTIVE || $userdata['U_STATUS'] == RS_LOCKED)
					&& $oldUserData['U_STATUS'] = RS_DELETED ) {

				$params = array();
				$params['U_STATUS'] = RS_DELETED;
				$params['U_ID'] = $oldUserData['U_ID'];

				$userCount = db_query_result( $qr_selectGlobalUserCount, DB_FIRST, array( 'U_STATUS'=>RS_DELETED ) );
				if ( PEAR::isError($userCount) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$res = userAdddingPermitted( $kernelStrings, $userCount, $action );
				if ( PEAR::isError($res) )
					return $res;

				$userCount = db_query_result( $qr_selectGlobalUserCountExcl, DB_FIRST, $params );
				if ( PEAR::isError($userCount) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );


				if ( strlen(MAX_USER_COUNT) && MAX_USER_COUNT != 0 && $userCount >= MAX_USER_COUNT )
					return PEAR::raiseError( sprintf($kernelStrings['amu_maxusers_message'], MAX_USER_COUNT), ERRCODE_APPLICATION_ERR );
			}
		}

		// Check if user deletion allowed
		//
		if ( $action == ACTION_EDIT && $userdata["U_STATUS"] == RS_DELETED ) {
			if ( $oldUserData["U_STATUS"] != RS_DELETED ) {
				$params = array( "U_ID"=>$userdata["U_ID"] );
				if ( PEAR::isError( $res = handleEvent( $AA_APP_ID, "onDeleteUser", $params, $language) ) )
					return $res;
			}
		}

		// Check user ID symbols
		//
		if ($action == ACTION_NEW)
			if ( !checkIDSymbols( $userdata["U_ID"], ID_SYMBOLS ) ) {
				$errorStr = $kernelStrings['amu_invidsymbols_message'];
				$invalidField = "U_ID|USER";

				return PEAR::raiseError ( $errorStr, ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
													$_PEAR_default_error_options, $invalidField);
			}

		// Check for password matching
		//
		if ( !$importing && $userdata["U_PASSWORD1"] != $userdata["U_PASSWORD2"] ) {
			$errorStr = $kernelStrings['app_passmismatch_message'];
			$invalidField = "U_PASSWORD1|USER";

			return PEAR::raiseError( $errorStr, ERRCODE_APPLICATION_ERR, $_PEAR_default_error_mode,
													$_PEAR_default_error_options, $invalidField );
		}

		// Validate password length
		//
		if ( !$importing && strlen( $userdata["U_PASSWORD1"] ) ) {
			$userdata["U_PASSWORD"] = strtolower( md5( $userdata["U_PASSWORD1"] ) );

			if ( strlen( $userdata["U_PASSWORD1"] ) < MIN_PASSWORD_LEN )
				return PEAR::raiseError( sprintf($kernelStrings['app_invpwdlen_message'], MIN_PASSWORD_LEN ),
										ERRCODE_APPLICATION_ERR,
										$_PEAR_default_error_mode,
										$_PEAR_default_error_options, "U_PASSWORD1|USER" );
		} elseif ($importing) {
			$userdata["U_PASSWORD"] = strtolower( md5( $userdata["U_PASSWORD1"] ) );
		}

		// Validate the quotas data
		//

		$saveQuotas = ($saveOnlyFull || $quotas);
		if ($saveQuotas) {
			$res = DiskQuotaManager::ValidateUserApplicationQuotes( $userdata["U_ID"], $quotas, $kernelStrings );
			if ( PEAR::isError($res) ) {
					return PEAR::raiseError( $res->getMessage(),
											ERRCODE_APPLICATION_ERR,
											$_PEAR_default_error_mode,
											$_PEAR_default_error_options, $res->getUserInfo()."|QUOTA" );
				return ;
			}
		}

		// Save user data to database
		//
		$userdata["U_ID"] = strtoupper( $userdata["U_ID"] );

		$userContactMode = true;

		if ( $contactAction == ACTION_NEW ) {
			// Add contact record
			//
			$C_ID = addmodContact( $userdata, $CF_ID, $contactAction, $kernelStrings, false, false, LANG_ENG, $typeDesc );
			if ( PEAR::isError($C_ID) )
				return $C_ID;

			$userdata['C_ID'] = $C_ID;
		} elseif ($saveContact) {
			$res = addmodContact( $userdata, $CF_ID, $contactAction, $kernelStrings, false, false, LANG_ENG, $typeDesc );
			if ( PEAR::isError($res) )
				return $res;
		}

		if ( $action == ACTION_NEW ) {
			// Add user record
			//
			$res = exec_sql( $qr_addUser, $userdata, $outputList, false );
		} else {

			if ( array_key_exists("U_PASSWORD", $userdata) && strlen($userdata["U_PASSWORD"]) )
				$res = exec_sql( $qr_modifyUser_pwd, $userdata, $outputList, false );
			elseif (array_key_exists("U_STATUS", $userdata))
				$res = exec_sql( $qr_modifyUserStatus, $userdata, $outputList, false );
		}

		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Save system settings
		//
		
		if ( $saveSettings )
			$res = writeIdentityCommonSysSettings( $userdata, IDT_USER, $kernelStrings );
		else
			$res = writeUserNotAccessCommonSettings( $userdata, $kernelStrings );

		if ( PEAR::isError($res) )
			return $res;

		// Save quotas information
		//
		DiskQuotaManager::SetUserApplicationsQuotes( $userdata["U_ID"], $quotas, $kernelStrings );

		return null;
	}

	function contactListsIsSupported()
	//
	// Returns true if the Contact Lists functionality is supported
	//
	//		Returns boolean
	//
	{
		$ver = getMySqlServerVersion();

		if ( is_null($ver) )
			return false;

		return $ver[0] >= 4;
	}

	function setUserActivityStatus( $U_ID, $status, &$kernelStrings )
	//
	// Sets user activity status ( RS_ACTIVE/RS_LOCKED )
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$status - new user status
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_selectUser;
		global $qr_modifyUserStatus;
		global $qr_modifyUserStatusOnly;
		global $qr_selectGlobalUserCountExcl;

		if ( !in_array( $status, array(RS_ACTIVE, RS_LOCKED) ) )
			return false;

		if ( $status == RS_ACTIVE ) {
			$params = array('U_ID'=>$U_ID);

			$userData = db_query_result( $qr_selectUser, DB_ARRAY, $params );
			if ( PEAR::isError($userData) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( $userData['U_STATUS'] == RS_DELETED && MAX_USER_COUNT != 0 ) {
				$params['U_STATUS'] = RS_DELETED;

				$userCount = db_query_result( $qr_selectGlobalUserCountExcl, DB_FIRST, $params );
				if ( PEAR::isError($userCount) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( $userCount >= MAX_USER_COUNT )
					return PEAR::raiseError( sprintf($kernelStrings['amu_maxusers_message'], MAX_USER_COUNT), ERRCODE_APPLICATION_ERR );
			}
		}

		$params = array();
		$params['U_ID'] = $U_ID;
		$params['U_STATUS'] = $status;

		$res = db_query( $qr_modifyUserStatusOnly, $params );
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function writeIdentityCommonSysSettings( $identityData, $ID_Type, &$kernelStrings, $accessOnly = false )
	//
	// Writes user or group system settings
	//
	//		Parameters:
	//			$userdata - array containing identity data
	//			$ID_Type - identity type (IDT_GROUP, IDT_USER)
	//			$kernelStrings - Kernel localization strings
	//			$accessOnly - save only access settings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $userCommonSysSettings;
		global $userCommonAccessSettings;

		$settingsSet = ($accessOnly) ? $userCommonAccessSettings : $userCommonSysSettings;

		foreach( $settingsSet as $setting )
			if ( isset($identityData[$setting]) ) {
				if ( $ID_Type == IDT_USER ) {
					if ( PEAR::isError( $res = writeUserCommonSetting( $identityData["U_ID"], $setting, $identityData[$setting], $kernelStrings ) ) )
						return PEAR::raiseError( $kernelStrings[ERR_SAVINGUSERSETTINGS] );
				} else
					if ( PEAR::isError( $res = writeGroupCommonSetting( $identityData["UG_ID"], $setting, $identityData[$setting], $kernelStrings ) ) )
						return PEAR::raiseError( $kernelStrings[ERR_SAVINGUSERSETTINGS] );
			}

		$ID = ( $ID_Type == IDT_USER ) ? $identityData["U_ID"] : $identityData["UG_ID"];
		$myKey = $ID . $ID_TYPE . $accessOnly;
		setGlobalCacheValue ("IDENTITYCOMMONSYSSETTINGS", $myKey, $identityData);

		return null;
	}

	function writeUserNotAccessCommonSettings( $userData, &$kernelStrings )
	//
	// Writes user system settings, not related to access settings
	//
	//		Parameters:
	//			$userData - array containing user data
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $userCommonSysSettings;
		global $userCommonAccessSettings;

		foreach( $userCommonSysSettings as $setting )
			if ( isset($userData[$setting]) ) {
				if ( in_array( $setting, $userCommonAccessSettings ) )
					continue;

				if ( PEAR::isError( $res = writeUserCommonSetting( $userData["U_ID"], $setting, $userData[$setting], $kernelStrings ) ) )
					return PEAR::raiseError( $kernelStrings[ERR_SAVINGUSERSETTINGS] );
			}

		return null;
	}

	function readIdentityCommonSysSettings( $identityData, $ID, $ID_Type, &$kernelStrings, $accessOnly = false )
	//
	// Reads user or group system settings
	//
	//		Parameters:
	//			$identityData - array containing identity data
	//			$ID - identity identifier
	//			$ID_Type - identity type (IDT_USER, IDT_GROUP)
	//			$kernelStrings - Kernel localization strings
	//			$accessOnly - read only access related settings
	//
	//		Returns updated array or PEAR_Error
	//
	{
		global $userCommonSysSettings;
		global $userCommonSysSettingsDefaults;
		global $userCommonAccessSettings;

		$ID_TYPE = (!empty($ID_TYPE))?$ID_TYPE:'';
		$myKey = $ID .$ID_TYPE . $accessOnly;
		$cacheValue = getLocalCacheValue ("IDENTITYCOMMONSYSSETTINGS", $myKey);
		if ($cacheValue)
			return $cacheValue;

		global $UR_Manager;
		global $userCommonAccessSettingPaths;

		$settingsSet = ($accessOnly) ? $userCommonAccessSettings : $userCommonSysSettings;

		foreach( $settingsSet as $setting ) {
			if ( $ID_Type == IDT_USER )
				$value = readUserCommonSetting( $ID, $setting );
			else
				$value = readGroupCommonSetting( $ID, $setting );

			if ( !strlen($value) )
				if ( array_key_exists( $setting, $userCommonSysSettingsDefaults ) )
					$value = $userCommonSysSettingsDefaults[$setting];

			$identityData[$setting] = $value;
		}

		foreach ( $userCommonAccessSettingPaths as $key=>$Path )
			if ( $ID_Type == IDT_USER )
				$identityData[$key] = $UR_Manager->CheckMask($UR_Manager->GetUserRightValue( $ID, $Path ), UR_BOOL_TRUE);
			else
				$identityData[$key] = $UR_Manager->CheckMask($UR_Manager->GetGroupRightValue( $ID, $Path ), UR_BOOL_TRUE);

		setGlobalCacheValue ("IDENTITYCOMMONSYSSETTINGS", $myKey, $identityData);

		return $identityData;
	}

	function readUserSummaryCommonSysSettings( $U_ID, &$kernelStrings, $userData = null, $groups = null )
	//
	// Returns user or group summary system settings, depending on access type (group, individual)
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$userData - array containing user data
	//			$groups - array of groups
	//
	//		Returns array containing user settings or PEAR_Error
	//
	{
		global $userCommonAccessSettings;
		global $userCommonAccessSettingPaths;
		global $UR_Manager;
		global $qr_selectUser;

		// Load user data
		//
		if ( is_null($userData) ) {
			$userData = db_query_result( $qr_selectUser, DB_ARRAY, array( 'U_ID'=>$U_ID ) );
			if ( PEAR::isError($userData) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		// Load user settings in case of access type == user
		//
		$personalSettings = readIdentityCommonSysSettings( $userData, $U_ID, IDT_USER, $kernelStrings );

		$result = array();

		$result[LANGUAGE] = $personalSettings[LANGUAGE];
		$result[MAILFORMAT] = "html";
		$result[START_PAGE] = $personalSettings[START_PAGE];
		$result[U_RECEIVESMESSAGES] = $personalSettings[U_RECEIVESMESSAGES];


		$timeZone = readUserCommonSetting( $U_ID, 'TIME_ZONE_ID' );
		$timeZone = ( strlen( $timeZone ) == 0 ) ? SERVER_TIME_ZONE_ID : $timeZone;
		define( 'USER_TIME_ZONE_ID',  $timeZone );

		$timeZone = readUserCommonSetting( $U_ID, 'TIME_ZONE_DST' );
		$timeZone = ( strlen( $timeZone ) == 0 ) ? SERVER_TIME_ZONE_DST : $timeZone;
		define( 'USER_TIME_ZONE_DST',  $timeZone );

		foreach ( $userCommonAccessSettingPaths as $key=>$Path )
			$result[$key] = $UR_Manager->CheckMask($UR_Manager->GetUserRightValue( $U_ID, $Path ), UR_BOOL_TRUE);

		return $result;
	}

	function getUserBooleanAppSetting( $U_ID, $APP_ID, $paramName, &$kernelStrings, $accessType = null )
	//
	// Returns user application-level boolean settings, taking into account user access type
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$APP_ID - application identifier
	//			$paramName - setting name
	//			$kernelStrings - Kernel localization strings
	//			$accessType - user access type
	//
	//		Returns setting value
	//
	{
		$personalValue = getAppUserCommonValue( $APP_ID, $U_ID, $paramName, null );
		$groups = findGroupsContaningUser( $U_ID, $kernelStrings, true );

		$groupValue = false;

		foreach ( $groups as $UG_ID ) {
			$value = getAppGroupCommonValue( $APP_ID, $UG_ID, $paramName, null );
			if ($value) {
				$groupValue = 1;
				break;
			}
		}

		return $groupValue || $personalValue;
	}
	
	
	function getAppUserSettings($U_ID, $APP_ID) 
	{
		$sql = 	"SELECT NAME, VALUE FROM USER_SETTINGS WHERE U_ID = '!U_ID!' AND APP_ID = '!APP_ID!'";
		$params = array(
			'U_ID' => $U_ID,
			'APP_ID' => $APP_ID
		);
		$res = db_query($sql, $params);
		$data = array();
		while ( $row = db_fetch_array($res) ) {
			$data[$row['NAME']] = $row['VALUE'];
		}
		return $data;
	}

	function sendUserNotification( &$kernelStrings, $U_ID, $password, $loginURL, $modifierU_ID )
	//
	// Sends account create/modify notification to user
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$U_ID - user identifier
	//			$password - user password
	//			$loginURL - URL of the login page
	//			$modifierU_ID - U_ID of user executing this routine
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_selectUser;
		global $DB_KEY;
		global $loc_str;
		global $global_applications;
		global $global_screens;

		$userData = db_query_result( $qr_selectUser, DB_ARRAY, array( 'U_ID'=>$U_ID ) );
		if ( PEAR::isError($userData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$language = readUserCommonSetting( $U_ID, LANGUAGE );
		$userStrings = $loc_str[$language];

		$userSummarySettings = readUserSummaryCommonSysSettings( $U_ID, $kernelStrings );

		$ALLOW_DRACCESS = $userSummarySettings[ALLOW_DRACCESS];

		$loginPageURL = prepareURLStr( $loginURL, array('DB_KEY'=>$DB_KEY) );

		if ( !strlen($password) )
			$password = $userStrings['amu_notchanged_text'];

		$message = $userStrings['amu_usermailheader_text'];
		$message .= "<br><br>";
		$message .= $userStrings['amu_loginurlcomment_text'];
		$message .= "<br>";
		$message .= sprintf( "<a href='%s'>%s</a>", $loginPageURL, $loginPageURL );
		$message .= "<br><br>";
		$message .= $userStrings['amu_loginparams_text'];
		$message .= "<br><br>";
		$message .= sprintf( "<b>%s</b>: %s<br>", $userStrings['amu_maildbkey_label'], $DB_KEY );
		$message .= sprintf( "<b>%s</b>: %s", $userStrings['amu_mailloginname_label'], $U_ID );
		$message .= sprintf( "<br><b>%s</b>: %s", $userStrings['amu_mailpassword_label'], $password );

		if ( $ALLOW_DRACCESS ) {
			$message .= "<br><br>";
			$message .= $userStrings['amu_maildacomment_text'];
			$message .= "<br><br>";

			// List user screens
			//
			$userScreens = listUserScreens($U_ID);

			$appList = sortApplicationList( $global_applications );

			foreach( $appList as $app_id=>$appData ) {
				if ( array_key_exists($app_id, $userScreens) ) {
					$screens = $userScreens[$app_id];

					$registeredScreens = listApplicationScreens( $app_id );

					foreach( $registeredScreens as $SCR_ID=>$SCR_DATA )
						if ( in_array($SCR_ID, $screens) )  {
							$appLang = getApplicationLanguage( $app_id, $language );
							$app_name = $global_applications[$app_id][APP_NAME][$appLang];

							$scrName = getScreenName( $app_id, $SCR_ID, $language );

							$fullScreenName = sprintf( "%s", $app_name);
							$directAccessURL = prepareURLStr( $loginURL, array('DBKEY'=>$DB_KEY, 'UID'=>$U_ID,
																'PASSWORD'=>$userData['U_PASSWORD'], 'PAGE'=>"$app_id/$SCR_ID") );
							$message .= sprintf( "%s<br><a href='%s'>%s</a><br><br>", $fullScreenName, $directAccessURL, $directAccessURL );
					}
				}
			}

		}

		$subject = $userStrings['amu_usermailheader_text'];
		$messageHeader = sprintf( $userStrings['amy_mailgreeting_text'], getUserName( $U_ID, false ) );

		@sendWBSMail( $U_ID, null, $modifierU_ID, $subject, 1, $message, $kernelStrings, null, null, $messageHeader, true, null, "", true, true, true );
	}

	function deleteContact( $C_ID, &$kernelStrings, $fieldsPlainDesc = null )
	//
	// Deletes contact and it's files
	//
	//		Parameters:
	//			$C_ID - contact identifier
	//			$kernelStrings - kernel localization strings
	//			$fieldsPlainDesc - contact type description
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_deletecontact;
		global $qr_selectcontact;
		global $qr_delete_contact_from_lists;

		if ( is_null($fieldsPlainDesc) ) {
			// Load type description
			//
			$typeDesc = getContactTypeDescription( CONTACT_BASIC_TYPE, LANG_ENG, $kernelStrings, false );
			if ( PEAR::isError($typeDesc) )
				return $typeDesc;

			// Obtain columns descriptions as a plain array
			//
			$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDesc, $kernelStrings, true );
		}

		// Delete contact files
		//
		$filesPath = getContactsAttachmentsDir();

		$QuotaManager = new DiskQuotaManager();

		$contactInfo = db_query_result( $qr_selectcontact, DB_ARRAY, array('C_ID'=>$C_ID) );

		foreach ( $fieldsPlainDesc as $fieldId=>$fieldData ) {
			if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_IMAGE && isset($contactInfo[$fieldId]) ) {
				$imgProperties = getContactImageFieldPropertieis( $contactInfo[$fieldId] );

				$imgFileName = base64_decode($imgProperties[CONTACT_IMGF_DISKFILENAME]);
				if ( strlen($imgFileName) ) {
					$srcFilePath = $filesPath."/".$imgFileName;

					if ( file_exists($srcFilePath) ) {
						$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, 'CM', -1*filesize($srcFilePath) );
						@unlink($srcFilePath);
					}

					$ext = null;
					$srcThumbFile = findThumbnailFile( $srcFilePath, $ext );
					if ( $srcThumbFile ) {
						$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, 'CM', -1*filesize($srcThumbFile) );
						@unlink($srcThumbFile);
					}
				}
			}
		}

		$QuotaManager->Flush( $kernelStrings );

		// Delete contact
		//
		$params = array( 'C_ID'=>$C_ID );

		$res = db_query( $qr_deletecontact, $params );
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Delete contact from contact lists
		//
		db_query( $qr_delete_contact_from_lists, $params );

		return null;
	}

	function getContactUser( $C_ID, &$kernelStrings )
	//
	//	Returns user identifier assigned with contact
	//
	//		Parameters:
	//			$C_ID - contact identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns user identifier or null, or PEAR_Error
	//
	{
		global $qr_selectContactUser;

		$params = array( 'C_ID'=>$C_ID );

		$res = db_query_result( $qr_selectContactUser, DB_FIRST, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( !strlen($res) )
			$res = null;

		return $res;
	}

	function deleteUser( $userdata, &$kernelStrings, $language, $deleteContact = true )
	//
	// Deletes user. If user status does not equal to RS_DELETED, user is deleted logically. Otherwise, physically
	//
	//		Parameters:
	//			$userdata - an associative array containing fields of WBS_USER record
	//			$kernelStrings - kernel localization strings
	//			$language - user language
	//			$deleteContact - indicates that the contact record must be deleted as well
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $AA_APP_ID;
		global $qr_deleteUser;
		global $qr_deleteUserScreenAccess;
		global $qr_selectUserLoginInfo;
		global $qr_saveUserStatus;
		global $qr_deletecontact;
		global $qr_selectUser;
		global $qr_delete_user_lists;

		$userInfo = $res = db_query_result( $qr_selectUser, DB_ARRAY, $userdata );
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Invoke onDelete User event
		//
		$params = array( "U_ID"=>$userdata["U_ID"] );
		if ( PEAR::isError( $res = handleEvent( $AA_APP_ID, "onDeleteUser", $params, $language) ) )
			return $res;

		// Invoke onRemoveUser event and delete user
		//
		$params = array( "U_ID"=>$userdata["U_ID"] );
		if ( PEAR::isError( $res = handleEvent( $AA_APP_ID, "onRemoveUser", $params, $language) ) )
			return $res;

		// Delete user access records
		//
		$res = exec_sql( $qr_deleteUserScreenAccess, $userdata, $outputList, false );
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Delete user from user groups
		//
		$userGroups = findGroupsContaningUser( $userInfo['U_ID'], $kernelStrings, true );
		foreach( $userGroups as $UG_ID )
			registerUserInGroup( $userdata['U_ID'], $UG_ID, $kernelStrings, true );

		// Delete user record
		//
		$res = exec_sql( $qr_deleteUser, $userdata, $outputList, false );
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Delete contact record
		//
		if ( $deleteContact )
			deleteContact( $userInfo["C_ID"], $kernelStrings );

		// Delete user private lists
		//
		db_query( $qr_delete_user_lists, $params );

		return null;
	}

	function restoreUser( $userdata, &$kernelStrings )
	//
	// Restores logically deleted user
	//
	//		Parameters:
	//			$userdata - an associative array containing fields of WBS_USER record
	//			$kernelStrings - kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_saveUserStatus;
		global $qr_selectGlobalUserCountExcl;

		$userdata["U_STATUS"] = RS_ACTIVE;

		$userCount = db_query_result( $qr_selectGlobalUserCountExcl, DB_FIRST, array( 'U_STATUS'=>RS_DELETED, 'U_ID'=>$userdata['U_ID'] ) );
		if ( PEAR::isError($userCount) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( MAX_USER_COUNT != 0 && $userCount >= MAX_USER_COUNT )
			return PEAR::raiseError( sprintf($kernelStrings['amu_maxusers_message'], MAX_USER_COUNT), ERRCODE_APPLICATION_ERR );

		$res = exec_sql( $qr_saveUserStatus, $userdata, $outputList, false );
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function modifyPersonalSettings( $userdata, &$kernelStrings, $language )
	//
	// Modifies personal user settings
	//
	//		Parameters:
	//			$userdata - an associative array containing fields of WBS_USER record
	//			$kernelStrings - kernel localization strings
	//			$language - user language
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $userGlobalSettings;
		global $qr_selectusercontactdata;

		$requiredFields = array( "U_ID" );

		if ( PEAR::isError( $invalidField = findEmptyField( $userdata, $requiredFields ) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		$allowChangeName = checkUserFunctionsRights( $userdata['U_ID'], 'MW', 'NC', $kernelStrings );
		if ( PEAR::isError($allowChangeName) )
			return $allowChangeName;

		$allowSwitchEmail = checkUserFunctionsRights( $userdata['U_ID'], 'MW', 'EMAIL', $kernelStrings );
		if ( PEAR::isError($allowSwitchEmail) )
			return $allowSwitchEmail;

		// Save contact data
		//
		if ( $allowChangeName ) {
			// Load contact folder data
			//
			$typeData = db_query_result( $qr_selectusercontactdata, DB_ARRAY, $userdata );
			if ( PEAR::isError($typeData) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			// Validate contact data
			//
			$res = validateContactData( $typeData['CT_ID'], $userdata, $language, $kernelStrings, true );
			if ( PEAR::isError($res) )
				return $res;

			// Update contact information
			//
			$userdata['C_ID'] = $typeData['C_ID'];
			$C_ID = addmodContact( $userdata, $typeData['CF_ID'], ACTION_EDIT, $kernelStrings, true );
			if ( PEAR::isError($C_ID) )
				return $C_ID;
		}

		// Save "enable email notifications" value
		//
		if ( $allowSwitchEmail )
			if ( PEAR::isError( $res = writeUserCommonSetting( $userdata["U_ID"], U_RECEIVESMESSAGES, $userdata[U_RECEIVESMESSAGES], $kernelStrings ) ) )
				return PEAR::raiseError( $kernelStrings[ERR_SAVINGUSERSETTINGS] );

		// Update other user settings
		//
		$userdata["U_ID"] = strtoupper( trim($userdata["U_ID"]) );

		if ( strlen($userdata[LANGUAGE]) )
			if ( PEAR::isError( $res = writeUserCommonSetting( $userdata["U_ID"], LANGUAGE, $userdata[LANGUAGE], $kernelStrings ) ) )
				return PEAR::raiseError( $kernelStrings[ERR_SAVINGUSERSETTINGS] );

		if ( strlen($userdata[WBS_ENCODING]) )
			if ( PEAR::isError( $res = writeUserCommonSetting( $userdata["U_ID"], WBS_ENCODING, $userdata[WBS_ENCODING], $kernelStrings ) ) )
				return PEAR::raiseError( $kernelStrings[ERR_SAVINGUSERSETTINGS] );

		return null;
	}

	function updateUserPassword( $passwordData, &$kernelStrings, $checkOldPassword = true )
	//
	// Examines incoming data and updates user (and administrator) password
	//
	//		Parameters:
	//			$passwordData - an associative array, containing user identifier (U_ID),
	//				old password (U_PASSWORD), and new password (PASSWORD1) with confirmation (U_PASSWORD2)
	//			$kernelStrings - kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_modifyPassword;
		global $qr_selectUserLoginInfo;
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		$requiredFields = array( "PASSWORD1", "PASSWORD2" );

		$passwordData = trimArrayData( $passwordData );

		if ( PEAR::isError( $invalidField = findEmptyField( $passwordData, $requiredFields ) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		$isAdminAccount = isAdministratorID( $passwordData["U_ID"] );

		if ($checkOldPassword) {
			if ( !$isAdminAccount ) {
				$userData = db_query_result( $qr_selectUserLoginInfo, DB_ARRAY, $passwordData );
				$oldPwdHash = $userData["U_PASSWORD"];
			} else {
				$adminData = loadAdminInfo();
				$oldPwdHash = $adminData[PASSWORD];
			}

			if ( $oldPwdHash != strtolower( md5($passwordData["U_PASSWORD"]) ) ) {
				$errorStr = $kernelStrings['amu_invpassword_message'];
				$invalidField = "U_PASSWORD";

				return PEAR::raiseError ( $errorStr, ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
													$_PEAR_default_error_options, $invalidField);
			}
		}

		if ( $passwordData["PASSWORD1"] != $passwordData["PASSWORD2"] ) {
			$errorStr = $kernelStrings['app_passmismatch_message'];

			return PEAR::raiseError ( $errorStr, ERRCODE_APPLICATION_ERR );
		}

		if ( strlen( $passwordData["PASSWORD1"] ) < MIN_PASSWORD_LEN )
			return PEAR::raiseError( sprintf($kernelStrings['app_invpwdlen_message'], MIN_PASSWORD_LEN ), ERRCODE_APPLICATION_ERR );


		$passwordData["U_PASSWORD"] = strtolower( md5( $passwordData["PASSWORD1"] ) );

		if ( !$isAdminAccount ) {
			if ( PEAR::isError( exec_sql( $qr_modifyPassword, $passwordData, $outputList, false ) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		} else
			if ( PEAR::isError($res = updateAdminInfo(array(PASSWORD=>$passwordData["U_PASSWORD"]), $kernelStrings) ) )
				return $res;

		return null;
	}

	function listUsers( &$kernelStrings, $all = false, $order = 'U_ID' , $support = false)
	//
	// Returs a list of WBS users
	//
	//		Parameters:
	//			$kernelStrings - kernel localization strings
	//			$all - selects all users if true. Returns only active and locked users if false.
	//				Function will return users with any status if status is null;
	//			$order - order string
	//			$support - select support team only
	//
	//		Returns array( U_ID1=>array( U_LASTNAME1, U_FIRSTNAME1... ), ... ) or PEAR_Error
	//
	{
		global $qr_selectUsers_2Status;
		global $qr_selectAllUsers;
		global $qr_selectUsers_Support;

		if ( $all )
			$qr = db_query($qr_selectAllUsers);
		elseif ($support)
			$qr = db_query(sprintf($qr_selectUsers_Support , SUPPORT_TEAM, RS_ACTIVE, $order));
		else
			$qr = db_query( sprintf($qr_selectUsers_2Status, RS_ACTIVE, RS_LOCKED, $order) );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr) )
			$result[$row['U_ID']] = $row;

		db_free_result($qr);

		return $result;
	}

	function getUserName( $U_ID, $short = true, $forceEmail = false )
	//
	// Returns user name, using function getArrUserName()
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$short - format of name
	//			$forceEmail - add email address after the name
	//
	//		Returns string containing user name
	//
	{
		if ( isAdministratorID( $U_ID ) ) {
			return ADMIN_USERNAME;
		}

		global $qr_selectUser;

		$result = db_query_result( $qr_selectUser, DB_ARRAY, array("U_ID"=>$U_ID) );
		if(PEAR::isError($result)){
			return $result->getMessage();
		}

		return addslashes(getArrUserName( $result, $short, false, $forceEmail ));
	}

	function getArrUserName( $arr, $short = true, $addLineBreaks = false, $forceEmail = false )
	//
	// Forms string containing user name taken from an array with fields of WBS_USER record. Format of name may be either short - ..., or full - ...
	//
	//		Parameters:
	//			$arr - an associative array containing fields of WBS_USER record
	//			$short - format of name
	//			$addLineBreaks - add line breaks between name components
	//			$forceEmail - add email address after the name
	//
	//		Returns string containing user name
	//
	{
		return df_contactname( $arr, $short, $addLineBreaks, $forceEmail );
	}

	function getGroupName( $UG_ID )
	//
	// Returns the name of the specified group
	//
	//		Parameters:
	//			$UG_ID - group identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns string
	//
	{
		global $qr_selectGroupName;

		return db_query_result( $qr_selectGroupName, DB_FIRST, array('UG_ID'=>$UG_ID) );
	}

	function checkUserLoginInfo( $userdata, $directAccess, &$kernelStrings, $admin_login = false )
	//
	// Checks if the user has rights to work with system - compares user name and password and examines user status
	//
	//		Parameters:
	//			$userdata - an array containing incoming data
	//			$directAccess - user attempts to access his start page directly
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns either Common function result values or Login result codes
	//
	{

		global $qr_selectUserLoginInfo;
		global $userCommonAccessSettingPaths;
		global $UR_Manager;

		if(isset($userdata['LOGIN']) && $userdata['LOGIN'] && isset($userdata['HASH']) && $userdata['HASH']){

			$lhashEntry = new LoginHash();
			$res = $lhashEntry->loadByHash($userdata['DB_KEY'], $userdata['HASH']);
			if($res && $lhashEntry->LOGIN == $userdata['LOGIN']){

				$lhashEntry->deleteHash();
				return ST_OK;
			}else{
				return ST_INVALIDDA;
			}
		}

		if( !is_array( $userdata ) )
			return ST_INVALID;

		$userdata = trimArrayData( $userdata );
		if (strtoupper( $userdata["U_ID"] ) == ADMIN_USERNAME && (!onWebAsystServer() || (onWebAsystServer() && $admin_login))) {
			$adminInfo = loadAdminInfo();

			if ( $adminInfo ) {
				if (strtolower($adminInfo[PASSWORD]) == strtolower($userdata['U_PASSWORD'])  && strtolower($userdata['U_PASSWORD']) != strtolower(md5('')) ){
					return ST_OK;
				}else{
					return LRC_INVALIDUSER;
				}
			}
		}

		$result = db_query_result( $qr_selectUserLoginInfo, DB_ARRAY, $userdata );

		if ( !strlen($result['U_ID']) )
			return LRC_INVALIDUSER;

		if ( $directAccess ) {
			$res = $UR_Manager->GetUserRightValue( $result['U_ID'], $userCommonAccessSettingPaths[ALLOW_DRACCESS] ) == UR_BOOL_TRUE;
			if ( !$res )
				return ST_INVALIDDA;
		}

		if( strtolower($userdata['U_PASSWORD']) == strtolower($result['U_PASSWORD']) ) {
		    if ($result["U_STATUS"] != RS_ACTIVE) {
			    return LRC_INACTIVEUSER;
		    }
			return ST_OK;
		} else {
			return LRC_INVALIDUSER;
		}
	}



	//
	// Checks if the user has rights to work with system - compares user name and email and examines user status
	//
	//		Parameters:
	//			$userdata - an array containing incoming data
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns either Common function result values or Login result codes
	//		If result is ST_OK (right) add to userdata user information from DB
	//
	function checkUserEmailInfo (&$userdata, $kernelStrings) {

		global $qr_selectUserEmailInfo;
		global $UR_Manager;

		if( !is_array( $userdata ) )
			return ST_INVALID;

		$userdata = trimArrayData( $userdata );

		$result = db_query_result($qr_selectUserEmailInfo, DB_ARRAY, $userdata );
		if (PEAR::isError($result))
			return $result;

		if ( !strlen($result['U_ID']) )
			return LRC_INVALIDUSER;

		if ($result["U_STATUS"] != RS_ACTIVE)
			return LRC_INACTIVEUSER;

		if( strtolower($userdata['EMAIL']) == strtolower($result['C_EMAILADDRESS']) ) {
			$userdata["C_FIRSTNAME"] = $result["C_FIRSTNAME"];
			$userdata["C_LASTNAME"] = $result["C_LASTNAME"];
			return ST_OK;
		} else {
			return LRC_INVALIDUSER;
		}
	}



	function listUserScreens( $U_ID )
	//
	// Returns list of accessible applications and screens allowed to be viewed by user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//
	//		Returns list in the form on an array( "APP1"=>array( "screen1Name"=>"screen1ID", "screen2Name"=>"screen2ID" ... ), "APP2"=>array( "screen1Name"=>"screen1ID" ... ) ... );
	//
	{
		global $UR_Manager;

		$screens = $UR_Manager->listUserScreens($U_ID);

		return $screens;
	}

	function checkUserAccessRights( $U_ID, $SCR_ID, $APP_ID, $public = false)
	//
	// Checks if user is allowed to work with a screen specified
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$SCR_ID - screen form identifier
	//			$APP_ID - application identifier
	//			$public - public form. If $public = true, form is accessible by all users
	//
	//		Returns true, if access is granted
	//
	{
		global $UR_Manager;

		if ( $public )
			return true;

		if ( !isAdministratorID( $U_ID ) )
		{
			$SCR_ID = strtoupper( $SCR_ID );
			$APP_ID = strtoupper( $APP_ID );

			if ( $UR_Manager->CheckMask( $UR_Manager->GetUserRightValue( $U_ID, "/ROOT/$APP_ID/SCREENS/$SCR_ID" ), UR_BOOL_TRUE ) )
				return true;
		} else {
			$adminInfo = loadAdminInfo();

			if ( $APP_ID == AA_APP_ID || $APP_ID == "UG" || $APP_ID == CM_APP_ID )
				return true;
		}

		return false;
	}

	function checkUserFunctionsRights( $U_ID, $APP_ID, $RIGHT, &$kernelStrings )
	//
	// Checks if user has the specified auxiliary right
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$APP_ID - application identifier
	//			$RIGHT - auxiliary identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $UR_Manager;

		$RIGHT = strtoupper( $RIGHT );
		$APP_ID = strtoupper( $APP_ID );

		if ( $UR_Manager->CheckMask($UR_Manager->GetUserRightValue( $U_ID, "/ROOT/$APP_ID/".UR_FUNCTIONS."/$RIGHT" ), UR_BOOL_TRUE ) )
			return true;

		return false;
	}

	//
	// Host functions
	//

	function db_exists( $database, &$kernelStrings, $serverName = null )
	//
	// Checks if database exists
	//
	//		Parameters:
	//			$database - database name
	//			$kernelStrings - an array containing strings in specific language
	//			$serverName - mySQL Server name
	//
	//		Returns boolean, ���� PEAR_Error
	//
	{
		global $qr_select_databases;

		if ( !is_null($serverName) ) {
			global $wbs_sqlServers;

			if ( !array_key_exists($serverName, $wbs_sqlServers) )
				return PEAR::raiseError( sprintf($kernelStrings['app_servernotfound_message'], $serverName), ERRCODE_APPLICATION_ERR );

			$sqlServerParams = $wbs_sqlServers[$serverName];

			$adminPassword = $sqlServerParams[WBS_ADMIN_PASSWORD];
			$adminUser = $sqlServerParams[WBS_ADMIN_USERNAME];
			$dbHost = $sqlServerParams[WBS_HOST];
			$dbPort = $sqlServerParams[WBS_PORT];

			$db_host = $dbHost;
			if ( strlen($dbPort) )
				$db_host = sprintf( "%s:%s", $db_host, $dbPort );

			$dbh = mysql_connect( $db_host, $adminUser, $adminPassword );
			if ( !$dbh ) {
				PEAR::raiseError( mysql_error() );
				return PEAR::raiseError( $kernelStrings['app_invsqlconnect_message']."\n".mysql_error(), ERRCODE_APPLICATION_ERR );
			}
		} else {
			$db_host = DB_HOST;
			if ( strlen(DB_PORT) )
				$db_host = sprintf( "%s:%s", $db_host, DB_PORT );

			$dbh = mysql_connect( $db_host, DB_ADMIN_USER, DB_ADMIN_PASSWORD );
			if ( !$dbh ) {
				PEAR::raiseError( mysql_error() );
				return PEAR::raiseError( $kernelStrings['app_invsqlconnect_message']."\n".mysql_error(), ERRCODE_APPLICATION_ERR );
			}
		}
		$res = mysql_select_db($database, $dbh);
		$result = mysql_errno($dbh) == 0;
		
/*
		$qr = mysql_query( $qr_select_databases );

		$result = false;
		while ( list($db_name) = mysql_fetch_row($qr) ) {
			if ( strtoupper($database) == strtoupper($db_name) ) {
				$result = true;
				break;
			}
		}

		mysql_free_result( $qr );*/
		mysql_close( $dbh );

		return $result;
	}

	function checkMySQLConnectionParams( $username, $password, $serverName, &$kernelStrings )
	//
	// Checks mySQL Connection parameters
	//
	//		Parameters:
	//			$username - mySQL user name
	//			$password - mySQL user password
	//			$serverName - mySQL server name
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_error
	//
	{
		global $wbs_sqlServers;

		if ( !array_key_exists($serverName, $wbs_sqlServers) )
			return PEAR::raiseError( sprintf($kernelStrings['app_servernotfound_message'], $serverName), ERRCODE_APPLICATION_ERR );

		$sqlServerParams = $wbs_sqlServers[$serverName];
		$dbHost = $sqlServerParams[WBS_HOST];
		$dbPort = $sqlServerParams[WBS_PORT];

		$db_host = $dbHost;
		if ( strlen($dbPort) )
			$db_host = sprintf( "%s:%s", $db_host, $dbPort );

		$dbh = mysql_connect( $db_host, $username, $password );
		if ( !$dbh ) {
			PEAR::raiseError( mysql_error() );
			return PEAR::raiseError( $kernelStrings['app_invsqlconnect_message']."\n".mysql_error(), ERRCODE_APPLICATION_ERR );
		}

		mysql_close($dbh);

		return null;
	}

	function metadata_exists( $database, &$kernelStrings, $serverName = null )
	//
	// Checks if any metadata exists in database
	//
	//		Parameters:
	//			$database - database to examine
	//			$kernelStrings - an array containing strings in specific language
	//			$serverName - mySQL Server name
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $qr_select_tables;
		global $databaseInfo;

		if ( !is_null($serverName) ) {
			global $wbs_sqlServers;

			if ( !array_key_exists($serverName, $wbs_sqlServers) )
				return PEAR::raiseError( sprintf($kernelStrings['app_servernotfound_message'], $serverName), ERRCODE_APPLICATION_ERR );

			$sqlServerParams = $wbs_sqlServers[$serverName];

			$dbHost = $sqlServerParams[WBS_HOST];
			$dbPort = $sqlServerParams[WBS_PORT];

			$db_host = $dbHost;
			if ( strlen($dbPort) )
				$db_host = sprintf( "%s:%s", $db_host, $dbPort );
		} else {
			if ( defined('DB_HOST') ) {
				$db_host = DB_HOST;
				if ( strlen(DB_PORT) )
					$db_host = sprintf( "%s:%s", $db_host, DB_PORT );
			} else {
				$db_host = 'localhost';
			}
		}

		$is_hosted = $databaseInfo[HOST_DBSETTINGS][HOST_DB_CREATE_OPTION] == DB_CREATION_USEEXISTING;

		$mySQLUser = $databaseInfo[HOST_DBSETTINGS][HOST_DBUSER];
		$mySQLPassword = $databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD];

		$dbh = mysql_connect( $db_host, $mySQLUser, $mySQLPassword );
		if ( !$dbh ) {
			PEAR::raiseError( mysql_error() );
			return PEAR::raiseError( $kernelStrings['app_invsqlconnect_message'].". ".mysql_error(), ERRCODE_APPLICATION_ERR );
		}

		if ( !($res = mysql_select_db( $database )) ) {
			PEAR::raiseError( mysql_error() );
			return PEAR::raiseError( sprintf($kernelStrings['app_invdbconnect_message'], $database).". ".mysql_error(), ERRCODE_APPLICATION_ERR );
		}

		$qr = mysql_query( $qr_select_tables, $dbh );
		if ( !$qr ) {
			PEAR::raiseError( mysql_error() );
			@mysql_close( $dbh );
			return PEAR::raiseError( sprintf($kernelStrings['app_invdbconnect_message'], $database).". ".mysql_error(), ERRCODE_APPLICATION_ERR );
		}
		$tablesList=array('COMPANY', 'CURRENCY', 
						  'WBS_USER', 'CONTACT', 'EMAIL_CONTACT',
						  'CONTACTTYPE', 'CONTACTFIELD',
						  'UGROUP', 'UGROUP_USER',
						  'FILE_IMPORT_FORMAT', 'APPSETTINGS',
						  'DISK_USAGE', 'USER_DISK_QUOTA',
						  'SMS_BALANCE', 'SMS_CREDIT_HISTORY',
						  'SMS_HISTORY', 'U_ACCESSRIGHTS',
						  'UG_ACCESSRIGHTS', 'WG_PARAM',
						  'WG_WIDGET', 'ACCESSRIGHTS_LINK ', );
		$tablesNum=0;
		while ($table=mysql_fetch_row($qr)){
			if(in_array(strtoupper($table[0]),$tablesList)){
				$tablesNum++;
			}
		}

		//$tablesNum = mysql_num_rows($qr);
		@mysql_free_result( $qr );

		@mysql_close( $dbh );

		return $tablesNum;
	}

	function dbUserExists( $userName, $serverName, &$kernelStrings )
	//
	// Checks if mySQL user exists on a given server
	//
	//		Parameters:
	//			$userName - mySQL user name
	//			$serverName - server name
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $wbs_sqlServers;
		global $qr_select_mysqlusercnt;

		if ( !array_key_exists($serverName, $wbs_sqlServers) )
			return PEAR::raiseError( sprintf($kernelStrings['app_servernotfound_message'], $serverName), ERRCODE_APPLICATION_ERR );

		$sqlServerParams = $wbs_sqlServers[$serverName];

		$dbHost = $sqlServerParams[WBS_HOST];
		$dbPort = $sqlServerParams[WBS_PORT];

		$db_host = $dbHost;
		if ( strlen($dbPort) )
			$db_host = sprintf( "%s:%s", $db_host, $dbPort );

		$mySQLUser = $sqlServerParams[WBS_ADMIN_USERNAME];
		$mySQLPassword = $sqlServerParams[WBS_ADMIN_PASSWORD];

		if($mySQLUser==$userName)return false;

		$dbh = mysql_connect( $db_host, $mySQLUser, $mySQLPassword );
		if ( !$dbh ) {
			PEAR::raiseError( mysql_error() );
			return PEAR::raiseError( $kernelStrings['app_invsqlconnect_message'].". ".mysql_error(), ERRCODE_APPLICATION_ERR );
		}

		if ( !($res = mysql_select_db( 'mysql' )) ) {
			PEAR::raiseError( mysql_error() );
			mysql_close( $dbh );
			return PEAR::raiseError( sprintf($kernelStrings['app_invdbconnect_message'], 'mysql').". ".mysql_error(), ERRCODE_APPLICATION_ERR );
		}

		$sql = sprintf( $qr_select_mysqlusercnt, $sqlServerParams[WBS_HOST], $userName );
		$qr = @mysql_query($sql);
		if ( !$qr ) {
			PEAR::raiseError( mysql_error() );
			mysql_close( $dbh );
			return PEAR::raiseError( sprintf($kernelStrings['app_invdbconnect_message'], 'mysql').". ".mysql_error(), ERRCODE_APPLICATION_ERR );
		}

		list($user_exists) = mysql_fetch_row($qr);

		@mysql_close( $dbh );

		return $user_exists;
	}

	function databaseIsAvailable( $databaseName )
	//
	// Check if database with metadata exists
	//
	//
	{
		global $qr_select_databases;
		global $qr_select_tables;
		global $databaseInfo;

		$db_host = DB_HOST;
		if ( strlen(DB_PORT) )
			$db_host = sprintf( "%s:%s", $db_host, DB_PORT );

		$existingDbName = $databaseInfo[HOST_DBSETTINGS][HOST_DBNAME];
		$is_hosted = $databaseInfo[HOST_DBSETTINGS][HOST_DB_CREATE_OPTION] == DB_CREATION_USEEXISTING;

		$mySQLUser = ($is_hosted) ? $databaseInfo[HOST_DBSETTINGS][HOST_DBUSER] : DB_ADMIN_USER;
		$mySQLPassword = ($is_hosted) ? $databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD] : DB_ADMIN_PASSWORD;

		$dbh = mysql_connect( $db_host, $mySQLUser, $mySQLPassword );
		if ( !$dbh ) {
			PEAR::raiseError( mysql_error() );
			return PEAR::raiseError( "Unable to connect to SQL Server", ERRCODE_APPLICATION_ERR );
		}
		//Commented out - use next block to check db existing
/*		
		$qr = mysql_query( $qr_select_databases );

		$result = false;
		while ( list($db_name) = mysql_fetch_row($qr) )
			if ( strtoupper($databaseName) == strtoupper($db_name) ) {
				$result = true;
				break;
			}
		mysql_free_result( $qr );

		if ( !$result ) {
			mysql_close( $dbh );
			return false;
		}
*/
		if ( !($res = mysql_select_db( $databaseName )) ) {
			$err = mysql_error();
			PEAR::raiseError( $err );
			mysql_close( $dbh );
			if ($err == 1049) 
				return false;
			else 
				return PEAR::raiseError( sprintf("Unable to connect to database %s ", $database), ERRCODE_APPLICATION_ERR );
		} else 
			$result = true;

		$qr = mysql_query( $qr_select_tables, $dbh );
		$tablesNum = mysql_num_rows($qr);
		mysql_free_result( $qr );

		mysql_close( $dbh );

		return $tablesNum;
	}

	function getDatabaseList( &$kernelStrings )
	//
	// Returns list of databases
	//
	//		Parameters:
	//			$kernelStrings - an array containing strings in specific language
	//
	//		Returns array of strings or PEAR::Error
	//
	{
		global $qr_select_databases;

		$db_host = DB_HOST;
		if ( strlen(DB_PORT) )
			$db_host = sprintf( "%s:%s", $db_host, DB_PORT );

		$dbh = mysql_connect( $db_host, DB_ADMIN_USER, DB_ADMIN_PASSWORD );
		if ( !$dbh ) {
			PEAR::raiseError( mysql_error() );
			return PEAR::raiseError( $kernelStrings['app_invsqlconnect_message'], ERRCODE_APPLICATION_ERR );
		}

		$qr = mysql_query( $qr_select_databases );
		if ( !$qr ) {
			mysql_close( $dbh );
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		$result = array();
		while ( list($db_name) = mysql_fetch_row($qr) )
			$result[] = strtoupper($db_name);

		mysql_close( $dbh );
		return $result;
	}

	function playSQLScript( $filePath, &$kernelStrings, $databaseInfo )
	//
	// Executes sql-script
	//
	//		Parameters:
	//			$filePath - path to sql-script file
	//			$kernelStrings - an array containing strings in specific language
	//			$databaseInfo - database description data
	//
	//		Returns null or PEAR_Eror
	//
	{
		$filePath = file_exists($filePath)?$filePath:(file_exists($filePath.'.RUS')?$filePath.'.RUS':$filePath.'.ENG');
		$fileData = @file($filePath);
		if ( !is_array($fileData) )
			return PEAR::raiseError( sprintf($kernelStrings['app_filenotfound_message'], basename($filePath)) );

		$fileData = implode( "", $fileData );

		if ( isset( $databaseInfo[HOST_BALANCE] ) )
		{
			if ( isset( $databaseInfo[HOST_BALANCE][MODULE_CLASS_SMS] ) )
			{
				if ( $databaseInfo[HOST_BALANCE][MODULE_CLASS_SMS][HOST_VALUE] == "UNLIMITED" )
					$fileData = str_replace( '%SMS_BALANCE%', 'NULL', $fileData );
				else
					$fileData = str_replace( '%SMS_BALANCE%', "'".$databaseInfo[HOST_BALANCE][MODULE_CLASS_SMS][HOST_VALUE]."'", $fileData );
			}
			else
				$fileData = str_replace( '%SMS_BALANCE%', '0', $fileData );
		}
		else
			$fileData = str_replace( '%SMS_BALANCE%', '0', $fileData );

		$fileData = str_replace( '%SUBSCRIBER_ID%', $databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME], $fileData );
		$fileData = str_replace( '%SUBSCRIBER_COMPANY%', $databaseInfo[HOST_FIRSTLOGIN][HOST_COMPANYNAME], $fileData );

		$nameArr = array();
		$nameArr['C_LASTNAME'] = $databaseInfo[HOST_FIRSTLOGIN][HOST_LASTNAME];
		$nameArr['C_FIRSTNAME'] = $databaseInfo[HOST_FIRSTLOGIN][HOST_FIRSTNAME];
		$nameArr['C_MIDDLENAME'] = null;

		$subscriberName = getArrUserName( $nameArr, true );
		$fileData = str_replace( '%SUBSCRIBER_NAME%', $subscriberName, $fileData );

		$statements = explode( ";", $fileData );

		if ( is_array($statements) && count($statements) )
			for ( $i = 0; $i < count($statements); $i++ )  {
				$statement = trim($statements[$i]);

				if ( !strlen($statement) )
					continue;

				$res = execPreparedQuery( $statement, array() );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( sprintf($kernelStrings['app_filequeryerr_message'], basename($filePath)) );
			}

		return null;
	}

	function playDataSQLScript( $filePath, &$kernelStrings, $databaseInfo )
	//
	// Executes sql-script (only INSERT INTO instruction)
	//
	//		Parameters:
	//			$filePath - path to sql-script file
	//			$kernelStrings - an array containing strings in specific language
	//			$databaseInfo - database description data
	//
	//		Returns null or PEAR_Eror
	//
	{
		$filePath = file_exists($filePath)?$filePath:(file_exists($filePath.'.RUS')?$filePath.'.RUS':$filePath.'.ENG');
		$fileData = @file($filePath);
		if ( !is_array($fileData) )
			return PEAR::raiseError( sprintf($kernelStrings['app_filenotfound_message'], basename($filePath)) );

		$fileData = implode( "", $fileData );
		if ( isset( $databaseInfo[HOST_BALANCE] ) )
		{
			if ( isset( $databaseInfo[HOST_BALANCE][MODULE_CLASS_SMS] ) )
			{
				if ( $databaseInfo[HOST_BALANCE][MODULE_CLASS_SMS][HOST_VALUE] == "UNLIMITED" )
					$fileData = str_replace( '%SMS_BALANCE%', 'NULL', $fileData );
				else
					$fileData = str_replace( '%SMS_BALANCE%', "'".$databaseInfo[HOST_BALANCE][MODULE_CLASS_SMS][HOST_VALUE]."'", $fileData );
			}
			else
				$fileData = str_replace( '%SMS_BALANCE%', '0', $fileData );
		}
		else
			$fileData = str_replace( '%SMS_BALANCE%', '0', $fileData );

		$fileData = str_replace( '%SUBSCRIBER_ID%', $databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME], $fileData );
		$fileData = str_replace( '%SUBSCRIBER_COMPANY%', $databaseInfo[HOST_FIRSTLOGIN][HOST_COMPANYNAME], $fileData );

		$nameArr = array();
		$nameArr['C_LASTNAME'] = $databaseInfo[HOST_FIRSTLOGIN][HOST_LASTNAME];
		$nameArr['C_FIRSTNAME'] = $databaseInfo[HOST_FIRSTLOGIN][HOST_FIRSTNAME];
		$nameArr['C_MIDDLENAME'] = null;

		$subscriberName = getArrUserName( $nameArr, true );
		$fileData = str_replace( '%SUBSCRIBER_NAME%', $subscriberName, $fileData );
		
		$instruction='INSERT INTO ';
		$statements = explode($instruction, $fileData );

		execPreparedQuery ("set character_set_client='utf8'", array() );
		execPreparedQuery ("set character_set_results='utf8'", array() );
		execPreparedQuery ("set collation_connection='utf8_general_ci'", array() );

		if ( is_array($statements) && count($statements) )
			for ( $i = 0; $i < count($statements); $i++ )  {
				$statement = trim($statements[$i]);

				if ( strlen($statement)<10 )
					continue;

				$res = execPreparedQuery( $instruction.$statement, array() );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( sprintf($kernelStrings['app_filequeryerr_message'], basename($filePath)) );
			}

		return null;
	}

	function installApplicationMetadata( $DB_KEY, $APP_ID, $kernelStrings, $databaseInfo, &$userSettings )
	//
	// Executes application metadata SQL-script
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$APP_ID - application identifier
	//			$kernelStrings - an array containing strings in specific language
	//			$databaseInfo - database description data
	//			$userSettings - user settings
	//
	//		Returns null or PEAR_Eror
	//
	{
		$trialDSAvailable = isset($databaseInfo[HOST_DBSETTINGS][HOST_TRIALDATASOURCE]);

		$filePath = sprintf( "%spublished/%s/%s_metadata.sql", WBS_DIR, strtoupper($APP_ID), strtolower($APP_ID) );
		if(onWebAsystServer()&&isset($databaseInfo[HOST_FIRSTLOGIN][HOST_LANGUAGE])&&!file_exists($filePath)){
			$filePath =sprintf( "%spublished/%s/%s_metadata.%s.sql", WBS_DIR, strtoupper($APP_ID), strtolower($APP_ID),strtoupper($databaseInfo[HOST_FIRSTLOGIN][HOST_LANGUAGE]));
		}else{
			$filePath = file_exists($filePath)?$filePath:(file_exists(str_replace('.sql','.RUS.sql',$filePath))?str_replace('.sql','.RUS.sql',$filePath):str_replace('.sql','.ENG.sql',$filePath));
		}
		//var_dump(array($DB_KEY, $APP_ID,$filePath));
		$dataFilePath = sprintf("%spublished/%s/%s_data.sql", WBS_DIR, strtoupper($APP_ID), strtolower($APP_ID) );
		if(onWebAsystServer()&&isset($databaseInfo[HOST_FIRSTLOGIN][HOST_LANGUAGE])&&!file_exists($dataFilePath)){
			$dataFilePath =sprintf( "%spublished/%s/%s_data.%s.sql", WBS_DIR, strtoupper($APP_ID), strtolower($APP_ID),strtoupper($databaseInfo[HOST_FIRSTLOGIN][HOST_LANGUAGE]));
		}else{
			$dataFilePath = file_exists($dataFilePath)?$dataFilePath:(file_exists(str_replace('.sql','.RUS.sql',$dataFilePath))?str_replace('.sql','.RUS.sql',$dataFilePath):str_replace('.sql','.ENG.sql',$dataFilePath));
		}

		if ( !@file_exists( $filePath ) )
			return PEAR::raiseError( sprintf($kernelStrings['app_filenotfound_message'], basename($filePath)) );
		if ( !$trialDSAvailable ) {
			if ( pear::isError( $res = playSQLScript($filePath, $kernelStrings, $databaseInfo) ) )
				return $res;
			if ( file_exists( $dataFilePath ) )
				if ( pear::isError( $res = playDataSQLScript($dataFilePath, $kernelStrings, $databaseInfo) ) )
					return $res;
		} else {
			$trialDataCreated = false;

			$trialDbName = $databaseInfo[HOST_DBSETTINGS][HOST_DBNAME];
			$trialDs = $databaseInfo[HOST_DBSETTINGS][HOST_TRIALDATASOURCE];
			$subscriberID = $databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME];
			$trialCreatorPath = sprintf( "%spublished/hostagent/trialcreator.php", WBS_DIR );
			if ( file_exists($trialCreatorPath) ) {
				include_once( $trialCreatorPath );

				$tc = new trialCreator();
				$trialDataCreated = $tc->createTrialData( $databaseInfo, $subscriberID, $DB_KEY, $trialDbName, $APP_ID, $trialDs, $filePath, $kernelStrings, $userSettings );
			}

			if ( !$trialDataCreated ) {
				if ( pear::isError( $res = playSQLScript($filePath, $kernelStrings, $databaseInfo) ) )
					return $res;
					if ( file_exists( $dataFilePath ) )
				if ( pear::isError( $res = playDataSQLScript($dataFilePath, $kernelStrings, $databaseInfo) ) )
					return $res;
			}
		}
		
		if (onWebAsystServer() && $APP_ID == 'ST') {
			addSupportEmail($DB_KEY);
		}
		
		
		$version = getCurrentSystemVersion($kernelStrings);
				
		$filePath = fixPathSlashes( sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DB_KEY)) );		
		$APP_ID = $APP_ID == 'AA' ? "SYSTEM" : $APP_ID;
		
		$xml = simplexml_load_file($filePath);
		if (isset($xml->VERSIONS)) {
			$xml->VERSIONS[$APP_ID] = $version;	
		} else {
			$versions = $xml->addChild('VERSIONS');
			$versions->addAttribute($APP_ID, $version);
		}
		// TODO: to tmp!!!
		$f = fopen($filePath, "w+");
		if ($f) {
			fwrite($f, $xml->asXML());
			fclose($f);
		}

		return null;
	}

	function prepareRollback($existingDbName, $dbUser , $dbPassword)
	{
		global $qr_select_tables;

		if(!strlen($existingDbName))
			return false;

		$db_host = DB_HOST;
		if ( strlen(DB_PORT) )
			$db_host = sprintf( "%s:%s", $db_host, DB_PORT );

		$dbUser = ( is_null($dbUser) ) ? DB_ADMIN_USER : $dbUser;
		$dbPassword = ( is_null($dbPassword) ) ? DB_ADMIN_PASSWORD : $dbPassword;

		$dbh = mysql_connect( $db_host, $dbUser, $dbPassword );
		if ( !$dbh )
			return $dbh;

		if ( !($res = mysql_select_db( $existingDbName )) ) {
			mysql_close( $dbh );
			return $res;
		}

		$qr = mysql_query( $qr_select_tables, $dbh );
		if ( !$qr ) {
			mysql_close( $dbh );
			return null;
		}
		$res = array();
		while ( $table_name = mysql_fetch_row($qr) ){
			$res[] = $table_name[0];
		}


		mysql_free_result( $qr );
		mysql_close( $dbh );

		return $res;
	}


	function rollbackDbCreation( $step, $databaseName, $existingDbName = null, $dbUser = null, $dbPassword = null, $existingTables = null)
	//
	// Rollbacks database creation process
	//
	//		Parameters:
	//			$step - process step
	//			$databaseName - name of database
	//			$existingDbName - the name of existing database
	//			$dbUser - database user name
	//			$dbPassword - database user password
	//
	{
		switch ( $step ) {
			case 2 : {
				$custFolder = realpath( sprintf( "%sdata/%s", WBS_DIR, strtoupper($DB_KEY) ) );

				@removeDir( $custFolder );
			}
			case 1 : {
				if ( !strlen($existingDbName) ) {
					@dropDatabase( $databaseName );
					@deleteDBUser( $databaseName, DB_WEBASYSTHOST );
				} else
					@deleteMetadata( $existingDbName, $dbUser, $dbPassword, $existingTables );
					//must delete WA tables only!
			}
		}
	}

	function dropDatabase( $databaseName )
	//
	// Deletes customer database
	//
	//		Parameters:
	//			$databaseName - database name
	//			$dbh -	������ �� ���� ������
	//
	//		Returns error code in case of error
	//
	{
		$db_host = DB_HOST;
		if ( strlen(DB_PORT) )
			$db_host = sprintf( "%s:%s", $db_host, DB_PORT );

		$dbh = mysql_connect( $db_host, DB_ADMIN_USER, DB_ADMIN_PASSWORD );
		if ( !$dbh )
			return $dbh;

		global $qr_host_dropDatabase;

		$res = @mysql_query( sprintf($qr_host_dropDatabase, $databaseName), $dbh );

		mysql_close( $dbh );

		return $res;
	}

	function deleteDBUser( $user_name, $webasyst_host )
	//
	// Deletes database user
	//
	//		Parameters:
	//			$user_name - user name
	//
	//		Returns null or error code
	//
	{
		$db_host = DB_HOST;
		if ( strlen(DB_PORT) )
			$db_host = sprintf( "%s:%s", $db_host, DB_PORT );

		$dbh = mysql_connect( $db_host, DB_ADMIN_USER, DB_ADMIN_PASSWORD );
		if ( !$dbh )
			return $dbh;

		global $qr_host_deleteUser;
		global $qr_host_revokePrivileges;

		$res = mysql_query( sprintf($qr_host_revokePrivileges, $user_name, $user_name, $webasyst_host), $dbh );
		if ( !$res ) {
			mysql_close( $dbh );
			return $res;
		}

		$res = mysql_query( sprintf($qr_host_deleteUser, $webasyst_host, $user_name), $dbh );
		if ( !$res ) {
			mysql_close( $dbh );
			return $res;
		}

		mysql_close( $dbh );

		return null;
	}

	function deleteMetadata( $databaseName, $dbUser = null, $dbPassword = null, $existingTables = null)
	//
	// Removes any metadata from database
	//
	//		Parameters:
	//			$databaseName - the name of database
	//			$dbUser - database user name
	//			$dbPassword - database user password
	//
	//		Returns null or error code
	//
	{
		global $qr_select_tables;
		global $qr_delete_table;

		$db_host = DB_HOST;
		if ( strlen(DB_PORT) )
			$db_host = sprintf( "%s:%s", $db_host, DB_PORT );

		$dbUser = ( is_null($dbUser) ) ? DB_ADMIN_USER : $dbUser;
		$dbPassword = ( is_null($dbPassword) ) ? DB_ADMIN_PASSWORD : $dbPassword;

		$dbh = mysql_connect( $db_host, $dbUser, $dbPassword );
		if ( !$dbh )
			return $dbh;

		if ( !($res = mysql_select_db( $databaseName )) ) {
			mysql_close( $dbh );
			return $res;
		}

		$qr = mysql_query( $qr_select_tables, $dbh );
		if ( !$qr ) {
			mysql_close( $dbh );
			return $qr;
		}

		while ( list($table_name) = mysql_fetch_row($qr) ){
			if(!$existingTables||($existingTables&&!in_array($table_name,$existingTables))){
				db_query( sprintf($qr_delete_table, $table_name) );
			}
		}

		mysql_free_result( $qr );
		mysql_close( $dbh );

		return null;
	}

	function removeDbProfile( $DB_KEY, $kernelStrings, $skipDBRemoving = false )
	//
	// Completely removes database and its profile file
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$kernelStrings - kernel localization strings
	//			$skipDBRemoving - skip database removing
	//
	//		Returns null or PEAR_Error
	//
	{
		global $wbs_sqlServers;

		if ( !$skipDBRemoving ) {
			$oldData = loadHostDataFile( $DB_KEY, $kernelStrings );
			if ( PEAR::isError($oldData) )
				return $oldData;

			$databaseName = $oldData[HOST_DBSETTINGS][HOST_DBNAME];

			// Drop database only if new database was created for this WebAsyst working DB
			//

			$newDb = $oldData[HOST_DBSETTINGS][HOST_DB_CREATE_OPTION] == DB_CREATION_NEW;

			if ( $newDb ) {
				$res = db_exists( $databaseName, $kernelStrings, $oldData[HOST_DBSETTINGS][HOST_SQLSERVER] );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings['app_errdropdb_message'] );

				if ($res) {
					if ( PEAR::isError( @dropDatabase($databaseName) ) )
						return PEAR::raiseError( $kernelStrings['app_errdropdb_message'] );

					$server = $oldData[HOST_DBSETTINGS][HOST_SQLSERVER];
					if ( array_key_exists( $server, $wbs_sqlServers ) ) {
						$db_sqlServer = $wbs_sqlServers[$server];

						if ( array_key_exists( WBS_WEBASYSTHOST, $db_sqlServer ) )
							$webasyst_host = $db_sqlServer[WBS_WEBASYSTHOST];
						else
							$webasyst_host = 'localhost';
					}

					if ( !strlen($webasyst_host) )
						$webasyst_host = 'localhost';

					if ( PEAR::isError( @deleteDBUser($databaseName, $webasyst_host) ) )
						return PEAR::raiseError( $kernelStrings['app_errdropuser_message'] );
				}
			}
		}

		$filePath = fixPathSlashes( sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DB_KEY)) );

		if ( file_exists($filePath) )
			if ( !@unlink($filePath) )
				return PEAR::raiseError( $kernelStrings['app_errdelprofile_message'] );


		global $_SERVER;
		$ip = $_SERVER['REMOTE_ADDR'];

		@logAccountOperation( $DB_KEY, $kernelStrings, aop_remove, $ip, null );

		return null;
	}

	function checkDBKey( $key_value, $kernelStrings, $dbSettingsData = null )
	//
	// Checks if database key already exists.
	//
	//		Parameters:
	//			$key_value - database key to check
	//			$dbSettingsData - database settings information. If not null database existence will be checked
	//			$kernelStrings - an array containing strings in specific language
	//
	//		Returns boolean
	//
	{
		$filePath = realpath( sprintf("%s/%s.xml", WBS_DBLSIT_DIR, $key_value) );
		if ( file_exists( $filePath ) )
			return true;

		if ( is_null($dbSettingsData) )
			return false;

		return db_exists( $key_value, $kernelStrings, $dbSettingsData[HOST_SQLSERVER] );
	}

	function createSysDatabase( $DB_KEY, $databaseInfo, $loginData, $kernelStrings, $existingDbName = null )
	//
	// Creates WBS database basing on database description file
	//
	//		Parameters:
	//			$DB_KEY - name of database
	//			$databaseInfo - database description data
	//			$loginData - login information (U_ID, U_PASSWORD, DB_KEY)
	//			$kernelStrings - an array containing strings in specific language
	//			$existingDbName - the name of existing database
	//
	//		Returns null or PEAR::Error
	//
	{
		global $DEF_USERSETTINGS;
		global $qr_create_database;
		global $qr_createDBUser;
		global $qr_flushPrivileges;
		global $wbs_database;
		global $qr_host_addUser;
		global $qr_host_addCompanyData;
		global $global_screens;
		global $global_applications;
		global $qr_host_addUserAccess;
		global $qr_createDBReadonlyUser;
		global $qr_host_addFolder;
		global $qr_host_addContact;
		global $qr_host_addContactType;
		global $userCommonAccessSettings;
		global $qr_host_grantAdminAccess;
		global $qr_host_deleteDemoUser;
		global $qr_host_deleteDemoUserContact;
		global $qr_host_addContactEmail;

		if ( !isset($databaseInfo[HOST_FIRSTLOGIN]) )
			return PEAR::raiseError( $kernelStrings['app_nosubscrdata_message'] );

		// Check login data
		//

		if(isset($loginData['LOGIN'])&&isset($loginData['HASH'])){

			$lhashEntry = new LoginHash();
			$res = $lhashEntry->loadByHash($loginData['DB_KEY'], $loginData['HASH']);

			if($res && $lhashEntry->LOGIN == $loginData['LOGIN']){

				$loginData["U_ID"] = $loginData['LOGIN'];
				if ( strtoupper( $loginData["U_ID"] ) == ADMIN_USERNAME ) {
					$adminInfo = loadAdminInfo();
					$loginData['U_PASSWORD'] = $adminInfo[PASSWORD];
					$lhashEntry->deleteHash();
				}elseif (strtoupper( $loginData["U_ID"] ) == strtoupper($databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME])){
					$loginData['U_PASSWORD'] = $databaseInfo[HOST_FIRSTLOGIN][HOST_PASSWORD];
					$lhashEntry->deleteHash();
				}
			}
		}

		if ( strtoupper( $loginData["U_ID"] ) == ADMIN_USERNAME ) {
			$adminInfo = loadAdminInfo();

			if (!$adminInfo || strtolower($adminInfo[PASSWORD]) != strtolower($loginData['U_PASSWORD']))
				return PEAR::raiseError( $kernelStrings['app_invlogindata_message'], ERRCODE_APPLICATION_ERR );
		} else
			if( strtoupper($loginData["U_ID"]) != strtoupper($databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME])
				|| strtoupper($loginData["U_PASSWORD"]) != strtoupper($databaseInfo[HOST_FIRSTLOGIN][HOST_PASSWORD]) )
				return PEAR::raiseError( $kernelStrings['app_invlogindata_message'], ERRCODE_APPLICATION_ERR );
		// Create new database and database user
		//
		$db_host = DB_HOST;
		if ( strlen(DB_PORT) )
			$db_host = sprintf( "%s:%s", $db_host, DB_PORT );

		$dbPassword = $databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD];

		if ( $databaseInfo[HOST_DBSETTINGS][HOST_DB_CREATE_OPTION] == DB_CREATION_NEW ) {

			$dbh = mysql_connect( $db_host, DB_ADMIN_USER, DB_ADMIN_PASSWORD );
			if ( !$dbh ) {
				PEAR::raiseError( mysql_error() );
				return PEAR::raiseError( $kernelStrings['app_invsqlconnect_message'] );
			}

			$databaseName = '`'.$databaseInfo[HOST_DBSETTINGS][HOST_DBNAME].'`';

			$def_charset = '';
			if(isset($databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET']) && $databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET']){

				$def_charset = ' DEFAULT CHARACTER SET '.$databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET'];
			}

			$qr = mysql_query( sprintf( $qr_create_database, $databaseName.$def_charset ) );
			if ( !$qr ) {
				PEAR::raiseError( mysql_error() );
				mysql_close( $dbh );
				return PEAR::raiseError( $kernelStrings['app_errcreatingdb_message'] );
			}

			$userSQL = $qr_createDBUser;
			if ( $databaseInfo[HOST_DBSETTINGS][HOST_READONLY] )
				$userSQL = $qr_createDBReadonlyUser;

			$user = $databaseInfo[HOST_DBSETTINGS][HOST_DBUSER];
			$password = $databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD];

			//Protect overwrite MySQL admin rights at create new Database
			if($user!=DB_ADMIN_USER){
				if ( !mysql_query( sprintf($userSQL, $databaseName, $user, DB_WEBASYSTHOST, $password) ) ) {
					rollbackDbCreation( 1, $databaseName, $databaseName );
					PEAR::raiseError( mysql_error() );
					mysql_close( $dbh );
					return PEAR::raiseError( $kernelStrings['app_errmysqluser_message'] );
				}

				if ( !mysql_query($qr_flushPrivileges) ) {
					rollbackDbCreation( 1, $databaseName, $databaseName );
					PEAR::raiseError( mysql_error() );
					mysql_close( $dbh );
					return PEAR::raiseError( $kernelStrings['app_errmysqluser_message'] );
				}
			}

			mysql_close( $dbh );
			$databaseName = str_replace('`','',$databaseName);
		} else
			$databaseName = $existingDbName;

		$is_hosted = $databaseInfo[HOST_DBSETTINGS][HOST_DB_CREATE_OPTION] == DB_CREATION_USEEXISTING;

		$mySQLUser = ($is_hosted) ? $databaseInfo[HOST_DBSETTINGS][HOST_DBUSER] : DB_ADMIN_USER;
		$mySQLPassword = ($is_hosted) ? $databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD] : DB_ADMIN_PASSWORD;

		$mySQLCharset = isset($databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET'])&&$databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET']?$databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET']:null;

		$existTables = prepareRollback($existingDbName, $mySQLUser, $mySQLPassword);

		// Connect to new database
		//
		if ( PEAR::isError( $wbs_database = db_custom_connect($databaseName, $mySQLUser, $mySQLPassword, null, null, $mySQLCharset) ) ) {
			rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
			return PEAR::raiseError( sprintf($kernelStrings['app_invdbconnect_message'], $DB_KEY),  ERRCODE_APPLICATION_ERR );
		}

		// Create applications metadata for new billing
		//
		if(onWebAsystServer()){
			$app_list = explode(',', $databaseInfo[HOST_DBSETTINGS][HOST_FREE_APPS] );
		}else{//or for OS version
			$app_list = array_keys($databaseInfo[HOST_APPLICATIONS]);
		}
		$subscriber_ID = $databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME];

		$app_list = array_merge( array(AA_APP_ID), $app_list );

		$userSettings = null;
		foreach( $app_list as $APP_ID ) {
			$res = installApplicationMetadata( $DB_KEY, trim($APP_ID) , $kernelStrings, $databaseInfo, $userSettings );
			if ( PEAR::isError($res) ) {
				rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
				return $res;
			}
		}

		if ( isset($databaseInfo[HOST_DBSETTINGS][HOST_TRIALDATASOURCE]) ) {
			// Delete demo user from db (only for trialdatasource based accounts)
			if ( PEAR::isError( $res = db_query($qr_host_deleteDemoUser, array ()) ) ) {
				rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			if ( PEAR::isError( $res = db_query($qr_host_deleteDemoUserContact, array ()) ) ) {
				rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}
			
			if ( PEAR::isError( $res = db_query("DELETE FROM EMAIL_CONTACT WHERE EC_EMAIL = 'demo@webasyst.net'", array ()) ) ) {
				rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}			
		}

		// Create Contact
		//
		$contactData = array();

		global $qr_selectmaxc_id ;
		$newC_ID = db_query_result( $qr_selectmaxc_id, DB_FIRST, array() );
		$newC_ID = incID($newC_ID);

		$contactData['C_ID'] = $newC_ID;
		$contactData['CT_ID'] = 1;
		$contactData['CF_ID'] = 'PRIVATE'.$newC_ID;
		$contactData['C_FIRSTNAME'] = $databaseInfo[HOST_FIRSTLOGIN][HOST_FIRSTNAME];
		$contactData['C_LASTNAME'] = $databaseInfo[HOST_FIRSTLOGIN][HOST_LASTNAME];
		$contactData['C_FULLNAME'] = $contactData['C_FIRSTNAME'].($contactData['C_LASTNAME'] ? " ".$contactData['C_LASTNAME'] : ""); 
		$contactData['C_MIDDLENAME'] = null;
		$contactData['C_EMAILADDRESS'] = $databaseInfo[HOST_FIRSTLOGIN][HOST_EMAIL];
		$contactData['C_CREATEDATETIME'] = convertToSqlDateTime( time() );
		$contactData['C_CREATECID'] = $newC_ID;

		if ( PEAR::isError( $res = db_query($qr_host_addContact, $contactData) )) {
			rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}
		if ($contactData['C_EMAILADDRESS']) {		
			if ( PEAR::isError( $res = db_query($qr_host_addContactEmail, $contactData) )) {
				rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}			
		}

		// Create user account
		//
		$userData = array();

		if ( is_null($userSettings) ) {
			if ( SHOW_TIPSANDTRICKS )
				$startPage = USE_TIPSANDTRICKS;
			else
				$startPage = USE_BLANK;
			
			$user_settins = $DEF_USERSETTINGS;
			$user_settins['language'] = $databaseInfo[HOST_ADMINISTRATOR][HOST_LANGUAGE];
			$user_settins['START_PAGE'] = $startPage;
			foreach ($user_settins as $name => $value) {
				$sql = "INSERT INTO USER_SETTINGS SET U_ID = '!U_ID!', APP_ID = '', NAME = '!NAME!', VALUE = '!VALUE!'";
				$params = array(
					'U_ID' => $databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME],
					'NAME' => $name,
					'VALUE' => $value
				);
				if ( PEAR::isError( $res = db_query($sql, $params) ) ) {
					rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}				
			}
			
			//$userSettings = sprintf( XML_DEF_USERSETTINGS, "?", "?", $startPage, $databaseInfo[HOST_ADMINISTRATOR][HOST_LANGUAGE] );
			
			$userSettings = "";
		}
		
		$sql = "UPDATE USER_SETTINGS SET U_ID = '!U_ID!' WHERE U_ID <> ''";
		if ( PEAR::isError( $res = db_query($sql, array('U_ID' => $databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME])))) {
			rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		$userData["U_ID"] = $databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME];
		$userData["U_PASSWORD"] = strtolower($databaseInfo[HOST_FIRSTLOGIN][HOST_PASSWORD]);
		$userData["U_STATUS"] = RS_ACTIVE;
		$userData["U_SETTINGS"] = $userSettings;
		$userData["U_ACCESSTYPE"] = ACCESS_SUMMARY;
		$userData["C_ID"] = $newC_ID;

		if ( PEAR::isError( $res = db_query($qr_host_addUser, $userData) ) ) {
			rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		// Create company information
		//
		$companyData = array();
		$companyData["COM_NAME"] = $databaseInfo[HOST_FIRSTLOGIN][HOST_COMPANYNAME];
		$companyData["COM_CONTACTPERSON"] = $databaseInfo[HOST_FIRSTLOGIN][HOST_FIRSTNAME]." ".$databaseInfo[HOST_FIRSTLOGIN][HOST_LASTNAME];
		$companyData["COM_EMAIL"] = $databaseInfo[HOST_FIRSTLOGIN][HOST_EMAIL];

		if ( PEAR::isError( db_query( $qr_host_addCompanyData, $companyData ) ) ) {
			rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		// Grant user access
		//
		// db_query( $qr_host_grantAdminAccess, array('U_ID'=>$databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME]) );
		global $UR_Manager;

		$host_applications = getHostApplications();
		$host_applications = array_merge( $host_applications, array( MYWEBASYST_APP_ID, AA_APP_ID, WIDGETS_APP_ID, UG_APP_ID ) );

		// System information registering
		//
		foreach ( $host_applications as $APP_ID )
			if ( !performAppRegistration( $APP_ID ) )
				die( "Error registering applications" );

		$ur_applications = array();
		foreach ( $host_applications as $application )
			$ur_applications[$application] = $application;

		foreach ( sortApplicationList( $ur_applications ) as $application )
			require_once( WBS_PUBLISHED_DIR . "/". strtoupper( $application ) . "/" .WBS_UR_APPCLASS_FILE );

		$UR_Manager->SetGlobalRightsPath( $databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME], UR_USER_ID, '/ROOT', UR_GRANT );

		// Create customer folder
		//
		$custFolder = sprintf( "%sdata/%s/attachments", WBS_DIR, strtoupper($DB_KEY) );

		if ( !@forceDirPath( $custFolder, $errStr ) ) {
			rollbackDbCreation( 1, $databaseName, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
			return PEAR::raiseError( $kernelStrings['app_createdir_message'] );
		}

		if ( PEAR::isError( writeHostDataFileParameter("/".HOST_DATABASE."/".HOST_DBSETTINGS, HOST_CREATEDATE, convertToSqlDateTime(time()), $kernelStrings) ) ) {
			rollbackDbCreation( 2, $DB_KEY, $existingDbName, $mySQLUser, $mySQLPassword, $existTables );
			return PEAR::raiseError( $kernelStrings['app_errxml_message'] );
		}

		// Process the OnDbCreateHandler
		//
		if ( defined('WBS_ONDBCREATE_HANDLER_PATH') )
			if ( file_exists(WBS_ONDBCREATE_HANDLER_PATH) ) {
				include_once(WBS_ONDBCREATE_HANDLER_PATH);

				$dbCreateHandler = new dbCreateHandler();
				$dbCreateHandler->handleDbCreation( $DB_KEY );
			}

		//
		// Create mail account on (remote) mail server (save it to "common DB")
		//
		if ( in_array( 'MM', $app_list ) && onWebAsystServer() )
		{
			global $mt_hosting_plan_extensions;

			$accountData['MMA_EMAIL'] = strtolower($loginData['U_ID']);
			$accountData['MMA_DOMAIN'] = $_SERVER['HTTP_HOST'];
			$accountData['MMA_OWNER'] = $DB_KEY;
			$limit = getApplicationResourceLimits( 'MM' );
			$accountData['MMA_QUOTA'] = $mt_hosting_plan_extensions['MM'][$limit]['MM_DISK_QUOTA'];
			// password generator
			$symb = 'qwertyuiopasdfghjkzxcvbnmQWERTYUPASDFGHJKLZXCVBNM23456789'; 
			$count = strlen($symb)-1; 
			$password = '';
			for( $i=0; $i < 7; $i++ ) 
				$password .= $symb[rand(0, $count)]; 
			$accountData['MMA_PASSWORD'] = $password;
			//
			// Read XML config
			//
			if ( !$xml = file_get_contents( WBS_DIR . 'kernel/wbs.xml' ) )
				return PEAR::raiseError( 'ERROR: File "wbs.xml" doesn\'t exist' );
			$sxml = new SimpleXMLElement( $xml );
			//
			// MAILDAEMONDB section doesn't exists or parameters not set
			//
			if( sizeof( (array)$sxml->MAILDAEMONDB->attributes() ) == 0 )
				return PEAR::raiseError( 'MAILDAEMONDB section doesn\'t exists or parameters not set' );
			//
			// Get data...
			//
			$serverName = (string)$sxml->MAILDAEMONDB->attributes()->SERVER_NAME;
			$pageUrl    = (string)$sxml->MAILDAEMONDB->attributes()->PAGE_URL;
			$pageUrl   .= "?action=add";
			foreach( $accountData as $key=>$val )
				$pageUrl .= "&" . rawurlencode( $key ) . "=" . rawurlencode( $val );
			//
			// Request remote url...
			//
			$ret = file_get_contents("$serverName/$pageUrl");
			if($ret == 'OK') //	return PEAR::raiseError( "Can't create mail account" );
			{
				//
				// Save account to local DB
				//
				global $qr_insertMailAccount;
				$accountData['MMA_INTERNAL'] = 1;
				$accountData['MMA_NAME'] = $contactData['C_CREATEUSERNAME'];
				$res = db_query( $qr_insertMailAccount, $accountData );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( "Can't save mail account to local DB" );
			}
		}
	}
	
	function addSupportEmail($DB_KEY)
	{
			if (substr($_SERVER['HTTP_HOST'], 0, 9) == 'webasyst.') {
				$sql = "DELETE FROM st_source WHERE id = 1";
				db_query($sql);
				$sql = "DELETE FROM st_source_param WHERE source_id = 1";
				db_query($sql);
				return true;
			}
			$accountData = array(
				'MMA_EMAIL' => 'support',
				'MMA_DOMAIN' => $_SERVER['HTTP_HOST'],
				'MMA_OWNER' => $DB_KEY,
				'MMA_QUOTA' => 100,
			);
			// password generator
			$symb = 'qwertyuiopasdfghjkzxcvbnmQWERTYUPASDFGHJKLZXCVBNM23456789'; 
			$count = strlen($symb)-1; 
			$password = '';
			for ($i = 0; $i < 7; $i++ ) { 
				$password .= $symb[rand(0, $count)];
			} 
			$accountData['MMA_PASSWORD'] = $password;
			//
			// Read XML config
			//
			if ( !$xml = file_get_contents( WBS_DIR . 'kernel/wbs.xml' ) )
				return PEAR::raiseError( 'ERROR: File "wbs.xml" doesn\'t exist' );
			$sxml = new SimpleXMLElement( $xml );
			//
			// MAILDAEMONDB section doesn't exists or parameters not set
			//
			if( sizeof( (array)$sxml->MAILDAEMONDB->attributes() ) == 0 )
				return PEAR::raiseError( 'MAILDAEMONDB section doesn\'t exists or parameters not set' );
			//
			// Get data...
			//
			$serverName = (string)$sxml->MAILDAEMONDB->attributes()->SERVER_NAME;
			$pageUrl    = (string)$sxml->MAILDAEMONDB->attributes()->PAGE_URL;
			$pageUrl   .= "?action=add";
			foreach( $accountData as $key=>$val )
				$pageUrl .= "&" . rawurlencode( $key ) . "=" . rawurlencode( $val );
			//
			// Request remote url...
			//
			$ret = file_get_contents("$serverName/$pageUrl");
			if($ret == 'OK') //	return PEAR::raiseError( "Can't create mail account" );
			{
				$email = $accountData['MMA_EMAIL'].'@'.$accountData['MMA_DOMAIN'];
				$sql = "REPLACE INTO st_source_param (`source_id`, `name`, `value`) VALUES
(1, 'login', '{$email}'),
(1, 'password', '{$accountData['MMA_PASSWORD']}'),
(1, 'email', '{$email}'),
(1, 'inner', 1),
(1, 'receipt_email', 'noreply@{$accountData['MMA_DOMAIN']}'),
(1, 'confirm_email', 'noreply@{$accountData['MMA_DOMAIN']}')";
				$res = db_query( $sql, array());
				if ( PEAR::isError($res) )
					return PEAR::raiseError( "Can't save mail account to local DB" );
				
			}
	}

	function installApplications( $DB_KEY, $installApps, $kernelStrings, $databaseInfo )
	//
	// Deletes or installs applications metadata
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$installApps - list of applications to install
	//			$kernelStrings - an array containing strings in specific language
	//			$databaseInfo - database description data
	//
	//		Returns null or PEAR_Error
	//
	{
		global $wbs_database;
		global $wbs_sqlServers;

		$serverName = $databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER];

		if ( !array_key_exists($serverName, $wbs_sqlServers) )
			return PEAR::raiseError( sprintf($kernelStrings['app_servernotfound_message'], $serverName) );

		$sqlServerParams = $wbs_sqlServers[$serverName];

		$dbHost = $sqlServerParams[WBS_HOST];
		$dbPort = $sqlServerParams[WBS_PORT];

		$databaseName = $databaseInfo[HOST_DBSETTINGS][HOST_DBNAME];

		$is_hosted = $databaseInfo[HOST_DBSETTINGS][HOST_DB_CREATE_OPTION] == DB_CREATION_USEEXISTING;
		$mySQLUser = ($is_hosted) ? $databaseInfo[HOST_DBSETTINGS][HOST_DBUSER] : DB_ADMIN_USER;
		$mySQLPassword = ($is_hosted) ? $databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD] : DB_ADMIN_PASSWORD;

		$subscriber_ID = $databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME];

		$prevConnection = $wbs_database;

		$mySQLCharset = isset($databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET'])&&$databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET']?$databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET']:null;

		if ( PEAR::isError( $wbs_database = db_custom_connect($databaseName, $mySQLUser, $mySQLPassword, $dbHost, $dbPort, $mySQLCharset) ) ) {
			$wbs_database = $prevConnection;
			return PEAR::raiseError( sprintf($kernelStrings['app_invdbconnect_message'], $DB_KEY),  ERRCODE_APPLICATION_ERR );
		}

		// Create applications metadata
		//
		$userSettings = null;
			
		foreach( $installApps as $APP_ID ) {
			$res = installApplicationMetadata( $DB_KEY, $APP_ID, $kernelStrings, $databaseInfo, $userSettings );

			if ( PEAR::isError($res) )
				return $res;
			
		}
		
		$wbs_database = $prevConnection;

		return null;
	}

	function updateDatabaseReadonlyFlag( $DB_KEY, $readOnly, $kernelStrings, $dbPassword )
	//
	// Updates database user grants
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$readOnly - database readonly flag value
	//			$kernelStrings - an array containing strings in specific language
	//			$dbPassword - database user password
	//
	//		Returns null or PEAR::Error
	//
	{
		global $qr_createDBUser;
		global $qr_createDBReadonlyUser;
		global $qr_flushPrivileges;
		global $wbs_sqlServers;

		$oldData = loadHostDataFile( $DB_KEY, $kernelStrings );
		if ( PEAR::isError($oldData) )
			return $oldData;

		$user_name = $databaseName = $oldData[HOST_DBSETTINGS][HOST_DBNAME];

		$server = $oldData[HOST_DBSETTINGS][HOST_SQLSERVER];

		if ( array_key_exists( $server, $wbs_sqlServers ) ) {
			$db_sqlServer = $wbs_sqlServers[$server];

			if ( array_key_exists( WBS_WEBASYSTHOST, $db_sqlServer ) )
				$webasyst_host = $db_sqlServer[WBS_WEBASYSTHOST];
			else
				$webasyst_host = 'localhost';
		}

		if ( !strlen($webasyst_host) )
			$webasyst_host = 'localhost';

		if ( PEAR::isError( deleteDBUser($user_name, $webasyst_host) ) )
			return PEAR::raiseError( $kernelStrings['app_sqluserrightsupd_message'] );

		$db_host = DB_HOST;
		if ( strlen(DB_PORT) )
			$db_host = sprintf( "%s:%s", $db_host, DB_PORT );

		$dbh = mysql_connect( $db_host, DB_ADMIN_USER, DB_ADMIN_PASSWORD );
		if ( !$dbh )
			return PEAR::raiseError( $kernelStrings['app_invsqlconnect_message']." ".mysql_error() );

		$userSQL = $qr_createDBUser;
		if ( $readOnly )
			$userSQL = $qr_createDBReadonlyUser;

		if ( !mysql_query( sprintf($userSQL, $databaseName, $databaseName, $webasyst_host, $dbPassword) ) ) {
			mysql_close($dbh);
			return PEAR::raiseError( $kernelStrings['app_errmysqluser_message']." ".mysql_error() );
		}

		if ( !mysql_query( $qr_flushPrivileges ) ) {
			mysql_close($dbh);
			return PEAR::raiseError( $kernelStrings['app_errmysqluser_message']." ".mysql_error() );
		}

		mysql_close($dbh);

		return null;
	}

	function host_login( $loginData, $kernelStrings, $ip, $client, $supressLoginLog = false, $directAccess = false )
	//
	// Checks if user can login to customer database, logs connection. Creates new customer database if necessary.
	//
	//		Parameters:
	//			$loginData - login information (U_ID, U_PASSWORD, DB_KEY)
	//			$kernelStrings - an array containing strings in specific language
	//			$ip - client IP address
	//			$client - user client (web, soap and so on)
	//			$supressLoginLog - do not add login record into the log
	//			$directAccess - user attempts to access his start page directly
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $databaseInfo;
		global $wbs_database;
		global $host_applications;
		global $DB_KEY;

		$loginData = trimArrayData( $loginData );

		$DB_KEY = strtoupper( $loginData["DB_KEY"] );

		if ( PEAR::isError($databaseInfo = loadHostDataFile($DB_KEY, $kernelStrings) ) ) {
			host_log( $DB_KEY, $loginData["U_ID"], $ip, $client, 1, 0 );
			return $databaseInfo;
		}

		if ( isset($databaseInfo[HOST_DBSETTINGS][HOST_STATUS]) && $databaseInfo[HOST_DBSETTINGS][HOST_STATUS] == HOST_STATUS_DELETED )
			return PEAR::raiseError( $kernelStrings['app_editdeldb_message'], ERRCODE_APPLICATION_ERR );

		if ( isset($databaseInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE]) && strlen($databaseInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE]) ) {
			$dbStamp = sqlTimestamp( $databaseInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE] );

			if ( $dbStamp <= time() ) {
				host_log( $DB_KEY, $loginData["U_ID"], $ip, $client, 1, 0 );
				return PEAR::raiseError( $kernelStrings['app_cancelledaccount_message'], ERRCODE_APPLICATION_ERR );
			}
		}

		$DB_NAME = $databaseInfo[HOST_DBSETTINGS][HOST_DBNAME];

		$is_hosted = $databaseInfo[HOST_DBSETTINGS][HOST_DB_CREATE_OPTION] == DB_CREATION_USEEXISTING;

		if ( !$is_hosted ) {
			$res = db_exists($databaseInfo[HOST_DBSETTINGS][HOST_DBNAME], $kernelStrings);
			if ( PEAR::isError($res) )
				return $res;
			if($res){
				$res = metadata_exists( $databaseInfo[HOST_DBSETTINGS][HOST_DBNAME], $kernelStrings, $databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER] );
				if ( PEAR::isError($res) )
					return $res;
			}
		} else {
			$res = metadata_exists( $databaseInfo[HOST_DBSETTINGS][HOST_DBNAME], $kernelStrings, $databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER] );
			if ( PEAR::isError($res) )
				return $res;
		}
		$host_applications = getHostApplications();

		$newDb = false;
		if ( !$res ) {
			if ( PEAR::isError( $res = createSysDatabase($DB_KEY, $databaseInfo, $loginData, $kernelStrings, ($is_hosted?$DB_NAME:null) ) ) )
				return $res;

			// Log record
			//
			global $_SERVER;
			if ( !strlen($ip) )
				$ip = $_SERVER['REMOTE_ADDR'];

			@logAccountOperation( $DB_KEY, $kernelStrings, aop_dbcreate, $ip, null, $databaseInfo[HOST_DBSETTINGS][HOST_SOURCE] );
			$newDb = true;

		} else {
			$DBPassword = $databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD];
			$DBUser = $databaseInfo[HOST_DBSETTINGS][HOST_DBUSER];
			$DBCharset = isset($databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET'])&&$databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET']?$databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET']:null;

			if ( PEAR::isError( $wbs_database = db_custom_connect($DB_NAME, $DBUser, $DBPassword, null, null, $DBCharset) ) )
				return PEAR::raiseError( sprintf($kernelStrings['app_invdbconnect_message'], $DB_KEY),  ERRCODE_APPLICATION_ERR );
		}

		$loginStatus = checkUserLoginInfo( $loginData, $directAccess, $kernelStrings, isset($databaseInfo[HOST_ADMINISTRATOR]['ADMIN_LOGIN'])?$databaseInfo[HOST_ADMINISTRATOR]['ADMIN_LOGIN']=="1":false );

		if ( $loginStatus == ST_OK ) {
			@cleanUpTemporaryDir();

			if ( !$supressLoginLog )
				host_log( $DB_KEY, $loginData["U_ID"], $ip, $client, 0, 0 );

			return null;
		} else
			if ( $loginStatus == LRC_INVALIDUSER ) {
				host_log( $DB_KEY, $loginData["U_ID"], $ip, $client, 0, 1 );
				return PEAR::raiseError( $kernelStrings['app_invlogindata_message'], ERRCODE_APPLICATION_ERR );
			} elseif ( $loginStatus == LRC_INACTIVEUSER ) {
				host_log( $DB_KEY, $loginData["U_ID"], $ip, $client, 0, 1 );
				return PEAR::raiseError( $kernelStrings['app_notactivelogin_message'], ERRCODE_APPLICATION_ERR );
		    } elseif ( $loginStatus == ST_INVALIDDA )
				return PEAR::raiseError( $kernelStrings['app_invdirectaccess_message'], ERRCODE_APPLICATION_ERR );
	}





	function host_forgot( $loginData, $kernelStrings, $ip, $client)
	//
	// Checks if user
	//
	//		Parameters:
	//			$loginData - login information (U_ID, EMAIL, DB_KEY)
	//			$kernelStrings - an array containing strings in specific language
	//			$ip - client IP address
	//			$client - user client (web, soap and so on)
	//
	//		Returns null, or PEAR_Error
	//
	{
		$res = loadDatabase ($loginData, $kernelStrings, $ip, $client);
		if (PEAR::isError ($res))
			return $res;

		$emailStatus = checkUserEmailInfo( $loginData, $directAccess, $kernelStrings );
		if (PEAR::isError($emailStatus))
			return $emailStatus;

		if ( $emailStatus == ST_OK ) {
			$newPassword = generateUserPassword(6);
			$newPasswordData = array("U_ID" => $loginData["U_ID"], "PASSWORD1" => $newPassword, "PASSWORD2" => $newPassword);
			$res = updateUserPassword ($newPasswordData, $kernelStrings, false);

			$fullName = $loginData["C_FIRSTNAME"];
			if (!empty($loginData["C_LASTNAME"]))
				$fullName .= " " . $loginData["C_LASTNAME"];

			if (PEAR::isError($res))
				return $res;

			loadDatabaseLanguageList ($loginData["DB_KEY"]);
			global $wbs_languages;
			global $language;
			global $wbs_robotemailaddress;

			$messagePath = sprintf( "%spublished/AA/includes/messages/remindpassword.%s.txt", WBS_DIR, $language );

			$bodyTemplate = implode( "", file($messagePath));
			$bodyTemplate = str_replace( "\r\n", "\n", $bodyTemplate );

			$bodyTemplate = str_replace( "%PASSWORD%", $newPassword, $bodyTemplate );
			$bodyTemplate = str_replace( "%FULLNAME%", $fullName, $bodyTemplate );

			//$mailer = new PHPMailer;
			$mailer = new WBSMailer;
			$mailer->isHTML(true);
			$mailer->CharSet = $wbs_languages[strtolower($language)][WBS_ENCODING];
			$mailer->Body = nl2br($bodyTemplate);
			$mailer->AltBody = strip_tags($bodyTemplate);
			$mailer->Subject = $kernelStrings["app_mail_forgotpassword_subject"];
			$mailer->From = $wbs_robotemailaddress;
			$mailer->FromName = 'WebAsyst';
			$mailer->Sender = $wbs_robotemailaddress;
			$mailer->AddReplyTo($wbs_robotemailaddress, '');

			$mailer->AddAddress($loginData["EMAIL"], "Test message");
			$res = $mailer->Send();

			return ST_OK;
		}	else return $emailStatus;
	}






	//
	// Checks if user can login to customer database, logs connection. Creates new customer database if necessary.
	//
	//		Parameters:
	//			$loginData - login information (U_ID, EMAIL, DB_KEY)
	//			$kernelStrings - an array containing strings in specific language
	//			$ip - client IP address
	//			$client - user client (web, soap and so on)
	//
	//		Returns null, or PEAR_Error
	//
	function loadDatabase ($loginData, $kernelStrings, $ip, $client) {
		global $databaseInfo;
		global $wbs_database;
		global $host_applications;
		global $DB_KEY;

		$loginData = trimArrayData( $loginData );

		$DB_KEY = strtoupper( $loginData["DB_KEY"] );

		if ( PEAR::isError($databaseInfo = loadHostDataFile($DB_KEY, $kernelStrings) ) ) {
			host_log( $DB_KEY, $loginData["U_ID"], $ip, $client, 1, 0 );
			return $databaseInfo;
		}

		if ( isset($databaseInfo[HOST_DBSETTINGS][HOST_STATUS]) && $databaseInfo[HOST_DBSETTINGS][HOST_STATUS] == HOST_STATUS_DELETED )
			return PEAR::raiseError( $kernelStrings['app_editdeldb_message'], ERRCODE_APPLICATION_ERR );

		if ( isset($databaseInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE]) && strlen($databaseInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE]) ) {
			$dbStamp = sqlTimestamp( $databaseInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE] );

			if ( $dbStamp <= time() ) {
				host_log( $DB_KEY, $loginData["U_ID"], $ip, $client, 1, 0 );
				return PEAR::raiseError( $kernelStrings['app_cancelledaccount_message'], ERRCODE_APPLICATION_ERR );
			}
		}

		$DB_NAME = $databaseInfo[HOST_DBSETTINGS][HOST_DBNAME];

		$is_hosted = $databaseInfo[HOST_DBSETTINGS][HOST_DB_CREATE_OPTION] == DB_CREATION_USEEXISTING;
		
		if ( !$is_hosted ) {
			$res = db_exists($databaseInfo[HOST_DBSETTINGS][HOST_DBNAME], $kernelStrings);
			if ( PEAR::isError($res) )
				return $res;
		} else {
			$res = metadata_exists( $databaseInfo[HOST_DBSETTINGS][HOST_DBNAME], $kernelStrings, $databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER] );
			if ( PEAR::isError($res) )
				return $res;
		}

		$host_applications = getHostApplications();

		$newDb = false;
		if ( !$res ) {
			if ( PEAR::isError( $res = createSysDatabase($DB_KEY, $databaseInfo, $loginData, $kernelStrings, $DB_NAME ) ) )
				return $res;

			// Log record
			//
			global $_SERVER;
			if ( !strlen($ip) )
				$ip = $_SERVER['REMOTE_ADDR'];

			@logAccountOperation( $DB_KEY, $kernelStrings, aop_dbcreate, $ip, null, $databaseInfo[HOST_DBSETTINGS][HOST_SOURCE] );
			$newDb = true;

		} else {
			$DBPassword = $databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD];
			$DBUser = $databaseInfo[HOST_DBSETTINGS][HOST_DBUSER];
			$DBCharset = isset($databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET'])&&$databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET']?$databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET']:null;

			if ( PEAR::isError( $wbs_database = db_custom_connect($DB_NAME, $DBUser, $DBPassword, null, null, $DBCharset) ) )
				return PEAR::raiseError( sprintf($kernelStrings['app_invdbconnect_message'], $DB_KEY),  ERRCODE_APPLICATION_ERR );
		}
	}





	//
	// User settings functions
	//

	function selectUserSettings( $U_ID )
	//
	//  Returns string containing user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//
	//	Returns string containing user settings
	//
	{
		global $qr_selectUserSettings;
		return db_query_result( $qr_selectUserSettings, DB_FIRST, array("U_ID"=>$U_ID ) );
	}

	function updateUserSettings( $U_ID, $U_SETTINGS, $kernelStrings )
	//
	//	Stores user settings in database
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$U_SETTINGS - string containing user settings
	//
	//	Returns null, or PEAR_Error
	//
	{
		global $qr_updateUserSettings;

		$params = array("U_ID"=>$U_ID, "U_SETTINGS"=>$U_SETTINGS);

		if ( PEAR::isError( $res = exec_sql( $qr_updateUserSettings, $params, $outputList, false ) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function writeUserCommonSetting( $U_ID, $paramName, $paramValue, $kernelStrings, $useCookies = false )
	//
	// Stores settings in user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$paramName - name of parameter
	//			$paramValue - value of parameter
	//			$kernelStrings - kernel localization strings
	//			$useCookies - use cookies instead of database
	//
	//	Returns null, or PEAR_Error
	//
	{
		if ( !$useCookies ) {
			$sql = "REPLACE INTO USER_SETTINGS SET U_ID = '!U_ID!', APP_ID = '', NAME = '!NAME!', VALUE = '!VALUE!'"; 
			$params = array(
				'U_ID' => $U_ID,
				'NAME' => $paramName,
				'VALUE' => $paramValue
			);	
			if ( PEAR::isError( $res = db_query($sql, $params))) {
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}		
			return null;
		} else {
			$cookieName = 'webasyst'.$U_ID.$paramName;
			$_COOKIE[$cookieName] = $paramValue;
			setcookie( $cookieName, $paramValue, time()+518400, "/" );
		}
	}

	function readUserCommonSetting( $U_ID, $paramName, $useCookies = false )
	//
	//	Returns value of user setting
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$paramName - name of parameter
	//			$useCookies - use cookies instead of database
	//
	//	Returns value of parameter, or null
	//
	{
		if ( !$useCookies ) {
			$sql = "SELECT VALUE FROM USER_SETTINGS WHERE U_ID = '!U_ID!' AND APP_ID = '' AND NAME = '!NAME!'";
			$params = array(
				"U_ID" => $U_ID,
				"NAME" => $paramName
			);
			$res = db_query_result($sql, DB_FIRST, $params);
			return $res;
		} else {
			if ( isset( $_COOKIE['webasyst'.$U_ID.$paramName] ) )
				return $_COOKIE['webasyst'.$U_ID.$paramName];
			else
				return null;
		}

		return null;
	}

	function getUserSettingsRoot( $U_ID, &$dom )
	//
	// Returns DOM XML object of node COMMONSETTINGS from user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$dom - variable for storing Dom Document object
	//
	//		Returns object DOM_ELEMENT, or null
	//
	{
		$str_settings = selectUserSettings( $U_ID );
		$dom = domxml_open_mem( $str_settings );

		if ( $dom ) {
			$element = getElementByTagname( $dom, COMMONSETTINGS );

			if ( $element )
				return $element;
		}

		return null;
	}

	function saveUserSettingsDOM( $U_ID, &$dom, $settingsNode, $kernelStrings )
	//
	// Saves XML in user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$dom - object DOM Document
	//			$settingsNode - DOM XML object corresponding to the node COMMONSETTINGS
	//			$kernelStrings - kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		if ( $dom ) {
			$U_SETTINGS = @$dom->dump_mem();

			if ( PEAR::isError( $res = updateUsersettings( $U_ID, $U_SETTINGS, $kernelStrings ) ) )
				return $res;
		} else
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		return null;
	}

	$u_settings_cache = array();
	
	function getAppUserCommonValue( $APP_ID, $U_ID, $paramName, $defaultValue = false, $useCookies = false )
	//
	// Loads user settings for specified application
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$U_ID - user identifier
	//			$paramName - parameter name to load
	//			$defaultValue - value to return in case of the parameter is not found
	//			$useCookies - use cookies instead of database
	//
	//		Returns a parameter value
	//
	{
	    global $u_settings_cache;
	    if (isset($u_settings_cache[$U_ID][$APP_ID][$paramName])) {
	        return $u_settings_cache[$U_ID][$APP_ID][$paramName];   
	    }
	    
		if ( !$useCookies ) {
			$sql = "SELECT VALUE FROM USER_SETTINGS WHERE U_ID = '!U_ID!' AND APP_ID = '!APP_ID!' AND NAME = '!NAME!'";
			$value = db_query_result($sql, DB_FIRST, array('U_ID' => $U_ID, 'APP_ID' => $APP_ID, 'NAME' => $paramName));
			
			if ( $value === null || $value === false) {
				return $defaultValue;
			}
			$u_settings_cache[$U_ID][$APP_ID][$paramName] = $value;
			return $value;
		} else {
			if ( isset($_COOKIE['webasyst'.$APP_ID.$U_ID.$paramName]) )
				return $_COOKIE['webasyst'.$APP_ID.$U_ID.$paramName];
			else
				return null;
		}
	}

	function setAppUserCommonValue( $APP_ID, $U_ID, $paramName, $paramValue, $kernelStrings, $useCookies = false )
	//
	// Saves user settings for application
	//
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$U_ID - user identifier
	//			$paramName - parameter name to save
	//			$defaultValue - parameter value to save
	//			$kernelStrings - kernel localization strings
	//			$useCookies - use cookies instead of database
	//
	//		Returns null
	//
	{
		if ( !$useCookies ) {
			$sql = "REPLACE INTO USER_SETTINGS SET U_ID = '!U_ID!', APP_ID = '!APP_ID!', NAME = '!NAME!', VALUE = '!VALUE!'";
			$res = db_query($sql, array('U_ID' => $U_ID, "APP_ID" => $APP_ID, "NAME" => $paramName, "VALUE" => $paramValue));
			if ( PEAR::isError($res) )
				return $res;
		} else {

			$cookieName = 'webasyst'.$APP_ID.$U_ID.$paramName;
			$_COOKIE[$cookieName] = $paramValue;
			setcookie( $cookieName, $paramValue, time()+518400, "/" );
		}
		$u_settings_cache[$U_ID][$APP_ID][$paramName] = $paramValue;

		return null;
	}

	//
	// Group settings functions
	//

	function selectGroupSettings( $UG_ID )
	//
	//  Returns string containing group settings
	//
	//		Parameters:
	//			$UG_ID - user identifier
	//
	//	Returns string containing group settings
	//
	{
		global $qr_selectugroupsettings;

		return db_query_result( $qr_selectugroupsettings, DB_FIRST, array("UG_ID"=>$UG_ID ) );
	}

	function getGroupSettingsRoot( $UG_ID, &$dom )
	//
	// Returns DOM XML object of node COMMONSETTINGS from group settings
	//
	//		Parameters:
	//			$UG_ID - group identifier
	//			$dom - variable for storing Dom Document object
	//
	//		Returns object DOM_ELEMENT, or null
	//
	{
		$str_settings = selectGroupSettings( $UG_ID );
		$dom = domxml_open_mem( $str_settings );

		if ( $dom ) {
			$element = getElementByTagname( $dom, COMMONSETTINGS );

			if ( $element )
				return $element;
		}

		return null;
	}

	function writeGroupCommonSetting( $UG_ID, $paramName, $paramValue, $kernelStrings, $useCookies = false )
	//
	// Stores settings in group settings
	//
	//		Parameters:
	//			$UG_ID - group identifier
	//			$paramName - name of parameter
	//			$paramValue - value of parameter
	//			$kernelStrings - kernel localization strings
	//			$useCookies - use cookies instead of database
	//
	//	Returns null, or PEAR_Error
	//
	{
		if ( !$useCookies ) {
			$str_settings = selectGroupSettings( $UG_ID );

			$dom = null;

			if ( strlen($str_settings) )
				$dom = @domxml_open_mem( $str_settings );

			if ( !$dom )
				$dom = @domxml_new_doc("1.0");

			if ( $dom ) {
				$root = $dom->root();
				if ( !$root )
					$root = @create_addElement( $dom, $dom, COMMONSETTINGS );

				if ( !$root )
					return PEAR::raiseError( $kernelStrings[ERR_XML] );

				@$root->set_attribute( $paramName, $paramValue );

				$UG_SETTINGS = $dom->dump_mem();

				if ( PEAR::isError( $res = updateGroupSettings( $UG_ID, $UG_SETTINGS, $kernelStrings ) ) )
					return $res;
			} else
				return PEAR::raiseError( $kernelStrings[ERR_XML] );

			return null;
		} else {

			$cookieName = 'webasystGROUP'.$UG_ID.$paramName;
			$_COOKIE[$cookieName] = $paramValue;
			setcookie( $cookieName, $paramValue, time()+518400, "/" );
		}
	}

	function updateGroupSettings( $UG_ID, $UG_SETTINGS, $kernelStrings )
	//
	//	Stores group settings in database
	//
	//		Parameters:
	//			$UG_ID - user identifier
	//			$UG_SETTINGS - string containing group settings
	//
	//	Returns null, or PEAR_Error
	//
	{
		global $qr_updateGroupSettings;

		$params = array("UG_ID"=>$UG_ID, "UG_SETTINGS"=>$UG_SETTINGS);

		if ( PEAR::isError( $res = exec_sql( $qr_updateGroupSettings, $params, $outputList, false ) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function saveGroupSettingsDOM( $UG_ID, &$dom, $settingsNode, $kernelStrings )
	//
	// Saves XML in group settings
	//
	//		Parameters:
	//			$UG_ID - group identifier
	//			$dom - object DOM Document
	//			$settingsNode - DOM XML object corresponding to the node COMMONSETTINGS
	//			$kernelStrings - kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		if ( $dom ) {
			$UG_SETTINGS = @$dom->dump_mem();

			if ( PEAR::isError( $res = updateGroupSettings( $UG_ID, $UG_SETTINGS, $kernelStrings ) ) )
				return $res;
		} else
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		return null;
	}

	function readGroupCommonSetting( $UG_ID, $paramName, $useCookies = false )
	//
	//	Returns value of group setting
	//
	//		Parameters:
	//			$UG_ID - group identifier
	//			$paramName - name of parameter
	//			$useCookies - use cookies instead of database
	//
	//	Returns value of parameter, or null
	//
	{
		if ( !$useCookies ) {
			$str_settings = selectGroupSettings( $UG_ID );
			$dom = domxml_open_mem( $str_settings );

			if ( $dom ) {
				$element = getElementByTagname( $dom, COMMONSETTINGS );
				if ( !$element )
					return null;

				return	$element->get_attribute( $paramName );
			}
		} else {

			return $_COOKIE['webasyst'.$U_ID.$paramName];
		}

		return null;
	}

	function setGroupUserCommonValue( $APP_ID, $UG_ID, $paramName, $paramValue, $kernelStrings, $useCookies = false )
	//
	// Saves group settings for application
	//
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$UG_ID - group identifier
	//			$paramName - parameter name to save
	//			$defaultValue - parameter value to save
	//			$kernelStrings - kernel localization strings
	//			$useCookies - use cookies instead of database
	//
	//		Returns null
	//
	{
		if ( !$useCookies ) {
			$settingsElement = getGroupSettingsRoot( $UG_ID, $dom );
			if ( !$settingsElement )
				return null;

			$result = array();

			$appNode = getElementByTagname( $settingsElement, $APP_ID );
			if ( !$appNode )
				$appNode = @create_addElement( $dom, $settingsElement, $APP_ID );

			@$appNode->set_attribute( $paramName, $paramValue );

			$res = saveGroupSettingsDOM( $UG_ID, $dom, $settingsElement, $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;
		} else {

			$cookieName = 'webasyst'.$APP_ID.$UG_ID.$paramName;
			$_COOKIE[$cookieName] = $paramValue;
			setcookie( $cookieName, $paramValue, time()+518400, "/" );
		}

		return null;
	}

	function getAppGroupCommonValue( $APP_ID, $UG_ID, $paramName, $defaultValue, $useCookies = false )
	//
	// Loads group settings for specified application
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$UG_ID - group identifier
	//			$paramName - parameter name to load
	//			$defaultValue - value to return in case of the parameter is not found
	//			$useCookies - use cookies instead of database
	//
	//		Returns a parameter value
	//
	{
		if ( !$useCookies ) {
			$settingsElement = getGroupSettingsRoot( $UG_ID, $dom );
			if ( !$settingsElement )
				return $defaultValue;

			$result = array();

			$appNode = getElementByTagname( $settingsElement, $APP_ID );
			if ( !$appNode )
				return $defaultValue;

			return @$appNode->get_attribute( $paramName );
		} else {
			return $_COOKIE['webasyst'.$APP_ID.$UG_ID.$paramName];
		}
	}

	//
	// Mail functions
	//

	function sendWBSMail( $recipient_U_ID, $recipientAddress, $senderU_ID, $subject, $priority, $body,
							$kernelStrings, $notificationID = null, $APP_ID = null, $header = null, $useTemplate = true,
							$replyAddress = null, $senderNamePrefix = null, $allowSendToSender = false,
							$showSenderEmail = false, $force = false, $stripHTMLTags = true, $cc_address = null, $bcc_address = null,
							$files = null, $fromName = null )
	//
	//	Sends WBS mail notification
	//
	//		Parameters:
	//			$recipient_U_ID - recepient identifier. Can equal to null, in this case address $recipientAddress is used
	//			$recipientAddress - recepient address
	//			$senderU_ID - sender identifier. Can equal to null
	//			$subject - notification subject
	//			$priority - priority. Values: 0 - low, 1 - normal, 2 - high
	//			$body - message text in HTML without header and footer
	//			$kernelStrings - kernel localization strings
	//			$notificationID - notification identifier
	//			$APP_ID - application identifier
	//			$header - message header. If it is not defined, company name is placed
	//			$useTemplate - defines whether the common notification template should be used
	//			$replyAddress - reply address for the message
	//			$senderNamePrefix - text to add before user name in the footer
	//			$allowSendToSender - allow function to send letter if $recipient_U_ID == $senderU_ID
	//			$showSenderEmail - output sender email next to sender name
	//			$force - force sending mail even if user is not allowed to receive mail in user settings
	//			$stripHTMLTags - strip tags in the text message
	//			$cc_address - copy address
	//			$bcc_address - carbon copy address
	//			$files - arrah of paths to files to attach
	//			$fromName - sender name
	//
	//	Returns bool, or PEAR_Error
	//
	{
		global $qr_selectUser;
		global $wbs_mail_address;
		global $ms_priority;
		global $sendmail_enabled;
		global $html_encoding;
		global $wbs_robotemailaddress;
		global $qr_selectUserLoginInfo;
		global $loc_str;

		if ( !$sendmail_enabled )
			return null;

		if ( !$allowSendToSender )
			if ( !is_null($recipient_U_ID) && !is_null($senderU_ID) && $recipient_U_ID == $senderU_ID )
				return null;

		if ( strlen( $recipient_U_ID ) ) {
			$userData = array( "U_ID"=>$recipient_U_ID );

			if ( PEAR::isError( exec_sql( $qr_selectUser, $userData, $userData, true ) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$recipientMail = $userData["C_EMAILADDRESS"];

			$language = readUserCommonSetting( $recipient_U_ID, LANGUAGE );
			if ( PEAR::isError($language) || !strlen($language) )
				$language = LANG_ENG;

			$timezoneId = readUserCommonSetting( $recipient_U_ID, 'TIME_ZONE_ID' );
			if ( PEAR::isError($timezoneId))
				$timezoneId = null;

			$timezoneDst = readUserCommonSetting( $recipient_U_ID, 'TIME_ZONE_DST' );
			if ( PEAR::isError($timezoneDst))
				$timezoneDst = null;

			$result = db_query_result( $qr_selectUserLoginInfo, DB_ARRAY, array('U_ID'=>$recipient_U_ID) );

			if ($result["U_STATUS"] == RS_DELETED)
				return null;

			$notificationsEnabled = readUserCommonSetting( $recipient_U_ID, U_RECEIVESMESSAGES );
			if ( $notificationsEnabled != "" && $notificationsEnabled == 0 && !$force ) {
				PEAR::raiseError( "Force: $force" );
				return;
			}
		} else {
			$recipientMail = $recipientAddress;
			$language = LANG_ENG;
			$timezoneId = null;
			$timezoneDst = null;
		}

		$userStrings = $loc_str[$language];

		if ( !is_null($notificationID) )
			if ( !strlen( $recipient_U_ID ) )
				return null;
			else {
				if ( is_null( $APP_ID ) )
					return null;

				$res = userNotificationAssigned( $recipient_U_ID, $APP_ID, $notificationID, $kernelStrings );
				if ( PEAR::isError($res) )
					return $res;

				if (!$res)
					return null;
			}

		$mailFormat = "html";

		if ( !strlen( $recipientMail ) )
			return PEAR::raiseError( $kernelStrings[ERR_UNKNOWNRECIPIENTADDRESS] );

		$company = getCompanyName();
		if ( PEAR::isError($company) )
			$company = "";

		if (!strlen($company))
			$company = WBS_DEF_NAME;

		if ( is_null( $header ) )
			$header = $company;

		$senderName = "";
		if ( strlen($senderU_ID) )
			$senderName = getUserName( $senderU_ID, true )." ";

		$sendTimestamp = time();
		if ($timezoneId != null)
			$sendTimestamp = convertTimestamp2Local($sendTimestamp, $timezoneId, $timezoneDst);
		$sendTime = displayDateTime( $sendTimestamp );

		$senderEmail = null;
		if ( $showSenderEmail ) {
			$senderData = array( "U_ID"=>$senderU_ID );

			if ( PEAR::isError( exec_sql( $qr_selectUser, $senderData, $senderData, true ) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$senderEmail = $senderData['C_EMAILADDRESS'];
		}

		if ( is_null($senderNamePrefix) )
			$senderNamePrefix = $userStrings['app_performedby_text'];

		if ( strlen($senderNamePrefix) )
			$senderName = sprintf( "%s: <b>%s</b>", $senderNamePrefix, $senderName );
		else
			$senderName = sprintf( "<b>%s</b>", $senderName );

		if ( $mailFormat == MAILFORMAT_TEXT ) {
			$bodyStripped = $stripHTMLTags ? strip_tags($body, "<br>") : $body;

			$body = str_replace( "<br>", "\n", $bodyStripped );
			$body = str_replace( "&nbsp;", " ", $body );

			$headerStripped = $stripHTMLTags ? strip_tags($header, "<br>") : $header;
			if ( strlen($header) )
				$header = str_replace( "<br>", "\n", $headerStripped );

			$senderName = strip_tags( $senderName );
		} else {
			if ( $senderEmail )
				$senderEmail = sprintf( "<a href='mailto:%s'>%s</a>", $senderEmail, $senderEmail );
		}

		if ( $useTemplate ) {
			if ( $mailFormat == MAILFORMAT_HTML )
				$html = file( MAIL_ENVELOPE_PATH_HTML );
			else
				$html = file( MAIL_ENVELOPE_PATH_TEXT );

			if ( is_array($html) )
				$html = implode( "", $html );
			else
				$html = "%s";

			if ( $mailFormat == MAILFORMAT_HTML )
				$html = @sprintf( $html, $html_encoding, $header, $body, $senderName, $senderEmail, $sendTime, sprintf( $userStrings['app_notifytitle_text'], $company ) );
			else
				$html = @sprintf( $html, $header, $body, $senderName, $senderEmail, $sendTime, sprintf( $userStrings['app_notifytitle_text'], $company ) );
		} else
			$html = $body;

		if ( is_null($replyAddress) || !strlen($replyAddress) )
			$replyAddress = $wbs_robotemailaddress;

		if ( strlen($replyAddress) )
			$returnHeader = "-f$replyAddress";
		else
			$returnHeader = null;

		$composer = new mailComposer();

		if ( strlen($recipient_U_ID) )
			$encoding = getUserEncoding($recipient_U_ID);
		else
			$encoding = $html_encoding;

		$composer->codePage = $encoding;

		$composer->fromName = is_null($fromName) ? $company : $fromName;
		$composer->fromReply = $replyAddress;

		if ( !is_null($files) )
			foreach( $files as $filePath )
				$composer->attachfile($filePath);

		if ( $mailFormat == MAILFORMAT_HTML  )
			$body = $composer->compose( $html, null );
		else
			$body = $composer->compose( null, $html );

		$headers = array();
		$headers[] = sprintf( "X-MSMail-Priority: %s", $ms_priority[$priority]);
		$headers[] = $composer->contentType;

		if ( !(strpos( $recipientMail, "<" ) === false) ) {
			$recipientName = null;
			$recipientMail = extractEmailAddress( $recipientMail, $recipientName );

			if ( strpos(PHP_OS, "WIN") === false )
				$recipientMail = sprintf( "\"%s\" <%s>", $recipientName, $recipientMail );
		}

		if ( strlen($cc_address) )
			$headers[] = "Cc: {$cc_address}";
		if ( strlen($bcc_address) )
			if ( !strlen($cc_address) )
				$headers[] = "Bcc: {$bcc_address}";
			else
				$headers[] = "Bcc: {$bcc_address}";
		$headers = implode("\n",$headers);	
		$headers = str_replace("\n\n","\n",$headers);
		return @mail( $recipientMail, encodeHeader($subject), $body, $headers, $returnHeader );
	}

	// Encode a header string to B (base64) or none.
	function encodeHeader($str)
	{
		if(preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $match)) {
			return '=?utf-8?B?'.base64_encode($str).'?=';
		} else {
			return $str;
		}
	}

	function listForbiddenMailAssignments( $ID, $ID_Type, $kernelStrings )
	//
	// Returns list of mail notifications banned for user
	//
	//		Parameters:
	//			$ID - identity identifier
	//			$ID_Type - identity type - IDT_GROUP, IDT_USER
	//			$kernelStrings - kernel localization strings
	//
	//		Returns an
	//			array( APP_ID1=>array( MN_ID1, MN_ID2, MN_ID3... ) ), or PEAR_Error
	//
	{
		$result = array();

		if ( $ID_Type == IDT_USER )
			$settingsElement = getUserSettingsRoot( $ID, $dom );
		else
			$settingsElement = getGroupSettingsRoot( $ID, $dom );

		if ( !$settingsElement )
			return $result;

		$notificationsNode = getElementByTagname( $settingsElement, MN_XML_NOTIFICATIONS );
		if ( !$notificationsNode )
			return $result;

		$notifications = @$notificationsNode->get_elements_by_tagname( MN_XML_NOTIFICATION );

		if ( is_array($notifications) )
			for ( $i = 0; $i < count($notifications); $i++ ) {
				$notification = $notifications[$i];
				$MN_ID = $notification->get_attribute( MN_XML_ID );
				$APP_ID = $notification->get_attribute( MN_XML_APP_ID );
				if ( strlen( $MN_ID ) )
					$result[$APP_ID][] = $MN_ID;
			}

		return $result;
	}

	function notificationAssigned( $ID, $ID_Type, $APP_ID, $notificationID, $kernelStrings )
	//
	// Checks if user or group is allowed to receive mail notifications
	//
	//		Parameters:
	//			$ID - identity identifier
	//			$ID_Type - identity type (IDT_GROUP, IDT_USER)
	//			$APP_ID - application identifier
	//			$notificationID - notification identifier
	//			$kernelStrings - kernel localization strings
	//
	//		Returns value of boolean type, or PEAR_Error
	//
	{
		$mn_list = listForbiddenMailAssignments( $ID, $ID_Type, $kernelStrings );
		if ( PEAR::isError($mn_list) )
			return $mn_list;

		if ( !array_key_exists( $APP_ID, $mn_list ) )
			return true;

		if ( in_array( $notificationID, $mn_list[$APP_ID] ) )
			return false;

		return true;
	}

	function userNotificationAssigned( $U_ID, $APP_ID, $notificationID, $kernelStrings )
	//
	// Checks if user is allowed to receive mail notifications
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$APP_ID - application identifier
	//			$notificationID - notification identifier
	//			$kernelStrings - kernel localization strings
	//
	//		Returns value of boolean type, or PEAR_Error
	//
	{
		global $UR_Manager;

		$notificationID = strtoupper( $notificationID );
		$APP_ID = strtoupper( $APP_ID );

		if ( $UR_Manager->GetUserRightValue( $U_ID, "/ROOT/$APP_ID/".UR_MESSAGES."/$notificationID" ) == UR_BOOL_TRUE )
			return true;
	}

	function saveIdentityMailAssignments( $ID, $ID_Type, $notificationList, $kernelStrings )
	//
	// Saves list of banned mail notifications in user or group settings
	//
	//		Parameters:
	//			$ID - identity identifier
	//			$ID_Type - identity type (IDT_GROUP, IDT_USER)
	//			$notificationList - list containing identifiers of notification, array( APP_ID1=>array( MN_ID1, MN_ID2, MN_ID3... ) )
	//			$kernelStrings - kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		if ( $ID_Type == IDT_USER )
			$settingsElement = getUserSettingsRoot( $ID, $dom );
		else
			$settingsElement = getGroupSettingsRoot( $ID, $dom );

		if ( !$settingsElement )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$notificationsNode = getElementByTagname( $settingsElement, MN_XML_NOTIFICATIONS );
		if ( !$notificationsNode )
			$notificationsNode = @create_addElement( $dom, $settingsElement, MN_XML_NOTIFICATIONS );

		if ( !$notificationsNode )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$notifications = @$notificationsNode->get_elements_by_tagname( MN_XML_NOTIFICATION );

		if ( is_array($notifications) )
			for ( $i = 0; $i < count($notifications); $i++ )
				$notificationsNode->remove_child( $notifications[$i] );

		if ( is_array($notificationList) )
			foreach ( $notificationList as $APP_ID=>$mn_list )
				for ( $i = 0; $i < count( $mn_list ); $i++ ) {
					$notification = @create_addElement( $dom, $notificationsNode, MN_XML_NOTIFICATION );
					if ($notification) {
						$notification->set_attribute( MN_XML_ID, $mn_list[$i] );
						$notification->set_attribute( MN_XML_APP_ID, $APP_ID );
					}
				}

		if ( $ID_Type == IDT_USER )
			$res = saveUserSettingsDOM( $ID, $dom, $settingsElement, $kernelStrings );
		else
			$res = saveGroupSettingsDOM( $ID, $dom, $settingsElement, $kernelStrings );

		if ( PEAR::isError( $res ) )
			return $res;
	}

	function saveForbiddenMailAssignment( $U_ID, $APP_ID, $notificationID, $status, $kernelStrings )
	//
	// Saves user rights to receive notifications in user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$APP_ID - application identifier
	//			$notificationID - notification identifier
	//			$status - if it is true, user has rights to receive notification, otherwise - does not
	//			$kernelStrings - kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if ( !$settingsElement )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$notificationsNode = getElementByTagname( $settingsElement, MN_XML_NOTIFICATIONS );
		if ( !$notificationsNode )
			$notificationsNode = @create_addElement( $dom, $settingsElement, MN_XML_NOTIFICATIONS );

		if ( !$notificationsNode )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$notifications = @$notificationsNode->get_elements_by_tagname( MN_XML_NOTIFICATION );

		if ( is_array($notifications) )
			for ( $i = 0; $i < count($notifications); $i++ ) {
				$notification = $notifications[$i];

				$MN_ID = $notification->get_attribute( MN_XML_ID );
				$MN_APP_ID = $notification->get_attribute( MN_XML_APP_ID );

				if ( $MN_APP_ID == $APP_ID && $MN_ID == $notificationID )
					$notificationsNode->remove_child( $notifications[$i] );
			}

		if ( !$status ) {
			$notification = @create_addElement( $dom, $notificationsNode, MN_XML_NOTIFICATION );

			if ($notification) {
				$notification->set_attribute( MN_XML_ID, $notificationID );
				$notification->set_attribute( MN_XML_APP_ID, $APP_ID );
			}
		}

		$res = saveUserSettingsDOM( $U_ID, $dom, $settingsElement, $kernelStrings );
		if ( PEAR::isError( $res ) )
			return $res;
	}

	//
	// Currency functions
	//

	function listCurrency()
	//
	// Returns currency list
	//
	//		Returns array( CUR_ID1=>array( "CUR_NAME"=>CUR_NAME1 )... ),
	//			or PEAR_Error
	//
	{
		global $qr_select_currency_list;

		$qr = db_query($qr_select_currency_list, array());
		if (PEAR::isError( $qr ))
			return $qr;

		$result = array();
		while ( $row = db_fetch_array($qr) )
			$result[$row["CUR_ID"]] = array( "CUR_NAME"=>$row["CUR_NAME"] );

		@db_free_result($qr);

		return $result;
	}

	function currencyExists( $CUR_ID )
	//
	// Checks if currency exists
	//
	//		Parameters:
	//			$CUR_ID - currency identifier
	//
	//		Returns value of boolean type
	//
	{
		global $qr_select_currency_count;

		$res = db_query_result( $qr_select_currency_count, DB_FIRST, array("CUR_ID"=>strtoupper($CUR_ID)) );
		if ( PEAR::isError($res) )
			return $res;

		return $res > 0;
	}

	function addmodCurrency( $action, $currencyData, $kernelStrings )
	//
	// Adds or modifies currency
	//
	//		Parameters:
	//			$action - action type - addition ($action = ACTION_NEW) or modification ($action = ACTION_EDIT)
	//			$userdata - an associative array containing fields of WBS_USER record
	//			$kernelStrings - kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_insert_currency;
		global $qr_update_currency;

		$currencyData = trimArrayData( $currencyData );

		$requiredFields = array( "CUR_NAME" );
		$CUR_ID_Len = 3;
		$CUR_NAME_Len = 50;

		if ( $action == ACTION_NEW )
			$requiredFields = array_merge( $requiredFields, array( "CUR_ID" ) );

		$requiredFields = array_merge( $requiredFields, array( "CUR_ID" ) );

		if ( PEAR::isError( $invalidField = findEmptyField($currencyData, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		$invalidField = checkStringLengths($currencyData, array("CUR_ID", "CUR_NAME"), array($CUR_ID_Len, $CUR_NAME_Len));
		if ( PEAR::isError($invalidField) ) {
			$invalidField->message = $kernelStrings[ERR_TEXTLENGTH];

			return $invalidField;
		}

		if ( $action == ACTION_NEW ) {
			$cExists = currencyExists( $currencyData["CUR_ID"] );
			if ( PEAR::isError($cExists) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( $cExists )
				return PEAR::raiseError( $kernelStrings['amc_curexists_message'], ERRCODE_APPLICATION_ERR );

			$res = db_query( $qr_insert_currency, $currencyData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		} else {
			$res = db_query( $qr_update_currency, $currencyData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		return null;
	}

	function deleteCurrency( $currencyData, $kernelStrings, $language )
	//
	// Deletes currency
	//
	//		Parameters:
	//			$currencyData - currency data
	//			$kernelStrings - kernel localization strings
	//			$language - user language
	//
	//		Returns null, or PEAR_Error
	//
	{
		$params = array( "CUR_ID"=>$currencyData["CUR_ID"] );
		if ( PEAR::isError( $res = handleEvent( "AA", "onDeleteCurrency", $params, $language) ) )
			return $res;

		global $qr_delete_currency;

		$res = db_query( $qr_delete_currency, $currencyData );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	//
	// Contacts support
	//

	function getConDescriptionElementName( &$xpath, &$element, $nameNodeName, $language )
	//
	// Returns contact description group or field name
	//
	//		Parameters:
	//			$xpath - xpath object reference
	//			$element - element object reference
	//			$nameNodeName - name of the element node
	//			$language - user language
	//
	//		Returns string or null
	//
	{
		// Find name element
		//
		$nameNodeElement = xpath_eval( $xpath, $nameNodeName, $element );

		if ( !count($nameNodeElement->nodeset) )
			return null;

		$nameNodeElement = $nameNodeElement->nodeset[0];

		// Find name language element
		//
		$languageElement = xpath_eval( $xpath, $language, $nameNodeElement );

		// Return language element value, if language element exists
		//
		$langElementExists = count($languageElement->nodeset);
		if ( $langElementExists ) {
			$languageElement = $languageElement->nodeset[0];
			$fieldNameExists = strlen( $languageElement->get_attribute(CONTACT_NAMEVALUE) );
		}

		if ( $langElementExists && $fieldNameExists ) {
			return base64_decode( $languageElement->get_attribute(CONTACT_NAMEVALUE) );
		} else {
			// Find English name element
			//
			$languageElement = xpath_eval( $xpath, LANG_ENG, $nameNodeElement );

			if ( !count($languageElement->nodeset) )
				return null;

			$languageElement = $languageElement->nodeset[0];
			return base64_decode( $languageElement->get_attribute(CONTACT_NAMEVALUE) );
		}
	}

	function getContactTypeDescription( $CT_ID, $language, $kernelStrings, $supressGeneralFields = true, $addImagefieldIndicator = false )
	//
	// Returns description of the contact fields and groups
	//
	//		Parameters:
	//			$CT_ID - contact type
	//			$language - user language
	//			$kernelStrings - strings identifier
	//			$supressGeneralFields - supress contact general fields
	//			$addImagefieldIndicator - adds the "(image)" suffix to the image field names
	//
	//		Returns type description or PEAR_Error
	//
	{
		global $qr_selectcontacttype;

		if ( !strlen($language) )
			$language = LANG_ENG;

		$folderData = db_query_result( $qr_selectcontacttype, DB_ARRAY, array('CT_ID'=>$CT_ID) );
		if ( PEAR::isError($folderData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( !strlen($folderData['CT_ID']) )
			return PEAR::raiseError( $kernelStrings['app_conttypenotfound_message'], ERRCODE_APPLICATION_ERR );

		$result = array();

		$typeSettings = $folderData['CT_SETTINGS'];
		if ( !strlen($typeSettings) )
			return PEAR::raiseError( $kernelStrings['app_invconttype_message'] );

		$dom = @domxml_open_mem( $typeSettings );
		if ( !$dom )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$xpath = xpath_new_context($dom);
		$groups = xpath_eval( $xpath, "/TYPE/FIELDGROUP" );

		foreach( $groups->nodeset as $group ) {
			$groupData = getAttributeValues( $group );

			if ( $supressGeneralFields && $groupData[CONTACT_GROUPID] == CONTACT_CONTACTGROUP_ID )
				continue;

			// Check if field name elements exists
			//
			$longNameElement = xpath_eval( $xpath, CONTACT_FIELDGROUP_LONGNAME, $group );
			if ( !count($longNameElement->nodeset) ) {
				// Load name from localization strings
				//
				$groupData[CONTACT_FIELDGROUP_LONGNAME] = $kernelStrings[$groupData[CONTACT_FIELDGROUP_LONGNAME]];
				$groupData[CONTACT_FIELDGROUP_SHORTNAME] = $kernelStrings[$groupData[CONTACT_FIELDGROUP_SHORTNAME]];
			} else {
				// Load name from section description
				//
				$groupData[CONTACT_FIELDGROUP_LONGNAME] = getConDescriptionElementName( $xpath, $group, CONTACT_FIELDGROUP_LONGNAME, $language );
				$groupData[CONTACT_FIELDGROUP_SHORTNAME] = getConDescriptionElementName( $xpath, $group, CONTACT_FIELDGROUP_SHORTNAME, $language );
				if ( !strlen($groupData[CONTACT_FIELDGROUP_SHORTNAME]) )
					$groupData[CONTACT_FIELDGROUP_SHORTNAME] = $groupData[CONTACT_FIELDGROUP_LONGNAME];
			}

			$groupFields = array();

			$fields = xpath_eval( $xpath, "FIELD", $group );
			foreach( $fields->nodeset as $field ) {
				$fieldDesc = getAttributeValues( $field );

				// Check if field name elements exists
				//
				$longNameElement = xpath_eval( $xpath, CONTACT_FIELDGROUP_LONGNAME, $field );
				if ( !count($longNameElement->nodeset) ) {
					// Load name from localization strings
					//
					$fieldDesc[CONTACT_FIELDGROUP_LONGNAME] = $kernelStrings[$fieldDesc[CONTACT_FIELDGROUP_LONGNAME]];
					$fieldDesc[CONTACT_FIELDGROUP_SHORTNAME] = $kernelStrings[$fieldDesc[CONTACT_FIELDGROUP_SHORTNAME]];
				} else {
					// Load name from field description
					//
					$fieldDesc[CONTACT_FIELDGROUP_LONGNAME] = getConDescriptionElementName( $xpath, $field, CONTACT_FIELDGROUP_LONGNAME, $language );
					$fieldDesc[CONTACT_FIELDGROUP_SHORTNAME] = getConDescriptionElementName( $xpath, $field, CONTACT_FIELDGROUP_SHORTNAME, $language );
					if ( !strlen($fieldDesc[CONTACT_FIELDGROUP_SHORTNAME]) )
						$fieldDesc[CONTACT_FIELDGROUP_SHORTNAME] = $fieldDesc[CONTACT_FIELDGROUP_LONGNAME];
				}

				if ( $addImagefieldIndicator && $fieldDesc[CONTACT_FIELD_TYPE] == CONTACT_FT_IMAGE ) {
					$fieldDesc[CONTACT_FIELDGROUP_LONGNAME] .= " (".$kernelStrings['app_imagefieldindicator_label'].")";
					$fieldDesc[CONTACT_FIELDGROUP_SHORTNAME] .= " (".$kernelStrings['app_imagefieldindicator_label'].")";
				}

				if ( !isset($fieldDesc[CONTACT_REQUIRED]) )
					$fieldDesc[CONTACT_REQUIRED] = false;

				if ( !isset($fieldDesc[CONTACT_REQUIRED_GROUP]) )
					$fieldDesc[CONTACT_REQUIRED_GROUP] = null;

				$groupFields[$fieldDesc[CONTACT_FIELDID]] = $fieldDesc;
			}

			$groupData[CONTACT_FIELDS] = $groupFields;

			$result[] = $groupData;
		}

		return $result;
	}

	function validateContactData( $CT_ID, &$contactData, $language, $kernelStrings, $contactFieldsOnly = false, $typeDesc = null, $convertToDBFormat = false )
	//
	// Validates contact data
	//
	//		Parameters:
	//			$CT_ID - contact type identifier
	//			$contactData - contact data, user input
	//			$language - user language
	//			$kernelStrings - Kernel localization strings
	//			$contactFieldsOnly - validate only contact group fields
	//			$typeDesc - contact type description
	//			$convertToDBFormat - convert data to database format values
	//
	//		Returns null or PEAR_Error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;
		global $qr_selectcontactfieldvalue;
		global $qr_selectcontactfieldvalueNew;
		global $dateFormats;

		// Load type description
		//
		if ( is_null($typeDesc) ) {
			$desc = getContactTypeDescription( $CT_ID, $language, $kernelStrings, false );
			if ( PEAR::isError($desc) )
				return $desc;
		} else
			$desc = $typeDesc;

		// Check Contact group
		//
		$res = checkContactRequiredGroups( $desc[0], $contactData, $kernelStrings );
		if ( PEAR::isError($res) ){
			return $res;
		}

		if ( $contactFieldsOnly )
			return null;

		$QuotaManager = new DiskQuotaManager();
		$limit = getApplicationResourceLimits( AA_APP_ID, 'SPACE' );

		$TotalUsedSpace = $QuotaManager->GetUsedSpaceTotal( $kernelStrings );

		if ( PEAR::isError($TotalUsedSpace) ){
			return $TotalUsedSpace;
		}

		// Check field values
		//
		foreach ( $desc as $groupIndex=>$groupData ) {

			// Check images field summary size
			//
			if ( $limit !== null || DATABASE_SIZE_LIMIT > 0 ) {
				$additiveSpace = 0;

				foreach( $groupData[CONTACT_FIELDS] as $fieldData ) {
					if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_IMAGE ) {
						$dbField = $fieldData[CONTACT_DBFIELD];
						$fieldData = $contactData[$dbField];
						$additiveSpace += getImageFieldAdditiveSize( $fieldData );

						if ( $fieldData[CONTACT_IMGF_MODIFIED] )
							$modifiedImageFieldsFound = true;
					}
				}

				// Check if the system disk space quota is not exceeded
				//
				if ( $modifiedImageFieldsFound && $QuotaManager->SystemQuotaExceeded($TotalUsedSpace + $additiveSpace) )
					return $QuotaManager->ThrowNoSpaceError( $kernelStrings );
			}

			// Check mandatory and unique fields
			//
			foreach( $groupData[CONTACT_FIELDS] as $fieldData ) {
				// Check mandatory fields
				//
				if ( isset($fieldData[CONTACT_FIELDMANDATORY]) && $fieldData[CONTACT_FIELDMANDATORY] ) {
					$dbField = $fieldData[CONTACT_DBFIELD];
					$fieldIsFilled = false;

					if ( $fieldData[CONTACT_FIELD_TYPE] != CONTACT_FT_IMAGE )
						$fieldIsFilled = strlen($contactData[$dbField]);
					else {
						$imgFieldData = $contactData[$dbField];
						$fieldIsFilled = strlen($imgFieldData[CONTACT_IMGF_FILENAME]);
					}

					if ( !$fieldIsFilled )
						return PEAR::raiseError ( $kernelStrings[ERR_REQUIREDFIELDS],
													ERRCODE_INVCONTACTFIELD,
													$_PEAR_default_error_mode,
													$_PEAR_default_error_options,
													$dbField.'|'.$groupData[CONTACT_GROUPID] );
				}

				// Check unique fields
				//
				if ( isset($fieldData[CONTACT_FIELDUNIQUE]) && $fieldData[CONTACT_FIELDUNIQUE]) {
					$dbField = $fieldData[CONTACT_DBFIELD];

					if ( isset($contactData[$dbField]) ) {
						$fieldVal = trim($contactData[$dbField]);

						if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_DATE ) {
							if ( !PEAR::isError(checkDateFieldsNT( $contactData, array($dbField), $sqlDates) ) )
								$fieldVal = $sqlDates[$dbField];
						}

						if ( !strlen($fieldVal) )
							$fieldVal = null;

						$parmas = array();
						$parmas['FIELD_VALUE'] = $fieldVal;

						if ( array_key_exists( 'C_ID', $contactData ) ) {
							$parmas['C_ID'] = $contactData['C_ID'];
							$res = db_query_result( sprintf($qr_selectcontactfieldvalue, $dbField), DB_FIRST, $parmas );
						} else {
							$res = db_query_result( sprintf($qr_selectcontactfieldvalueNew, $dbField), DB_FIRST, $parmas );
						}

						if ( PEAR::isError($res) )
							return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

						if ( $res ) {
							$fieldFullName = $fieldData[CONTACT_FIELDGROUP_LONGNAME];

							return PEAR::raiseError ( sprintf($kernelStrings['app_uniquefieldviolation_message'], $fieldFullName, $fieldFullName),
														ERRCODE_INVCONTACTFIELD,
														$_PEAR_default_error_mode,
														$_PEAR_default_error_options,
														$dbField.'|'.$groupData[CONTACT_GROUPID] );
						}
					}
				}
			}
		}

		// Check required field groups
		//
		$res = checkContactRequiredGroups( $groupData, $contactData, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		// Check other groups
		//
		foreach ( $desc as $groupIndex=>$groupData ) {

			// Check group fields
			//
			foreach( $groupData[CONTACT_FIELDS] as $fieldData ) {
				// Check date fields
				//
				if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_DATE ) {
					$dbField = $fieldData[CONTACT_DBFIELD];

					if ( !isset($contactData[$dbField]) )
						continue;

					$sqlDates = array();
					if ( PEAR::isError(checkDateFieldsNT( $contactData, array($dbField), $sqlDates) ) ) {
						$QuotaManager->Flush( $kernelStrings );
						return PEAR::raiseError ( sprintf( $kernelStrings[ERR_DATEFORMAT], $dateFormats[DATE_DISPLAY_FORMAT] ),
													ERRCODE_INVALIDFIELD,
													$_PEAR_default_error_mode,
													$_PEAR_default_error_options,
													$dbField.'|'.$groupData[CONTACT_GROUPID] );
					}

					if ( $convertToDBFormat )
						$contactData[$dbField] = $sqlDates[$dbField];
				} elseif ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_IMAGE ) {
					$dbField = $fieldData[CONTACT_DBFIELD];

					if ( !isset($contactData[$dbField]) )
						continue;

					$fieldData = $contactData[$dbField];

					// Move files to the attachments directory
					//
					if ( $fieldData[CONTACT_IMGF_MODIFIED] ) {
						$res = moveUpdateImageFieldFile( $fieldData, $kernelStrings, $QuotaManager );
						if ( PEAR::isError($res) ) {
							$QuotaManager->Flush( $kernelStrings );
							return $res;
						}
					}

					// Prepare image XML document
					//
					$dom = @domxml_new_doc("1.0");
					$root = @create_addElement( $dom, $dom, CONTACT_IMGF_IMAGE );

					$root->set_attribute( CONTACT_IMGF_FILENAME, base64_encode($fieldData[CONTACT_IMGF_FILENAME]) );
					$root->set_attribute( CONTACT_IMGF_SIZE, $fieldData[CONTACT_IMGF_SIZE] );
					$root->set_attribute( CONTACT_IMGF_DISKFILENAME, $fieldData[CONTACT_IMGF_DISKFILENAME] );
					$root->set_attribute( CONTACT_IMGF_TYPE, $fieldData[CONTACT_IMGF_TYPE] );
					$root->set_attribute( CONTACT_IMGF_MIMETYPE, $fieldData[CONTACT_IMGF_MIMETYPE] );
					$root->set_attribute( CONTACT_IMGF_DATETIME, $fieldData[CONTACT_IMGF_DATETIME] );

					$contactData[$dbField] = $dom->dump_mem();
				}

				// Check numeric fields
				//
				if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_NUMERIC ) {
					$dbField = $fieldData[CONTACT_DBFIELD];

					if ( !isset($contactData[$dbField]) )
						continue;

					if ( !isFloatStr( $contactData[$dbField] ) ) {
						$QuotaManager->Flush( $kernelStrings );
						return PEAR::raiseError ( sprintf($kernelStrings[ERR_INVALIDNUMFORMAT], $contactData[$dbField]),
													ERRCODE_INVALIDFIELD,
													$_PEAR_default_error_mode,
													$_PEAR_default_error_options,
													$dbField.'|'.$groupData[CONTACT_GROUPID] );
					}
				}
			}
		}

		$QuotaManager->Flush( $kernelStrings );

		return null;
	}

	function getUpgradeLink( &$kernelStrings, $urlOnly = false )
	//
	// Returns the Upgrade Account link
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$urlOnly - return only URL
	//
	//		Return string
	//
	{
		global $databaseInfo;
		global $currentUser;
		global $language;
		global $DB_KEY;

		$temporary = isset($databaseInfo[HOST_DBSETTINGS][HOST_TEMPORARY]) && $databaseInfo[HOST_DBSETTINGS][HOST_TEMPORARY];

		$message = "<a target=\"_top\" class=\"activelink Inline\" href=\"%s\">%s</a>";
		//$message .=" <a target=\"_blank\" class=\"activelink Inline\" href=\"%s\">[%s]</a>";

		if ( $temporary )
		{
			$registerUrl = sprintf( URL_REGISTER, base64_encode($DB_KEY), base64_encode($currentUser), base64_encode($language) );
			return $message = sprintf( $message, $registerUrl, $kernelStrings['app_registeracc_label'], URL_REGISTER_HELP, $kernelStrings['app_helplink_text'] );
		}
		else
		{
			$upgradeUrl = getDBUpgradeLink($DB_KEY);

			if ( $urlOnly )
				return $upgradeUrl;

			return $message = sprintf( $message, $upgradeUrl, $kernelStrings['app_upgradeacc_label'], URL_UPGRADE_HELP, $kernelStrings['app_helplink_text'] );
		}
	}

	function getDBUpgradeLink($DB_KEY){
		return PAGE_UPGRADEACCOUNT;
	}

	function getCCLinkWithAuth($DB_KEY, $url){

		return URL_MYWEBASYST.'?ukey=walogin&DBKEY='.base64_encode($DB_KEY).'&AUTH='.md5($DB_KEY.gmdate('YmdH').floor(gmdate('i')/10)).'&redirect='.base64_encode($url);
	}

	function getExtendLink()
	//
	// Returns the Extend Account link
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$urlOnly - return only URL
	//
	//		Return string
	//
	{
		global $databaseInfo;
		global $currentUser;
		global $DB_KEY;
		global $language;

		//return URL_MYWEBASYST.'?ukey=walogin&DBKEY='.base64_encode($DB_KEY).'&AUTH='.md5($DB_KEY.gmdate('YmdH').floor(gmdate('i')/10)).'&redirect='.base64_encode(sprintf(URL_EXTEND, base64_encode($DB_KEY)));
		return './'.PAGE_CHANGE_PLAN;
	}

	function userAdddingPermitted( &$kernelStrings, $curUserCount, $mode = ACTION_NEW )
	//
	// Checks whether adding user is permitted
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$curUserCount - current user count
	//			$mode - record operation mode
	//
	//		Returns null or PEAR_Error
	//
	{
		global $currentUser;

		$limit = getApplicationResourceLimits( 'AA', 'USERS' );

		if ( $limit === null )
			return null;

		if ( ($mode == ACTION_NEW && $curUserCount >= $limit ) || ($mode == ACTION_EDIT && $curUserCount > $limit) )
		{
			if ( hasAccountInfoAccess($currentUser) )
				$Message = sprintf( $kernelStrings['app_userslimit_message'], $limit )." ".getUpgradeLink( $kernelStrings );
			else
				$Message = sprintf( $kernelStrings['app_userslimit_message'], $limit )." ".$kernelStrings['app_referadmin_message'];

			return PEAR::raiseError( $Message, ERRCODE_APPLICATION_ERR );
		}
	}

	function contactAddingPermitted( &$kernelStrings, $mode = ACTION_NEW )
	//
	// Checks whether adding contact is permitted
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$mode - record operation mode
	//
	//		Returns null or PEAR_Error
	//
	{
		global $currentUser;

		$limit = getApplicationResourceLimits( 'CM' );
		if ( $limit === null )
			return null;

		$sql = "SELECT COUNT(*) FROM CONTACT C LEFT JOIN WBS_USER U ON U.C_ID=C.C_ID WHERE U.C_ID IS NULL";

		$res = db_query_result( $sql, DB_FIRST, array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( ($mode == ACTION_NEW && $res >= $limit) || ($mode == ACTION_EDIT && $res > $limit) )
		{
			if ( hasAccountInfoAccess($currentUser) ){
				$message = sprintf( $kernelStrings['app_contactslimit_message'], $limit )." ".getUpgradeLink( $kernelStrings );
			}
			else{
				$message = sprintf( $kernelStrings['app_contactslimit_message'], $limit )." ".$kernelStrings['app_referadmin_message'];
			}

			return PEAR::raiseError( $message, ERRCODE_APPLICATION_ERR );
		}
	}

	function addmodContact( $contactData, $CF_ID, $action, $kernelStrings, $contactFieldsOnly = false, $validateData = false, $language = LANG_ENG, $typeDesc = null )
	//
	// Adds/modifies contact data
	//
	//		Parameters:
	//			$contactData - contact data
	//			$CF_ID - contact folder
	//			$contactData - contact data, user input
	//			$kernelStrings - Kernel localization strings
	//			$contactFieldsOnly - validate only contact group fields
	//			$typeDesc - optional type description data
	//
	//		Returns contact identifier or PEAR_Error
	//
	{
		global $qr_selectmaxc_id;
		global $qr_insertcontact;
		global $qr_selectcontactfolder;
		global $qr_updatecontact;
		global $userContactMode;

		// Load list of existing contact fields
		//
		$existingFields = array_keys( $contactData );

		// Load type description and prepare contact query
		//
		if ( is_null($typeDesc) ) {
			$typeDesc = getContactTypeDescription( CONTACT_BASIC_TYPE, null, $kernelStrings, false );
			if ( PEAR::isError($typeDesc) )
				return $typeDesc;
		}

		if ( $validateData ) {
			// Validate contact data
			//
			$res = validateContactData( CONTACT_BASIC_TYPE, $contactData, $language, $kernelStrings, false, $typeDesc, true );
			if ( PEAR::isError($res) )
				return $res;
		}

		$sqlFields = array( 'C_ID', 'CF_ID', 'C_MODIFYDATETIME', 'C_MODIFYUSERNAME' );

		if ( $action == ACTION_NEW ) {
			$sqlFields = array_merge( array('C_CREATEDATETIME', 'C_CREATEUSERNAME'), $sqlFields );
		}

		foreach ( $typeDesc as $group ) {
			$fields = $group[CONTACT_FIELDS];

			foreach ( $fields as $fieldData ) {
				// Real fields
				//
				if ( isset($fieldData[CONTACT_DBFIELD]) ) {
					$fieldDBName = $fieldData[CONTACT_DBFIELD];
					if ( in_array($fieldDBName, $existingFields) )
						$sqlFields[] = $fieldDBName;
				}
			}
		}

		if (isset($contactData["C_SUBSCRIBER"]))
			$sqlFields[] = "C_SUBSCRIBER";

		// Insert and values clauses
		//
		$insertClause = implode( ",", $sqlFields );
		$valuesClause = sprintf( "'!%s!'", implode( "!','!", $sqlFields ) );

		// Update clause
		//
		$updateFields = array();
		foreach( $sqlFields as $sqlField )
			if ( !in_array($sqlField, array('C_ID', 'CF_ID') ) )
				$updateFields[] = sprintf( "%s='!%s!'", $sqlField, $sqlField );

		$updateClause = implode( ",", $updateFields );

		foreach ( $contactData as $key=>$value )
			if ( !is_array($value) && !is_object($value) && !strlen($value) )
				$contactData[$key] = null;

		if ( !$userContactMode ) {
			$res = contactAddingPermitted( $kernelStrings, $action );
			if ( PEAR::isError($res) ){
				$res->userinfo = "LIMIT";
				return $res;
			}
		}

		// Update database record
		//
		if ( $action == ACTION_NEW ) {
			// Generate new C_ID
			//
			$C_ID = db_query_result( $qr_selectmaxc_id, DB_FIRST, array() );
			if ( PEAR::isError($C_ID) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$C_ID = incID($C_ID);
			$contactData['C_ID'] = $C_ID;
			$contactData['CF_ID'] = $CF_ID;

			// Prepare SQL string
			//

			$sql = sprintf( $qr_insertcontact, $insertClause, $valuesClause );

			// Execute query
			//

			$res = db_query( $sql, $contactData );

		} else {
			// Prepare SQL string
			//

			$sql = sprintf( $qr_updatecontact, $updateClause );

			// Execute query
			//

			$res = db_query( $sql, $contactData );

		}

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $contactData['C_ID'];
	}

	//
	// User groups support
	//

	function listUserGroups( $kernelStrings, $includeSystemGroups = true )
	//
	// Returns list of user groups
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$includeSystemGroups - include Active Users, Inactive Users, Deleted Users to the result set
	//
	//		Returns array
	//
	{
		global $qr_select_ugroups;

		$qr = db_query( $qr_select_ugroups, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		if ( $includeSystemGroups ) {
			$result[UGR_ACTIVE] = array( UG_ID=>UGR_ACTIVE, UG_NAME=>$kernelStrings['app_activeusergr_title'] );
			$result[UGR_INACTIVE] = array( UG_ID=>UGR_INACTIVE, UG_NAME=>$kernelStrings['app_inactiveusergr_title'] );
		}

		while ( $row = db_fetch_array($qr) ) {
			$result[$row['UG_ID']] = $row;
		}

		db_free_result( $qr );

		return $result;
	}

	function getGroupUserCount( $UG_ID, $kernelStrings )
	//
	// Return count of users in user group
	//
	//		Parameters:
	//			$UG_ID - user group identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns integer or PEAR_Error
	//
	{
		global $qr_selectugcount;
		global $qr_selectuserstatuscount;
		global $user_group_status_link;

		if ( isSystemGroup($UG_ID) ) {
			$status = $user_group_status_link[$UG_ID];

			$params = array( 'U_STATUS'=>$status );
			$result = db_query_result( $qr_selectuserstatuscount, DB_FIRST, $params );
		} else {
			$params = array( 'UG_ID'=>$UG_ID, 'U_STATUS'=>RS_DELETED );
			$result = db_query_result( $qr_selectugcount, DB_FIRST, $params );
		}

		if ( PEAR::isError($result) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $result;
	}

	function findGroupsContaningUser( $U_ID, $kernelStrings, $physicalList = false )
	//
	// Returns list of groups containing specified user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$physicalList - if true, return deleted users as well
	//
	//		Returns array of group identifiers or PEAR_Error
	//
	{
		global $qr_selectusergroups;

		$result = array();

		$params = array();
		$params['U_ID'] = $U_ID;
		$params['U_STATUS'] = RS_DELETED;

		if ( $physicalList )
			$params['U_STATUS'] = -10;

		$qr = db_query( $qr_selectusergroups, $params );
		if ( PEAR::isError( $qr ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array($qr) )
			$result[] = $row['UG_ID'];

		db_free_result( $qr );

		return $result;
	}

	function registerUserInGroup( $U_ID, $UG_ID, $kernelStrings, $unregister = false )
	//
	// Registers user in user group. Resets user personal access settings.
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$UG_ID - user group identifier
	//			$kernelStrings - Kernel localization strings
	//			$unregister - unregister user
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_selectugroup;

		$U_ID = strtoupper( trim($U_ID) );

		$sorting = 'C_FIRSTNAME  asc, C_LASTNAME  asc, C_MIDDLENAME asc, C_EMAILADDRESS asc';

		// Select group content
		//
		$groupContent = loadUserGroupContent( $UG_ID, 'U_ID asc', $kernelStrings, true );
		if ( PEAR::isError( $groupContent ) )
			return $groupContent;

		$groupContent = array_keys( $groupContent );

		// Check if user is not registered yet
		//
		if ( !$unregister && in_array( $U_ID, $groupContent ) )
			return null;

		if ( $unregister && !in_array( $U_ID, $groupContent ) )
			return null;

		if ( !$unregister )
			$groupContent[] = $U_ID;
		else {
			$newContent = array();
			foreach( $groupContent as $user )
				if ( $user != $U_ID )
					$newContent[] = $user;

			$groupContent = $newContent;
		}

		// Update group content
		//
		$res = updateUserGroupContent( $UG_ID, $groupContent, $kernelStrings );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function loadUserGroupContent( $UG_ID, $sorting, $kernelStrings, $physicalList = false )
	//
	// Returns content of user group
	//
	//		Parameters:
	//			$UG_ID - user group identifier
	//			$sorting - sorting string
	//			$kernelStrings - Kernel localization strings
	//			$physicalList - if true, return deleted users as well
	//
	//		Returns array representing group content or PEAR_Error
	//
	{
		global $qr_selectugcontent;
		global $qr_selectugenumcontent;
		global $user_group_status_link;
		global $qr_selectugroup;

		$result = array();

		$qr = null;

		if ( isSystemGroup($UG_ID) ) {
			$status = $user_group_status_link[$UG_ID];

			$qr = db_query( sprintf( $qr_selectugcontent, $sorting ), array('U_STATUS'=>$status) );
		} else {
			$sql = sprintf( $qr_selectugenumcontent, $sorting );

			$params = array( 'U_STATUS'=>RS_DELETED );
			$params['UG_ID'] = $UG_ID;
			if ( $physicalList )
				$params['U_STATUS'] = -10;

			$qr = db_query( $sql, $params );
		}

		if ( !is_null($qr) ) {
			if ( PEAR::isError( $qr ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			while ( $row = db_fetch_array( $qr ) )
				$result[$row['U_ID']] = $row;

			db_free_result( $qr );
		}

		return $result;
	}

	function loadUserListViewSettings( $U_ID, &$visibleColumns, &$viewMode, &$sorting, $kernelStrings, $useCookies = false, $suppressIDField = false, $actualColumns = false )
	//
	// Returns user list view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$visibleColumns - array of visible columns
	//			$viewMode - view mode (UL_GRID_VIEW, UL_LIST_VIEW)
	//			$sorting - sorting column
	//			$kernelStrings - Kernel localization strings
	//			$useCookies - use cookies instead of database
	//			$suppressIDField - do not return ID field in the visible columns list
	//			$actualColumns - return actual visible columns list stored in DB, regardless of the current view mode
	//
	//		Returns null
	//
	{
		global $AA_APP_ID;
		global $ul_defaultColumnSet;
		global $ul_listColumnSet;

		$visibleColumns = getAppUserCommonValue( $AA_APP_ID, $U_ID, 'AA_UL_VISIBLECOLUMNS', null, $useCookies );

		if ( $visibleColumns === 0 || !strlen($visibleColumns) && $visibleColumns != UL_NOCOLUMNS )
			$visibleColumns = $ul_defaultColumnSet;
		else
			if ( $visibleColumns != UL_NOCOLUMNS )
				$visibleColumns = explode( ",", $visibleColumns );
			else
				$visibleColumns = array();

		$viewMode = getAppUserCommonValue( $AA_APP_ID, $U_ID, 'AA_UL_VIEWMODE', null, $useCookies );
		if ( !strlen($viewMode) )
			$viewMode = UL_GRID_VIEW;

		if ( $viewMode == UL_LIST_VIEW && !$actualColumns ) {
			if ( !$suppressIDField )
				$visibleColumns = $ul_listColumnSet;
			else
				$visibleColumns = array_slice($ul_listColumnSet, 1);
		}

		$sorting = getAppUserCommonValue( $AA_APP_ID, $U_ID, 'AA_UL_SORTING', null, $useCookies );
		if ( !strlen($sorting) )
			$sorting = sprintf( "%s asc", CONTACT_IDFIELD );
		else {
			$sortData = parseSortStr($sorting);

			if ( !in_array($sortData['field'], $visibleColumns) )
				$sorting = sprintf( "%s asc", CONTACT_IDFIELD );
		}

		return null;
	}

	function listSystemUsers( $statusList, $kernelStrings, $sortByName = true )
	//
	// Returns list of system users
	//
	//		Parameters:
	//			$statusList - array of allowed statuses
	//			$kernelStrings - Kernel localization strings
	//			$sortByName - sort users by full name, otherwise by U_ID
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_selectusers;
		global $qr_namesortclause;

		if ($statusList) {
			$statConstraint = sprintf( "'%s'", implode( "','", $statusList ) );
		} else {
			$statConstraint = "''";
		}

		if ( $sortByName )
			$sortStr = $qr_namesortclause;
		else
			$sortStr = 'U_ID';

		$qr = db_query( sprintf( $qr_selectusers, $statConstraint, $sortStr ) );
		if (PEAR::isError($qr))
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();
		while ( $row = db_fetch_array($qr) )
			$result[$row['U_ID']] = $row;

		db_free_result($qr);

		return $result;
	}

	function addmodUserGroup( $action, $groupData, $content, $kernelStrings )
	//
	// Adds/modifies user group
	//
	//		Parameters:
	//			$action - form action
	//			$groupData - array containing group data
	//			$included_users - users to include into the group
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns new group identifier or PEAR_Error
	//
	{
		global $qr_selectMaxUGID;
		global $qr_insertusergroup;
		global $qr_updateugroup;

		$requiredFields = array( "UG_NAME" );

		// Check group required fields
		//
		if ( PEAR::isError( $invalidField = findEmptyField($groupData, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];
			return $invalidField;
		}

		// Create/modify database records
		//
		if ( $action == ACTION_NEW ) {
			// Generate new group identifier
			//
			$UG_ID = db_query_result( $qr_selectMaxUGID, DB_FIRST, array() );
			if ( PEAR::isError($UG_ID) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$UG_ID = incID($UG_ID);

			// Insert record to the database
			//
			$groupData['UG_ID'] = $UG_ID;
			$groupData['UG_SETTINGS'] =  sprintf( XML_DEF_GROUPSETTINGS, "?", "?" );

			$res = db_query( $qr_insertusergroup, $groupData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		} else {
			// Update database record
			//
			$UG_ID = $groupData['UG_ID'];

			$res = db_query( $qr_updateugroup, $groupData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		// Populate group
		//
		$res = updateUserGroupContent( $UG_ID, $content, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		// Save system settings
		//
		$res = writeIdentityCommonSysSettings( $groupData, IDT_GROUP, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		return $UG_ID;
	}

	function updateUserGroupContent( $UG_ID, $groupUsers, $kernelStrings )
	//
	//	Updates user group content
	//
	//		Parameters:
	//			$UG_ID - user identifier
	//			$groupUsers - group users list
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_deletegroupusers;
		global $qr_insertgroupusers;

		$params = array();
		$params['UG_ID'] = $UG_ID;

		// Clear group
		//
		$res = db_query( $qr_deletegroupusers, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Insert group user records
		//
		foreach( $groupUsers as $U_ID ) {
			$params['UG_ID'] = $UG_ID;
			$params['U_ID'] = $U_ID;

			$res = db_query( $qr_insertgroupusers, $params );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		return null;
	}

	function deleteUserGroup( $UG_ID, $kernelStrings )
	//
	// Deleting user group
	//
	//		Parameters:
	//			$UG_ID - group to delete
	//			$kernelStrings - Kernel localization strings
	//			$language - user language
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_deleteugroup;
		global $qr_deletegroupusers;
		global $qr_deleteGroupScreenAccess;
		global $global_applications;

		$params = array('UG_ID'=>$UG_ID);

		// Delete group content
		//
		$res = db_query( $qr_deletegroupusers, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Delete group
		//
		$res = db_query( $qr_deleteugroup, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Delete group screen access
		//
		db_query( $qr_deleteGroupScreenAccess, $params );

		// Delete the group folder access rights
		//
		foreach ( $global_applications as $APP_ID=>$appData ) {
			if ( isset($appData[APP_REG_USERRIGHTS]) && isset($appData[APP_REG_USERRIGHTS][APP_REG_TREEDOCUMENT]) ) {
				$groupAccessData = $appData[APP_REG_USERRIGHTS][APP_REG_TREEDOCUMENT][APP_REG_GROUPACCESSTABLE];

				$groupAccessTable = $groupAccessData[APP_REG_TABLENAME];
				$fieldName = $groupAccessData[APP_REG_GROUPID];
			}
		}

		return null;
	}

	function userGroupExists( $UG_ID )
	//
	// Checks if user group with identifier UG_ID exists in database
	//
	//		Parameters:
	//			$UG_ID - group identifier
	//
	//		Returns 0 or 1, or PEAR_Error
	//
	{
		global $qr_selectUserGroupCount;

		return db_query_result( $qr_selectUserGroupCount, DB_FIRST, array("UG_ID"=>$UG_ID) );
	}

	//
	// CSV-files support
	//

	function saveFileFormat( $listName, $scheme, $formatName, $kernelStrings, $U_ID )
	//
	// Saves file format into the database
	//
	//		Parameters:
	//			$listName - list name
	//			$scheme - file import sheme
	//			$formatName - format name
	//			$kernelStrings - Kernel localization strings
	//			$U_ID - identifier of user owning the file format
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_selectmaxfif_id;
		global $qr_insertFileFormat;

		// Calculate next format ID
		//
		$params = array( 'FIF_LIST'=>$listName );

		$FIF_ID = db_query_result( $qr_selectmaxfif_id, DB_FIRST, $params );
		if ( PEAR::isError($FIF_ID) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$FIF_ID = incID($FIF_ID);

		// Parse scheme and prepare field links string
		//
		$fieldPairs = array();
		foreach( $scheme[CSV_LINKS] as $dbFleid=>$schemeItem )
			$fieldPairs[] = sprintf( "%s=>%s", $dbFleid, $schemeItem[CSV_FILEFIELD] );

		$fieldsStr = implode( "&&", $fieldPairs );

		// Prepare settings string
		//
		$settingsStr = sprintf( "%s||%s||%s", $scheme[CSV_DELIMITER], $scheme[CSV_IMPORTFIRSLN], $fieldsStr );

		// Save format
		//
		$params = array();
		$params['FIF_ID'] = $FIF_ID;
		$params['FIF_LIST'] = $listName;
		$params['FIF_NAME'] = $formatName;
		$params['FIF_SETTINGS'] = $settingsStr;
		$params['FIF_OWNER_U_ID'] = $U_ID;

		$res = db_query( $qr_insertFileFormat, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function listFileFormats( $listName, $kernelStrings, $U_ID = null, $listAllFormats = true )
	//
	// Returns list of file formats
	//
	//		Parameters:
	//			$listName - list name
	//			$kernelStrings - Kernel localization strings
	//			$U_ID - user identifier. If this parameter is null only the system formats will be returned.
	//			$listAllFormats - if true the function will return both system and user file formats
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_selectFileFormats;
		global $qr_selectSystemFileFormats;
		global $qr_selectUserFileFormats;

		$result = array();

		$params = array();
		$params['FIF_LIST'] = $listName;
		$params['U_ID'] = $U_ID;

		if ( !is_null($U_ID) ) {
			if ( $listAllFormats )
				$qr = db_query( $qr_selectFileFormats, $params );
			else
				$qr = db_query( $qr_selectUserFileFormats, $params );
		} else
			$qr = db_query( $qr_selectSystemFileFormats, $params );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array($qr) )
			$result[$row['FIF_ID']] = $row['FIF_NAME'];

		db_free_result( $qr );

		return $result;
	}

	function loadFileFormat( $listName, $formatID, $kernelStrings )
	//
	// Loads file import format from database and returns file import scheme
	//
	//	Parameters:
	//		$listName - list name
	//		$formatID - format identifier
	//		$kernelStrings - Kernel localization strings
	//
	//	Returns array or PEAR_Error
	//
	{
		global $qr_selectFileFormat;

		$params = array();
		$params['FIF_LIST'] = $listName;
		$params['FIF_ID'] = $formatID;

		$formatData = db_query_result( $qr_selectFileFormat, DB_ARRAY, $params );
		if ( PEAR::isError($formatData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Extract scheme parts from format data
		//
		$schemeParts = explode( "||", $formatData['FIF_SETTINGS'] );
		$delimiter = $schemeParts[0];
		$importfirstline = $schemeParts[1];
		$fields = $schemeParts[2];

		$fields = explode( "&&", $fields );

		// Prepare scheme array
		//
		$scheme = array();

		// Bugfix by timur-kar (fixed wrong delimiter in database format)
		if (strpos($formatData["FIF_NAME"], "Outlook") !== false)
			$scheme[CSV_DELIMITER] = ";";
		else
			$scheme[CSV_DELIMITER] = $delimiter;
		$scheme[CSV_IMPORTFIRSLN] = $importfirstline;

		$links = array();
		foreach( $fields as $fieldData ) {
			$dbLinkData = explode( "=>", $fieldData );
			$linkData = array();
			$linkData[CSV_FILEFIELD] = $dbLinkData[1];
			$linkData[CSV_DBFIELD] = $dbLinkData[0];
			$links[$dbLinkData[0]] = $linkData;
		}

		$scheme[CSV_LINKS] = $links;

		return $scheme;
	}

	function deleteFileFormat( $FIF_ID, $FIF_LIST, $kernelStrings )
	//
	// Deletes file format from the database
	//
	//		Parameters:
	//			$FIF_ID - file format identifier
	//			$FIF_LIST - list indetifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_deleteFileFormat;

		$params = array();
		$params['FIF_ID'] = $FIF_ID;
		$params['FIF_LIST'] = $FIF_LIST;

		$res = db_query( $qr_deleteFileFormat, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function renameFileFormat( $FIF_ID, $FIF_LIST, $FIF_NAME, $kernelStrings )
	//
	// Renames import file format
	//
	//		Parameters:
	//			$FIF_ID - file format identifier
	//			$FIF_LIST - list indetifier
	//			$FIF_NAME - file format name
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_updateFileFormatName;

		$params = array();
		$params['FIF_ID'] = $FIF_ID;
		$params['FIF_LIST'] = $FIF_LIST;
		$params['FIF_NAME'] = $FIF_NAME;

		$res = db_query( $qr_updateFileFormatName, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	//
	// Application settings functions
	//

	function getApplicationSettingsRoot( $APP_ID, $kernelStrings, &$dom )
	//
	// Returns XML node object representing application settings root
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$kernelStrings - Kernel localization strings
	//			$dom - variable for storing Dom Document object
	//
	//		Returns object DOM_ELEMENT, or null
	//
	{
		global $qr_selectAppSettings;

		$settings = db_query_result( $qr_selectAppSettings, DB_FIRST, array('APP_ID'=>$APP_ID) );

		if ( PEAR::isError( $settings ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$dom = null;

		if ( $settings != "" )
			$dom = @domxml_open_mem( $settings );

		if ( !$dom )
			$dom = @domxml_new_doc("1.0");

		$root = $dom->root();
		if ( !$root )
			$root = @create_addElement( $dom, $dom, APP_SETTINGS );

		return $root;
	}

	function saveApplicationSettings( &$dom, $APP_ID, $kernelStrings )
	//
	// Saves application settings to database
	//
	//		Parameters:
	//			$dom - object DOM Document
	//			$APP_ID - application identifier
	//			$kernelStrings - kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_insertAppSettings;
		global $qr_deleteAppSettings;

		// Delete application settigns
		//
		$res = db_query( $qr_deleteAppSettings, array('APP_ID'=>$APP_ID) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Insert new application settings value
		//
		$settings = @$dom->dump_mem();

		$res = db_query( $qr_insertAppSettings, array('APP_ID'=>$APP_ID, 'SETTINGS'=>$settings) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function writeApplicationSettingValue( $APP_ID, $paramName, $paramValue, $kernelStrings )
	//
	// Writes application setting to the application settings table
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$paramName - parameter name
	//			$paramValue - parameter value
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		// Obtain application settings root
		//
		$dom = null;
		$root = getApplicationSettingsRoot( $APP_ID, $kernelStrings, $dom );
		if ( PEAR::isError($root) )
			return $root;

		// Set attribute value
		//
		$root->set_attribute( $paramName, $paramValue );

		// Write application settings to database
		//
		return saveApplicationSettings( $dom, $APP_ID, $kernelStrings );
	}

	function readApplicationSettingValue( $APP_ID, $paramName, $defaultValue, $kernelStrings )
	//
	// Reads application setting from application settings table
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$paramName - parameter name
	//			$defaultValue - default parameter value
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		// Obtain application settings root
		//
		$dom = null;
		$root = getApplicationSettingsRoot( $APP_ID, $kernelStrings, $dom );
		if ( PEAR::isError($root) )
			return $root;

		// Set attribute value
		//
		$value = $root->get_attribute( $paramName );

		if ( !strlen($value) )
			return $defaultValue;

		return $value;
	}

	//
	// Contacts sugn up functions
	//

	function getSubscribeConfirmationLink( $DB_KEY, $C_ID, $URL=null )
	//
	//	Returns address of the Contact Manager subscribtion confirmation form
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$C_ID - contact identifier
	//
	//		Returns string
	//
	{
		global $qr_select_contact_address;

		if ( is_null( $URL ) )
			$URL = dirname( getCurrentAddress() );

		$pathData = explodePath( $URL );

		if ( !strlen($pathData[count($pathData)-1]) )
			array_pop($pathData);

		for ($i = 1; $i <= 3; $i++)
			array_pop($pathData);

		$path = implode("/", $pathData)."/CM/html/scripts";

		$contactAddr = md5(db_query_result( $qr_select_contact_address, DB_FIRST, array('C_ID'=>$C_ID)));

		return prepareUrlStr( $path.'/'.PAGE_CM_CONFIRMSIGNUPFORM, array('DB_KEY'=>base64_encode($DB_KEY), 'C_ID'=>base64_encode($C_ID), 'E'=>$contactAddr) );
	}




	function getWidgetTypes ($appId, $case = null) {
		$path = sprintf("%s/%s/widgets/", WBS_PUBLISHED_DIR, WIDGETS_APP_ID );
		require_once( WBS_PUBLISHED_DIR . "/" . WIDGETS_APP_ID . "/wg.php" );

		global $language;
		global $loc_str;
		global $wg_loc_str;
		$kernelStrings = &$loc_str[$language];
		$wgStrings = &$wg_loc_str[$language];

		$typeManager = new WidgetTypeManager ($kernelStrings, $wgStrings);
		$objList = $typeManager->getObjsList();
		$result = array ();
		foreach ($objList as $cObj) {
			if (is_array($cObj->applications) && in_array($appId,$cObj->applications))
				$result[] = $cObj;
		}
		return $result;
	}

	function getAppWidgetsSubtypes($appId, $folderRight = null) {
		$wgTypesObj = getWidgetTypes($appId);
		$widgetSubtypes = array ();
		foreach ($wgTypesObj as $cTypeObj) {
			foreach ($cTypeObj->subtypes as $cSubtype) {
				if ($folderRight && $cSubtype->minFolderRights && $cSubtype->minFolderRights > $folderRight)
					continue;
				if ($cSubtype->embType == "link")
					continue;
				$widgetsSubtypes[] = $cSubtype;
			}
		}
		return $widgetsSubtypes;
	}

	function getWidgetManager () {
		$path = sprintf("%s/%s/widgets/", WBS_PUBLISHED_DIR, WIDGETS_APP_ID );
		require_once( WBS_PUBLISHED_DIR . "/" . WIDGETS_APP_ID . "/wg.php" );

		global $language;
		global $loc_str;
		global $wg_loc_str;
		$kernelStrings = &$loc_str[$language];
		$wgStrings = &$wg_loc_str[$language];

		$widgetManager = new WidgetManager ($kernelStrings, $wgStrings);
		return $widgetManager;
	}



	function getFullEmailsFromContactsString ($contactsStr) {
		$mailsList = str_replace("\n", ";", $contactsStr);
		$mailsList = split(";", $mailsList);
		$toMails = array ();
		foreach ($mailsList as $cMail) {
			$cMail = trim($cMail);
			if (strlen($cMail) < 7)
				continue;
			$toMails[] = $cMail;
		}

		$lists = array ();
		foreach ($toMails as $cKey => $cMail) {
			if (preg_match("/\[ID:(.*)\]/", $cMail, $matches)) {
				unset($toMails[$cKey]);
				$listId = $matches[1];
				$lists[] = $listId;
			}
		}
		if ($lists) {
			$listsMails = getContactsFromContactLists($lists, $kernelStrings);
			$toMails = array_merge($toMails, $listsMails);
			if ( PEAR::isError( $listsMails) )
				return $listsMails;
		}
		return $toMails;
	}



	function sendEmailToContacts ($fromUser, $contactsStr, $subject, $body ) {
		global $wbs_languages;
		global $language;
		global $wbs_robotemailaddress;
		global $kernelStrings;

		$toMails = getFullEmailsFromContactsString($contactsStr);

		$statusList = null;
		$systemUsers = listSystemUsers( $statusList, $kernelStrings );
		$userData = $systemUsers[$fromUser];

		$emailName = getUserName( $fromUser, false );
		if (!$emailName)
			$emailName = "Webasyst";

		$emailAddress = $userData["C_EMAILADDRESS"];
		if (!$emailAddress)
			$emailAddress = $wbs_robotemailaddress;

		if (!sizeof($toMails))
			return PEAR::raiseError($kernelStrings["sm_recipientnull_message"]);

		if (sizeof($toMails) > EMAIL_MAX_RECEPIENTS_COUNT)
			return PEAR::raiseError (sprintf($kernelStrings["sm_recipientlimit_message"], sizeof($toMails), EMAIL_MAX_RECEPIENTS_COUNT));

		foreach ($toMails as $cMail) {
			$mailer = new WBSMailer();

			$mailer->From = $emailAddress;
			$mailer->FromName = $emailName;
			$mailer->AddReplyTo( $emailAddress, $emailName );
			$mailer->Sender = $emailAddress;

			/*$mailer = new PHPMailer;*/
			$mailer->isHTML(true);
			$mailer->CharSet = "UTF-8"; //$wbs_languages[strtolower($language)][WBS_ENCODING];
			$mailer->Body = $body;
			$mailer->AltBody = strip_tags($body);
			$mailer->Subject = $subject;

			$cMailName = null;

			$mailer->AddAddress($cMail);
			$res = $mailer->Send();
		}
		return sizeof($toMails);
	}


	function getContactsFromContactLists ($listsIds, $kernelStrings) {

		$typeDescription = $fieldsPlainDesc = null;
		$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc );

		$ContactCollection->loadAsArrays  = true;

		// Get mail recipients' contact list

	
		$emptyArray = array ();
		$listRes = $ContactCollection->loadMixedEntityContactWithEmails( $emptyArray, $emptyArray, $listsIds, $emptyArray, 'C_ID', $kernelStrings, true );
		if (PEAR::isError($list))
			return $listRes;

		$list =array ();
		foreach ($ContactCollection->items as $contact)
			$list[] = trim($contact["C_FIRSTNAME"] . " " . $contact["C_LASTNAME"]) . " <" . $contact["C_EMAILADDRESS"] . ">";
		return $list;
	}

	function getDomainServiceParams($kernelStrings) {
		$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );
		if ( !file_exists($filePath) )
		return PEAR::raiseError( $kernelStrings['app_noverfile_message'], ERRCODE_APPLICATION_ERR );

		$dom = @domxml_open_file( realpath($filePath) );
		if ( !$dom )
		return PEAR::raiseError( $kernelStrings['app_openverfile_message'], ERRCODE_APPLICATION_ERR );

		$xpath = @xpath_new_context($dom);
		if ( !( $domainsnode = &xpath_eval($xpath, '/WBS/DOMAINS') ) )
		return array();

		if ( !count($domainsnode->nodeset) )
		return array();

		return getXMLAttributes( $domainsnode->nodeset[0]);
	}

?>
