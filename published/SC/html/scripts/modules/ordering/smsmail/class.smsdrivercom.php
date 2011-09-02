<?php
/**
 * @connect_module_class_name SMSDriverCom
 */
class SMSDriverCom extends SMSMail {
	
	function _initVars(){
		
		$this->title = 'SMS Driver';
		$this->description = '<a href="http://smsdriver.com">smsdriver.com</a>';
		$this->sort_order = 0;
		
		$this->Settings[] = 'CONF_SMSDRIVERCOM_LOGIN';
		$this->Settings[] = 'CONF_SMSDRIVERCOM_PASSWORD';
		$this->Settings[] = 'CONF_SMSDRIVERCOM_UNICODE';
		$this->Settings[] = 'CONF_SMSDRIVERCOM_ORIGINATOR';
	}
	
	function _initSettingFields(){
		
		$this->SettingsFields['CONF_SMSDRIVERCOM_LOGIN'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> SMSDRIVERCOM_CFG_LOGIN_TTL, 
			'settings_description' 	=> SMSDRIVERCOM_CFG_LOGIN_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSDRIVERCOM_PASSWORD'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> SMSDRIVERCOM_CFG_PASSWORD_TTL, 
			'settings_description' 	=> SMSDRIVERCOM_CFG_PASSWORD_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSDRIVERCOM_UNICODE'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> SMSDRIVERCOM_CFG_UNICODE_TTL, 
			'settings_description' 	=> SMSDRIVERCOM_CFG_UNICODE_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSDRIVERCOM_ORIGINATOR'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> SMSDRIVERCOM_CFG_ORIGINATOR_TTL, 
			'settings_description' 	=> SMSDRIVERCOM_CFG_ORIGINATOR_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
	}

	function _prepareRequest($_SMSMessage, $_PhonesList, $_Params){
		
		if(!$_SMSMessage)return null;
		if(!count($_PhonesList))return null;
		
		$Request = array();
		foreach ($_PhonesList as $_Phone){
			
			$Request[] = 'smsNUMBER='.$_Phone.
				'&smsSENDER='.urlencode($this->_getSettingValue('CONF_SMSDRIVERCOM_UNICODE')?$this->_getSettingValue('CONF_SMSDRIVERCOM_ORIGINATOR'):$this->_translit($this->_getSettingValue('CONF_SMSDRIVERCOM_ORIGINATOR'))).
				'&smsTEXT='.urlencode($this->_getSettingValue('CONF_SMSDRIVERCOM_UNICODE')?$_SMSMessage:$this->_translit($_SMSMessage)).
				'&smsUSER='.$this->_getSettingValue('CONF_SMSDRIVERCOM_LOGIN').
				'&smsPASSWORD='.$this->_getSettingValue('CONF_SMSDRIVERCOM_PASSWORD').
				'&smsTYPE='.'file.sms';
		}
		return $Request;
	}
	
	function _sendRequest($_Request){
		
		$host = 'post.smsdriver.com';
		$doc = '/smshurricane3.0.asp';
		$result = '';
		$errno = '';
		$errstr = '';
		
		$_TC = count($_Request)-1;
		for( ;$_TC>=0; $_TC--){
			
			$so = pfsockopen($host, 80, $errno, $errstr, 30);
			if(!$so)return "Couldnt open socket";
			$in = "POST ".$doc." HTTP/1.1"."\n".
				"Host: ".$host."\n".
				"Content-Length: ".strlen($_Request[$_TC])."\n".
				"Content-type: application/x-www-form-urlencoded"."\n".
				"Cache-Control: no-cache"."\n".
				"Connection: Close"."\n".
				"\n";
			fputs($so,$in.$_Request[$_TC]);
			while(!feof($so)){
				
				$result .= fgets($so,128);
			}
			fclose($so);
		}
		return $result;
	}
	
	function _parseResponce($_Responce){
		
//		print '<pre>';
//		print_r($_Responce);
//		print '</pre>';
//		die;
	}
}
?>