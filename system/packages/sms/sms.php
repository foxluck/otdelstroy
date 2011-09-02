<?php

class SMS
{
	const SMS_SYSTEM_USER         = '$SYSTEM';
	const SMS_STATUS_DELIVERED    = 'DELIVERED';
	const SMS_STATUS_PENDING      = 'PENDING';
	const SMS_STATUS_CANCELED     = 'CANCELED';
	const SMS_STATUS_CHARGE_ERROR = 'CHARGE_ERROR';
	const SMS_CANCELING_TIMEOUT   = 86400;
//	const MAIN_XML_CONFIG         = 'kernel/wbs.xml';
	const SMS_XML_CONFIG          = 'kernel/includes/wbsmodules/wbsmodules.xml';

	public static function send($u_id, $to, $message, $app_id, $from = '')
	//
	//	Sends SMS
	//
	//		Parameters:
	//			$u_id - user id
	//			$to - phone number
	//			$message - message text
	//			$app_id - application message was sent from
	//
	//		Returns value returned by SMS module
	{
		if (!($sms_module = Wbs::getDbkeyObj()->getModule('sms'))) {
			exit('SMS module disabled');
		}

		// Read XML config
		//
		if (!file_exists(WBS_DIR.self::SMS_XML_CONFIG)) {
			exit ( 'File "'.self::SMS_XML_CONFIG.'" doesn\'t exist' );
		}
		$xml = simplexml_load_file(WBS_DIR.self::SMS_XML_CONFIG);

		$params = array();

		$params['module'] = (string)$xml->class->module->attributes()->ID;
		
		$result = $xml->xpath('//*[@ID="'.$params['module'].'"]/values');
		foreach($result[0] as $node) {
			$params[(string)($node->attributes()->name)] = (string)$node;
		}
		$params['unicode'] = 1;
		$params['debug'] = 1;

		// Load module instance
		//
		$instance = new $params['module'];
		$instance->params = $params;
		
		$cost = $instance->getSMSCost();
		$reserve = $instance->getSMSReserve( );

		$message = substr($message, 0, $instance->getSMSLength());

		if (method_exists( $instance, "getSMSPartLength")) {
			$msgQty = (int)ceil(strlen($message) / $instance->getSMSPartLength());
		} else {
			$msgQty = 1;
		}

		$balance = self::getSMSBalance('$SYSTEM');
		if ($balance) {
			$systemBalance = $balance['SMS_BALANCE'];
			if (!is_null($systemBalance) && $systemBalance - $msgQty*$reserve < 0 ) {
				exit(_('Available SMS balance has been exceeded.'));
			}
		} else {
			exit(_('Available SMS balance has been exceeded.'));
		}

		$balance = self::getSMSBalance( $u_id );
		if ($balance) {
			$userBalance = $balance['SMS_BALANCE'];
			if (!is_null($userBalance) && $userBalance < 1)
				exit(_('Your available SMS balance has been exceeded.'));
		}
	
		$len = strlen( $to );
		$toPhone = '';
		for($i=0; $i<$len; $i++)
		{
			if (ereg('([0-9])', $to{$i}))
				$toPhone .= $to{$i};
		}

		if (strlen($toPhone) != 11 && strlen($toPhone) != 12 && strlen($toPhone) != 10) {
			exit(_('Invalid phone number.'));
		}

		$ret = $instance->sendSMS($toPhone, $message, $from);

		if (is_array($ret) && count($ret) > 1) {
			$msgId = $ret[0];
			$status = $status_text = '';
			$charge_value = floatval(str_replace(',', '.', $ret[1]));
		} else {
			$msgId = '';
			$status = 'ERROR';
			$status_text = $ret;
			$charge_value = 0;
		}

		$charged = true;
		$historyData = array(
			"SMSH_USER_ID"     => $u_id,
			"SMSH_PHONE"       => $toPhone,
			"SMSH_WIDTH"       => strlen($message),
			"SMSH_TEXT"        => $message,
			"SMSH_APP"         => $app_id,
			"SMSH_QTY"         => $msgQty,
			"SMSH_MODULEID"    => $params['module'],
			"SMSH_MSGID"       => $msgId,
			"SMSH_UNLIM"       => is_null($systemBalance) ? '1' : '0',
			"SMSH_CHARGED"     => '1',
			"SMSH_STATUS"      => $status,
			"SMSH_STATUS_TEXT" => $status_text
		);

		if (method_exists($instance, 'chargeSMS') && $msgId) {

			$charge = $instance->chargeSMS($msgId);
			if ($charge["CHARGED"] == 0)
				$historyData["SMSH_CHARGED"] = '0';

			$historyData["SMSH_STATUS"] = $charge["STATUS"];
			$historyData["SMSH_STATUS_TEXT"] = isset($charge["STATUS_TEXT"]) ? $charge["STATUS_TEXT"] : null;

			$mn = ($historyData["SMSH_CHARGED"] == '0') ? $msgQty : 1;
			if ($charge_value == 0)
				$charge_value = $charge["CHARGE"]*$mn;
		}
		else {
			$charge_value = $cost;
		}
		
		$historyData["SMSH_CHARGE"] = $msgId ? self::roundCharge(floatval($charge_value)) : 0;

		self::addToSMSHistory($historyData);

		self::subSMSBalanceValue($u_id, 1, $historyData["SMSH_CHARGE"]);

		return $historyData;
	}

