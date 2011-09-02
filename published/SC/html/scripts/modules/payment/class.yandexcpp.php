<?php
/**
 * Модуль интеграции с платежной системой Яндекс.Деньги Центр Приема Платежей (ЦПП)
 * @link http://money.yandex.ru/doc.xml?id=157411
 * @connect_module_class_name YandexCPP
 * @package DynamicModules
 * @subpackage Payment
 */
class YandexCPP extends PaymentModule
{

	var $type = PAYMTD_TYPE_ONLINE;//self::PAYMTD_TYPE_ONLINE;
	var $language = 'rus';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/yandexmoney.gif';
	/*
		<xs:enumeration value = "0"/>	<!-- Success -->
		<xs:enumeration value = "1"/>	<!-- Authorization failed -->
		<xs:enumeration value = "100"/>	<!-- Payment refused by shop -->
		<xs:enumeration value = "200"/>	<!-- Bad request -->
		<xs:enumeration value = "1000"/><!-- Temporary technical problems -->
		**/
	const XML_RESPONCE_SUCCESS			 = '0';//<!-- Success -->
	const XML_RESPONCE_AUTH_FAILED		 = '1';//<!-- Authorization failed -->
	const XML_RESPONCE_PAYMENT_REFUSED	 = '100';//<!-- Payment refused by shop -->
	const XML_RESPONCE_BAD_REQUEST		 = '200';//<!-- Bad request -->
	const XML_RESPONCE_TEMPORAL_PROBLEMS = '1000';//<!-- Temporary technical problems -->


	/*
	 * @todo remove DEVELOPER in production
	 */
	function _initVars()
	{
			
		parent::_initVars();
		$this->title = YANDEXCPP_TTL;
		$this->description = YANDEXCPP_DSCR;
		$this->sort_order = 1;
			
		$this->Settings = array(
				'CONF_YANDEXCPP_SHOPID',
				'CONF_YANDEXCPP_SCID',
		//	'CONF_YANDEXCPP_BANKID',
		//		'CONF_YANDEXCPP_TARGETBANKID',
				'CONF_YANDEXCPP_MODE',
		//		'CONF_YANDEXCPP_TARGETCURRENCY',
				'CONF_YANDEXCPP_TRANSCURRENCY',
				'CONF_YANDEXCPP_SHOPPASSWORD',
				'CONF_YANDEXCPP_ORDERSTATUS',
				'CONF_YANDEXCPP_DEV',
		
		);
	}

