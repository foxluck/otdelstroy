<?php
/**
 * Parent for all SMS modules
 * @package DynamicModules
 */
class SMSMail extends virtualModule {
	
	var $Request;
	var $Responce;
	
	/**
	 * constructor
	 *
	 * @param integer $_ModuleConfigID - module config id
	 */
	function __construct($_ModuleConfigID = 0){
		
		$this->LanguageDir = DIR_MODULES.'/ordering/smsmail/languages/';
		$this->ModuleType = SMSMAIL_MODULE;
		
		parent::__construct($_ModuleConfigID);
	}
	static function &getInstance($_ModuleConfigID)
	{
		return parent::getInstance($_ModuleConfigID,SMSMAIL_MODULE);
	}

	/**
	 * Send SMS-message by phone lists
	 *
	 * @param string $_SMSMessage - sms message
	 * @param array or string $_PhonesList - phone list
	 * @param array $_Params - some params
	 * @return parsed responce
	 */
	function sendSMS($_SMSMessage, $_PhonesList, $_Params = array()){
		
		if(!is_array($_PhonesList)){
			
			$_PhonesList = array($_PhonesList);
		}
		
		$this->Request 	= $this->_prepareRequest($_SMSMessage, $_PhonesList, $_Params);
		$this->Responce 	= $this->_sendRequest($this->Request);
		return $this->_parseResponce($this->Responce);
	}

	/**
	 * Prepare request for sending SMS
	 *
	 * @param string $_SMSMessage
	 * @param array or string $_PhonesList
	 * @param array $_Params
	 */
	function _prepareRequest($_SMSMessage, $_PhonesList, $_Params){
		
		;
	}
	
	/**
	 * Send request for sms sending
	 *
	 * @param unknown_type $_Request
	 */
	function _sendRequest($_Request){
		
		;
	}
	
	/**
	 * Parse responce on sms-sending request
	 *
	 * @param unknown_type $_Responce
	 */
	function _parseResponce($_Responce){
		
		;
	}
	
	function _translit($_Message){
		
		$s=strtr($_Message,array(
'а'=>'a',
'б'=>'b',
'в'=>'v',
'г'=>'g',
'д'=>'d',
'е'=>'e',
'ё'=>'jo',
'ж'=>'zh',
'з'=>'z',
'и'=>'i',
'й'=>'jj',
'к'=>'k',
'л'=>'l',
'м'=>'m',
'н'=>'n',
'о'=>'o',
'п'=>'p',
'р'=>'r',
'с'=>'s',
'т'=>'t',
'у'=>'u',
'ф'=>'f',
'х'=>'kh',
'ц'=>'c',
'ч'=>'ch',
'ш'=>'sh',
'щ'=>'shh',
'ъ'=>'"',
'ы'=>'y',
'ь'=>"'",
'э'=>'eh',
'ю'=>'yu',
'я'=>'ya',
'А'=>'A',
'Б'=>'B',
'В'=>'V',
'Г'=>'G',
'Д'=>'D',
'Е'=>'E',
'Ё'=>'JO',
'Ж'=>'ZH',
'З'=>'Z',
'И'=>'I',
'Й'=>'JJ',
'К'=>'K',
'Л'=>'L',
'М'=>'M',
'Н'=>'N',
'О'=>'O',
'П'=>'P',
'Р'=>'R',
'С'=>'S',
'Т'=>'T',
'У'=>'U',
'Ф'=>'F',
'Х'=>'KH',
'Ц'=>'C',
'Ч'=>'CH',
'Ш'=>'SH',
'Щ'=>'SHH',
'Ъ'=>'"',
'Ы'=>'Y',
'Ь'=>"'",
'Э'=>'EH',
'Ю'=>'YU',
'Я'=>'YA',
		));
		return $s;
	}
}
?>