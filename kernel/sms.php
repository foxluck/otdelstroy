<?php
	function getSMSBalanceHistory( $U_ID = "" )
	//
	// Returns SMS Balance History records
	//
	//		Parameters:
	//			$U_ID - user id
	//
	//		Returns array, or PEAR_Error
	//
	{
		global	$qr_selectSMSBalanceHistory;
		global	$qr_selectSMSUserBalanceHistory;

		$query = ( $U_ID == "" ) ? $qr_selectSMSBalanceHistory : $qr_selectSMSUserBalanceHistory;

		$params = array( "U_ID"=>$U_ID );

		$qr = db_query( $query, $params );
		if ( PEAR::isError( $qr ) )
			return $qr;

		$result = array();

		while ( $row = db_fetch_array($qr) )
			$result[] = $row;

		db_free_result( $qr );

		return $result;
	}


	function getSMSHistorySum( $to = "", $from = "" )
	//
	// Returns SMS charge sums divided to charged and reserved
	//
	//		Parameters:
	//			$to - to date
	//			$from - from date
	//
	//		Returns array, or PEAR_Error
	//
	{
		global	$qr_selectSMSSum;
		global	$qr_selectSMSSumTo;
		global	$qr_selectSMSSumFrom;
		global	$qr_selectSMSSumBetween;


		if ( $from == "" && $to == "" )
			$query = $qr_selectSMSSum;
		else
		{
			if ( $from == "" )
				$query = $qr_selectSMSSumTo;
			else if ( $to == "" )
				$query = $qr_selectSMSSumFrom;
			else
				$query = $qr_selectSMSSumBetween;
		}

		$params = array( "FROM"=>$from, "TO"=>$to );

		$qr = db_query( $query, $params );
		if ( PEAR::isError( $qr ) )
			return $qr;

		$result = array( 0, 0 );

		while ( $row = db_fetch_array($qr) )
			$result[$row["SMSH_CHARGED"]] = $row["SM"];

		db_free_result( $qr );

		return $result;
	}


	function getSMSHistoryCount( $U_ID = "" )
	//
	// Returns count of sent SMSs for $U_ID user or for all SMS history
	//
	//		Parameters:
	//			$U_ID - user id
	//
	//		Returns array, or PEAR_Error
	//
	{
		global $qr_getSMSHistoryCount;
		global $qr_getSMSUserHistoryCount;

		return db_query_result( $U_ID == "" ? $qr_getSMSHistoryCount : $qr_getSMSUserHistoryCount, DB_FIRST, array("U_ID"=>$U_ID) );
	}

	function getSMSHistory( $U_ID = "", $to = "", $from = "" )
	//
	// Returns SMS History records
	//
	//		Parameters:
	//			$U_ID - user id
	//			$from - from date
	//			$to - to date
	//
	//		Returns array or PEAR_Error
	//
	{
		global	$qr_selectSMSHistory;
		global	$qr_selectSMSHistoryTo;
		global	$qr_selectSMSHistoryFrom;
		global	$qr_selectSMSHistoryBetween;
		global	$qr_selectSMSUserHistory;
		global	$qr_selectSMSUserHistoryTo;
		global	$qr_selectSMSUserHistoryFrom;
		global	$qr_selectSMSUserHistoryBetween;

		if ( $U_ID == "" )
		{
			if ( $from == "" && $to == "" )
				$query = $qr_selectSMSHistory;
			else
			{
				if ( $from == "" )
					$query = $qr_selectSMSHistoryTo;
				else if ( $to == "" )
					$query = $qr_selectSMSHistoryFrom;
				else
					$query = $qr_selectSMSHistoryBetween;
			}
		}
		else
		{
			if ( $from == "" && $to == "" )
				$query = $qr_selectSMSUserHistory;
			else
			{
				if ( $from == "" )
					$query = $qr_selectSMSUserHistoryTo;
				else if ( $to == "" )
					$query = $qr_selectSMSUserHistoryFrom;
				else
					$query = $qr_selectSMSUserHistoryBetween;
			}
		}

		$params = array( "U_ID"=>$U_ID, "FROM"=>$from, "TO"=>$to );


		$qr = db_query( $query, $params );
		if ( PEAR::isError( $qr ) )
			return $qr;

		$result = array();

		while ( $row = db_fetch_array($qr) )
			$result[] = $row;

		db_free_result( $qr );

		return $result;
	}

	function getSMSBalance( $U_ID )
	//
	// Gets user's SMS balance
	//
	//	Parameters:
	//
	//		$U_ID - user ID
	//
	//		Returns null, array of balance or PEAR_Error
	//
	{
		global $qr_getSMSBalance;

		$params = array( "SMS_USER_ID"=>$U_ID );
		$qr = db_query( $qr_getSMSBalance, $params );

		if ( PEAR::isError( $qr ) )
			return $qr;

		$numrows = db_result_num_rows( $qr );
		if ( $numrows == 0 )
		{
			if ( $U_ID != SMS_SYSTEM_USER )
				addsetSMSBalance( "SET", null, "AUTO", $U_ID, false );

			return null;
		}

		return( db_fetch_array($qr) );
	}

	function getSMSFromHistory( $ID )
	//
	// Gets user's SMS balance
	//
	//	Parameters:
	//
	//		$U_ID - user ID
	//
	//		Returns null, array of balance or PEAR_Error
	//
	{
		global $qr_getSMS;

		$params = array( "SMSH_ID"=>$ID );
		$qr = db_query( $qr_getSMS, $params );

		if ( PEAR::isError( $qr ) )
			return $qr;

		$numrows = db_result_num_rows( $qr );
		if ( $numrows == 0 )
			return null;

		return( db_fetch_array($qr) );
	}

	function addsetSMSBalance( $action, $qty, $source = "WA-ADMIN", $U_ID = SMS_SYSTEM_USER, $existing = true )
	//
	// Adds or sets system's SMS balance
	//
	//	Parameters:
	//
	//		$action - ADD or SET
	//		$qty - value to add or set
	//		$source - application balance was changed from
	//		$U_ID - user ID
	//		$existing - if true do not request smsBalanceValue - it is internal parameter for getSMSBalance
	//
	//		Returns PEAR_Error, or nothing
	//
	{
		global $qr_addSMSBalanceHistoryRecord;
		global $qr_addSMSBalance;
		global $qr_modSMSBalance;

		$balance = null;

		if ( $existing )
		{
			$balance = getSMSBalance( $U_ID );
			if (PEAR::isError( $balance ) )
				return $balance;

			$query = $qr_modSMSBalance;
		}
		else
			$query = $qr_addSMSBalance;

		if ( is_null( $balance ) )
			$balance = array( "SMS_SENT"=>0, "SMS_BALANCE"=>0 );

		if ( $action == "SET" )
		{
			if ( is_null( $qty ) )
				$balQTY = null;
			else
				$balQTY = ( $U_ID == SMS_SYSTEM_USER ) ? floatval( $qty ) : intval( $qty );
		}
		else
		{
			if ( is_null( $balance["SMS_BALANCE"] ) )
				$balQTY = null;
			else
				$balQTY = $balance["SMS_BALANCE"]+ ( ( $U_ID == SMS_SYSTEM_USER ) ? floatval( $qty ) : intval( $qty ) );
		}

		$params = array(
							'SMS_USER_ID'=>$U_ID,
							'SMS_SENT'=>$balance["SMS_SENT"],
							'SMS_BALANCE'=>$balQTY
		);

		// Set or modify user's balance

		$qr = db_query( $query, $params );

		if ( PEAR::isError( $qr ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $U_ID == SMS_SYSTEM_USER )
		{
			$params = array(
							'SMSG_USER_ID'=>$U_ID,
							'SMSG_QTY'=>is_null( $balQTY ) ? null : ( ( $U_ID == SMS_SYSTEM_USER ) ? floatval( $qty ) : intval( $qty ) ),
							'SMSG_QS'=>$action,
							'SMSG_SOURCE'=>$source
			);

			// Add entry to QUOTES history

			$qr = db_query( $qr_addSMSBalanceHistoryRecord, $params );
			if ( PEAR::isError( $qr ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		return true;
	}

	function getSMSUsersBalance( $kernelStrings )
	//
	// Gets all users' SMS balance list
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_getSMSUsersBalance;


		$qr = db_query( $qr_getSMSUsersBalance, array() );
		if ( PEAR::isError( $qr ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr) )
		{
//			if ( is_null( $row["SMS_BALANCE"] ) )
//				$row["SMS_BALANCE"] = "";

			if ( is_null( $row["SMS_SENT"] ) )
				$row["SMS_SENT"] = 0;

			$result[$row["U_ID"]] = $row;
		}

		db_free_result( $qr );

		return $result;
	}


	function subSMSBalanceValue( $U_ID, $msgQty, $charge, $kernelStrings )
	//
	// Substract desired message quantity from user's and system's balances
	//
	//		Parameters:
	//			$U_ID - user id
	//			$msgQty - quantity of sent messages
	//			$charge - amount to subscribe from current value of balance
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns true or PEAR_Error
	//
	{
		global $qr_subSMSSystemBalance;
		global $qr_subSMSUserBalance;

		$params = array(
					"SYS_USER" => '$SYSTEM',
					"SMS_USER_ID" => $U_ID,
					"CHARGE" => $charge,
					"QTY" => $msgQty
		);

		$qr = db_query( $qr_subSMSSystemBalance, $params );
		if ( PEAR::isError( $qr ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $U_ID != SMS_SYSTEM_USER )
		{
			$qr = db_query( $qr_subSMSUserBalance, $params );
			if ( PEAR::isError( $qr ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		return true;
	}


	function addToSMSHistory( $historyData, $kernelStrings )
	//
	// Adds history record into SMS messages log
	//
	//		Parameters:
	//			$historyData
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns true or PEAR_Error
	//
	{
		global $qr_addSMSHistory;

		$qr = db_query( $qr_addSMSHistory, $historyData );

		if ( PEAR::isError( $qr ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return true;
	}

	function chargeUsersSMS( $U_ID = "" )
	//
	// Charge Pending users' sms's
	//
	//		Parameters:
	//			$U_ID - user id
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_modSMSHistory;
		global $qr_subSMSSystemBalance;
		global $qr_selectSMSUserHistoryStatus;
		global $qr_selectSMSHistoryStatus;
		global $WBS_MODULES;

		$params = array(
						"U_ID"=>$U_ID,
						"SMSH_STATUS"=>SMS_STATUS_PENDING
						);

		$query = ( $U_ID == "" ) ? $qr_selectSMSHistoryStatus : $qr_selectSMSUserHistoryStatus;

		$qr = db_query( $query, $params );
		if ( PEAR::isError( $qr ) )
			return $qr;

		$class = &$WBS_MODULES->getClass( MODULE_CLASS_SMS );
		if ( PEAR::isError( $class ) )
			return $class;

		$charged = 0;

		while ( $row = db_fetch_array($qr) )
		{
			$obj = &$class->getModule( $row["SMSH_MODULEID"] );

			if ( PEAR::isError( $obj ) )
				continue;

			$instance = &$obj->getInstance();

			if ( $chargable = method_exists( $instance, "chargeSMS" ) )
			{
				$charge = $instance->chargeSMS( $row["SMSH_MSGID"] );

				$params = $row;
				$params["SMSH_STATUS"] = $charge["STATUS"];
				$params["SMSH_STATUS_TEXT"] = isset( $charge["STATUS_TEXT"] ) ? $charge["STATUS_TEXT"] : null ;

				$doRecharge = false;

				switch( $charge["STATUS"] )
				{
					case SMS_STATUS_DELIVERED:

							$doRecharge = $charge["CHARGE"] > 0;
							if ($doRecharge)
								$params["SMSH_CHARGE"] = $charge["CHARGE"];
							$params["SMSH_CHARGED"] = 1;


							break;

					case SMS_STATUS_CANCELED:

							$params["SMSH_CHARGE"] = 0;
							$params["SMSH_CHARGED"] = 1;
							$charge["CHARGE"] = 0;

							$doRecharge = true;

							break;

					case SMS_STATUS_PENDING:

							if ( time()-$row["TIMESTAMP"] > SMS_CANCELING_TIMEOUT )
							{
								$params["SMSH_CHARGE"] = 0;
								$params["SMSH_CHARGED"] = 1;
								$params["SMSH_STATUS"] = SMS_STATUS_CANCELED;
								$charge["CHARGE"] = 0;

								$doRecharge = true;
							}

							break;
				}

				$q = db_query( $qr_modSMSHistory, $params );
				if ( PEAR::isError( $q ) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( $doRecharge )
				{

					$params = array(
						"SYS_USER" => '$SYSTEM',
						"SMS_USER_ID" => $U_ID,
						"CHARGE" => $charge["CHARGE"] - $row["SMSH_CHARGE"],
					);

					$q = db_query( $qr_subSMSSystemBalance, $params );
					if ( PEAR::isError( $q ) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

					++$charged;
				}
				else
					continue;
			}
			else
				continue;

		}

		db_free_result( $qr );

		return $charged;
	}

	function roundCharge( $i )
	{
		return ( floor( $i * 100  ) / 100 ) + ( ( ( floor( round( $i, 3 )* 1000 ) % 10 ) == 0 ) ? 0 : 0.01 );
	}

	function sendSMS( $U_ID, $to, $message, $app, $kernelStrings, $from="" )
	//
	//	Sends SMS
	//
	//		Parameters:
	//			$U_ID - user id
	//			$to - phone number
	//			$message - message text
	//			$app - application message was sent from
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns value returned by SMS module or PEAR_error
	{
		global $WBS_MODULES;

		$obj = &$WBS_MODULES->getDefaultModule( MODULE_CLASS_SMS );

		if ( PEAR::isError( $obj ) )
			return $obj;

		$instance = &$obj->getInstance();

		$cost = $instance->getSMSCost( );
		$reserve = $instance->getSMSReserve( );

		$message = substr( $message, 0, $instance->getSMSLength() );

		if ( method_exists( $instance, "getSMSPartLength" ) )
		{
			$partLen = $instance->getSMSPartLength( );
			$msgQty = ceil( strlen( $message ) / $partLen );
		}
		else
			$msgQty = 1;

		$balance = getSMSBalance( SMS_SYSTEM_USER );
		if (PEAR::isError( $balance ) )
			return $balance;

		if ( !is_null( $balance ) )
		{
			$systemBalance = $balance["SMS_BALANCE"];
			if ( !is_null( $systemBalance ) && $systemBalance - $msgQty*$reserve < 0 )
				return PEAR::raiseError( $kernelStrings["app_sms_out_system_balance"] );
		}
		else
			return PEAR::raiseError( $kernelStrings["app_sms_out_system_balance"] );

		$balance = getSMSBalance( $U_ID );
		if (PEAR::isError( $balance ) )
			return $balance;

		if ( !is_null( $balance ) )
		{
			$userBalance = $balance["SMS_BALANCE"];
			if ( !is_null( $userBalance ) && $userBalance < 1 )
				return PEAR::raiseError( $kernelStrings["app_sms_out_user_balance"] );
		}

		$len = strlen( $to );
		$toPhone = "";
		for( $i=0; $i<$len; $i++ )
		{
			if ( ereg( "([0-9])", $to{$i} ) )
				$toPhone .= $to{$i};
		}

		if ( strlen( $toPhone ) != 11 && strlen( $toPhone ) != 12 && strlen( $toPhone ) != 10)
			return PEAR::raiseError( $kernelStrings["app_sms_recipient_error"] );

		//$msgId = $instance->sendSMS( $toPhone, $message, $from );
		$ret = $instance->sendSMS( $toPhone, $message, $from );
		if (PEAR::isError( $ret ) )
			return $ret;
		if (is_array($ret) && count($ret) > 1) {
			$msgId = $ret[0];
			$charge_value = $ret[1];
		} else {
			$msgId = $ret;
			$charge_value = 0;
		}
		$charged = true;
		$historyData = array(
					"SMSH_USER_ID" => $U_ID,
					"SMSH_PHONE" => $toPhone,
					"SMSH_WIDTH"=>strlen( $message ),
					"SMSH_TEXT"=>$message,
					"SMSH_APP"=>$app,
					"SMSH_QTY"=>$msgQty,
					"SMSH_MODULEID"=>$obj->getID(),
					"SMSH_MSGID"=>$msgId,
					"SMSH_UNLIM"=>is_null( $systemBalance ) ? '1' : '0',
					"SMSH_CHARGED"=>'1',
					"SMSH_STATUS"=>'',
					"SMSH_STATUS_TEXT"=>''
		);

		if ( $chargable = method_exists( $instance, "chargeSMS" ) )
		{
			$charge = $instance->chargeSMS( $msgId );
			if ( $charge["CHARGED"] == 0 )
				$historyData["SMSH_CHARGED"] = '0';

			$historyData["SMSH_STATUS"] = $charge["STATUS"];
			$historyData["SMSH_STATUS_TEXT"] = isset( $charge["STATUS_TEXT"] ) ? $charge["STATUS_TEXT"] : null ;

			$mn = ( $historyData["SMSH_CHARGED"] == '0' ) ? $msgQty : 1;
			if ($charge_value == 0)
				$charge_value = $charge["CHARGE"]*$mn;
		}
		else
			$charge_value = $cost;

		$historyData["SMSH_CHARGE"] = roundCharge( floatval( $charge_value ) );

		addToSMSHistory( $historyData, $kernelStrings );

		subSMSBalanceValue( $U_ID, 1, $historyData["SMSH_CHARGE"], $kernelStrings );

		return $historyData;
	}

?>