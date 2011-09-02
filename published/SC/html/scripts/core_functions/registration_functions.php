<?php
// *****************************************************************************
// Purpose  add administrator login into database and set default address 
// Inputs   $admin_login - administrator login, $admin_pass - administrator password
// Remarks	this function is called by installation
// Returns	this function always returns true
function regRegisterAdmin( $admin_login, $admin_pass, $Update = false, $OldAdminLogin = '' )
{
	if($Update && $OldAdminLogin){
		
		$CustomerInfo = regGetCustomerInfo2($OldAdminLogin);
		if(is_array($CustomerInfo)){
			
			$sql = '
				UPDATE `'.CUSTOMERS_TABLE.'`
				SET Login="'.xEscapeSQLstring($admin_login).'",cust_password="'.Crypt::PasswordCrypt( $admin_pass, null ).'"
				WHERE customerID="'.xEscapeSQLstring($CustomerInfo['customerID']).'"
			';
			db_query($sql);
			return true;
		}
	}
	$admin_login	= xEscapeSQLstring( $admin_login );
	$admin_pass		= xEscapeSQLstring( $admin_pass );
	// $q_count = db_query( "SELECT COUNT(*) FROM  ".CUSTOMERS_TABLE." WHERE Login='".$admin_login."'" );
	// $count = db_fetch_row( $q_count );
	// $count = $count[0];
	$OldID = 
	db_query( "delete from ".CUSTOMERS_TABLE." where Login='".$admin_login."'" );

	if ( CONF_DEFAULT_CUSTOMER_GROUP=='0' )
		$custgroupID = "NULL";
	else		
		$custgroupID = CONF_DEFAULT_CUSTOMER_GROUP;

	$admin_pass = Crypt::PasswordCrypt( $admin_pass, null );

	$currencyID = defined('CONF_DEFAULT_CURRENCY')?constant('CONF_DEFAULT_CURRENCY'):null;
	if(is_null($currencyID)){
		$q = db_phquery( "select CID from ?#CURRENCY_TYPES_TABLE LIMIT 1" );
		$currencyID = "NULL";
		if ( $currency = db_fetch_row($q) )
			$currencyID = $currency["CID"];
	}

	db_query( "insert into ".CUSTOMERS_TABLE.
		" (Login, cust_password, Email, first_name, last_name, subscribed4news, ". 
		" 	custgroupID, addressID, reg_datetime, CID ) values ".
				"('".$admin_login."','".$admin_pass."', ".
						" '-', '-', '-', 0, $custgroupID, NULL, ".
						" '".Time::dateTime()."', ".$currencyID." )" );
	$errorCode = 0;
	$zoneID = "1";
	$state	= "";
	$countryID = "223";
	$defaultAddressID = regAddAddress( 
				"-", "-", 
				$countryID, 
				$zoneID, 
				$state, 
				"-", 
				"-", 
				"-", 
				$admin_login, 
				$errorCode );
	regSetDefaultAddressIDByLogin( $admin_login, $defaultAddressID );
	return true;
}


// *****************************************************************************
// Purpose
// Inputs   $login - login
// Remarks	
// Returns	true if login exists in database, false otherwise
function regIsRegister( $login ){
	
	$q=db_phquery('SELECT COUNT(Login) FROM ?#CUSTOMERS_TABLE WHERE Login=?',$login);
	$r = db_fetch_row($q);
	return  ( $r[0] != 0 );
}


// *****************************************************************************
// Purpose
// Inputs   $customerID - custmer ID 
// Remarks	
// Returns	false if customer does not exist, login - otherwise
function regGetLoginById( $customerID )
{
	$customerID = (int) $customerID;
	if ($customerID == 0) return false;

	$q = db_query("select Login from ".CUSTOMERS_TABLE." where customerID=".$customerID);
	if ( ($r=db_fetch_row($q)) )
 		return $r["Login"];
	else
		return false;
}

/**
 * @return int || null
 */
function regGetIdByLogin( $login ){
	
	if(!$login)return null;
	static $cache = array();
	
	if(!isset($cache[$login])){
		$cache[$login] = db_phquery_fetch(DBRFETCH_FIRST,"SELECT customerID FROM ?#CUSTOMERS_TABLE WHERE Login=?",$login);
	}
	return $cache[$login];
}


