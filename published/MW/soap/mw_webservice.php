<?php
	require_once("../../common/soap/includes/soapinit.php");
	require_once("../../common/soap/includes/soapclient_funcs.php");

	require_once ("SOAP/Value.php");
	require_once ("SOAP/Fault.php");

	class SOAP_MW_Server {
		var $__typedef     = array();
		var $__dispatch_map = array();

		function SOAP_MW_Server() {
			global $LOGIN_INFO;
			global $NAMED_ITEM;
			global $ARRAY_OF_STRINGS;

			$this->__dispatch_map['mw_UpdatePassword'] =
			//
			//	function aa_UpdatePassword();
			//
			//	Description
			//		Change user password
			//
			//	Parameters
			//		oldPassword - old password (base64)
			//		newPassword - new password (base64)
			//		repeatPassword - password confirmation (base64)
			//		CURRENT_U_ID - user identifier (base64) 
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		error - error message (base64) or blank string
			//		infalidField - required field name (base64) or empty string (base64)
			//
				array(
					'in' => array('oldPassword' => 'string', 'newPassword' => 'string', 'repeatPassword' => 'string', 'CURRENT_U_ID' => 'string', 'PASSWORD' => 'string', 'lang' => 'string'),
					'out' => array('error' => 'string', 'invalidField' => 'string')
				);

			$this->__dispatch_map['mw_GetPersonalSettings'] =
			//
			//	function aa_GetPersonalSettings();
			//
			//	Description
			//		Get personal settings of given user
			//
			//	Parameters
			//		U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[return] - PersonalSettings record
			//		result[error] - error message (base64) or blank string
			//
				array(
					'in' => $LOGIN_INFO,
					'out' => array('return' => '{urn:MWServer}PersonalSettings', 'error' => 'string')
				);

			$this->__dispatch_map['mw_SetPersonalSettings'] =
			//
			//	function aa_GetPersonalSettings();
			//
			//	Description
			//		Update personal settings of current user
			//
			//	Parameters
			//		newSettings - updated PersonalSettings record
			//		U_ID - user identifier (base64)
			//		PASSWORD - user password (md5, base64)
			//		lang - user language
			//
			//	Returns
			//		result[error] - error message (base64) or blank string
			//		result[infalidField] - required field name (base64) or empty string (base64)
			//
				array(
					'in' => array('newSettings' => '{urn:MWServer}PersonalSettings', 'U_ID' => 'string', 'PASSWORD' => 'string', 'lang' => 'string'),
					'out' => array('error' => 'string', 'invalidField' => 'string')
				);

			//
			//	PersonalSettings record
			//
			//	Description
			//		used to hold information about current user
			//
			//	Fields
			//		FirstName - first name (base64)
			//		MiddleName - middle name (base64)
			//		LastName - last name (base64)
			//		EMail - email address (base64)
			//		Phone - phone (base64)
			//		Language - language (base64)
			//		MailFormat - MainFormat (base64)
			//
			$this->__typedef['PersonalSettings'] = 
				array(
					'FirstName' => 'string',
					'MiddleName' => 'string',
					'LastName' => 'string',
					'EMail' => 'string',
					'Phone' => 'string',
					'Language' => 'string',
					'MailFormat' => 'string'
				);

		}
		function __dispatch($methodname) {
			if (isset($this->__dispatch_map[$methodname]))
				return $this->__dispatch_map[$methodname];
			return NULL;
		}

		function mw_UpdatePassword($oldPassword, $newPassword, $repeatPassword, $CURRENT_U_ID, $PASSWORD, $lang)
		{
			global $loc_str;

			$res = CheckSoapUser(array(
				"U_ID" => $CURRENT_U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang
				));
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'string', base64_encode($res->getMessage())),
					new SOAP_Value('invalidField', 'string', "")
				);
				
			$UID =  strtoupper(base64_decode( $CURRENT_U_ID )) ;
			$passwordData = array("U_ID" => $UID, "U_PASSWORD" => base64_decode($oldPassword), "PASSWORD1" => base64_decode($newPassword), "PASSWORD2" => base64_decode($repeatPassword));
			$res = updateUserPassword($passwordData, $loc_str[$lang]);
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

		function mw_SetPersonalSettings($newSettings, $U_ID, $PASSWORD, $lang)
		{
			global $loc_str;
			$res = CheckSoapUser(array(
				"U_ID" => $U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang
				));
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'string', base64_encode($res->getMessage())),
					new SOAP_Value('invalidField', 'string', "")
				);
				
			$UID =  strtoupper(base64_decode( $U_ID )) ;
			$userdata = array();
			$userdata["U_ID"] = $UID;
			$userdata["C_LASTNAME"] = base64_decode($newSettings->LastName);
			$userdata["C_FIRSTNAME"] = base64_decode($newSettings->FirstName);
			$userdata["C_EMAILADDRESS"] = base64_decode($newSettings->EMail);
			$userdata["C_MIDDLENAME"] = base64_decode($newSettings->MiddleName);
			$userdata[LANGUAGE] = base64_decode($newSettings->Language);
			$userdata[MAILFORMAT] = base64_decode($newSettings->MailFormat);

			$res = modifyPersonalSettings($userdata, $loc_str[$lang]);

			if (PEAR::isError($res)) {
			        $invalidField = "";
				if ( $res->getCode() == ERRCODE_INVALIDFIELD )
					$invalidField = $res->getUserInfo();
				return array(
					new SOAP_Value('error', 'string', base64_encode($res->getMessage())),
					new SOAP_Value('invalidField', 'string', base64_encode($invalidField))
				);
			}
			if ( PEAR::isError( writeUserCommonSetting($UID, MAILFORMAT, base64_decode($newSettings->MailFormat), $loc_str[$lang]) ) ) {
				return array(
					new SOAP_Value('error', 'string', base64_encode($loc_str[$lang][ERR_SAVINGUSERSETTINGS])),
					new SOAP_Value('invalidField', 'string', "")
				);
			}			

			return array(
				new SOAP_Value('error', 'string', ""),
				new SOAP_Value('invalidField', 'string', "")
			);
		}

		function mw_GetPersonalSettings($U_ID, $PASSWORD, $lang)
		{
			global $qr_selectUser;
			global $loc_str;

			$res = CheckSoapUser(array(
				"U_ID" => $U_ID, 
				"U_PASSWORD" => $PASSWORD, 
				"LANGUAGE" => $lang
				));
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('return', '{urn:SOAP_AA_Server}PersonalSettings',array(
					"FirstName" => "", 
					"MiddleName" => "", 
					"LastName" => "", 
					"EMail" => "", 
					"Phone" => "", 
					"Language" => "",
					"MailFormat" => ""
					)),
					new SOAP_Value('error', 'string', base64_encode($res->getMessage()))
				);
				
			$UID =  strtoupper(base64_decode( $U_ID )) ;
				

			$userdata["U_ID"] = $UID;

			$res = exec_sql( $qr_selectUser, $userdata, $userdata, true );
			if ( PEAR::isError( $res ) ) {
				return array(
					new SOAP_Value('return', '{urn:SOAP_AA_Server}PersonalSettings',array(
					"FirstName" => "", 
					"MiddleName" => "", 
					"LastName" => "", 
					"EMail" => "", 
					"Phone" => "", 
					"Language" => "",
					"MailFormat" => ""
					)),
					new SOAP_Value('error', 'string', base64_encode($loc_str[$lang][ERR_QUERYEXECUTING]))
				);
			}

			$userdata[LANGUAGE] = readUserCommonSetting( $UID, LANGUAGE );
			$userdata[MAILFORMAT] = "html";

			return array(
				new SOAP_Value('return', '{urn:SOAP_MW_Server}PersonalSettings',array(
				"FirstName" => base64_encode($userdata["C_FIRSTNAME"]), 
				"MiddleName" => base64_encode($userdata["C_MIDDLENAME"]), 
				"LastName" => base64_encode($userdata["C_LASTNAME"]), 
				"EMail" => base64_encode($userdata["C_EMAILADDRESS"]), 
				"Phone" => base64_encode($userdata["C_HOMEPHONE"]), 
				"Language" => base64_encode($userdata[LANGUAGE]),
				"MailFormat" => base64_encode($userdata[MAILFORMAT]),
				)),
				new SOAP_Value('error', 'string', "")
			);
		}
		                                              
	}


	require_once 'SOAP/Server.php';
	$server = new SOAP_Server;

	$soapclass = new SOAP_MW_Server();
	$server->_auto_translation = true;
	$server->addObjectMap($soapclass, 'urn:SOAP_MW_Server');

		
	if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST') {
		$server->service($HTTP_RAW_POST_DATA);
	} else {
		require_once 'SOAP/Disco.php';
		$disco = new SOAP_DISCO_Server($server,'MWServer');
		header("Content-type: text/xml");
		echo $disco->getWSDL();
		exit;
	}
?>