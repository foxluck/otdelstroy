<?php

class SmsModel extends DbModel
{
	protected $table = 'SMS_HISTORY';
	
	/**
	 * Set sms balance to user
	 * 
	 * @param string $user_id
	 * @param int|float $balance
	 * @return bool
	 */
	public function setBalance($user_id, $balance)
	{
		$balance = $user_id == '$SYSTEM' ? (float)$balance : (int)$balance;
		if (!$balance) {
			$balance = null;
		}
		$sql = "INSERT INTO SMS_BALANCE 
				SET SMS_USER_ID = s:user_id, SMS_BALANCE = s:balance 
				ON DUPLICATE KEY UPDATE SMS_BALANCE = VALUES(SMS_BALANCE)";
		return $this->prepare($sql)->exec(array('user_id' => $user_id, 'balance' => $balance));
	}
	
	/**
	 * Returns sms balance of the user
	 * 
	 * @param string $user_id
	 * @return int|float
	 */
	public function getBalance($user_id = '$SYSTEM')
	{
		$sql = "SELECT SMS_BALANCE FROM SMS_BALANCE WHERE SMS_USER_ID = s:user_id";
		return $this->prepare($sql)->query(array('user_id' => $user_id))->fetchField('SMS_BALANCE');
	}
}

?>