// *****************************************************************************
// Purpose  authenticate user
// Inputs   $login - login, $password - password
// Remarks  if user is authenticated successfully then this function sets sessions variables,
//		update statistic, move cart content into DB 
// Returns	false if authentication failure, true - otherwise
function regAuthenticate($login, $password, $Redirect = true)
{
	$login		= xEscapeSQLstring( $login );
	$password	= xEscapeSQLstring($password );
	$q = db_query("SELECT cust_password, CID, ActivationCode FROM ".CUSTOMERS_TABLE.
		" WHERE Login='".$login."'");
	$row = db_fetch_row($q);

	if(CONF_ENABLE_REGCONFIRMATION && $row['ActivationCode']){
		
		if($Redirect)RedirectSQ('&ukey=act_customer&notact=1');
		else return false;
	}
	if ($row && strlen( trim($login) ) > 0)
	{
		if ($row["cust_password"] == Crypt::PasswordCrypt($password, null) )
		{
			// set session variables
			$_SESSION["log"] 	= $login;
			$_SESSION["pass"] 	= Crypt::PasswordCrypt($password, null);

			
			$_SESSION["current_currency"] = $row["CID"];
			
			// move cart content into DB
			moveCartFromSession2DB();
			return true;
		}
		else
			return false;
	}
	else return false;
}



// *****************************************************************************
// Purpose  	sends password to customer email 
// Inputs   
// Remarks   
// Returns	true if success
function regSendPasswordToUser( $login, &$smarty_mail ){
	
	$sql = '
		SELECT Login, cust_password, Email FROM '.CUSTOMERS_TABLE.'
		WHERE (Login="'.xEscapeSQLstring($login).'" OR Email="'.xEscapeSQLstring($login).'")
		AND (ActivationCode="" OR ActivationCode IS NULL)
	';
	$q = db_query($sql);
	if ($row = db_fetch_row($q)) //send password
	{
		$password = Crypt::PasswordDeCrypt( $row["cust_password"], null );
		$smarty_mail->assign( "user_pass", $password );
		$smarty_mail->assign( "user_login", $row['Login'] );
		$html = $smarty_mail->fetch("remind_password.txt");
		ss_mail($row["Email"], translate("email_subject_forgot_password"), $html, true);
		return true;		
	}
	else
		return false;
}


// *****************************************************************************
// Purpose  determine administrator user
// Inputs   $login - login
// Remarks  if user is authenticated successfully then this function sets sessions variables,
//		update statistic, move cart content into DB 
// Returns	false if authentication failure, true - otherwise
function regIsAdminiatrator( $login )
{
	return ( !strcmp($login, ADMIN_LOGIN) );
}



// *****************************************************************************
// Purpose	register new customer
// Inputs   
//				$login				- login
//				$cust_password		- password
//				$Email				- email
//				$first_name			- customer first name
//				$last_name			- customer last name
//				$subscribed4news	- if 1 customer is subscribed to news
//				$additional_field_values - additional field values is array of item
//									"additional_field" is value of this field
//									key is reg_field_ID
// Remarks	
// Returns
function regRegisterCustomer( $login, $cust_password, $Email, $first_name, 
		$last_name, $subscribed4news, $additional_field_values
		, $affiliateLogin = ''
		)
{
	$login				= xEscapeSQLstring( $login );
	$cust_password		= xEscapeSQLstring( $cust_password );
	$Email				= xEscapeSQLstring( $Email );
	$first_name			= xEscapeSQLstring( $first_name );
	$last_name			= xEscapeSQLstring( $last_name );
	$affiliateLogin	= xEscapeSQLstring( $affiliateLogin);
	$affiliateID		= 0;
	
	if ($affiliateLogin){
		
		$sql = "
			SELECT customerID  FROM ".CUSTOMERS_TABLE."
			WHERE Login='{$affiliateLogin}'
		";
		list($affiliateID) = db_fetch_row(db_query($sql));
	}

	foreach( $additional_field_values as $key => $val)
		$additional_field_values[$key] = xEscapeSQLstring( $val );

	if(isset($_SESSION["current_currency"])){
		$currencyID = $_SESSION["current_currency"];
	}else{
		$currencyID = defined('CONF_DEFAULT_CURRENCY')?constant('CONF_DEFAULT_CURRENCY'):null;
	}
	if(is_null($currencyID)){
		$q = db_phquery( "select CID from ?#CURRENCY_TYPES_TABLE LIMIT 1" );
		$currencyID = "NULL";
		if ( $currency = db_fetch_row($q) )
			$currencyID = $currency["CID"];
	}

	$cust_password = Crypt::PasswordCrypt( $cust_password, null );
	// add customer to CUSTOMERS_TABLE

	$custgroupID = CONF_DEFAULT_CUSTOMER_GROUP;
	if ( $custgroupID == 0 )
		$custgroupID = "NULL";

	/**
	 * Activation code
	 */
	$ActivationCode = '';
	if(CONF_ENABLE_REGCONFIRMATION){
		
		$CodeExists = true;
		while ($CodeExists) {
			
			$ActivationCode = generateRndCode(16);
			$sql = '
				SELECT 1 FROM '.CUSTOMERS_TABLE.'
				WHERE ActivationCode="'.xEscapeSQLstring($ActivationCode).'"
			';
			@list($CodeExists) = db_fetch_row(db_query($sql));
		}
	}
		
	$sql = "insert into ".CUSTOMERS_TABLE.
		"( Login, cust_password, Email, first_name, last_name, subscribed4news, reg_datetime, CID, custgroupID".
		", affiliateID".
		", ActivationCode)".
		"values( '".$login."', '".$cust_password."', '".$Email."', ".
		" '".$first_name."', '".$last_name."', '".$subscribed4news."', '".Time::dateTime()."', ".
			$currencyID.", ".$custgroupID.
			", {$affiliateID}".
			",'{$ActivationCode}' )";
	db_query($sql);

	// add additional values to CUSTOMER_REG_FIELDS_TABLE
	foreach( $additional_field_values as $key => $val )
		SetRegField($key, $login, $val["additional_field"]);

	$customerID = regGetIdByLogin($login);
	//db_query("update ".CUSTOMERS_TABLE." set addressID='".$addressID.
	//	"' where Login='".$login."'" );

	if ( $subscribed4news )
		subscrAddRegisteredCustomerEmail( $customerID );

	return true;
}