	function _initSettingFields()
	{

		$this->SettingsFields['CONF_YANDEXCPP_SHOPID'] = array(
			'settings_value' 		=> '',
			'settings_title' 			=> YANDEXCPP_CFG_SHOPID_TTL,
			'settings_description' 	=> YANDEXCPP_CFG_SHOPID_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_YANDEXCPP_SCID'] = array(
			'settings_value' 		=> '',
			'settings_title' 			=> YANDEXCPP_CFG_SCID_TTL,
			'settings_description' 	=> YANDEXCPP_CFG_SCID_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 1,
		);/*
		$this->SettingsFields['CONF_YANDEXCPP_BANKID'] = array(
		'settings_value' 		=> '1001',
		'settings_title' 			=> YANDEXCPP_CFG_BANKID_TTL,
		'settings_description' 	=> YANDEXCPP_CFG_BANKID_DSCR,
		'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
		'sort_order' 			=> 1,
		);*/
		/*$this->SettingsFields['CONF_YANDEXCPP_TARGETBANKID'] = array(
			'settings_value' 		=> '1001',
			'settings_title' 			=> YANDEXCPP_CFG_TARGETBANKID_TTL,
			'settings_description' 	=> YANDEXCPP_CFG_TARGETBANKID_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 1,
		);*/
		$this->SettingsFields['CONF_YANDEXCPP_MODE'] = array(
			'settings_value' 		=> 'live',
			'settings_title' 			=> YANDEXCPP_CFG_MODE_TTL,
			'settings_description' 	=> YANDEXCPP_CFG_MODE_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(YandexCPP::_getModes(),',
			'sort_order' 			=> 1,
		);
		/*$this->SettingsFields['CONF_YANDEXCPP_TARGETCURRENCY'] = array(
			'settings_value' 		=> '643',
			'settings_title' 			=> YANDEXCPP_CFG_TARGETCURRENCY_TTL,
			'settings_description' 	=> YANDEXCPP_CFG_TARGETCURRENCY_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(YandexCPP::_getTargetCurrencies(),',
			'sort_order' 			=> 1,
		);*/
		$this->SettingsFields['CONF_YANDEXCPP_TRANSCURRENCY'] = array(
			'settings_value' 		=> '',
			'settings_title' 			=> YANDEXCPP_CFG_TRANSCURRENCY_TTL,
			'settings_description' 	=> YANDEXCPP_CFG_TRANSCURRENCY_DSCR,
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(',
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_YANDEXCPP_SHOPPASSWORD'] = array(
			'settings_value' 		=> '',
			'settings_title' 		=> YANDEXCPP_CFG_SHOPPASSWORD_TTL,
			'settings_description' 	=> YANDEXCPP_CFG_SHOPPASSWORD_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_YANDEXCPP_ORDERSTATUS'] = array(
			'settings_value' 		=> '-1',
			'settings_title' 			=> YANDEXCPP_CFG_ORDERSTATUS_TTL,
			'settings_description' 	=> YANDEXCPP_CFG_ORDERSTATUS_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),',
			'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_YANDEXCPP_DEV'] = array(
			'settings_value' 		=> '0',
			'settings_title' 			=> YANDEXCPP_CFG_DEVELOPER_TTL,
			'settings_description' 	=> YANDEXCPP_CFG_DEVELOPER_DSCR,
			'settings_html_function' 	=> 'setting_CHECK_BOX(',
			'sort_order' 			=> 2,
		);
	}

	function getCustomProperties()
	{
		$customProperties = array();
		$customProperties[] = array(
			'settings_title'=>YANDEXCPP_CUST_CHECKURL_TTL,
			'settings_description'=>YANDEXCPP_CUST_CHECKURL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getDirectTransactionResultURL('check',null,true))
			.'">'
			);
			$customProperties[] = array(
			'settings_title'=>YANDEXCPP_CUST_RESULTURL_TTL,
			'settings_description'=>YANDEXCPP_CUST_RESULTURL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getDirectTransactionResultURL('result',null,true))
			.'">'
			);
			$customProperties[] = array(
			'settings_title'=>YANDEXCPP_CUST_SUCCESURL_TTL,
			'settings_description'=>YANDEXCPP_CUST_SUCCESURL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getTransactionResultURL('success'))
			.'">'
			);
			$customProperties[] = array(
			'settings_title'=>YANDEXCPP_CUST_FAILURE_TTL,
			'settings_description'=>YANDEXCPP_CUST_FAILURE_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getTransactionResultURL('failure'))
			.'">'
			);
			return $customProperties;
	}

	function after_processing_html( $orderID )
	{

		$html_form = '';
		$order = ordGetOrder( $orderID );
		$order_amount = RoundFloatValue(PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_YANDEXCPP_TRANSCURRENCY')));
		$post_fields=array(
		//Номер магазина в ЦПП. Выдается ЦПП.
			'ShopId' => $this->_getSettingValue('CONF_YANDEXCPP_SHOPID'),
		//Номер витрины магазина в ЦПП. Выдается ЦПП.
			'scid'=>$this->_getSettingValue('CONF_YANDEXCPP_SCID'),
		//Сумма заказа (десятичный разделитель – точка, разделители тысяч недопустимы)
			'Sum' => $order_amount,
		//Идентификатор Покупателя, любая строка не более 64 символов.
		//Номер оплачиваемого мобильного телефона, договора и т.п., специфично для Магазина.
			'CustomerNumber' => $orderID,
			//'BankId'=> $this->_getSettingValue('CONF_YANDEXCPP_TARGETBANKID'),
			//'OrderDetails'=>'',
		);
/*
		$order_content = ordGetOrderContent( $orderID );
		foreach ($order_content as $item){
			$post_fields['OrderDetails'] .= $item['name']."; ";
		}
		*/
		/*	
		$implAddress = array('shipping_country', 'shipping_state', 'shipping_zip', 'shipping_city', 'shipping_address');
		$address_parts = array();
		foreach ($implAddress as $k){
			if($order[$k]){
				$address_parts[] = trim($order[$k]);
			}
		}
		$address_parts = array_filter($address_parts,'strlen');
		$post_fields['CustAddr'] = preg_replace("/[\s]{2,}/msi",' ',str_replace(array("\r","\n"),' ',implode(', ',$address_parts)));
			*/
		$hidden_fields_html = '';
		$post_fields['secure_key'] = $this->generateSecureKey(array(__FILE__));
		foreach($post_fields as $name=>$value){
			$value = xHtmlSpecialChars($value);
			$hidden_fields_html .= "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\"/>\n";
		}

		if($this->_getSettingValue('CONF_YANDEXCPP_MODE')=='test'){
			$processing_url = 'http://demomoney.yandex.ru/select-wallet.xml';
			$processing_url = 'https://demomoney.yandex.ru/eshop.xml';
		}else{
			$processing_url = 'http://money.yandex.ru/select-wallet.xml';
			$processing_url = 'https://money.yandex.ru/eshop.xml';
		}
		$processing_url = xHtmlSpecialChars($processing_url);
		$html_form = '
				<form method="post" action="'.$processing_url.'" style="text-align:center;">
					'.$hidden_fields_html.'
					<input type="submit" value="'.xHtmlSpecialChars(YANDEXCPP_TXT_PROCESS).'" />
				</form>
';
			
		return $html_form;
	}

	function _getModes(){
		return YANDEXCPP_TXT_TESTMODE.':test,'.YANDEXCPP_TXT_LIVEMODE.':live';
	}

	function _getTargetCurrencies(){
		return YANDEXCPP_TXT_RUR.':643,'.YANDEXCPP_TXT_DEMORUR.':10643';
	}

	function transactionResultHandler($transaction_result = '',$message = '',$source = 'frontend')
	{
		if($source != 'handler'){
			return parent::transactionResultHandler($transaction_result,$message,$source);
		}else{
			$shopId = $this->_getSettingValue('CONF_YANDEXCPP_SHOPID');
			$invoiceId = preg_replace('/[\D]+/','',Env::Post('invoiceId',Env::TYPE_STRING,0));
			$action = Env::Post('action',Env::TYPE_STRING,'');

			try{
				$secure_key = $this->generateSecureKey(array(__FILE__));
				if($secure_key != Env::Post('secure_key')){
					throw new Exception("Invalid secure key, please check callback URL",self::XML_RESPONCE_AUTH_FAILED);
				}
				$log = '';
					
				$order = new Order();
				$orderID = Env::Post('CustomerNumber',Env::TYPE_INT,0);
				if(!$order->loadByID($orderID)){
					throw new Exception("Order not found",self::XML_RESPONCE_BAD_REQUEST);
				}
				if($order->payment_module_id!=$this->ModuleConfigID){
					//throw new Exception("invalid module config id sended",self::XML_RESPONCE_AUTH_FAILED);
				}

				//verify md5 and SSL
				if(!$this->verifyHash()){
					throw new Exception("Invalid hash",self::XML_RESPONCE_AUTH_FAILED);
				}
				$this->addDebugLog("attempt action ({$action}) payment\n".var_export(array('GET'=>$_GET,'POST'=>$_POST),true));
		
				//or verify pgp

				//get payment transaction result
				$message = '';
				switch($action){
					case 'Check'://Проверка заказа
						//verify confirm payment
						$order_amount = $order->order_amount;
						//$order_amount = $this->_convertCurrency($order_amount,$order->currency_code,$this->_getSettingValue('CONF_YANDEXCPP_TRANSCURRENCY'));
						$fields = array(
							'shopId'=>$shopId,
							'scid'=>$this->_getSettingValue('CONF_YANDEXCPP_SCID'),
							//'orderSumCurrencyPaycash'=>($this->_getSettingValue('CONF_YANDEXCPP_MODE')=='test')?10643:643,
							'orderSumAmount'=>$order_amount,
							'orderSumBankPaycash'=>($this->_getSettingValue('CONF_YANDEXCPP_MODE')=='test')?1003:1001,
						);
						$code = self::XML_RESPONCE_SUCCESS;
						foreach($fields as $field=>$value){
							if(Env::Post($field)!=$value){
								$code = self::XML_RESPONCE_PAYMENT_REFUSED;
								$message = "Invalid value of field {$field}: Get [".Env::Post($field)."] but expect [{$value}]";
								throw new Exception($message,$code);
							}
						}
						if(in_array($order->statusID,array(CONF_ORDSTATUS_CANCELLED,CONF_ORDSTATUS_DELIVERED,$this->_getSettingValue('CONF_YANDEXCPP_ORDERSTATUS')))){
							$status_name = ostGetOrderStatusName($order->statusID);
							//$status_name = OrderStatus::getStatusName($statusID);
							$code = self::XML_RESPONCE_PAYMENT_REFUSED;
							$message = "Заказ в статусе \"{$status_name}\" не может быть оплачен";//." /{$value}";
							throw new Exception($message,$code);
						}
						break;
					case 'PaymentSuccess'://Уведомления об оплате
						//verify confirm payment
						//$order_amount = RoundFloatValue(PaymentModule::_convertCurrency($order->order_amount,0,$this->_getSettingValue('CONF_YANDEXCPP_TRANSCURRENCY')));
						$order_amount = $order->order_amount;
						$fields = array(
							'shopId'=>$shopId,
							'scid'=>$this->_getSettingValue('CONF_YANDEXCPP_SCID'),
							//'orderSumCurrencyPaycash'=>($this->_getSettingValue('CONF_YANDEXCPP_MODE')=='test')?10643:643,
							'orderSumAmount'=>$order_amount,
							'orderSumBankPaycash'=>($this->_getSettingValue('CONF_YANDEXCPP_MODE')=='test')?1003:1001,
						);
						$code = self::XML_RESPONCE_SUCCESS;
						foreach($fields as $field=>$value){
							if(Env::Post($field)!=$value){
								$code = self::XML_RESPONCE_PAYMENT_REFUSED;
								$message = "Invalid value of field {$field} {$value} expected";
								throw new Exception($message,$code);
							}
						}
						$statusID = $this->_getSettingValue('CONF_YANDEXCPP_ORDERSTATUS');
						if($statusID!=-1){
							$comment = $invoiceId?sprintf('Yandex ЦПП invoiceId: %d',$invoiceId):'статус изменен модулем Yandex ЦПП';
							if($this->_getSettingValue('CONF_YANDEXCPP_MODE')=='test'){
								$comment .= "\nТестовый режим";
							}
							if($paymentPayerCode = Env::Post('paymentPayerCode')){
								$comment .= "\nПлатеж принят от {$paymentPayerCode}";
							}
							if($order->statusID == $statusID){
								$comment .= "\n(повтор)";
								ostSetOrderStatusToOrder($orderID, $statusID,$comment,0,true);
							}else{
								ostSetOrderStatusToOrder($orderID, $statusID,$comment,0);
							}
							$message = $comment;
						}
						$code = self::XML_RESPONCE_SUCCESS;
						break;
					case 'PaymentFail'://после неуспешного платежа.
						break;
					default://unknown action
						$code = self::XML_RESPONCE_BAD_REQUEST;
						break;
				}
				$this->sendResponce($code,$action,$shopId,$invoiceId,$message);

			}catch(Exception $e){
				$code = $e->getCode();
				if(!$code){
					$code =self::XML_RESPONCE_TEMPORAL_PROBLEMS;
				}
				$this->sendResponce($code,$action,$shopId,$invoiceId,$e->getMessage());
			}
		}
	}
	/**
	 * Check MD5 hash of transfered data
	 * @return boolean
	 */
	private function verifyHash()
	{
		//orderIsPaid;orderSumAmount;orderSumCurrencyPaycash;orderSumBankPaycash;shopId;invoiceId;customerNumber
		//В случае расчета криптографического хэша, в конце описанной выше строки добавляется «;shopPassword»

		$hash_string_items = array();
		$hash_params = array(
				'orderIsPaid',
				'orderSumAmount',
				'orderSumCurrencyPaycash',
				'orderSumBankPaycash',
				'shopId',
				'invoiceId',
				'CustomerNumber',
		);
			
		foreach($hash_params as $param){
			$hash_string_items[] = Env::Post($param,Env::TYPE_STRING,'');
		}
		$hash_string_items[] = $this->_getSettingValue('CONF_YANDEXCPP_SHOPPASSWORD');
		$hash_string = implode(';',$hash_string_items);
		$hash = strtoupper(md5($hash_string));
		$hash_post = Env::Post('md5',Env::TYPE_STRING);
		return ($hash == $hash_post)?true:false;
	}
	private function sendResponce($code,$action,$shopId,$invoiceId,$techMessage = null)
	{
		$performedDatetime = date('c');
		header("Content-type: text/xml; charset=windows-1251;");
		$responce = <<<XML
<?xml version="1.0" encoding="windows-1251"?>
<response performedDatetime="{$performedDatetime}">
<result code="{$code}" action="{$action}" shopId="{$shopId}" invoiceId="{$invoiceId}"
XML;
		if($techMessage&&$code){
			$techMessage = preg_replace('@[\s\n]+@',' ',$techMessage);
			$techMessage = htmlentities($techMessage,ENT_QUOTES,'utf-8');
			$techMessage = iconv('utf-8','cp1251',$techMessage);
			if(strlen($techMessage)>64){
				$techMessage = substr($techMessage,0,64);
			}
			$responce .= " techMessage=\"{$techMessage}\"";
		}
		$responce .= " />\n";
		$responce .= "</response>";
		print $responce;
		$this->addDebugLog("code={$code}\t{$techMessage}\n".var_export(array('GET'=>$_GET,'POST'=>$_POST,'responce'=>$responce),true));
		exit;
	}

	private function addDebugLog($text)
	{
		if($this->_getSettingValue('CONF_YANDEXCPP_DEV')){
			$text .= "\nMemory: ".sprintf('%0.2fMb',memory_get_usage(true)/1048576);
			$this->LogFile = DIR_LOG.'/yandexCPP.log';
			$this->log_mode |= LOGMODE_MSG|LOGMODE_ERROR;
			$this->_log(LOGMODE_MSG,$text);
		}
	}
	
	function getDirectTransactionResultURL($transaction_result,$params = array(),$https = false)
	{
		$url = parent::getDirectTransactionResultURL($transaction_result,$params,$https);
		if(!SystemSettings::is_hosted()){
			$url = str_replace('/published/SC/html/scripts/callbackhandlers/','/shop/callbackhandlers/',$url);
		}
		return $url;
	}
}
?>