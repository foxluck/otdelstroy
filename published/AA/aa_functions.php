<?php

	require_once "SOAP/Client.php";
	
	if (!function_exists("json_encode") ) {
		
		include (WBS_DIR . "kernel/classes/JSON.php");
		/**
		 * Returns the JSON representation of a value
		 * 
		 * @param mixed $string
		 * @return string
		 */
		function json_encode($value) 
		{
			$json = new Services_JSON();
			return $json->encode($value);
		}
		
		/**
		 * Decodes a JSON string
		 * 
		 * @param string $string
		 * @param bool $assoc
		 * @return object|array
		 */
		function json_decode($string, $assoc = false) 
		{
			$json = new Services_JSON();
			if ($assoc) {
				$json->use = SERVICES_JSON_LOOSE_TYPE;
			} 
			return $json->decode($string);
		}
	}
	
	
	//
	// Account Administrator non-DMBS application functions
	//

	function aa_canManageContacts( $U_ID )
	//
	// Checks whether the user has rights for managing contacts
	//
	//		Parameters:
	//			$U_ID - user identifier
	//
	//		Returns boolean
	//
	{
		$screens = listUserScreens($U_ID);

		if ( !isset($screens['CM']) )
			return  false;

		return in_array('UC', $screens['CM']);
	}

	function aa_canManageUsers( $U_ID )
	//
	// Checks whether the user has rights for managing users
	//
	//		Parameters:
	//			$U_ID - user identifier
	//
	//		Returns boolean
	//
	{
		$screens = listUserScreens($U_ID);

		if ( !isset($screens['UG']) )
			return  false;

		return in_array('UNG', $screens['UG']);
	}

	function aa_getAppName( $APP_ID )
	//
	// Returns application name
	//
	{
		$APP_ID = strtoupper($APP_ID);

		$filePath = WBS_PUBLISHED_DIR."/$APP_ID/".APP_REGISTER_FILE;

		$appInfo = loadApplicationRegisterData( $filePath );
		if ( !is_array($appInfo) )
			return false;

		$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, $APP_ID );
		$appStrings = loadLocalizationStrings( $localizationPath, strtolower($APP_ID) );

		return sliceLocalizaionArray( $appStrings, $appInfo[APP_REG_APPLICATION][APP_REG_NAME] );
	}
	
	/**
	 * Returns SOAP connection
	 * 
	 * @return SOAP_Client
	 */
	function aa_createSOAPConnection () {
		global $kernelStrings;
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection )
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		else {
			$connection->setOpt( 'timeout', 60 );
			return $connection;
		}
	}
	
	//
	// Change user`s profile
	//
	
		
	/**
	 * Change to custom plan
	 * 
	 * @param string $custom_log log of changes
	 * @param string $wbs_username user`s login
	 * @param string $DB_KEY 
	 * @param string $language
	 * @param string $user_mail unconfirmed user`s e-mail $amount = 0, $payment, $amount_sms
	 * @param float $amount cost of change
	 * @param string $payment payment way
	 * @param float $amount_sms cost of additional SMS balance
	 * 
	 * @return error or id of order
	 */
	function aa_changeToCustom($custom_log, $wbs_username, $DB_KEY, $language, $user_mail = null, $amount = 0, $payment, $amount_sms = 0, $customerInfo = array())
		{
		global $databaseInfo, $kernelStrings;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		}
		
		$connection->setOpt( 'timeout', 10000 );
		
		$currency = $databaseInfo[HOST_FIRSTLOGIN][HOST_LANGUAGE] == 'rus' ? 'RUR' : 'USD';

		$exists_customer = aa_customerByMail($DB_KEY, true);
		
		$COMPANY = (!empty($exists_customer->MTC_COMPANY)) ? $exists_customer->MTC_COMPANY :$databaseInfo[HOST_FIRSTLOGIN][HOST_COMPANYNAME];
		$FIRSTNAME = (!empty($exists_customer->MTC_FIRSTNAME)) ? $exists_customer->MTC_FIRSTNAME :$databaseInfo[HOST_FIRSTLOGIN][HOST_FIRSTNAME];
		$LASTNAME = (!empty($exists_customer->MTC_LASTNAME)) ? $exists_customer->MTC_LASTNAME :$databaseInfo[HOST_FIRSTLOGIN][LASTNAME];
		$LASTNAME = ($LASTNAME) ? $LASTNAME : '-';
		
		$wbs_username = $FIRSTNAME.' '.$LASTNAME;

		if (isset($customerInfo['MTC_EMAIL'])) 
			{
			$user_mail = $customerInfo['MTC_EMAIL'];
			}
		
		$customerInfoForSend = $customerInfo;
		foreach ($customerInfo as $k => $v) 
			{
			$customerInfoForSend[$k] = base64_encode($v);
			}
			
		$parameters = array(
			"U_ID" 				=> base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" 			=> base64_encode(AA_MT_SOAP_PWD),
			"custom_log" 			=> base64_encode($custom_log), 
			"wbs_username" 			=> base64_encode($wbs_username),
			"DB_KEY" 				=> base64_encode($DB_KEY),
			"MTO_AMOUNT" 			=> $amount,
			"MTO_PAYMENT_OPTION" 	=> base64_encode($payment), 
			"LANGUAGE" 			=> base64_encode($databaseInfo[HOST_FIRSTLOGIN][HOST_LANGUAGE]),
			"COMPANY" 			=> base64_encode($COMPANY),
			"FIRSTNAME" 			=> base64_encode($FIRSTNAME),
			"LASTNAME"			=> base64_encode($LASTNAME),
			"MAIL" 				=> base64_encode($user_mail),
			"CURRENCY" 			=> base64_encode($currency),
			"LOGINNAME" 			=> base64_encode($databaseInfo[HOST_FIRSTLOGIN][HOST_LOGINNAME]),
			"SMS" 				=> $amount_sms,
			"ICUSTOMER"			=> $customerInfoForSend,
			"IPCLIENT"				=> base64_encode($_SERVER['REMOTE_ADDR'])
			 );
			 
		$res = $connection->call( "mt_changeToCustom", $parameters, $mt_service_options );
 
		if ( PEAR::isError($res) )
			{
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );
			}
		else
			if ( $res->error )
				return PEAR::raiseError( sprintf($kernelStrings['bill_error_soap_internal'],$res->error), $res->error  );
			elseif($res->ccLoginInfo == 'unconfirmed')
				return PEAR::raiseError( $kernelStrings['app_accountunconfirmed_note'] );
				
		return $res->MTO_ID;
		}

	/**
	 * Check exists customer by Email
	 * 
	 * @param string $user_mail user`s Email
	 * @param bool $DBKey = true if $user_mail is DBKEY
	 * 
	 * @return error|icustomer
	 */
	function aa_customerByMail($user_mail, $DBKey = false) {
		global $kernelStrings;
 		$connection = aa_createSOAPConnection();
 		if (PEAR::isError($connection))
 			return $connection;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		$parameters = array(
			"U_ID" 				=> base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" 			=> base64_encode(AA_MT_SOAP_PWD),
			"EMAIL"				=> base64_encode($user_mail) 
			 );

		if ($DBKey) {

			$res = $connection->call( "mt_customerByDBKEY", $parameters, $mt_service_options );

		}	else {

			$res = $connection->call( "mt_customerByMail", $parameters, $mt_service_options );
		}

		if ( PEAR::isError($res) ) {
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );
		}	elseif ( $res->error )
			return PEAR::raiseError( sprintf($kernelStrings['bill_error_soap_internal'],$res->error), $res->error  );
		if ($res->exist == 0) {
			return null;
		}

		return json_decode($res->customer);
	}

	/**
	 * Check exists customer by DBKey
	 * 
	 * @param string $BKey
	 * 
	 * @return error|icustomer
	 */
	function aa_customerByDBkey($DBKey) {
		return aa_customerByMail($DBKey, true);
	}

	/**
	 * Check exists contact by Email
	 * 
	 * @param string $user_mail user`s Email
	 * 
	 * @return error | contact
	 */
	function aa_checkContactByMail($user_mail) {
		global $kernelStrings;
 		$connection = aa_createSOAPConnection();
 		if (PEAR::isError($connection))
 			return $connection;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		$parameters = array(
			"U_ID" 				=> base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" 			=> base64_encode(AA_MT_SOAP_PWD),
			"EMAIL"				=> base64_encode($user_mail) 
		 );

		$res = $connection->call( "mt_checkContactByMail", $parameters, $mt_service_options );
		
		if ( PEAR::isError($res) ) {
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );
		} elseif ( $res->error ) {
			return $kernelStrings['contact_email_exist_error'];
		}

		return null;
	}
	
	/**
	 * Hack billing date
	 * 
	 * @param string $DBXML
	 * @param string $DATE
	 * 
	 * @return void
	 */
	function aa_hackBillingDate($DBXML, $DATE)
		{
		global $kernelStrings;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		}
		
		$parameters = array(
			"DBXML" 				=> base64_encode($DBXML),
			"DATE" 				=> base64_encode($DATE),

			 );
			 
		$res = $connection->call( "mt_hackBillingDate", $parameters, $mt_service_options );

		if ( PEAR::isError($res) )
			{
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );
			}
		else
			if ( $res->error )
				return PEAR::raiseError( sprintf($kernelStrings['bill_error_soap_internal'],$res->error), $res->error  );

		return $res->exist;
		}
			
	/**
	 * Cancel hosted account
	 * 
	 * @param string $DB_KEY
	 * @param string $wbs_username
	 * 
	 * @return error or not error, is question 
	 */
	function aa_cancelAccount($DB_KEY, $wbs_username)
	{
		global $kernelStrings;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		}
 
		$parameters = array(
			"DB_KEY" => base64_encode($DB_KEY), 
			"U_ID" => base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" => base64_encode(AA_MT_SOAP_PWD),
			"wbs_username" => base64_encode($wbs_username)
			 );
			 
		$res = $connection->call( "mt_cancelHostedAccount", $parameters, $mt_service_options );
		if ( PEAR::isError($res) )
			{
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );
			}
		else
			if ( $res->error )
				return PEAR::raiseError(sprintf($kernelStrings['bill_error_soap_internal'],$res->error), $res->error  );

	}
	
	/**
	 * Register Card Payment
	 * 
	 * @return error or not error, is question 
	 */
	function aa_registerCardPayment( $cardData, $MTO_ID, &$invalidField, $verifiedCard = false ){
		global $kernelStrings;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";

		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		}

		$connection->setOpt( 'timeout', 1000 );

		$cardData['CC_EXPIRE_DATE'] = date( sprintf( '%s-%s-01', $cardData['CC_EXPIRE_DATE_YEAR'], $cardData['CC_EXPIRE_DATE_MON'] ) );

		$cardData = encodeArray( $cardData );
		
		if ( $verifiedCard )
			$cardData['CC_CARD_IS_VERIFIED'] = 1;

		$parameters = array(
			"cardInfo" => $cardData, 
			"MTO_ID" => $MTO_ID,
			"U_ID" => base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" => base64_encode(AA_MT_SOAP_PWD),
			"timeout" => 3000
			 );

		$res = $connection->call("mt_registerCardPayment", $parameters, $mt_service_options); 
		 
		if ( PEAR::isError($res) )
			return PEAR::raiseError($kernelStrings['bill_error_soap']  );

		if ( $res->error != 0 ) {
			$invalidField = base64_decode($res->invalidField);
			$errMessage = ($res->errorCode);
			 
			$errMessage = base64_decode($res->errStr);
			
			return PEAR::raiseError( $errMessage );
		}
	}

	/**
	 * Register Paypal Payment
	 * 
	 * @return error or not error, is question 
	 */
	function aa_registerPaypalPayment( $orderID, $txn_id, $payer_email, $payment_date ){
		global $kernelStrings;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";

		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		}

		$connection->setOpt( 'timeout', 25 );

		$parameters = array(
			"MTO_ID" => $orderID,
			"TXN_ID" => base64_encode($txn_id), 
			"PAYER_EMAIL" => base64_encode($payer_email),
			"PAYMENT_DATE" => base64_encode($payment_date),
			"U_ID" => base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" => base64_encode(AA_MT_SOAP_PWD),
			"timeout" => 3000
			 );

		$res = $connection->call("mt_registerPaypalPayment", $parameters, $mt_service_options); 
		
		if ( PEAR::isError($res) )
			return PEAR::raiseError($kernelStrings['bill_error_soap']  );

		if ( $res->error != 0 ) {
			 
			$errMessage = base64_decode($res->errStr);
			
			return PEAR::raiseError( $errMessage, $res->errorCode );
		}
	}

	/**
	 * Register or update WAHOST account
	 * @param array $order: ("DBKEY" => string, "LOGIN" => string, "COMPANY" => string,
			"FIRSTNAME" => string,
			"LASTNAME" => string,
			"EMAIL" => string,
			"LANGUAGE" => string,
			"DBSIZE" => int,
			"USERS" => int,
			"SMS" => float,
			"PERIOD" => int,
			"APPLICATIONS" => array: (array('APP_ID'=>string, 'INFO'=>FREE|PAID)),
			"MTO_PAYMENT_OPTION" => string,
			"MTO_AMOUNT"=> float,
			"MTO_CUR"=> string,

	 * @return array: 'customer_id'=>int, 'order_info' => string, 'order_id' => int
	 */
	function aa_registerWAHOST( $Params, &$invalidField ){
		global $kernelStrings;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";

		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		}

		$connection->setOpt( 'timeout', 25 );

		$parameters = array(
			"DBKEY" => base64_encode($params['DBKEY']),
			"LOGIN" => base64_encode($params['LOGIN']),
			"COMPANY" => base64_encode($params['COMPANY']),
			"FIRSTNAME" => base64_encode($params['FIRSTNAME']),
			"LASTNAME" => base64_encode($params['LASTNAME']),
			"EMAIL" => base64_encode($params['EMAIL']),
			"LANGUAGE" => base64_encode($params['LANGUAGE']),
			"DBSIZE" => $params['DBSIZE'],
			"USERS" => $params['USERS'],
			"SMS" => $params['SMS'],
			"PERIOD" => (int)$params['PERIOD'],
			"APPLICATIONS" => $params['APPLICATIONS'],
			"MTO_PAYMENT_OPTION"=> base64_encode($params['MTO_PAYMENT_OPTION']),
			"MTO_AMOUNT"=> $params['MTO_AMOUNT'],
			"MTO_CUR"=> base64_encode($params['MTO_CUR']),
			"UPGRADE" => isset($params['UPGRADE'])?$params['UPGRADE']:0,
			"U_ID" => base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" => base64_encode(AA_MT_SOAP_PWD),
			 );

		$res = $connection->call("mt_updateAccount", $parameters, $mt_service_options); 
		
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );

		if ( $res->error != 0 ) {
			$invalidField = base64_decode($res->invalidField);
			$errMessage = Error::getCodeMessage($res->errorCode);
			if ( !strlen($errMessage) )
				$errMessage = base64_decode($res->errStr);
			
			return PEAR::raiseError( $errMessage );
		}
		$result = array(
			'customer_id' => $res->MTC_ID, 
			'order_info' => base64_decode($res->orderInfo), 
			'payment_info' => base64_decode($res->paymentInfo), 
			'wahostlogin_info' => base64_decode($res->wahostLoginInfo), 
			'order_id' => $res->MTO_ID
			);
		return $result;
	}

	
	/**
	 * Add free application for current account
	 * 
	 * @param string $new_service additional service
	 * @param string $current_plan account plan
	 * @param string $DB_KEY XML file name
	 * @param string $wbs_username	user`s name
	 * 
	 * @return error|not error 
	 */
	function aa_AddFreeApplication($new_service, $current_plan,  $DB_KEY, $wbs_username)
	{
		global $databaseInfo, $kernelStrings;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );

		$exists_customer = aa_customerByMail($DB_KEY, true);
		
		$FIRSTNAME = (!empty($exists_customer->MTC_FIRSTNAME)) ? $exists_customer->MTC_FIRSTNAME :$databaseInfo[HOST_FIRSTLOGIN][HOST_FIRSTNAME];
		$LASTNAME = (!empty($exists_customer->MTC_LASTNAME)) ? $exists_customer->MTC_LASTNAME :$databaseInfo[HOST_FIRSTLOGIN][LASTNAME];
		$LASTNAME = ($LASTNAME) ? $LASTNAME : '-';
		
		$wbs_username = $FIRSTNAME.' '.$LASTNAME;
		
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		}
		$connection->setOpt( 'timeout', 10000 );
		
		$parameters = array(
			"DB_KEY" => base64_encode($DB_KEY), 
			"U_ID" => base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" => base64_encode(AA_MT_SOAP_PWD),
			"new_service" => base64_encode($new_service),
			"current_plan" => base64_encode($current_plan),
			"wbs_username" => base64_encode($wbs_username),
			"free_applications" => base64_encode($databaseInfo[HOST_DBSETTINGS][HOST_FREE_APPS])
			 );
		$res = $connection->call( "mt_addFreeApp", $parameters, $mt_service_options );
 
		if ( PEAR::isError($res) )
			{
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );
			}
		else
			if ( $res->error )
				return PEAR::raiseError( sprintf($kernelStrings['bill_error_soap_internal'],$res->error), $res->error  );
				
			//global $UR_Manager;
			//require_once( WBS_PUBLISHED_DIR . "/". strtoupper( $new_service ) . "/" .WBS_UR_APPCLASS_FILE );
			//$ret=$UR_Manager->SetGlobalRightsPath( 'JOHN', UR_USER_ID, '/ROOT/'.strtoupper( $new_service ), UR_GRANT );

	}
	
	/**
	 * Delete free application for current account
	 * 
	 * @param string $service service for delete
	 * @param string $current_plan account plan
	 * @param string $DB_KEY XML file name
	 * @param string $wbs_username user`s name
	 * 
	 * @return error or not error, is question 
	 */
	function aa_DeleteFreeApplication($service, $current_plan,  $DB_KEY, $wbs_username)
	{
		global $databaseInfo, $kernelStrings;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );

		$exists_customer = aa_customerByMail($DB_KEY, true);
		
		$FIRSTNAME = (!empty($exists_customer->MTC_FIRSTNAME)) ? $exists_customer->MTC_FIRSTNAME :$databaseInfo[HOST_FIRSTLOGIN][HOST_FIRSTNAME];
		$LASTNAME = (!empty($exists_customer->MTC_LASTNAME)) ? $exists_customer->MTC_LASTNAME :$databaseInfo[HOST_FIRSTLOGIN][LASTNAME];
		$LASTNAME = ($LASTNAME) ? $LASTNAME : '-';
		
		$wbs_username = $FIRSTNAME.' '.$LASTNAME;
		
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		}
	
		$parameters = array(
			"DB_KEY" => base64_encode($DB_KEY), 
			"U_ID" => base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" => base64_encode(AA_MT_SOAP_PWD),
			"service" => base64_encode($service),
			"current_plan" => base64_encode($current_plan),
			"wbs_username" => base64_encode($wbs_username),
			"free_applications" => base64_encode($databaseInfo[HOST_DBSETTINGS][HOST_FREE_APPS])
			 );
			 
		$res = $connection->call( "mt_deleteFreeApp", $parameters, $mt_service_options );
 
		if ( PEAR::isError($res) )
			{
			return PEAR::raiseError( $kernelStrings['bill_error_soap']);
			}
		else
			if ( $res->error )
				return PEAR::raiseError(sprintf($kernelStrings['bill_error_soap_internal'],$res->error), $res->error  );
		
		$res = handleEvent( $service, "onUninstallService", array(), $language) ;
	}
	
	
	
	// 
	// Function for calculation rate of month for plan 
	// 
	
	function aa_getYearDiff( $start, $end )
	{
		$startDt = strtotime( $start );
		$endDt = strtotime( $end );

		if ( $startDt > $endDt ) { 
			$tmp = $endDt;
			$endDt = $startDt;
			$startDt = $tmp;
		}

		$calculatedEndDt = strtotime( "+1 year", $startDt );
		if ( $endDt >= $calculatedEndDt )
			return 1;

		return 0;
	}
	

	function aa_calculateHostingMonthInterval( $startDate, $endDate )
	//
	// Calculates a total months number between two dates
	//
	{
		if ( is_null($startDate) )
			$startDate = date( 'Y-m-d' );

		$startDateInfo = getHostingDateParts( $startDate );
		$endDateInfo = getHostingDateParts( $endDate );

		if ( $endDateInfo['Y'] > $startDateInfo['Y'] )
			$endDateInfo['M'] += 12;

		if ( strcmp( $startDate, $endDate ) > 0 )
			$result = $endDateInfo['M'] - $startDateInfo['M'] - 1 + min( 1, ($endDateInfo['D'] - $startDateInfo['D'] +30)/30 );
		else
			$result = $endDateInfo['M'] - $startDateInfo['M'] + min( 1, ($endDateInfo['D'] - $startDateInfo['D'])/30 );

		if ( $result < 0 )
			return null;

		$yearDiff = aa_getYearDiff( $startDate, $endDate );
		$yearDiff = $yearDiff>1?($yearDiff-1):0;
		
		$result = abs($result) + $yearDiff*12;
		
		$result = ceil($result*100)/100;
		
		return $result;
	}
	
	
	function aa_calculateHostingMonthlyRate( $Applications, $Params)
	{
		global $mt_Price, $language;
		$applicationsPrice = 0;
		
		// collect price for applications
		//
		if ( is_array($Applications) )
			foreach ( $Applications as $APP_ID ) 
				{
				$applicationsPrice += (isset($mt_Price[$language][HOST_CUSTOM_PLAN][$APP_ID])) ? $mt_Price[$language][HOST_CUSTOM_PLAN][$APP_ID] : 0;
				}
		// collect price for common parameters
		//	
		if ( is_array($Params) )
			foreach ( $Params as $param => $value) 
				{
				if(isset($mt_Price[$language][HOST_CUSTOM_PLAN][AA_APP_ID][key($value)][$value[key($value)]]))
					$applicationsPrice += $mt_Price[$language][HOST_CUSTOM_PLAN][AA_APP_ID][key($value)][$value[key($value)]];
				else 
					{
					// if selected unstandart value of parameter then search top nearly value of standart
					//
					foreach ( $mt_Price[$language][HOST_CUSTOM_PLAN][AA_APP_ID][key($value)] as $param => $value) 
						{
							
						}
					$nearly_of_top = 0; 
					$applicationsPrice += $nearly_of_top; 
					}
				}
				
		return $applicationsPrice;
	}
	

	function aa_getAllowMonth($current_billing_date, $needed_period=0)
		{
		$current_date_micro = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		
		$allow_period = array(); 
/*		
			$allow_period[1] = ((strtotime( "+1 months", mktime(0, 0, 0, $current_billing_date['M'], $current_billing_date['D'], $current_billing_date['Y'])) - $current_date_micro) < 31622400) ? 1 : 0;
			$allow_period[3] = ((strtotime( "+3 months", mktime(0, 0, 0, $current_billing_date['M'], $current_billing_date['D'], $current_billing_date['Y'])) - $current_date_micro) < 31622400) ? 1 : 0;
			$allow_period[6] = ((strtotime( "+6 months", mktime(0, 0, 0, $current_billing_date['M'], $current_billing_date['D'], $current_billing_date['Y'])) - $current_date_micro) < 31622400) ? 1 : 0;
			$allow_period[9] = ((strtotime( "+9 months", mktime(0, 0, 0, $current_billing_date['M'], $current_billing_date['D'], $current_billing_date['Y'])) - $current_date_micro) < 31622400) ? 1 : 0;
			$allow_period[12] = ((strtotime( "+12 months", mktime(0, 0, 0, $current_billing_date['M'], $current_billing_date['D'], $current_billing_date['Y'])) - $current_date_micro) < 31622400) ? 1 : 0;
*/			

			$_year = 31622400;
			$_billingdate = strtotime( "+1 months", mktime(0, 0, 0, $current_billing_date['M'], $current_billing_date['D'], $current_billing_date['Y']));
			$allow_period[1] = ($_billingdate > $current_date_micro  && ($_billingdate - $current_date_micro) < $_year) ? 1 : 0;
			
			$_billingdate = strtotime( "+3 months", mktime(0, 0, 0, $current_billing_date['M'], $current_billing_date['D'], $current_billing_date['Y']));
			$allow_period[3] = ($_billingdate > $current_date_micro  && ($_billingdate - $current_date_micro) < $_year) ? 1 : 0;
			
			$_billingdate = strtotime( "+6 months", mktime(0, 0, 0, $current_billing_date['M'], $current_billing_date['D'], $current_billing_date['Y']));
			$allow_period[6] = ($_billingdate > $current_date_micro  && ($_billingdate - $current_date_micro) < $_year) ? 1 : 0;
			
			$_billingdate = strtotime( "+9 months", mktime(0, 0, 0, $current_billing_date['M'], $current_billing_date['D'], $current_billing_date['Y']));
			$allow_period[9] = ($_billingdate > $current_date_micro  && ($_billingdate - $current_date_micro) < $_year) ? 1 : 0;
			
			$_billingdate = strtotime( "+12 months", mktime(0, 0, 0, $current_billing_date['M'], $current_billing_date['D'], $current_billing_date['Y']));

			$allow_period[12] = ($_billingdate - $current_date_micro - 86400*10 < $_year) ? 1 : 0; // allow extend for one year starts from 10 days before expire date
			$_billingdate = mktime(0, 0, 0, $current_billing_date['M'], $current_billing_date['D'], $current_billing_date['Y']);
			
			if ($needed_period > 0 && $needed_period < 12) {
				$allow_period[$needed_period] = 1;
			}
		ksort($allow_period);
		reset($allow_period);
		
		return $allow_period;
		}
		
		
	
	//
	// Payments
	//
	
	/**
	 * Get information about user`s order
	 * 
	 * @param array $kernelStrings localication
	 * @param int $orderID
	 * @param string $DB_KEY
	 * 
	 * @return error or row of payment
	 */
	function aa_getUserOrder(&$kernelStrings, $orderID, $DB_KEY)  
		{
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		}
 
		$parameters = array(
			"U_ID" => base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" => base64_encode(AA_MT_SOAP_PWD),
			"DB_KEY" => base64_encode($DB_KEY), 
			"MTO_ID" => $orderID 
			 );

		$res = $connection->call( "mt_getUserOrder", $parameters, $mt_service_options );
		if ( PEAR::isError($res) )
			{
			return PEAR::raiseError( $kernelStrings['bill_error_soap']);
			}
		else
			if ( $res->error )
				return PEAR::raiseError( sprintf($kernelStrings['bill_error_soap_internal'],$res->error), $res->error  );

		return $res;
		}
		
	/**
	 * Below code from Customer Center
	 *
	 * @param array $src_array
	 * @param array $excludes
	 * @return unknown
	 */
	function encodeArray( $src_array, $excludes = null )
	{
		if ( is_null($excludes) )
			$excludes = array();

		$result = array();

		foreach( $src_array as $key=>$value )
			if ( !in_array( $key, $excludes ) ){
				$result[$key] = base64_encode( is_string($value)?$value:'__NOTSTRING__' );
			}else
				$result[$key] = $value;

		return $result;
	}
	
	
	$CardFields = array(
		'CC_ORGANIZATION_TYPE',
		'CC_COMPANY',
		'CC_LASTNAME',
		'CC_FIRSTNAME',
		'CC_ADDRESS',
		'CC_CITY',
		'CC_STATE',
		'CC_ZIP',
		'CC_COUNTRY',
		'CC_PHONE',
		'CC_EMAIL',
		'CC_EXPIRE_DATE_MON',
		'CC_EXPIRE_DATE_YEAR',
		'CC_CARDNUM',
		'CC_CARD_CODE',
		''
	);
	
	$OrderFields = array(
		'MTO_ORGANIZATION_TYPE',
		'MTO_COMPANY',
		'MTO_LASTNAME',
		'MTO_FIRSTNAME',
		'MTO_ADDRESS',
		'MTO_CITY',
		'MTO_STATE',
		'MTO_ZIP',
		'MTO_COUNTRY',
		'MTO_PHONE',
		'MTO_EMAIL',
		'MTO_EXPIRE_DATE_MON',
		'MTO_EXPIRE_DATE_YEAR',
		'MTO_CARDNUM',
		'MTO_CARD_CODE',
	);
	
	$ccp_FormFields = array(
		'cardtype',
		'company',
		'lastname',
		'firstname',
		'address',
		'city',
		'state',
		'zip',
		'country',
		'phone',
		'email',
		'exp_month',
		'exp_year',
		'card_num',
		'cvv',
	);

	$aa_customer_fieldnames = array(
	
		'email'=>'MTC_EMAIL',
		'company'=>'MTC_COMPANY',
		'firstname'=>'MTC_FIRSTNAME',
		'lastname'=>'MTC_LASTNAME',
		'address'=>'MTC_ADDRESS',
		'city'=>'MTC_CITY',
		'state'=>'MTC_STATE',
		'zip'=>'MTC_ZIP',
		'country'=>'MTC_COUNTRY',

		'jur_address'=>'MTC_JUR_ADDRESS',
		'jur_city'=>'MTC_JUR_CITY',
		'jur_state'=>'MTC_JUR_STATE',
		'jur_zip'=>'MTC_JUR_ZIP',
		'jur_country'=>'MTC_JUR_COUNTRY',

		'phone'=>'MTC_PHONE',
		'inn'=>'MTC_INN',
		//''=>'MTC_KPP',
		'rs'=>'MTC_BANKACCOUNT',
		'bank_name'=>'MTC_BANKNAME',
		'bank_address'=>'MTC_BANKADDRESS',
		//''=>'MTC_BANKBIK',
		//''=>'MTC_BANKCORACCOUNT'

		
	);
	
	
	/**
	 * Copy values of the first array to the second by keys
	 *
	 * @param array $Source
	 * @param array $Destination
	 * @param array $PossibleKeys
	 */
	function acopyArrayValues(&$Source, &$Destination, $PossibleKeys){
		
		foreach ($PossibleKeys as $Key){
			
			$Destination[$Key] = isset($Source[$Key])?$Source[$Key]:'';
		}
	}
	
	
	function acopyArrayKey2Key(&$Source, &$SourceKeys, &$Destination, &$DestinationKeys){
		
		foreach ($SourceKeys as $Ind=>$SourceKey){
			
			$Destination[$DestinationKeys[$Ind]] = $Source[$SourceKey];
		}
	}


	/**
	 * Get additional information for bank transfer
	 *
	 * @param string & $errorStr
	 * @since PHP 5.2.5 - 04.04.2008
	 * @return array|null
	**/
	function aa_forBankTransfer(&$errorStr, &$kernelStrings)
		{
		global $aa_customer_fieldnames;
			
		$convertRapam = array();
		$customerInfo = array();
		
		if ($_REQUEST['payment'] == PAYMENTTYPE_JUR || $_REQUEST['payment'] == PAYMENTTYPE_BANK_PHYS) 
			{
			$suffix = ($_REQUEST['payment'] == PAYMENTTYPE_JUR) ? 'B' : 'I';
			$customerInfo = $_REQUEST['custInfo'][$suffix];
			$contractInfo = (count($_REQUEST['custInfo']['treaty'])) ? $_REQUEST['custInfo']['treaty'] : array();
			$jur_match = (isset($customerInfo['jur_match_physical_address']) && $customerInfo['jur_match_physical_address'] == 1) ? 1 : 0;
			$convertRapam = array();
			unset($customerInfo['jur_match_physical_address']);
			
			foreach ($customerInfo as $variable => $value) 
				{
				if ($value == '-' || empty($value)) 
					{
					$errorStr = $kernelStrings['app_requiredfields_message']; 
					unset($_REQUEST['conrifm_proceed']); 
					return null;
					}
				$convertRapam[$aa_customer_fieldnames[$variable]] = $value;
				}
				
			if (count($contractInfo)) 
				{
				foreach ($contractInfo as $variable => $value) 
					{
					$convertRapam[$aa_customer_fieldnames[$variable]] = $value;
					}
				}
	
			if ($jur_match) 
				{
				$convertRapam['MTC_JUR_ADDRESS'] 	= $convertRapam['MTC_ADDRESS'];
				$convertRapam['MTC_JUR_CITY'] 		= $convertRapam['MTC_CITY'];
				$convertRapam['MTC_JUR_STATE'] 	= $convertRapam['MTC_STATE'];
				$convertRapam['MTC_JUR_ZIP'] 		= $convertRapam['MTC_ZIP'];
				$convertRapam['MTC_JUR_COUNTRY'] 	= $convertRapam['MTC_COUNTRY'];
				}
			
			}
		return $convertRapam;
		}



	
	/**
	 * @desc Craete additional E-mails for specified user
	 * 
	 * @param array $EC_EMAILS User`s E-mails
	 * @param string $MTC_ID Customer ID
	 * @param string $ACTIVATION_URL WTF
	 * 
	 * @return error|null
	 */
	function aa_addCustomerExtraEmails($EC_EMAILS, $MTC_ID, $ACTIVATION_URL)
	{
		global $kernelStrings, $databaseInfo;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
 		}
		
 		$EC_EMAILS = serialize( $EC_EMAILS );
 		
		$parameters = array(
			"MTC_ID"         => base64_encode($MTC_ID),
			"EC_EMAILS"      => base64_encode($EC_EMAILS),
			"ACTIVATION_URL" => base64_encode($ACTIVATION_URL),
			"U_ID"           => base64_encode(AA_MT_SOAP_USER),
			"PASSWORD"       => base64_encode(AA_MT_SOAP_PWD)
			 );

		$res = $connection->call( "mt_addCustomerExtraEmails", $parameters, $mt_service_options );

		if ( PEAR::isError($res) || !empty($res->error) ) {
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );
		}
		
		return null;
	}
		
	/**
	 * @desc Get user`s extra mails
	 * 
	 * @param array $MTCE_EMAILS User`s E-mails
	 * @param string $MTC_ID Customer ID
	 * @param string $ACTIVATION_URL WTF
	 * 
	 * @return error|null
	 */