// *****************************************************************************
// Purpose	send notification message to email
// Inputs   
//				$login				- login
//				$cust_password		- password
//				$Email				- email
//				$first_name			- customer first name
//				$last_name			- customer last name
//				$subscribed4news	- if 1 customer is subscribed to news
//				$additional_field_values - additional field values is array of item
//									"additional_field" is value of this field
//									key is reg_field_ID
//				$updateOperation	- 1 if customer info is updated, 0 
//								otherwise
// Remarks	
// Returns	
function regEmailNotification($smarty_mail, $login, $cust_password, $Email, $first_name, 
		$last_name, $subscribed4news, $additional_field_values, 
		$countryID, $zoneID, $state, $zip, $city, $address, $updateOperation )
{
	$user = array();
	$smarty_mail->assign( "login", $login );
	$smarty_mail->assign( "cust_password", $cust_password );
	$smarty_mail->assign( "first_name", $first_name );
	$smarty_mail->assign( "last_name", $last_name );
	$smarty_mail->assign( "Email", $Email );
	$additional_field_values = GetRegFieldsValues( $login );
	$smarty_mail->assign( "additional_field_values", $additional_field_values );

	$addresses = regGetAllAddressesByLogin( $login );
	for( $i=0; $i<count($addresses); $i++ )
		$addresses[$i]["addressStr"] = regGetAddressStr( $addresses[$i]["addressID"] , true);
	$smarty_mail->assign( "addresses", $addresses );
	
	if(CONF_ENABLE_REGCONFIRMATION){
		
		$sql = '
			SELECT ActivationCode FROM '.CUSTOMERS_TABLE.'
			WHERE Login="'.xEscapeSQLstring($login).'" AND cust_password="'.xEscapeSQLstring(Crypt::PasswordCrypt($cust_password, null)).'"
		';
		@list($ActivationCode) = db_fetch_row(db_query($sql));
		
		$smarty_mail->assign('ActURL', set_query('ukey=act_customer&act_code='.$ActivationCode,CONF_FULL_SHOP_URL.(substr(CONF_FULL_SHOP_URL, strlen(CONF_FULL_SHOP_URL)-1,1)=='/'?'':'/')));
		$smarty_mail->assign('ActCode', $ActivationCode);
	}
	
	$html = $smarty_mail->fetch( "register_successful.txt" );
	ss_mail($Email, translate("email_subject_registration"), $html, true);

}

// *****************************************************************************
// Purpose	get customer info
// Inputs   
//				$login				- login
//				$cust_password		- password
//				$Email				- email
//				$first_name			- customer first name
//				$last_name			- customer last name
//				$subscribed4news	- if 1 customer is subscribed to news
//				$additional_field_values - additional field values is array of item
//									"additional_field" is value of this field
//									key is reg_field_ID
//				$updateOperation	- 1 if customer info is updated, 0 
//								otherwise
// Remarks	
// Returns	
function regGetCustomerInfo($login, & $cust_password, & $Email, & $first_name, 
		& $last_name, & $subscribed4news, & $additional_field_values, 
		& $countryID, & $zoneID, & $state, & $zip, & $city, & $address )
{
	$q=db_query("select customerID, cust_password, Email, first_name, last_name, ".
		" subscribed4news, custgroupID, addressID  from ".CUSTOMERS_TABLE.
		" where Login='".$login."'");
	$r = db_fetch_row($q);
	$cust_password		= Crypt::PasswordDeCrypt( $r["cust_password"], null );
	if (CONF_BACKEND_SAFEMODE)
		$row["Email"] = translate("msg_safemode_info_blocked");
	else
		$Email = $r["Email"];
	$first_name			= $r["first_name"];
	$last_name			= $r["last_name"];
	$subscribed4news	= $r["subscribed4news"];
	$addressID			= $r["addressID"];
	$customerID			= $r["customerID"];
	$q=db_query("select countryID, zoneID, zip, state, city, address from ".
		CUSTOMER_ADDRESSES_TABLE." where customerID='".$customerID."'");
	$r=db_fetch_row($q);
	$countryID  = $r["countryID"];
	$zoneID		= $r["zoneID"];
	$zip		= $r["zip"];
	$state		= $r["state"];
	$city		= $r["city"];
	$address	= $r["address"];
	$additional_field_values = GetRegFieldsValues( $login );
}