	public static function getSMSBalance($u_id)
	//
	// Gets user's SMS balance
	//
	//	Parameters:
	//
	//		$u_id - user ID
	//
	//		Returns null or array of balance
	//
	{
		$sql = "SELECT * FROM SMS_BALANCE WHERE SMS_USER_ID=s:SMS_USER_ID";

		$model = new DbModel();
		$res = $model->prepare($sql)->query(array('SMS_USER_ID'=>$u_id))->fetch();
		if(!$res) {
			if ($u_id != '$SYSTEM') {
				self::addsetSMSBalance('SET', null, 'AUTO', $u_id, false);
			}
			return null;
		}
		return($res);
	}

	public static function addsetSMSBalance($action, $qty, $source = 'WA-ADMIN', $u_id = '$SYSTEM', $existing = true)
	//
	// Adds or sets system's SMS balance
	//
	//	Parameters:
	//
	//		$action - ADD or SET
	//		$qty - value to add or set
	//		$source - application balance was changed from
	//		$u_id - user ID
	//		$existing - if true do not request smsBalanceValue - it is internal parameter for getSMSBalance
	//
	//		Returns nothing
	//
	{
		$qr_addSMSBalanceHistoryRecord = "INSERT INTO SMS_CREDIT_HISTORY ( SMSG_DATETIME, SMSG_USER_ID, SMSG_QTY, SMSG_QS, SMSG_SOURCE ) VALUES
			( NOW(), s:SMSG_USER_ID, s:SMSG_QTY, s:SMSG_QS, s:SMSG_SOURCE)";
		$qr_addSMSBalance = "INSERT INTO SMS_BALANCE ( SMS_USER_ID, SMS_SENT, SMS_BALANCE ) VALUES
			( s:SMS_USER_ID, s:SMS_SENT, s:SMS_BALANCE)";
		$qr_modSMSBalance = "UPDATE SMS_BALANCE SET SMS_SENT=s:SMS_SENT, SMS_BALANCE=s:SMS_BALANCE WHERE SMS_USER_ID=s:SMS_USER_ID";

		$balance = null;

		if ($existing) {
			$balance = getSMSBalance($u_id);
			$query = $qr_modSMSBalance;
		} else {
			$query = $qr_addSMSBalance;
		}

		if (is_null($balance)) {
			$balance = array( "SMS_SENT"=>0, "SMS_BALANCE"=>0 );
		}

		if ($action == "SET") {
			if (is_null($qty)) {
				$balQTY = null;
			} else {
				$balQTY = ($u_id == '$SYSTEM') ? floatval($qty) : intval($qty);
			}
		} else {
			if (is_null($balance["SMS_BALANCE"])) {
				$balQTY = null;
			} else {
				$balQTY = $balance["SMS_BALANCE"]+ (($u_id == '$SYSTEM') ? floatval($qty) : intval($qty));
			}
		}

		$params = array(
			'SMS_USER_ID' => $u_id,
			'SMS_SENT'    => $balance["SMS_SENT"],
			'SMS_BALANCE' => $balQTY
		);

		// Set or modify user's balance

		$model = new DbModel();
		$model->prepare($query)->exec($params);

		if ($u_id == '$SYSTEM') {
			$params = array(
				'SMSG_USER_ID' => $u_id,
				'SMSG_QTY'     => is_null($balQTY) ? null : (($u_id == '$SYSTEM') ? floatval($qty) : intval($qty)),
				'SMSG_QS'      => $action,
				'SMSG_SOURCE'  => $source
			);
			// Add entry to QUOTES history
			$model->prepare($qr_addSMSBalanceHistoryRecord)->exec($params);
		}
		return true;
	}

