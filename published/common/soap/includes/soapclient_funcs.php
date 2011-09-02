<?php
	//	LOGIN_INFO record
	//
	//	Description
	//		Used to hold login information
	//
	//	Fields
	//		U_ID - user identifier (base64)
	//		PASSWORD - user password (md5, base64)
	//		lang - user language
	//
	$LOGIN_INFO = array('U_ID' => 'string', 'PASSWORD' => 'string', 'lang' => 'string');

	//	NAMED_ITEM record
	//
	//	Description
	//		Used to hold id=value pair
	//
	//	Fields
	//		Id - value identifier
	//		Value - string assigned to identifier
	//
	$NAMED_ITEM = array('Id' => 'string', 'Value' => 'string');

	//
	//	DATETIME_STRUCT record
	//
	//	Description
	//		Used to hold date
	//
	//	Fields
	//		Year - year
	//		Month - month
	//		Day - day
	//
	$DATETIME_STRUCT = array(
		'Year' => 'int', 
		'Month' => 'int', 
		'Day' => 'int', 
		);

	//
	//	ARRAY_OF_STRINGS array
	//
	//	Description
	//		Used to hold array of strings
	//
	$ARRAY_OF_STRINGS = array(
		array(
			'item' => 'string'
		)
	);


	function toSoapDate($dt)
	//	function toSoapDate();
	//
	//	Description
	//		used to covert given date to DATETIME_STRUCT record
	//
	//	Parameters
	//		$dt - linux timestamp
	//
	//	Returns 
	//		DATETIME_STRUCT record
	//
	{
		$r = localtime($dt, 1);
		return array(
			"Year" => $r["tm_year"] + 1900,
			"Month" => $r["tm_mon"] + 1,
			"Day" => $r["tm_mday"]
		);
	}

	function toLinuxDate($dt)
	//
	//	function toLinuxDate();
	//
	//	Description
	//		Coverts DATETIME_STRUCT to linux timestamp
	//
	//	Returns
	//		linux timestamp
	//
	{
		return mktime(0, 0, 0, $dt->Month, $dt->Day, $dt->Year);
	}

	function convertSoapToDisplayDate( $SoapDate)
	//
	//	function convertSoapToDisplayDate();
	//
	//	Description
	//		Converts DATETIME_STRUCT to string representation of date
	//
	//	Returns
	//		String representation of date
	//
	{
		if (is_null($SoapDate)) return "";
		if (($SoapDate->Month==0) && ($SoapDate->Day==0) && ($SoapDate==0))
			return "";
		else
			return displayDate(toLinuxDate($SoapDate));
	}

	function convertSqlToSoapDate( $str )
	//
	//	function convertSqlToSoapDate();
	//
	//	Description
	//		Converts value from date field of database to DATETIME_STRUCT
	//
	//	Returns
	//		DATETIME_STRUCT record
	//
	{
		if (strval($str)=="")
			return array(
				"Year" => 0,
				"Month" => 0,
				"Day" => 0
			);
		validateGeneralDateStr( $str, $month, $day, $year, DATE_SQL_OUTPUT_FORMAT, DATE_SQL_OUTPUT_DELIMITER );

		return array(
			"Year" => intval($year),
			"Month" => intval($month),
			"Day" => intval($day)
		);
	}

	function CheckSoapUser( $userdata )
	//
	//	function CheckSoapUser()
	//
	//	Description
	//		Authorization of user
	//
	//	Parameters
	//		$userdata - Record with fields U_ID, U_PASSWORD, LANGUAGE, U_STATUS, APP_ID, SCR_ID
	//
	//	Returns
	//		PEAR_Error or blank string
	//
	{
		global $qr_selectUserLoginInfo;
		global $loc_str;
		global $html_encoding;

		$U_ID = strtoupper( base64_decode($userdata["U_ID"]) );
		
		$html_encoding = getUserEncoding( $U_ID );

		$pwd = strtolower( base64_decode($userdata["U_PASSWORD"]) );
		$lang = $userdata["LANGUAGE"];

		if ($U_ID == ADMIN_USERNAME ) {
			$adminInfo = loadAdminInfo();

			if ( $adminInfo ) {
				if ($adminInfo[PASSWORD] == $pwd)
					return null;
			}
		}

		if (!userExists( $U_ID ))
			return PEAR::raiseError($loc_str[$lang]['app_invlogindata_message'], ERRCODE_APPLICATION_ERR);

		$u_data = db_query_result($qr_selectUserLoginInfo, DB_ARRAY, array("U_ID"=>$U_ID));

		if (PEAR::isError($u_data))
			return $u_data;

		if ($u_data["U_STATUS"] != RS_ACTIVE)
			return PEAR::raiseError($loc_str[$lang]['app_notactivelogin_message'], ERRCODE_APPLICATION_ERR);

		$thisSidePwd = strtolower($u_data["U_PASSWORD"]);
		if ($thisSidePwd != substr($pwd, 0, strlen($thisSidePwd)))
			return PEAR::raiseError($loc_str[$lang]['amu_invpassword_message'], ERRCODE_APPLICATION_ERR);

		if ( array_key_exists("APP_ID", $userdata) && array_key_exists("SCR_ID", $userdata))
		{
			if (!checkUserAccessRights( $U_ID, $userdata["SCR_ID"], $userdata["APP_ID"], false ))
				return PEAR::raiseError($loc_str[$lang]['app_generalaccess_message'], ERRCODE_APPLICATION_ERR);
		}
		
		return null;
	}

	function BooleanToInt($v) 
	//
	//	function booleanToInt();
	//
	//	Description
	//		Converts boolean to integer.
	//
	//	Parameters
	//		v - value to convert
	//
	//	Returns
	//		if true returns 1 else returns 0;
	//
	{
		if ($v) return 1;
		return 0;
	}

?>