// *****************************************************************************
// Purpose	get customer info
// Inputs   
// Remarks	
// Returns	
function regGetCustomerInfo2( $login )
{
	$q = db_query("select customerID, cust_password, Email, first_name, last_name, ".
		" subscribed4news, custgroupID, addressID, Login, ActivationCode from ".CUSTOMERS_TABLE.
		" where Login='".xEscapeSQLstring($login)."'");
	if ( $row=db_fetch_row($q) )
	{
		if ( $row["custgroupID"] != null )
		{
			$q = db_phquery("SELECT * FROM ?#CUSTGROUPS_TABLE WHERE custgroupID=?", $row["custgroupID"] );
			$custGroup = db_fetch_row($q);
			LanguagesManager::ml_fillFields(CUSTGROUPS_TABLE, $custGroup);

			$row["custgroup_name"] =$custGroup["custgroup_name"];
		}
		else
			$row["custgroup_name"] = "";
		$row["cust_password"] = Crypt::PasswordDeCrypt( $row["cust_password"], null );

		if (CONF_BACKEND_SAFEMODE)$row["Email"] = translate("msg_safemode_info_blocked");

		$row["allowToDelete"]  = regVerifyToDelete( $row["customerID"] );
	}
	return $row;
}



// -----------------------------------------------

function regAddAddress($first_name, $last_name, $countryID, $zoneID, $state, $zip, $city, $address, $log, &$errorCode ){
	
	$sql = 'SELECT COUNT(zoneID) FROM ?#ZONES_TABLE WHERE countryID=?';
	$q = db_phquery($sql,$countryID);
	$r = db_fetch_row( $q );
	if ( ($r[0] != 0) && ($zoneID == 0) && (CONF_ADDRESSFORM_STATE == 0)){
		$errorCode = "err_region_does_not_belong_to_country";
		return false;
	}

	$customerID = regGetIdByLogin($log);

	if ( $zoneID == 0 ) $zoneID = "NULL";
	$sql = '
		INSERT ?#CUSTOMER_ADDRESSES_TABLE ( first_name, last_name, countryID, zoneID, zip, state, city, address, customerID)
		VALUES("'.xEscapeSQLstring($first_name).'","'.xEscapeSQLstring($last_name).'",
		"'.xEscapeSQLstring($countryID).'","'.xEscapeSQLstring($zoneID).'",
		"'.xEscapeSQLstring($zip).'","'.xEscapeSQLstring($state).'",
		"'.xEscapeSQLstring($city).'","'.xEscapeSQLstring($address).'",
		"'.xEscapeSQLstring($customerID).'")';
	db_phquery($sql,$last_name,$countryID,$zoneID,$zip,$state,$city,$address,$customerID);
	return db_insert_id();
}

function redDeleteAddress( $addressID ){
	
	$sql = '
		UPDATE ?#CUSTOMERS_TABLE SET addressID=NULL WHERE addressID=?
	';
	db_phquery($sql,$addressID);
	$sql = '
		DELETE FROM ?#CUSTOMER_ADDRESSES_TABLE WHERE addressID=?
	';
	db_phquery($sql,$addressID);
}


function regGetAddress( $addressID){
	
	if ( $addressID != null ){
		// $customerID
		$sql = '
			SELECT first_name, last_name, countryID, zoneID, zip, state, city, address, customerID 
			FROM ?#CUSTOMER_ADDRESSES_TABLE WHERE addressID=?
		';
		$q = db_phquery($sql, $addressID);
		$row=db_fetch_row($q);
		return $row;
	}else{
		return false;
	}
}


function regGetAddressByLogin( $addressID, $login )
{
	$customerID = regGetIdByLogin( $login );
	$address = regGetAddress( $addressID );
	if ( (int)$address["customerID"] == (int)$customerID )
		return $address;
	else
		return false;
}

function regGetAllAddressesByID($customerID){
	
	$customerID = (int) $customerID;
	if ($customerID == 0) return NULL;

	$q = db_query( "select addressID, first_name, last_name, countryID, zoneID, zip, state, city, address ".
					" from ".
					CUSTOMER_ADDRESSES_TABLE." where customerID=$customerID order by addressID asc" );
	$data = array();
	while( $row = db_fetch_row($q) )
	{

		if ( $row["countryID"] != null )
		{
			$q1=db_query("select * from ".COUNTRIES_TABLE." where countryID=".$row["countryID"] );
			$country = db_fetch_assoc($q1);
			LanguagesManager::ml_fillFields(COUNTRIES_TABLE, $country);
			$row["country"] = $country['country_name'];
		}
		else
			$row["country"] = "-";

		if ( $row["zoneID"] != null )
		{
			$q1 = db_query("select * from ".ZONES_TABLE." where zoneID=".$row["zoneID"] );
	 		$zone = db_fetch_assoc( $q1 );
	 		LanguagesManager::ml_fillFields(ZONES_TABLE, $zone);
			$row["state"] = $zone['zone_name'];
		}

		$data[] = $row;
	}
	return $data;
}