	public static function roundCharge($i)
	{
		return (floor($i * 100) / 100) + (((floor(round($i, 3) * 1000) % 10) == 0) ? 0 : 0.01);
	}

	public static function addToSMSHistory($historyData)
	//
	// Adds history record into SMS messages log
	//
	//		Parameters:
	//			$historyData
	//
	//		Returns true
	//
	{
		$sql = "INSERT INTO SMS_HISTORY
			(SMSH_DATETIME, SMSH_USER_ID, SMSH_PHONE, SMSH_WIDTH, SMSH_QTY, SMSH_APP, SMSH_MODULEID, SMSH_TEXT, SMSH_MSGID, SMSH_CHARGE, SMSH_CHARGED, SMSH_UNLIM, SMSH_STATUS, SMSH_STATUS_TEXT)
			VALUES
			(NOW(), s:SMSH_USER_ID, s:SMSH_PHONE, s:SMSH_WIDTH, s:SMSH_QTY, s:SMSH_APP, s:SMSH_MODULEID, s:SMSH_TEXT, s:SMSH_MSGID, s:SMSH_CHARGE, s:SMSH_CHARGED, s:SMSH_UNLIM, s:SMSH_STATUS, s:SMSH_STATUS_TEXT)";

		$model = new DbModel();
		$model->prepare($sql)->exec($historyData);

		return true;
	}

	public static function subSMSBalanceValue($u_id, $msgQty, $charge)
	//
	// Substract desired message quantity from user's and system's balances
	//
	//		Parameters:
	//			$u_id - user id
	//			$msgQty - quantity of sent messages
	//			$charge - amount to subscribe from current value of balance
	//
	//		Returns true
	//
	{
		$charge = str_replace(',', '.', $charge);

		$qr_subSMSSystemBalance = "UPDATE SMS_BALANCE SET SMS_BALANCE=IF(SMS_BALANCE IS NULL, NULL, SMS_BALANCE-s:CHARGE)
			WHERE SMS_USER_ID=s:SYS_USER";
		$qr_subSMSUserBalance = "UPDATE SMS_BALANCE SET SMS_BALANCE=IF(SMS_BALANCE IS NULL, NULL, SMS_BALANCE-s:CHARGE),
			SMS_SENT=SMS_SENT+s:QTY WHERE SMS_USER_ID=s:SMS_USER_ID";

		$params = array(
			"SYS_USER"    => '$SYSTEM',
			"SMS_USER_ID" => $u_id,
			"CHARGE"      => $charge,
			"QTY"         => $msgQty
		);
		$model = new DbModel();

		$model->prepare($qr_subSMSSystemBalance)->exec($params);

		if ($u_id != '$SYSTEM') {
			$model->prepare($qr_subSMSUserBalance)->exec($params);
		}
		return true;
	}

}
?>