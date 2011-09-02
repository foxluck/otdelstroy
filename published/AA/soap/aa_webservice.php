<?php
	require_once("../../common/soap/includes/soapinit.php");
	require_once("../../common/soap/includes/soapclient_funcs.php");

	require_once ("SOAP/Value.php");
	require_once ("SOAP/Fault.php");

	class SOAP_AA_Server {
		var $__typedef     = array();
		var $__dispatch_map = array();

		function SOAP_AA_Server() {
			global $LOGIN_INFO;
			global $NAMED_ITEM;
			global $ARRAY_OF_STRINGS;

			$this->__dispatch_map['aa_getDestinationURL'] =
			//
			//	function aa_getDestinationURL();
			//
			//	Description
			//		Returns (base64) url where file can be uploaded (unique file name in the temprorary folder).
			//		Checks ability to upload file with specified size.
			//
			//	Parameters
			//		FileSize - size of file to upload (in bytes)
			//		lang - language of messages
			//
			//	Returns
			//		result[return] - unique temprorary file name in the temprorary directory (base64)
			//		result[error] - error message (base64) or blank string
			//
				array(
					'in' => array('FileSize' => 'int', 'lang' => 'string'),
					'out' => array(
						'return' => 'string',
						'error' => 'string'
					)
				);
			$this->__dispatch_map['aa_GetCompanyInfo'] =
			//
			//	function aa_GetCompanyInfo();
			//
			//	Description
			//		Get information about company
			//
			//	Parameters
			//		U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[return] - CompanyInfo record
			//		result[error] - error message (base64) or blank string
			//
				array(
					'in' => $LOGIN_INFO,
					'out' => array('return' => '{urn:AAServer}CompanyInfo', 'error' => 'string'),
				);
		
			$this->__dispatch_map['aa_SetCompanyInfo'] =
			//
			//	function aa_SetCompanyInfo();
			//
			//	Description
			//		Update information about company
			//
			//	Parameters
			//		newInfo - updated CompanyInfo record
			//		U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[error] - error message (base64) or blank string
			//		result[infalidField] - required field name (base64) or empty string (base64)
			//
				array(
					'in' => array('newInfo' => '{urn:AAServer}CompanyInfo', 'U_ID' => 'string', 'PASSWORD' => 'string', 'lang' => 'string'),
					'out' => array('error' => 'string', 'invalidField' => 'string')
				);

			$this->__dispatch_map['aa_listScreens'] =
			//
			//	function aa_listScreens();
			//
			//	Description
			//		Returns list of all applications, screens and email notifications.
			//
			//	Parameters
			//		SkipSysScreens - skip screens always avaible for user
			//		U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[return] - containing enumerations of applications and their screens as xml string (base64)
			//		result[notifications] - array of Notification records
			//		result[error] - error message (base64) or blank string
			//
				array(
					'in' => $LOGIN_INFO,
					'out' => array(
						'return' => 'string', 
						'notifications' => '{urn:AAServer}Notifications',
						'error' => 'string')
				);

			$this->__dispatch_map['aa_GetScreenAccess'] =
			//
			//	function aa_GetScreenAccess();
			//
			//	Description
			//		Get the list of users and list of applications, screens and 
			//		email notifications available for this users
			//
			//	Parameters
			//		U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[return] - AccessList record
			//		result[error] - error message (base64) or blank string
			//
				array(
					'in' => $LOGIN_INFO,
					'out' => array('return' => '{urn:AAServer}AccessList', 'error' => 'string')
				);

			$this->__dispatch_map['aa_updateScreenAccess'] =
			//
			//	function aa_updateScreenAccess();
			//
			//	Description
			//		Update information about user rights for applications, screens and email notifications.
			//
			//	Parameters
			//		U_ID - user identifier (base64) to update
			//		AccessInfo - updated AccessInfo record with information about accessible screens
			//		NotificationInfo - updated AccessInfo with information about accessible notifications
			//		CURRENT_U_ID - current user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[return] - error message (base64) or blank string
			//
				array(
					'in' => array('U_ID' => 'string', 'AccessInfo' => '{urn:AAServer}AccessInfo', 'NotificationInfo' => '{urn:AAServer}AccessInfo', 'CURRENT_U_ID' => 'string', 'PASSWORD' => 'string', 'lang' => 'string'),
					'out' => array('return' => 'string')
				);


			$this->__dispatch_map['aa_System'] =
			//
			//	function aa_System();
			//
			//	Description
			//		Return data for "System" screen
			//
			//	Parameters
			//		U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[return] - SysInfo record
			//		result[error] - error message (base64) or blank string
			//
				array(
					'in' => $LOGIN_INFO,
					'out' => array(
						'return' => '{urn:AAServer}SysInfo',
						'error' => 'string'
					)
				);

			$this->__dispatch_map['aa_getSysConsts'] = 
			//
			//	function aa_getSysConsts();
			//
			//	Description
			//		Get content of $wbs_languages and $mail_formats kernel arrays
			//
			//	Parameters
			//		lang - user language
			//
			//	Returns
			//		result[langs] - accessible languages in NamedList record
			//		result[mail_formats] - accessible notification formats in NamedList record
			//
				array(
					'in' => array('lang' => 'string'),
					'out' => array(
						'langs' => '{urn:AAServer}NamedList',
						'mail_formats' => '{urn:AAServer}NamedList'
					)
				);
		
			$this->__dispatch_map['aa_listUsers'] =
			//
			//	function aa_listUsers();
			//
			//	Description
			//		Get list of users
			//
			//	Parameters
			//		U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[return] - array of UserInfo records
			//		result[error] - error message (base64) or blank string
			//
				array(
					'in' => $LOGIN_INFO,
					'out' => array('return' => '{urn:AAServer}UsersList', 'error' => 'string' ),
				);

			$this->__dispatch_map['aa_SetUserInfo'] =
			//
			//	function aa_SetUserInfo();
			//
			//	Description
			//		Update user information or create new one
			//
			//	Parameters
			//		newUser - true to create new user
			//		newSettings - UserInfo record 
			//		CURRENT_U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[error] - error message (base64) or blank string
			//		result[infalidField] - required field name (base64) or empty string (base64)
			//
				array(
					'in' => array('newUser' => 'boolean', 'newSettings' => '{urn:AAServer}UserInfo', 'CURRENT_U_ID' => 'string', 'PASSWORD' => 'string', 'lang' => 'string'),
					'out' => array('error' => 'string', 'invalidField' => 'string')
				);

			$this->__dispatch_map['aa_DeleteUser'] =
			//
			//	function aa_DeleteUser();
			//
			//	Description
			//		Delete given user (mark as deleted)
			//
			//	Parameters
			//		U_ID - user identifier to delete (Base64)
			//		CURRENT_U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[return] - error message (base64) or blank string
			//
				array(
					'in' => array('U_ID' => 'string', 'CURRENT_U_ID' => 'string', 'PASSWORD' => 'string', 'lang' => 'string'),
					'out' => array('return' => 'string')
				);

			$this->__dispatch_map['aa_RestoreUser'] =
			//
			//	function aa_RestoreUser();
			//
			//	Description
			//		Restore deleted user
			//
			//	Parameters
			//		U_ID - user identifier to restore (Base64)
			//		CURRENT_U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[return] - error message (base64) or blank string
			//
				array(
					'in' => array('U_ID' => 'string', 'CURRENT_U_ID' => 'string', 'PASSWORD' => 'string', 'lang' => 'string'),
					'out' => array('return' => 'string')
				);

			$this->__dispatch_map['aa_updateCur'] =
			//
			//	function aa_updateCur();
			//
			//	Description
			//		Update currency information or create new one
			//
			//	Parameters
			//		new - true - to create new currency
			//		cur_id - currency identifier (base64)
			//		cur_name - currency description (base64)
			//		CURRENT_U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[error] - error message (base64) or blank string
			//		result[infalidField] - required field name (base64) or empty string (base64)
			//
				array(
					'in' => array('new' => 'boolean', 'cur_id' => 'string', 'cur_name' => 'string', 'CURRENT_U_ID' => 'string', 'PASSWORD' => 'string', 'lang' => 'string'),
					'out' => array('error' => 'string', 'invalidField' => 'string')
				);

			$this->__dispatch_map['aa_deleteCur'] =
			//
			//	function aa_deleteCur();
			//
			//	Description
			//		Delete currency
			//
			//	Parameters
			//		cur_id - currency identifier to delete (Base64)
			//		CURRENT_U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[return] - error message (base64) or blank string
			//
				array(
					'in' => array('cur_id' => 'string', 'CURRENT_U_ID' => 'string', 'PASSWORD' => 'string', 'lang' => 'string'),
					'out' => array('return' => 'string')
				);

			$this->__dispatch_map['listCurs'] =
			//
			//	function listCurs();
			//
			//	Description
			//		Get the list of currencies
			//
			//	Returns
			//		result[return] - CurrencyList record
			//
				array(
					'in' => array(),
					'out' => array('return'=>'{urn:AAServer}CurrencyList')
				);


			//
			//	NamedList array 
			//
			//	Description
			//		Array of NamedItem records to hold id=value pairs.
			//
			$this->__typedef['NamedList'] =
				array(
					array(
						'item' => '{urn:AAServer}NamedItem'
					)
				);
			//
			//	NamedItem record
			//
			//	Description
			//		used to hold id=value pair
			//
			//	Fields
			//		Id - string identifier (base64)
			//		Value - string value associated with id (Base64)
			//
			$this->__typedef['NamedItem'] = $NAMED_ITEM;

			//
			//	CurrencyList array 
			//
			//	Description
			//		Array of CurrencyItem records 
			//
			$this->__typedef['CurrencyList'] =
				array(
					array(
						'item' => '{urn:AAServer}CurrencyItem'
					)
				);
			//
			//	CurrencyItem record
			//
			//	Description
			//		used to hold information about currency
			//
			//	Fields
			//		Id - currency identifier (base64)
			//		Value - currency description (base64)
			//
			$this->__typedef['CurrencyItem'] = $NAMED_ITEM;

			//
			//	SysInfo array
			//
			//	Description
			//		array of SysInfoItem records
			//
			$this->__typedef['SysInfo'] = 
				array(
					array(
						'item' => '{urn:AAServer}SysInfoItem'
					)
				);
			//
			//	SysInfoItem record
			//
			//	Description
			//		Used to hold property that can be changed on "System" screen
			//
			//	Fields
			//		Id - property identifier (base64)
			//		Value - property description (base64)
			//
			$this->__typedef['SysInfoItem'] = $NAMED_ITEM;

			//
			//	UsersList array
			//
			//	Description
			//		user list: array of UserInfo records
			//
			$this->__typedef['UsersList'] =
				array(
					array(
						'item' => '{urn:AAServer}UserInfo'
					)
				);
			//
			// UserInfo record
			//
			//	Description
			//		Used to hold information about user 
			//
			//	Fields
			//		U_ID - user identifier (base64)
			//		FirstName - first name (base64)
			//		MiddleName - middle name (base64)
			//		LastName - last name (base64)
			//		EMail - email address (base64)
			//		Phone - phone (base64)
			//		Password - user password (base64)
			//		RepeatPassword - password confirmation (base64)
			//		Status - user status (base64)
			//		Language - user language (base64)
			//		MailFormat - email notification format (base64)
			//
			$this->__typedef['UserInfo'] = 
				array(
					'U_ID' => 'string',
					'FirstName' => 'string',
					'MiddleName' => 'string',
					'LastName' => 'string',
					'EMail' => 'string',
					'Phone' => 'string',
					'Password' => 'string',
					'RepeatPassword' => 'string',
					'Status' => 'string',
					'Language' => 'string',
					'MailFormat' => 'string'
				);

			//
			//	ArrayOfStrings array
			//
			//	Description
			//		array of strings
			//
			$this->__typedef['ArrayOfStrings'] = $ARRAY_OF_STRINGS;

			
			// 
			//	LocStrings array
			//
			//	Description
			//		array of LocString records (used to hold id=value pairs)
			//
			$this->__typedef['LocStrings'] =
				array(
					array(
						'item' => '{urn:AAServer}LocString'
					)
				);
			//
			//	LocString record
			//
			//	Description
			//		used to hold id=value pair
			//
			//	Fields
			//		Id - string identifier (base64)
			//		Value - string value associated with id (Base64)
			//
			$this->__typedef['LocString'] = $NAMED_ITEM;

			//
			//	array of Notification records
			//
			//	Notifications array
			//
			//	Description
			//		array of Notification records
			//
			$this->__typedef['Notifications'] = 
				array(
					array(
						'item' => '{urn:AAServer}Notification'
					)
				);
			//
			//	Notification record
			//
			//	Description
			//		used to hold information about email notification
			//
			//	Fields
			//		APP_ID - application identifier (base64)
			//		Id - notification identifier (base64)
			//		Caption - notification description (base64)
			//
			$this->__typedef['Notification'] = array(
				'APP_ID' => 'string',
				'Id' => 'string',
				'Caption' => 'string'
			);

			//
			//	AccessList array
			//
			//	Description
			//		array of AccessListItem records
			//
			$this->__typedef['AccessList'] =
				array(
					array(
						'item' => '{urn:AAServer}AccessListItem'
					)
				);
			//
			//	AccessListItem record
			//
			//	Description
			//		used to hold information about user access rights to screens and notifications
			//
			//	Fields
			//		U_ID - user identifier (Base64)
			//		UserName - user full name (Base64)
			//		AccessInfo - AccessInfo array containing user rights to screens
			//		NotificationInfo - AccessInfo array containing user access rights to notifications
			//
			$this->__typedef['AccessListItem'] = 
				array(
					'U_ID' => 'string',
					'UserName' => 'string',
					'AccessInfo' => '{urn:AAServer}AccessInfo',
					'NotificationInfo' => '{urn:AAServer}AccessInfo'
				);

			//
			//	AccessInfo array
			//
			//	Description
			//		array of AccessInfoItem records
			//
			$this->__typedef['AccessInfo'] =
				array(
					array(
						'item' => '{urn:AAServer}AccessInfoItem'
					)
				);
			//
			//	AccessInfoItem record
			//
			//	Description
			//		used to hold information about granted sceren or notification
			//
			//	Fields
			//		APP_ID - application identifier (base64)
			//		Id - granted screen or application identifier (base64)
			//
			$this->__typedef['AccessInfoItem'] = 
				array(
					'APP_ID' => 'string',
					'Id' => 'string'
				);

			//
			//	CompanyInfo record
			//
			//	Description
			//		used to hold information about company
			//
			//	Fields
			//		Name - company name (base64)
			//		Street - street (base64)
			//		City - city  (base64)
			//		State - state (base64)
			//		Zip - zip (base64)
			//		Country - country (base64)
			//		Contact - contact (base64)
			//		EMail - email address (base64)
			//		Phone - phone
			//		Fax - fax (base64)
			//		LogoPath -  LogoPath (base64)
			//
			$this->__typedef['CompanyInfo'] = 
				array(
					'Name' => 'string',
					'Street' => 'string',
					'City' => 'string',
					'State' => 'string',
					'Zip' => 'string',
					'Country' => 'string',
					'Contact' => 'string',
					'EMail' => 'string',
					'Phone' => 'string',
					'Fax' => 'string',
					'LogoPath' => 'string'
				);


			$this->__dispatch_map['aa_addUser'] =
			//
			//	function aa_addUser();
			//
			//	Description
			//		Adds User record to the database
			//
			//	Parameters
			//		addUserInfo - user information
			//		USER - system user (login name)
			//		PASSWORD - user password
			//
			//	Returns
			//		error - error message (base64) or blank string
			//		infalidField - required field name (base64) or empty string (base64)
			//
			array(
				'in' => array( 
								'addUserInfo' => '{urn:AAServer}userInfo', 
								'USER' => 'string', 
								'PASSWORD' => 'string' ),
				'out' => array(
								'error' => 'int', 
								'errorMessage'=>'string' )
			);

			$this->__typedef['addUserInfo'] = array(
						'U_LANGUAGE' => 'string',
						'UG_ID' => 'int',
						'U_ID' => 'string',
						'U_PASSWORD' => 'string',
						'U_FIRSTNAME' => 'string',
						'U_LASTNAME' => 'string',
						'U_MIDDLENAME' => 'string',
						'U_NICKNAME' => 'string',
						'U_EMAILADDRESS' => 'string',
						'SEND_NOTIFICATION' => 'int'
			);
		}

		function __dispatch($methodname) {
			if (isset($this->__dispatch_map[$methodname]))
				return $this->__dispatch_map[$methodname];
			return NULL;
		}

		function aa_getDestinationURL($FileSize, $lang)
		{
			global $loc_str;
			$tmpFileName = uniqid(TMP_FILES_PREFIX);
			$maxSize = DATABASE_SIZE_LIMIT*MEGABYTE_SIZE - getSystemSpaceUsed() - getDatabaseSize();
			$maxUploadSize = getMaxUploadSize();
			if ($maxUploadSize > 0)
			{
				$sizeFormated = formatFileSizeStr( $maxUploadSize );

				if ( $FileSize >$maxUploadSize ) 
					return array(
						new SOAP_Value('return', 'string', ''),
						new SOAP_Value('error', 'string', base64_encode(sprintf($loc_str[$lang]['app_filesizelimit_message'], $sizeFormated)))
					);
			}
			if (($maxSize > 0)&&($FileSize > $maxSize))
				return array(
					new SOAP_Value('return', 'string', ''),
					new SOAP_Value('error', 'string', base64_encode($loc_str[$lang][ERR_SPACELIMITEXCEEDED]))
				);
			return array(
				new SOAP_Value('return', 'string', base64_encode(WBS_TEMP_DIR."/".$tmpFileName)),
				new SOAP_Value('error', 'string', '')
			);
		}

		function aa_System($U_ID, $PASSWORD, $lang)
		{
			global $loc_str;
			$res = CheckSoapUser(array(
				"U_ID" => $U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "SYS"
				));
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('return', '{urn:SOAP_AA_Server}SysInfo', array()),
					new SOAP_Value('error', 'string', base64_encode($res->getMessage()))
				);
			$db_size = getDatabaseSize();
			if ( PEAR::isError($db_size) )
				$db_size = $loc_str[$lang]['sys_errgettingdbsize_message'];

			$dataPath = sprintf( WBS_ATTACHMENTS_DIR );
			dirInfo( $dataPath, $fileCount, $attachmentsSize );
			$totalSize = $db_size + $attachmentsSize;
			$ratio = round( $totalSize/(DATABASE_SIZE_LIMIT*MEGABYTE_SIZE)*100 );

			$res = array();
			$res[] = array(
				"Id" => base64_encode($loc_str[$lang]['sys_database_label']),
				"Value" => base64_encode(formatFileSizeStr( $db_size ))
			);
			$res[] = array(
			        
				"Id" => base64_encode($loc_str[$lang]['sys_files_label']),
				"Value" => base64_encode(formatFileSizeStr( $attachmentsSize ).sprintf( $loc_str[$lang]['sys_totalfiles_text'], $fileCount ))
			);
			$res[] = array(
				"Id" => base64_encode($loc_str[$lang]['sys_total_label']),
				"Value" => base64_encode(formatFileSizeStr( $totalSize )." ".sprintf( $loc_str[$lang]['app_weekwedshort_name'], $ratio ))
			);

			$res[] = array(
				"Id" => "",
				"Value" => ""
			);

			$res[] = array(
				"Id" => base64_encode($loc_str[$lang]['sys_totallimit_label']),
				"Value" => base64_encode(formatFileSizeStr( DATABASE_SIZE_LIMIT*MEGABYTE_SIZE ))
			);
			$res[] = array(
				"Id" => base64_encode($loc_str[$lang]['sys_attachmentlimit_label']),
				"Value" => base64_encode(formatFileSizeStr( FILE_UPLOAD_SIZE_LIMIT*MEGABYTE_SIZE ))
			);

			return array(
				new SOAP_Value('return', '{urn:SOAP_AA_Server}SysInfo', $res),
				new SOAP_Value('error', 'string', "")
			);

		}

		function aa_listUsers($U_ID, $PASSWORD, $lang)
		{
			global $qr_selectUserIDs;
			global $qr_selectUser;
			global $loc_str;

			$res = CheckSoapUser(array(
				"U_ID" => $U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "UL"
				));
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('return', '{urn:SOAP_AA_Server}UsersList', array()),
					new SOAP_Value('error', 'string', base64_encode($res->getMessage()))
				);

		
			$result = array();
		
			$qr = db_query($qr_selectUserIDs);
			if (PEAR::isError($qr))
			{
				return array(
					new SOAP_Value('return', '{urn:SOAP_AA_Server}UsersList', array()),
					new SOAP_Value('error', 'string', base64_encode($qr->getMessage()))
				);
			}

			while($user_id = db_fetch_array($qr))	{
				$u_data = db_query_result($qr_selectUser, DB_ARRAY, array("U_ID"=>$user_id["U_ID"]));
				if (PEAR::isError($u_data))
				{
					return array(
						new SOAP_Value('return', '{urn:SOAP_AA_Server}UsersList', array()),
						new SOAP_Value('error', 'string', base64_encode($u_data->getMessage()))
					);
				}
				$ulang = readUserCommonSetting( $user_id["U_ID"], LANGUAGE );
				$uformat = readUserCommonSetting( $user_id["U_ID"], MAILFORMAT );
				$result[] = array(
					'U_ID' => base64_encode($user_id["U_ID"]),
					'FirstName' => base64_encode($u_data["U_FIRSTNAME"]),
					'MiddleName' => base64_encode($u_data["U_MIDDLENAME"]),
					'LastName' => base64_encode($u_data["U_LASTNAME"]),
					'EMail' => base64_encode($u_data["U_EMAIL"]),
					'Phone' => base64_encode($u_data["U_PHONE"]),
					'Password' => "",
					'RepeatPassword' => "",
					'Status' => base64_encode($u_data["U_STATUS"]),
					'Language' => base64_encode($ulang),
					'MailFormat' => base64_encode($uformat)
				);

			}

			db_free_result($qr);
		
			return array(
				new SOAP_Value('return', '{urn:SOAP_AA_Server}UsersList', $result),
				new SOAP_Value('error', 'string', "")
			);
		}

		function aa_GetCompanyInfo($U_ID, $PASSWORD, $lang)
		{
			global $qr_selectCompanyInfo;
			global $loc_str;

			$emptyresult = array(
				"Name" => "", 
				"Street" => "", 
				"City" => "", 
				"State" => "", 
				"Zip" => "", 
				"Country" => "", 
				"Contact" => "", 
				"EMail" => "", 
				"Phone" => "", 
				"Fax" => "",
				"LogoPath" => ""
			);

			$res = CheckSoapUser(array(
				"U_ID" => $U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "CI"
				));
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('return', '{urn:SOAP_AA_Server}CompanyInfo',$emptyresult),
					new SOAP_Value('error', 'string', base64_encode($res->getMessage()))
				);
		

			$qr = db_query_result( $qr_selectCompanyInfo, DB_ARRAY );
			if (PEAR::isError($qr))
			{
				return array(
					new SOAP_Value('return', '{urn:SOAP_AA_Server}CompanyInfo',$emptyresult),
					new SOAP_Value('error', 'string', base64_encode($qr->getMessage()))
				);
			}

			$fileName = "logo.gif";
			$filePath = getKernelAttachmentsDir();
			$filePath .= "/".$fileName;


			return array(
				new SOAP_Value('return', '{urn:SOAP_AA_Server}CompanyInfo',array(
					"Name" => base64_encode($qr["COM_NAME"]), 
					"Street" => base64_encode($qr["COM_ADDRESSSTREET"]), 
					"City" => base64_encode($qr["COM_ADDRESSCITY"]), 
					"State" => base64_encode($qr["COM_ADDRESSSTATE"]), 
					"Zip" => base64_encode($qr["COM_ADDRESSZIP"]), 
					"Country" => base64_encode($qr["COM_ADDRESSCOUNTRY"]), 
					"Contact" => base64_encode($qr["COM_CONTACTPERSON"]), 
					"EMail" => base64_encode($qr["COM_EMAIL"]), 
					"Phone" => base64_encode($qr["COM_PHONE"]), 
					"Fax" => base64_encode($qr["COM_FAX"]),
					"LogoPath" => base64_encode($filePath)
					)),
				new SOAP_Value('error', 'string', "")
			);

		}
		function aa_SetCompanyInfo($newInfo, $U_ID, $PASSWORD, $lang)
		{
			global $loc_str;

			$res = CheckSoapUser(array(
				"U_ID" => $U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "CI"
				));
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'string', base64_encode($res->getMessage())),
					new SOAP_Value('invalidField', 'string', "")
				);

			$locStrings = $loc_str[$lang];

			if (READ_ONLY) {
				$src = base64_decode($newInfo->LogoPath);
				if ($src!="") {
					if (file_exists($src)) @unlink( $src );
				}
				return array(
					new SOAP_Value('error', 'string', base64_encode($locStrings[ERR_QUERYEXECUTING])),
					new SOAP_Value('invalidField', 'string', "")
				);
			}
			$info = array(
				"COM_NAME" => base64_decode($newInfo->Name), 
				"COM_ADDRESSSTREET" => base64_decode($newInfo->Street), 
				"COM_ADDRESSCITY" => base64_decode($newInfo->City), 
				"COM_ADDRESSSTATE" => base64_decode($newInfo->State), 
				"COM_ADDRESSZIP" => base64_decode($newInfo->Zip), 
				"COM_ADDRESSCOUNTRY" => base64_decode($newInfo->Country), 
				"COM_CONTACTPERSON" => base64_decode($newInfo->Contact), 
				"COM_EMAIL" => base64_decode($newInfo->EMail), 
				"COM_PHONE" => base64_decode($newInfo->Phone), 
				"COM_FAX" => base64_decode($newInfo->Fax)
				);

			$fileName = "logo.gif";
			$filePath = getKernelAttachmentsDir();
			$fdError = 0;
			$res = @forceDirPath( $filePath, $fdError ); 
			if (PEAR::isError($res))
			{
				return array(
					new SOAP_Value('error', 'string', base64_encode($res->getMessage())),
					new SOAP_Value('invalidField', 'string', "")
				);
			}
			if ( !$res ) {
				return array(
					new SOAP_Value('error', 'string', base64_encode($loc_str[$lang][ERR_CREATEDIRECTORY])),
					new SOAP_Value('invalidField', 'string', "")
				);
			}
			$filePath .= "/".$fileName;
			$src = base64_decode($newInfo->LogoPath);
			if ($src=="") {
				if (file_exists($filePath)) @unlink( $filePath );
			} else {
				if ($src!=$filePath) {
					if ( !@copy($src, $filePath) )
						return PEAR::raiseError($loc_str[$lang][ERR_COPYFILE]);

					@unlink($src);
				}
			}
			
			$res = updateCompanyInfo( $info, $locStrings );
			if ( PEAR::isError( $res ) ) {
			        $invalidField = "";
				if ( $res->getCode() == ERRCODE_INVALIDFIELD )
					$invalidField = $res->getUserInfo();
				return array(
					new SOAP_Value('error', 'string', base64_encode($res->getMessage())),
					new SOAP_Value('invalidField', 'string', base64_encode($invalidField))
				);
			}
			return array(
				new SOAP_Value('error', 'string', ""),
				new SOAP_Value('invalidField', 'string', "")
			);
		}

		function aa_listScreens( $U_ID, $PASSWORD, $lang)
		{
			global $loc_str;
			global $global_applications;
			global $global_screens;

			$res = CheckSoapUser(array(
				"U_ID" => $U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "USA"
				));
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('return', 'string', ""),
					new SOAP_Value('notifications', '{urn:AAServer}notifications', array()),
					new SOAP_Value('error', 'string', base64_encode($res->getMessage()))
				);
			
			$result = sprintf("<%sxml version=\"1.0\" encoding=\"windows-1251\"%s>", "?", "?");
			$result .= "<applications>";
			
			$apps = sortApplicationList($global_applications);
			$scrList = $global_screens;
			foreach( $apps as $APP_ID => $app_value ) {
				if ($APP_ID != MYWEBASYST_APP_ID) {
					$app_name = getAppName($APP_ID, $lang);
					$result .= @sprintf( "<app ID = \"%s\" NAME = \"%s\">", base64_encode($APP_ID), base64_encode($app_name));
					foreach( $scrList[$APP_ID] as $SCR_ID=>$scr_value ) {
						$scr_name = getScreenName($APP_ID, $SCR_ID, $lang);
						$result .= @sprintf( "<screen NAME = \"%s\" ID = \"%s\" />", base64_encode($scr_name), base64_encode($SCR_ID));

					}
					$result .= "</app>";
				}
			}
			$result .= "</applications>";

			$fullList = listMailNotifications( $lang );
			$notifs = array();
			foreach($fullList as $mn_id =>$mn_data)
			{
				$notifs[] = array(
					"Id" => base64_encode($mn_id),
					"APP_ID" => base64_encode($mn_data[APP_ID]),
					"Caption" => base64_encode($mn_data[MN_NAME])
				);

			}
			return array(
				new SOAP_Value('return', 'string', base64_encode($result)),
				new SOAP_Value('notifications', '{urn:AAServer}Notifications', $notifs),
				new SOAP_Value('error', 'string', "")
			);
		}

		function aa_GetScreenAccess($U_ID, $PASSWORD, $lang)
		{
			global $qr_selectUsers_2Status;
			global $loc_str;
		
			$res = CheckSoapUser(array(
				"U_ID" => $U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "USA"
				));
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('return', '{urn:SOAP_AA_Server}AccessList', array()),
					new SOAP_Value('error', 'string', base64_encode($res->getMessage()))
				);

			$result = array();
			$qr = db_query(sprintf($qr_selectUsers_2Status, RS_ACTIVE, RS_LOCKED, "U_ID"));
			if (PEAR::isError($qr))
			{
				return array(
					new SOAP_Value('return', '{urn:SOAP_AA_Server}AccessList', array()),
					new SOAP_Value('error', 'string', base64_encode($qr->getMessage()))
				);
			}


			while($user_id = db_fetch_array($qr))	{
				if (PEAR::isError($user_id))
				{
					return array(
						new SOAP_Value('return', '{urn:SOAP_AA_Server}AccessList', array()),
						new SOAP_Value('error', 'string', base64_encode($user_id->getMessage()))
					);
				}
				$a = array();
				$mail = array();
				$app_list = sortAppScreenList(listUserScreens($user_id["U_ID"]));

				foreach ($app_list as $app => $screens)
				{
				        if ($app != MYWEBASYST_APP_ID) {
						foreach ($screens as $k => $item)
						{
							$a[] = array(
								"APP_ID" => base64_encode($app),
								"Id" => base64_encode($item)
							);
						}
					}
				}

				$forbidden_list = listForbiddenMailAssignments($user_id["U_ID"], $loc_str[$lang]);
				$forbiddenKeys = array_keys( $forbidden_list );
				$fullList = listMailNotifications( $lang );

				foreach($fullList as $mn_id =>$mn_data)
				{
					$full_app_id = $mn_data[APP_ID];
					$skip = false;
					if ( in_array( $full_app_id, $forbiddenKeys ) ) {
						$forbiddenMailList = $forbidden_list[$full_app_id];
						if ( in_array($mn_id, $forbiddenMailList) ) {
							$skip = true;
						}
					}
					if (!$skip) {
						$mail[] = array(
							"APP_ID" => base64_encode($full_app_id),
							"Id" => base64_encode($mn_id)
						);
					}

				}
				$result[] = array(
					"U_ID" => base64_encode($user_id["U_ID"]),
					"UserName" => base64_encode(getUserName($user_id["U_ID"])),
					"AccessInfo" => $a,
					"NotificationInfo" => $mail
				);

			}

			db_free_result($qr);
		

			return array(
				new SOAP_Value('return', '{urn:SOAP_AA_Server}AccessList', $result),
				new SOAP_Value('error', 'string', "")
			);

		}

		function aa_UpdateScreenAccess($U_ID, $AccessInfo, $NotificationInfo, $CURRENT_U_ID, $PASSWORD, $lang)
		{
			global $loc_str;
			$res = CheckSoapUser(array(
				"U_ID" => $CURRENT_U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "USA"
				));
			if (PEAR::isError($res))
				return base64_encode($res->getMessage());
			$ainfo=array();
			if (is_array($AccessInfo)) {
				foreach ($AccessInfo as $InfoData)
				{
					$app_id = base64_decode($InfoData->APP_ID);
					$scr_id = base64_decode($InfoData->Id);
					if (!array_key_exists($app_id, $ainfo))
						$ainfo[$app_id] = array();
					$ainfo[$app_id][$scr_id] = 1;
				}
			}
			$UID = strtoupper(base64_decode($U_ID));
			updateUserScreenAccess( $UID, $ainfo, $loc_str[$lang] );
			
			$all_notifs = listMailNotifications( $lang );
			$mailinfo = array();
			if (is_array($NotificationInfo)) {
				foreach( $all_notifs as $id =>$mn_data ) {
					$APP_ID = $mn_data[APP_ID];
					if (!array_key_exists($APP_ID, $mailinfo))
						$mailinfo[$APP_ID] = array();
					$mailinfo[$APP_ID][] = $id;
				}

				foreach ($NotificationInfo as $InfoData) {
					if (!is_null($i=array_search(base64_decode($InfoData->Id), $mailinfo[base64_decode($InfoData->APP_ID)])))
						unset($mailinfo[base64_decode($InfoData->APP_ID)][$i]);
				}

			} else {
				foreach( $all_notifs as $id =>$mn_data ) {
					$APP_ID = $mn_data[APP_ID];
					if (!array_key_exists($APP_ID, $mailinfo))
						$mailinfo[$APP_ID] = array();
					$mailinfo[$APP_ID][] = $id;
				}
			}
			if ( PEAR::isError( $res = saveForbiddenMailAssignments( $UID, $mailinfo, $loc_str[$lang] ) ) ) 
				return base64_encode($res->getMessage());

			
			return "";

		}

		function aa_getSysConsts($lang)
		{
			global $wbs_languages;
			global $mail_formats;
			global $loc_str;

			$langs = array();
			$fmts = array();
			foreach( $wbs_languages as $lang_data ) {
				$langs[] = array(
					"Id" => base64_encode($lang_data[WBS_LANGUAGE_ID]),
					"Value" => base64_encode($lang_data[WBS_LANGUAGE_NAME])
				);
			}

			foreach($mail_formats as $fName => $fValue)
			{
				$fmts[] = array(
					"Id" => base64_encode($fName),
					"Value" => base64_encode($loc_str[$lang][$fValue])
				);
			}
			return array(
				new SOAP_Value('langs', '{urn:AAServer}NamedList', $langs),
				new SOAP_Value('mail_formats', '{urn:AAServer}NamedList', $fmts)
			);
		}

		function aa_SetUserInfo($newUser, $newSettings, $CURRENT_U_ID, $PASSWORD, $lang)
		{
			global $loc_str;
				
			$res = CheckSoapUser(array(
				"U_ID" => $CURRENT_U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "UL"
				));
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'string', base64_encode($res->getMessage())),
					new SOAP_Value('invalidField', 'string', "")
				);

			$UID =  strtoupper(base64_decode( $newSettings->U_ID )) ;
			$userdata = array();
			$userdata["U_ID"] = $UID;
			$userdata["U_LASTNAME"] = base64_decode($newSettings->LastName);
			$userdata["U_FIRSTNAME"] = base64_decode($newSettings->FirstName);
			$userdata["U_EMAIL"] = base64_decode($newSettings->EMail);
			$userdata["U_MIDDLENAME"] = base64_decode($newSettings->MiddleName);
			$userdata["U_PHONE"] = base64_decode($newSettings->Phone);
			$userdata["U_PASSWORD1"] = base64_decode($newSettings->Password);
			$userdata["U_PASSWORD2"] = base64_decode($newSettings->RepeatPassword);
			$userdata["U_STATUS"] = base64_decode($newSettings->Status);
			$userdata["U_PASSWORD"] = "";
			$userdata[TEMPLATE] = "";
			$userdata[MAILFORMAT] = base64_decode($newSettings->MailFormat);
			$userdata[LANGUAGE] = base64_decode($newSettings->Language);

			$action = ACTION_EDIT;
			if ($newUser)
				$action = ACTION_NEW;

			$res = addmodUser($action, $userdata, $loc_str[$lang], $lang);

			if (PEAR::isError($res)) {
			        $invalidField = "";
				if ( $res->getCode() == ERRCODE_INVALIDFIELD )
					$invalidField = $res->getUserInfo();
				return array(
					new SOAP_Value('error', 'string', base64_encode($res->getMessage())),
					new SOAP_Value('invalidField', 'string', base64_encode($invalidField))
				);
			}
			return array(
				new SOAP_Value('error', 'string', ""),
				new SOAP_Value('invalidField', 'string', "")
			);
		}

		function listCurs()
		{
			$res = listCurrency();
			if ( PEAR::isError($res) ) return null;
			$list = array();
			if ( is_array($res) ) 
				foreach( $res as $CUR_ID=>$CUR_DATA ) {
					$list[] = array(
						"Id" => base64_encode($CUR_ID),
						"Value" => base64_encode($CUR_DATA["CUR_NAME"])
					);
				}
			return new SOAP_Value("return", "{urn:AAServer}CurrencyList", $list);

		}

		function aa_updateCur($new, $cur_id, $cur_name, $CURRENT_U_ID, $PASSWORD, $lang)
		{
			global $loc_str;
				
			$res = CheckSoapUser(array(
				"U_ID" => $CURRENT_U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "CL"
				));
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'string', base64_encode($res->getMessage())),
					new SOAP_Value('invalidField', 'string', "")
				);
			$currencyData=array(
				"CUR_ID" => base64_decode($cur_id),
				"CUR_NAME" => base64_decode($cur_name)
			);
			$action = ACTION_EDIT;
			if ($new) $action = ACTION_NEW;
			$res = addmodCurrency( $action, $currencyData, $loc_str[$lang] );
			if ( PEAR::isError( $res ) ) {
				$invalidField = "";
				if ( $res->getCode() == ERRCODE_INVALIDFIELD )
					$invalidField = $res->getUserInfo();
				return array(
					new SOAP_Value('error', 'string', base64_encode($res->getMessage())),
					new SOAP_Value('invalidField', 'string', base64_encode($invalidField))
				);
			}
			return array(
				new SOAP_Value('error', 'string', ""),
				new SOAP_Value('invalidField', 'string', "")
			);

		}
		function aa_deleteCur($cur_id, $CURRENT_U_ID, $PASSWORD, $lang)
		{
			global $loc_str;
				
			$res = CheckSoapUser(array(
				"U_ID" => $CURRENT_U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "CL"
				));
			if (PEAR::isError($res))
				return base64_encode($res->getMessage());
			$currencyData=array(
				"CUR_ID" => base64_decode($cur_id)
			);
			$res = deleteCurrency( $currencyData, $loc_str[$lang], $lang );
			if ( PEAR::isError( $res ) ) 
				return base64_encode($res->getMessage());
			return "";

		}

		function aa_DeleteUser($U_ID, $CURRENT_U_ID, $PASSWORD, $lang)
		{
			global $loc_str;			
			$res = CheckSoapUser(array(
				"U_ID" => $CURRENT_U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "UL"
				));
			if (PEAR::isError($res))
				return base64_encode($res->getMessage());
			$u_data = array(
				"U_ID" => base64_decode($U_ID)
			);

			$res = deleteUser( $u_data, $loc_str[$lang], $lang );
			if (PEAR::isError($res))
				return base64_encode($res->getMessage());
			return "";
		}
		
		function aa_RestoreUser($U_ID, $CURRENT_U_ID, $PASSWORD, $lang)
		{
			global $loc_str;			
			$res = CheckSoapUser(array(
				"U_ID" => $CURRENT_U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang,
				"APP_ID" => "AA",
				"SCR_ID" => "UL"
				));
			if (PEAR::isError($res))
				return base64_encode($res->getMessage());
			$u_data = array(
				"U_ID" => base64_decode($U_ID)
			);

			$res = restoreUser( $u_data, $loc_str[$lang], $lang );
			if (PEAR::isError($res))
				return base64_encode($res->getMessage());
			return "";
		}
		
		function aa_addUser( $userInfo, $USER, $PASSWORD )
		{
			global $AA_APP_ID;
			global $_SERVER;

			global $loc_str;

			$res = CheckSoapUser( array(
				"U_ID" => $USER, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => LANG_ENG,
				"APP_ID" => $AA_APP_ID,
				"SCR_ID" => "UL"
			) );

			if ( PEAR::isError($res) )
				return array(
					new SOAP_Value('error', 'int', 1),
					new SOAP_Value('errorMessage', 'string', $res->getMessage() )
				);

			$userInfo = (array)$userInfo;

			foreach( $userInfo as $key=>$value ) 
				$userInfo[$key] = base64_decode( $value );

			$lang = $userInfo['U_LANGUAGE'];

			$userInfo['U_PASSWORD1'] = $userInfo['U_PASSWORD'];
			$userInfo['U_PASSWORD2'] = $userInfo['U_PASSWORD'];

			$userInfo['C_FIRSTNAME'] = $userInfo['U_FIRSTNAME'];
			$userInfo['C_LASTNAME'] = $userInfo['U_LASTNAME'];
			$userInfo['C_MIDDLENAME'] = $userInfo['U_MIDDLENAME'];
			$userInfo['C_NICKNAME'] = $userInfo['U_NICKNAME'];
			$userInfo['C_EMAILADDRESS'] = $userInfo['U_EMAILADDRESS'];

			$userInfo['U_ACCESSTYPE'] = ACCESS_SUMMARY;
			$userInfo[U_STATUS] = RS_ACTIVE;
			$userInfo[LANGUAGE] = $lang;
			$userInfo[WBS_ENCODING] = $wbs_languages[$lang][WBS_ENCODING];
			$userInfo[START_PAGE] = USE_BLANK;
			$userInfo[MAILFORMAT] = MAILFORMAT_HTML;
			$userInfo[ALLOW_DRACCESS] = 0;
			$userInfo[U_RECEIVESMESSAGES] = 1;

			// Check if user group exists
			//
			if ( !userGroupExists($userInfo["UG_ID"]) )
				return array(
					new SOAP_Value('error', 'int', 1),
					new SOAP_Value('errorMessage', 'string', $loc_str[LANG_ENG]['amu_groupnotfound_message'] )
				);

			// Add user
			//
			$res = addmodUser( ACTION_NEW, $userInfo, $loc_str[LANG_ENG], $lang, DEF_CONTACT_FOLDER, false, null, true );
			if ( PEAR::isError( $res ) ) {
				return array(
					new SOAP_Value('error', 'int', 1),
					new SOAP_Value('errorMessage', 'string', $res->getMessage() )
				);
			}

			// Register user in group
			//
			$res = registerUserInGroup( $userInfo["U_ID"], $userInfo["UG_ID"], $loc_str[LANG_ENG] );
			if ( PEAR::isError($res) ) {
				return array(
					new SOAP_Value('error', 'int', 1),
					new SOAP_Value('errorMessage', 'string', $res->getMessage() )
				);
			}

			// Send user notification
			//
			if ( $userInfo['SEND_NOTIFICATION'] ) {
				$pagePath = $_SERVER['PHP_SELF'];
				$pageHost = $_SERVER['HTTP_HOST'];
				$pageProtocol = ( $_SERVER['SERVER_PORT'] != HTTPS_PORT ) ? 'http://' : 'https://';

				$address = sprintf( "%s%s%s", $pageProtocol, $pageHost, $pagePath );
				$level = 2;

				$URL = dirname( $address );
				$pathData = explodePath( $URL );
				if ( !strlen($pathData[count($pathData)-1]) )
					array_pop($pathData);

				for ( $i = 1; $i <= $level; $i++ )
					array_pop( $pathData );

				$loginURL = implode("/", $pathData).'/login.php';

				sendUserNotification( $loc_str[LANG_ENG], $userInfo["U_ID"], $userInfo["U_PASSWORD1"], $loginURL, $USER );
			}

			return array(
				new SOAP_Value('error', 'int', 0 ),
				new SOAP_Value('errorMessage', 'string', "" )
			);
		}
	}


	require_once 'SOAP/Server.php';
	$server = new SOAP_Server;

	$soapclass = new SOAP_AA_Server();
	$server->_auto_translation = true;
	$server->addObjectMap($soapclass, 'urn:SOAP_AA_Server');

	if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST') {
		$server->service($HTTP_RAW_POST_DATA);
	} else {
		require_once 'SOAP/Disco.php';
		$disco = new SOAP_DISCO_Server($server,'AAServer');
		header("Content-type: text/xml");
		echo $disco->getWSDL();
		exit;
	}
?>