function regGetAllAddressesByLogin( $log )
{

	return regGetAllAddressesByID(regGetIdByLogin( $log ));
}

function regGetDefaultAddressIDByLogin( $log ){
	
	$q = db_phquery('SELECT addressID FROM ?#CUSTOMERS_TABLE WHERE Login=?',$log);
	if ( $row = db_fetch_row( $q ) )
		return $row[0];
	else
		return null;
}

function regSetDefaultAddressIDByLogin( $log, $defaultAddressID ){
	$sql = '
		UPDATE ?#CUSTOMERS_TABLE SET addressID=? WHERE Login=?
	';
	db_phquery($sql,$defaultAddressID,$log);
}


function _testStrInvalidSymbol( $str )
{
	$res = strstr( $str, "'" );
	if ( is_string($res) )
		return false;

	$res = strstr( $str, "\\" );
	if ( is_string($res) )
		return false;

	$res = strstr( $str, '"' );
	if ( is_string($res) )
		return false;

	$res = strstr( $str, "<" );
	if ( is_string($res) )
		return false;

	$res = strstr( $str, ">" );
	if ( is_string($res) )
		return false;

	return true;
}

function _testStrArrayInvalidSymbol( $array )
{
	foreach( $array as $str )
	{
		$res = _testStrInvalidSymbol( $str );
		if ( !$res )
			return false;
	}
	return true;
}



// *****************************************************************************
// Purpose	verify address input data
// Inputs   
//				$first_name			- customer first name
//				$last_name			- customer last name
//				$countryID			- country ID
//				$zoneID 
//				$state 
//				$zip 
//				$city 
//				$address
// Remarks	
// Returns	empty string if success, error message otherwise
function regVerifyAddress($first_name, $last_name,	$countryID, $zoneID, $state, $zip, $city, $address ){
	
	$error = '';
	if ( trim($first_name) == '' ){
		$error = translate("err_input_name");
	}elseif ( trim($last_name) == '' ){
		$error = translate("err_input_name");
	}elseif ( (CONF_ADDRESSFORM_ADDRESS == 0) && (trim($address)=='')){
		$error = translate("err_input_address");
	}elseif ( (CONF_ADDRESSFORM_CITY == 0) && (trim($city) == '')){
		$error = translate("err_input_city");
	}elseif ( (CONF_ADDRESSFORM_STATE == 0) && (trim($state) == '') && ($zoneID == 0) ){
		$error = translate("err_input_state");
	}elseif ( (CONF_ADDRESSFORM_ZIP == 0) && (trim($zip) == '')){
		$error = translate("err_input_zip");
	}
	
	$sql = 'SELECT COUNT(*) FROM ?#ZONES_TABLE WHERE countryID=?';
	$q = db_phquery($sql,$countryID);
	$r = db_fetch_row( $q );
	$countZone = $r[0];

	if ( $countZone != 0 ){
		
		$sql = 'SELECT COUNT(*) FROM ?#ZONES_TABLE WHERE zoneID=? AND countryID=?';
		$q = db_phquery($sql,$zoneID,$countryID);
		$r = db_fetch_row( $q );
		if ( ($r[0] == 0) && (CONF_ADDRESSFORM_STATE == 0)){
			$error = translate("err_region_does_not_belong_to_country");
		}
		//
		/*elseif($zoneID!=0){
			$error = translate("err_input_state");
		}
		*/
	}

	return $error;
}

function regGetContactInfo( $login, &$cust_password, &$Email, &$first_name, 
				&$last_name, &$subscribed4news, &$additional_field_values ){
	if($login === false)return;
	$q=db_phquery('SELECT customerID, cust_password, Email, first_name, last_name, subscribed4news,custgroupID,addressID FROM ?#CUSTOMERS_TABLE WHERE Login=?',$login);
	$row = db_fetch_row( $q );
	if(!is_array($row))return;
	$cust_password				= Crypt::PasswordDeCrypt( $row["cust_password"], null );
	$Email						= ( $row["Email"] );
	$first_name					= ( $row["first_name"] );
	$last_name					= ( $row["last_name"] );
	$subscribed4news			= $row["subscribed4news"];
	$additional_field_values	= GetRegFieldsValues($login);
}