/*
	function aa_getCustomerExtraEmails($MTCE_EMAILS, $MTC_ID, $ACTIVATION_URL)
		{
		global $kernelStrings, $databaseInfo;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
 		}
		
 		$MTCE_EMAILS = serialize( $MTCE_EMAILS );
 		
		$parameters = array(
			"MTC_ID" 				=> base64_encode($MTC_ID),
			"MTCE_EMAILS" 			=> base64_encode($MTCE_EMAILS),
			"ACTIVATION_URL" 		=> base64_encode($ACTIVATION_URL),
			"U_ID" 				=> base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" 			=> base64_encode(AA_MT_SOAP_PWD)
			 );
	 
		$res = $connection->call( "mt_addCustomerExtraEmails", $parameters, $mt_service_options );

			
		if ( PEAR::isError($res) )
			{
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );
			}
		
		return null;
		}
*/
		
	/**
	 * @desc Delete some user`s additional mail
	 *
	 * @param integer $MTC_ID Customer ID
	 * @param string $EC_EMAIL Mail for delete
	 * 
	 * @author Ivan Chura
	 * @since PHP 5.2.5 - 28.05.2008
	 * @return error|null
	**/
	function aa_deleteCustomerExtraEmail($MTC_ID, $EC_EMAIL){
		
		global $kernelStrings, $databaseInfo;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
 		}
		
		$parameters = array(
			"MTC_ID"   => base64_encode($MTC_ID),
			"EC_EMAIL" => base64_encode($EC_EMAIL),
			"U_ID"     => base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" => base64_encode(AA_MT_SOAP_PWD)
			 );
	 
		$res = $connection->call( "mt_deleteCustomerExtraEmail", $parameters, $mt_service_options );

		if ( PEAR::isError($res) ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );
		}
		
		if ($res->error == 1){
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );;
		}
		
		return null;

	}

	function aa_getOrderInfo($MTO_ID){
		
		global $kernelStrings;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
 		}
		
		$parameters = array(
			"MTO_ID" 				=> $MTO_ID,
			"U_ID" 				=> base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" 			=> base64_encode(AA_MT_SOAP_PWD)
			 );
	 
		$res = $connection->call( "mt_getOrderInfo", $parameters, $mt_service_options );
			
		if ( PEAR::isError($res) ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );
		}
		if ($res->error == 1){
			return PEAR::raiseError( $kernelStrings['bill_error_soap'] );;
		}

		$res = decodeObjectFields( $res->orderInfo );

		return $res;

	}
	

	function aa_listThemes()
	{
		global $kernelStrings;
		$Themes = array();

		$path = WBS_DIR."published/common/html/cssbased/themes";

		if ( !($handle = @opendir($path)) )
			return null;

		while ( false !== ($file = readdir($handle)) )
		{
			if ( $file != "." && $file != ".." ) {
				$filename = $path.'/'.$file;

				if ( is_dir($filename) ) {
					$theme_info = $filename.'/info.php';

					if ( file_exists($theme_info) ) {
						include($theme_info);
						$Themes[$file] = $themeInfo;
						$Themes[$file]['name'] = $file;//$kernelStrings[$themeInfo['name']];
						$Themes[$file]['color'] = $themeInfo['color'];
					}
				}
			}
		}

		uasort($Themes, 'aa_sortThemes');
		
		closedir( $handle );
		return $Themes;
	}
	
	function aa_sortThemes( $a, $b )
	{
		return strcmp( $a['name'], $b['name'] );
	}

	function aa_getHostedAccount(&$kernelStrings, $DB_KEY)  
	{
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
		
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection ){
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		}
 
		$parameters = array(
			"U_ID" => base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" => base64_encode(AA_MT_SOAP_PWD),
			"DB_KEY" => base64_encode($DB_KEY), 
		 );

		$res = $connection->call( "mt_getHostedAccount", $parameters, $mt_service_options );
		if ( PEAR::isError($res) )
			{
			return PEAR::raiseError( $kernelStrings['bill_error_soap']);
			}
		else
			if ( $res->error )
				return PEAR::raiseError( sprintf($kernelStrings['bill_error_soap_internal'],$res->error), $res->error  );

		return $res;
	}

	function translit_string($str) {
		$table = array(
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Й' => 'J',
			'К' => 'K', 'Л' => 'L', 'М' => 'M', 'М' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F',
			'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'CSH', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA',
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j',
			'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
			'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'csh', 'ь' => '', 'ы' => 'y', 'ъ' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
		);
		return str_replace(array_keys($table), array_values($table), $str);
	}

	function get_wbs_cc_url() {
		if(strpos($_SERVER['HTTP_HOST'], 'dev.webasyst.net') !== false) {
			return 'https://dev.webasyst.net/cc/';
		} elseif(strpos($_SERVER['HTTP_HOST'], 'qa.webasyst.net') !== false) {
			return 'https://qa.webasyst.net/cc/';
		} elseif(strpos($_SERVER['HTTP_HOST'], 'qa.webasyst.ru') !== false) {
			return 'https://qa.webasyst.ru/cc/';
		}
		return 'https://my.webasyst.net/';
	}

?>