<?php
/**
 * @connect_module_class_name WASMSSender
 */
require_once(DIR_CLASSES.'/class.virtual.smsmail.php');

class WASMSSender extends SMSMail {
	
	function is_installed(){
		return $this->ModuleConfigID>0;
	}
	
	function _initVars(){
		
		$this->title = 'WebAsyst SMS Sender';
		$this->description = '';
		$this->sort_order = 0;
	}

	function _prepareRequest($_SMSMessage, $_PhonesList, $_Params){
		
		if(!$_SMSMessage)return null;
		if(!count($_PhonesList))return null;
		
		$Request = array(
			'TO' => $_PhonesList,
			'MESSAGE' => $_SMSMessage,
			'FROM' => preg_replace('/^([^\.]+).*$/u', '$1', CONF_SHOP_URL),
		);
		return $Request;
	}
	
	function _sendRequest($_Request){

		sc_setSessionData('_SMS_DATA', $_Request);
		$session_id = session_id();
		session_write_close();
	
		$auth_part = md5(date('YmdHi'));
		
		$messageClient = new WbsHttpMessageClient('', 'wbs_msgserver.php');
		$messageClient->putData('action', 'SEND_SMS');
		$messageClient->putData('auth_part', $auth_part);
		$messageClient->putData('auth_key', md5($auth_part.':'.SystemSettings::get('DB_USER').':'.SystemSettings::get('DB_PASS')));
		$messageClient->putData('session_id', $session_id);
		$res=$messageClient->send();

	
		session_id($session_id);
		session_start();
		
		if(!$res||$messageClient->getResult('success')!=='true')return PEAR::raiseError('Couldnt send sms');

		return $messageClient->getResult('send_result')!=1?PEAR::raiseError($messageClient->getResult('error_message')):true;
	}
	
	function _parseResponce($_Responce){

		if(PEAR::isError($_Responce)){
			
			$this->_writeLogMessage('', 'SMS order notification error: '.$_Responce->getMessage());
		}
	}
}
?>