function regVerifyContactInfo( $login, $cust_password1, $cust_password2, $Email, $first_name, $last_name, $subscribed4news, $additional_field_values ){
	
	$error = "";
	if ( 
			!_testStrArrayInvalidSymbol( 
										array( $login, $cust_password1, $cust_password2 ) 
									) 
		)
		$error = translate("err_invalid_symbols_in_login_or_password");
	else
	if ( trim($login) == "" ) $error = translate("err_input_login");
	else
	if (!(((ord($login)>=ord("a")) && (ord($login)<=ord("z"))) ||
			((ord($login)>=ord("A")) && (ord($login)<=ord("Z")))))
				$error = translate("err_login_should_start_with_latin_symbol");
	else
	if ( $cust_password1 == "" ||  $cust_password2 == "" || $cust_password1 != $cust_password2 )
		$error = translate("err_password_confirm_failed");
	else
	if ( trim($first_name) == "" ) $error = translate("err_input_name");
	else
	if ( trim($last_name) == "" ) $error = translate("err_input_name");
	else
	if ( trim($Email) == "" ) $error = translate("err_input_email");
	else if(!preg_match("/^[a-z0-9_-]+(\.[a-z0-9_-]+)*@([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i", $Email) )
	{ //e-mail validation
		$error = translate("err_input_email");
	}

	$dbq = 'SELECT 1 FROM ?#CUSTOMERS_TABLE WHERE Email=? AND login<>"" AND login<>?';

	$is_free_email = !db_phquery_fetch(DBRFETCH_FIRST, $dbq, $Email, $login);
	if(!$is_free_email){
		return translate('err_occupied_email');
	}
	
	if (isset($_POST['affiliationLogin']))
	if ( !regIsRegister($_POST['affiliationLogin']) && $_POST['affiliationLogin'])
			$error = translate('err_wrong_referrer');

	//aux fields
	foreach($_POST as $key => $val)
	{
		if (strstr($key,"additional_field_"))
		{
			$id = (int) str_replace("additional_field_","",$key);
			if (GetIsRequiredRegField($id) && strlen(trim($val))==0)
				$error = translate('err_input_all_required_fields');
		}
	}
	
	return $error;
}


function regUpdateContactInfo( $old_login, $login, $cust_password,
						$Email, $first_name, $last_name, $subscribed4news, 
						$additional_field_values ){

	$sql = '
		UPDATE ?#CUSTOMERS_TABLE SET Login=?, cust_password=?, Email=?, first_name=?, last_name=?, subscribed4news=?
		WHERE Login=?
	';
	db_phquery($sql, $login,Crypt::PasswordCrypt( $cust_password, null ),$Email, $first_name, $last_name, $subscribed4news, $old_login );
	foreach( $additional_field_values as $key => $val )
		SetRegField($key, $login, $val["additional_field"]);

	$customerID = regGetIdByLogin( $login );



	if ( $subscribed4news )
		subscrAddRegisteredCustomerEmail( $customerID );
	else
		subscrUnsubscribeSubscriberByEmail( base64_encode($Email) );
}


// *****************************************************************************
// Purpose	get address string by address ID
// Inputs   
// Remarks	
// Returns	
function regGetAddressStr( $addressID, $NoTransform = false ){
	
	$address = regGetAddress( $addressID,$NoTransform);
	if(!is_array($address))return '';
	
	// countryID, zoneID, zip, state
	$country = cnGetCountryById( $address["countryID"] );
	$country = $country["country_name"];
	if ( trim($address["state"]) == "" )
	{
		$zone = znGetSingleZoneById( $address["zoneID"] );
		$zone = $zone["zone_name"];
	}
	else{
		$zone = trim($address["state"]);
	}
	
	if(!$NoTransform){
		$address = xHtmlSpecialChars($address);
		$zone = xHtmlSpecialChars($zone);
		$country = xHtmlSpecialChars($country);
	}

	if ( $country != "" )
	{
		$strAddress = $address["first_name"]."  ".$address["last_name"];
		if (strlen($address["address"])>0) $strAddress .= "<br>".$address["address"];
		if (strlen($address["city"])>0) $strAddress .= "<br>".$address["city"];
		if (strlen($zone)>0) $strAddress .= "  ".$zone;
		if (strlen($address["zip"])>0) $strAddress .= "  ".$address["zip"];
		if (strlen($country)>0) $strAddress .= "<br>".$country;
	}
	else
	{
		$strAddress = $address["first_name"]."  ".$address["last_name"];
		if (strlen($address["address"])>0) $strAddress .= "<br>".$address["address"];
		if (strlen($address["city"])>0) $strAddress .= "<br>".$address["city"];
		if (strlen($zone)>0) $strAddress .= " ".$zone;
		if (strlen($address["zip"])>0) $strAddress .= " ".$address["zip"];
	}

	return $strAddress;
}


