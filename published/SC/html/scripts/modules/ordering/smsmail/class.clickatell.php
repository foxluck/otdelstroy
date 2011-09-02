<?php
/**
 * @connect_module_class_name Clickatell
 */
class Clickatell extends SMSMail {
	
	function _initVars(){
		
		$this->title = 'Clickatell';
		$this->description = '<a href="http://clickatell.com">clickatell.com</a>';
		$this->sort_order = 0;
		
		$this->Settings = array(
			'CONF_SMSMAIL_MODULE_CLICKATELL_API_ID',
			'CONF_SMSMAIL_MODULE_CLICKATELL_USER',
			'CONF_SMSMAIL_MODULE_CLICKATELL_PASSWORD',
			'CONF_SMSMAIL_MODULE_CLICKATELL_ORIGINATOR',
		);
	}
	
	function _initSettingFields(){
		
		$this->SettingsFields['CONF_SMSMAIL_MODULE_CLICKATELL_API_ID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> SMSMAIL_MODULE_CLICKATELL_API_ID_TTL, 
			'settings_description' 	=> SMSMAIL_MODULE_CLICKATELL_API_ID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSMAIL_MODULE_CLICKATELL_USER'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> SMSMAIL_MODULE_CLICKATELL_USER_TTL, 
			'settings_description' 	=> SMSMAIL_MODULE_CLICKATELL_USER_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSMAIL_MODULE_CLICKATELL_PASSWORD'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> SMSMAIL_MODULE_CLICKATELL_PASSWORD_TTL, 
			'settings_description' 	=> SMSMAIL_MODULE_CLICKATELL_PASSWORD_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSMAIL_MODULE_CLICKATELL_ORIGINATOR'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> SMSMAIL_MODULE_CLICKATELL_ORIGINATOR_TTL, 
			'settings_description' 	=> SMSMAIL_MODULE_CLICKATELL_ORIGINATOR_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
	}

	function _prepareRequest($_SMSMessage, $_PhonesList, $_Params){
		
		if(!$_SMSMessage)return null;
		if(!count($_PhonesList))return null;
		
		$Request = 'api_id='.$this->_getSettingValue('CONF_SMSMAIL_MODULE_CLICKATELL_API_ID').
				'&user='.$this->_getSettingValue('CONF_SMSMAIL_MODULE_CLICKATELL_USER').
				'&password='.$this->_getSettingValue('CONF_SMSMAIL_MODULE_CLICKATELL_PASSWORD').
				'&to='.implode(',',$_PhonesList).
				'&from='.urlencode($this->_translit($this->_getSettingValue('CONF_SMSMAIL_MODULE_CLICKATELL_ORIGINATOR'))).
				'&text='.urlencode($this->_translit($_SMSMessage)).
				'';
		return $Request;
	}
	
	function _sendRequest($_Request){
		
		$url = 'http://api.clickatell.com/http/sendmsg?'.$_Request;
		return file($url);
	}
	
	function _parseResponce($_Responce){
		
//		print '<pre>';
//		print_r($_Responce);
//		print '</pre>';
//		die;
	}
}
?>