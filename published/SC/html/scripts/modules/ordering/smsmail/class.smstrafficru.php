<?php
/**
 * @connect_module_class_name SMSTrafficRu
 */

class SMSTrafficRu extends SMSMail {

	var $language = 'rus';

	function _initVars(){

		$this->title = 'SMS traffic';
		$this->description = '<a href="http://www.smstraffic.ru">www.smstraffic.ru</a>';
		$this->sort_order = 0;

		$this->Settings[] = 'CONF_SMSTRAFFICRU_LOGIN';
		$this->Settings[] = 'CONF_SMSTRAFFICRU_PASSWORD';
		$this->Settings[] = 'CONF_SMSTRAFFICRU_RUS';
		$this->Settings[] = 'CONF_SMSTRAFFICRU_ORIGINATOR';
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_SMSTRAFFICRU_LOGIN'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Логин', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSTRAFFICRU_PASSWORD'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Пароль', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSTRAFFICRU_RUS'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> 'Передавать ли сообщение по русски(максимальная длина 70 символов)', 
			'settings_description' 	=> 'Если нет сообщение будет транслитерировано.', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSTRAFFICRU_ORIGINATOR'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Отправитель сообщения, как он будет выглядеть на телефоне получателя', 
			'settings_description' 	=> 'Отправитель может быть цифровым, в этом случае его длина ограничена 15-ю символами, или буквенно-цифровым (например, название вашей компании), в этом случае длина ограничена 11-ю символами. Русские буквы в имени отправителя не разрешены. По умолчанию ставится originator=999.', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
	}

	function _prepareRequest($_SMSMessage, $_PhonesList, $_Params){

		if(!$_SMSMessage)return null;
		if(!count($_PhonesList))return null;

		return array(
			'login' => $this->_getSettingValue('CONF_SMSTRAFFICRU_LOGIN'),
			'password' => $this->_getSettingValue('CONF_SMSTRAFFICRU_PASSWORD'),
			'phones' => implode(',',$_PhonesList),
			'message' => iconv('utf-8','cp1251//IGNORE',$_SMSMessage),
			'rus' => $this->_getSettingValue('CONF_SMSTRAFFICRU_RUS'),
			'originator' => $this->_getSettingValue('CONF_SMSTRAFFICRU_ORIGINATOR'),
		);
	}

	function _sendRequest($_Request){
		$debug = false;

		$url = 'https://www.smstraffic.ru/multi.php';

		if ( !($ch = curl_init()) ){
				
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Local error: '.ERR_CURLINIT);
			return ERR_CURLINIT;
		}

		if ( curl_errno($ch) != 0 ){
				
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: '.curl_errno($ch).' '.curl_error($ch));
			return ERR_CURLINIT;
		}

		@curl_setopt($ch, CURLOPT_URL, $url );
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		@curl_setopt($ch, CURLOPT_HEADER, 0);
		@curl_setopt($ch, CURLOPT_POST, 1);
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $_Request);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		initCurlProxySettings($ch);
		if($debug){
			$this->_writeLogMessage(MODULE_LOG_CURL,var_export(array('url'=>$url,'post'=>$_Request),true));
		}

		$result = @curl_exec($ch);
		if($debug){
			$this->_writeLogMessage(MODULE_LOG_CURL,var_export(array('result'=>$result),true));
		}
		if ( curl_errno($ch) != 0){
				
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: #'.curl_errno($ch).' '.curl_error($ch));
			return ERR_CURLEXEC;
		}

		curl_close($ch);
		if($xml = @simplexml_load_string($result)){
			if($code = (int)$xml->code){
				$this->_writeLogMessage(MODULE_LOG_CURL, 'Gateway error: #'.$xml->code.' '.$xml->result.' '.$xml->description);	
			}elseif($debug){
				$this->_writeLogMessage(MODULE_LOG_CURL, 'Gateway debug: #'.$xml->code.' '.$xml->result.' '.$xml->description);
			}
		}
		return $result;
	}

	function _parseResponce($_Responce){
		;
	}
}
?>