// *****************************************************************************
// Purpose	gets all customers 
// Inputs   
// Remarks	
// Returns	
function regGetCustomers( $callBackParam, &$count_row, $navigatorParams = null )
{
	if ( $navigatorParams != null )
	{
		$offset		= $navigatorParams["offset"];
		$CountRowOnPage	= $navigatorParams["CountRowOnPage"];
	}
	else
	{
		$offset = 0;
		$CountRowOnPage = 0;
	}

	$where_clause = "";
	if ( isset($callBackParam["Login"]) )
	{
		$callBackParam["Login"] = xEscapeSQLstring($callBackParam["Login"] );
		$where_clause .= " Login LIKE '%".$callBackParam["Login"]."%' ";
	}
		
	if ( isset($callBackParam["first_name"]) )
	{
		$callBackParam["first_name"] = xEscapeSQLstring($callBackParam["first_name"] );
		$callBackParam["first_name"] = str_replace('\\', '\\\\', $callBackParam["first_name"]);
		if ( $where_clause != "" ) $where_clause .= " AND ";
		$where_clause .= " first_name LIKE '%".$callBackParam["first_name"]."%' ";
	}

	if ( isset($callBackParam["last_name"]) )
	{
		$callBackParam["last_name"] = xEscapeSQLstring( $callBackParam["last_name"] );
		$callBackParam["last_name"] = str_replace('\\', '\\\\', $callBackParam["last_name"]);
		if ( $where_clause != "" ) $where_clause .= " AND ";
		$where_clause .= " last_name LIKE '%".$callBackParam["last_name"]."%' ";
	}

	if ( isset($callBackParam["email"]) )
	{
		$callBackParam["email"] = xEscapeSQLstring( $callBackParam["email"] );
		if ( $where_clause != "" ) $where_clause .= " AND ";
		$where_clause .= " Email LIKE '%".$callBackParam["email"]."%' ";
	}
	
	if ( isset($callBackParam["groupID"]) )
	{
		if ( $callBackParam["groupID"] != 0 )
		{
			if ( $where_clause != "" ) $where_clause .= " AND ";
			$where_clause .= " custgroupID = ".$callBackParam["groupID"]." ";
		}
	}
	
	if ( isset($callBackParam["ActState"]) )
	{
		switch ($callBackParam["ActState"]){
			
			#activated
			case 1:
				if ( $where_clause != "" ) $where_clause .= " AND ";
				$where_clause .= " (ActivationCode='' OR ActivationCode IS NULL)";
				break;
			#not activated
			case 0:
				if ( $where_clause != "" ) $where_clause .= " AND ";
				$where_clause .= " ActivationCode<>''";
				break;
		}
	}
	
	if ( $where_clause != "" )
		$where_clause = " where ".$where_clause;


	$order_clause = "";
	if ( isset($callBackParam["sort"]) )
	{
		$order_clause .= " order by ".$callBackParam["sort"]." ";
		if ( isset($callBackParam["direction"]) )
		{
			if ( $callBackParam["direction"] == "ASC" )
				$order_clause .=  " ASC ";
			else
				$order_clause .=  " DESC ";
		}
	}
	

	
	$sql = "select customerID, Login, cust_password, Email, first_name, last_name, subscribed4news, ".
		 " custgroupID, addressID, reg_datetime, ActivationCode ".
		 " from ".CUSTOMERS_TABLE." ".$where_clause." ".$order_clause;
	$q=db_query( $sql );
	$data = array();
	$i=0;
	while( $row=db_fetch_row($q) )
	{
		
		if ( ($i >= $offset && $i < $offset + $CountRowOnPage) || 
				$navigatorParams == null  )
		{
			$group = GetCustomerGroupByCustomerId( $row["customerID"] );
			$row["custgroup_name"] = $group["custgroup_name"];
			$row["allowToDelete"]  = regVerifyToDelete( $row["customerID"] );
			$row["reg_datetime"]  = Time::standartTime( $row["reg_datetime"] );
			$data[] = $row;
		}
		$i++;
	}
	$count_row = $i;
	return $data;
}


function regSetSubscribed4news( $customerID, $value )
{
	db_query( "update ".CUSTOMERS_TABLE." set subscribed4news = ".$value.
			" where customerID=".$customerID );
	if ($value > 0)
	{
		subscrAddRegisteredCustomerEmail($customerID);
	}
	else
	{
		subscrUnsubscribeSubscriberByCustomerId($customerID);
	}
}

function regSetCustgroupID( $customerID, $custgroupID )
{
	$customerID  = (int) $customerID;
	$custgroupID = (int) $custgroupID;
	db_query( "update ".CUSTOMERS_TABLE." set custgroupID=".$custgroupID.
			" where customerID=".$customerID );
}


// *****************************************************************************
// Purpose	 
// Inputs   
// Remarks	
// Returns	true if customer has address specified by $addressID
function regAddressBelongToCustomer( $customerID, $addressID )
{
	$customerID = (int) $customerID;
	if (!$customerID) return false;
	$addressID  = (int) $addressID;
	if (!$addressID) return false;

	$q_count = db_query( "select count(*) from ".CUSTOMER_ADDRESSES_TABLE.
		" where customerID=".$customerID." AND addressID=".$addressID );
	$count = db_fetch_row( $q_count );
	$count = $count[0];
	return ( $count != 0 );
}



// *****************************************************************************
// Purpose	 
// Inputs   
// Remarks	
// Returns	true if customer has address specified by $addressID
function regVerifyToDelete( $customerID )
{
	$customerID = (int) $customerID;
	if (!$customerID) return 0;

	$q = db_query( "select count(*) from ".CUSTOMERS_TABLE." where customerID=".$customerID );
	$row = db_fetch_row($q);

	if ( regIsAdminiatrator(regGetLoginById($customerID))  )
		return false;

	return ($row[0] == 1);
}

// Returns	true if customer has address specified by $addressID
function regDeleteCustomer( $customerID ){
	
	if ( $customerID == null || trim($customerID) == ""  )
		return false;

	$customerID = (int) $customerID;
	if (!$customerID) return 0;

	if ( regVerifyToDelete( $customerID ) )
	{
		db_query( "delete from ".SHOPPING_CARTS_TABLE." where customerID=".$customerID );
		db_query( "delete from ".MAILING_LIST_TABLE." where customerID=".$customerID );
		db_query( "delete from ".CUSTOMER_ADDRESSES_TABLE." where customerID=".$customerID );
		db_query( "delete from ".CUSTOMER_REG_FIELDS_VALUES_TABLE." where customerID=".$customerID );
		db_query( "delete from ".CUSTOMERS_TABLE." where customerID=".$customerID );
		db_query( "update ".ORDERS_TABLE." set customerID=NULL where customerID=".$customerID );
		return true;
	}
	else
		return false;
}

function regActivateCustomer($_CustomerID){
	
	$sql = '
		UPDATE '.CUSTOMERS_TABLE.'
		SET ActivationCode = ""
		WHERE customerID="'.xEscapeSQLstring($_CustomerID).'"
	';
	db_query($sql);
}

function customerWithEmailExists($email){

	$dbres = db_phquery('SELECT 1 FROM ?#CUSTOMERS_TABLE WHERE `Email`=? AND Login<>""', $email);
	return db_num_rows($dbres['resource']);
}

function _addNewNoLoginCustomer($customer_info, $reg_fields, $address)
{
    $customer_info = xStripSlashesGPC($customer_info);
    $address = xStripSlashesGPC($address);
    $reg_fields = xStripSlashesGPC($reg_fields);
    
	$custgroupID = CONF_DEFAULT_CUSTOMER_GROUP;
	if ( $custgroupID == 0 )
		$custgroupID = "NULL";
		
	if(isset($_SESSION["current_currency"])){
		$currencyID = $_SESSION["current_currency"];
	}else{
		$currencyID = defined('CONF_DEFAULT_CURRENCY')?constant('CONF_DEFAULT_CURRENCY'):null;
	}
	if(is_null($currencyID)){
		$q = db_phquery( "select CID from ?#CURRENCY_TYPES_TABLE LIMIT 1" );
		$currencyID = "NULL";
		if ( $currency = db_fetch_row($q) )
			$currencyID = $currency["CID"];
	}
	
    $sql = "insert into ".CUSTOMERS_TABLE.
           " (Email, first_name, last_name, subscribed4news, custgroupID, reg_datetime, CID) ".
           "values (?, ?, ?, 0, ?, ?, ?)";
    db_phquery($sql,$customer_info['email'], $customer_info['first_name'], $customer_info['last_name'],$custgroupID,Time::dateTime(),$currencyID);
    
    $customer_id = db_insert_id();
    
    if(!empty($reg_fields))
    {
        $strs = array();
        foreach($reg_fields as $rf_id => $rf_val)
        {
        	$rf_id = intval($rf_id);
        	$rf_val = mysql_real_escape_string($rf_val);
        	if($rf_id&&strlen($rf_val))
            $strs[] = "({$rf_id}, {$customer_id}, '{$rf_val}')";
        };
        $sql = "insert into ".CUSTOMER_REG_FIELDS_VALUES_TABLE." (reg_field_ID, customerID, reg_field_value) values ".implode(', ', $strs);
        
        db_query($sql);
    };
    
    $sql = "insert into ".CUSTOMER_ADDRESSES_TABLE.
           " (customerID, first_name, last_name, countryID, zoneID, zip, state, city, address)".
           "values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    db_phquery($sql,
    			$customer_id, $customer_info['first_name'], $customer_info['last_name'],
    			$address['country_id'],($address['state_id']?$address['state_id']:'NULL'),
    			$address['zip'],$address['state'],$address['city'],$address['address']);
    $address_id = db_insert_id();
    
    $sql = "update ?#CUSTOMERS_TABLE set addressID = ? where customerID = ?";
    db_phquery($sql,$address_id,$customer_id);
    
    return $customer_id;
}

function _getBaseCustomerInfo($customer_id)
{
    $sql = "select * from ".CUSTOMERS_TABLE." where customerID = {$customer_id}";
    $res = db_query($sql);
    $row = db_fetch_assoc($res);
    return $row;
}
?>