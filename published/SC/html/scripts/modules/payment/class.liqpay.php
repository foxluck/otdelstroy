<?php
/**
 * @connect_module_class_name LiqPay
 * @package DynamicModules
 * @subpackage Payment
 * @author WebAsyst Team
 * @link http://www.liqpay.ru/
 *
 */
class LiqPay extends PaymentModule
{
	var $type = PAYMTD_TYPE_ONLINE;
	var $default_logo = 'http://webasyst.net/images/shop/modules/liqpay.gif';
	var $language = 'rus';

	private $url = 'https://www.liqpay.com/?do=clickNbuy';
	function _initVars(){
			
		parent::_initVars();
		$this->title = LIQPAY_TTL;
		$this->description = LIQPAY_DSCR;
		$this->sort_order = 1;
			
		$this->Settings = array(
			'CONF_LIQPAY_MERCHANT_ID',
			'CONF_LIQPAY_SECRET_KEY',
			'CONF_LIQPAY_TRANSACTION_CURRENCY',
			'CONF_LIQPAY_SHOP_CURRENCY',
			'CONF_LIQPAY_GATEWAY',
			'CONF_LIQPAY_ORDERSTATUS',
			'CONF_LIQPAY_CUSTOMER_PHONE',
		);

		$this->DebugMode = LOGMODE_DEBUG;
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_LIQPAY_MERCHANT_ID'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> LIQPAY_CFG_MERCHANT_ID_TTL,
			'settings_description' 		=> LIQPAY_CFG_MERCHANT_ID_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_LIQPAY_SECRET_KEY'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> LIQPAY_CFG_SECRET_KEY_TTL,
			'settings_description' 		=> LIQPAY_CFG_SECRET_KEY_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_LIQPAY_TRANSACTION_CURRENCY'] = array(
			'settings_value'	 		=> '',
			'settings_title' 			=> LIQPAY_CFG_TRANSACTION_CURRENCY_TTL,
			'settings_description'	 	=> LIQPAY_CFG_TRANSACTION_CURRENCY_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(LiqPay::_getTransactionCurrencies(),',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_LIQPAY_SHOP_CURRENCY'] = array(
			'settings_value'	 		=> '',
			'settings_title' 			=> LIQPAY_CFG_SHOP_CURRENCY_TTL,
			'settings_description'	 	=> LIQPAY_CFG_SHOP_CURRENCY_DSCR,
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_LIQPAY_GATEWAY'] = array(
			'settings_value' 		=> '',
			'settings_title' 			=> LIQPAY_CFG_GATEWAY_TTL,
			'settings_description' 	=> LIQPAY_CFG_GATEWAY_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(LiqPay::_getTransactionGateway(),',
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_LIQPAY_ORDERSTATUS'] = array(
			'settings_value' 			=> '-1',
			'settings_title' 			=> LIQPAY_CFG_ORDERSTATUS_TTL,
			'settings_description' 		=> LIQPAY_CFG_ORDERSTATUS_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),',
			'sort_order' 				=> 1,
		);


		$this->SettingsFields['CONF_LIQPAY_CUSTOMER_PHONE'] = array(
			'settings_value' 			=> '',
			'settings_title' 			=> LIQPAY_CFG_CUSTOMER_PHONE_TTL,
			'settings_description' 		=> LIQPAY_CFG_CUSTOMER_PHONE_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(LiqPay::_getCustomerFields(),',
			'sort_order' 				=> 1,
		);
	}

	function after_processing_html( $orderID, $active = true )
	{
		$order = ordGetOrder( $orderID );

		$order_amount	 = PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_LIQPAY_SHOP_CURRENCY'));
		$currency		 = $this->_getSettingValue('CONF_LIQPAY_TRANSACTION_CURRENCY');
		$merchant_id	 = $this->_getSettingValue('CONF_LIQPAY_MERCHANT_ID');
		$customer_phone = '';
		if($field_id = $this->_getSettingValue('CONF_LIQPAY_CUSTOMER_PHONE')){
			$checkoutEntry = Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
			$customerEntry = $checkoutEntry->customer();
			$fields = GetRegFieldsValuesByCustomerID($order['customerID']);
			foreach($fields as $field){
				if($field['reg_field_ID'] == $field_id){
					$customer_phone = $field['reg_field_value'];
					break;
				}
			}
		}
		$description	 = htmlentities('Оплата заказа #'.$orderID,ENT_QUOTES,'utf-8');
		$method			 = $this->_getSettingValue('CONF_LIQPAY_GATEWAY');
		$server_url		 = htmlentities($this->getDirectTransactionResultURL('result',array(__FILE__,$orderID,$order['customer_email'])),ENT_QUOTES,'utf-8');
		$result_url		 = htmlentities($this->getTransactionResultURL('success'),ENT_QUOTES,'utf-8');

		$xml=<<<XML
<request>      
		<version>1.2</version>
		<result_url>{$result_url}</result_url>
		<server_url>{$server_url}</server_url>
		<merchant_id>{$merchant_id}</merchant_id>
		<order_id>{$orderID}</order_id>
		<amount>{$order_amount}</amount>
		<currency>{$currency}</currency>
		<description>{$description}</description>
		<default_phone>{$customer_phone}</default_phone>
		<pay_way>{$method}</pay_way> 
</request>
XML;


		$xml_encoded = base64_encode($xml);
		$signature	 = $this->_getSettingValue('CONF_LIQPAY_SECRET_KEY');
		$lqsignature = base64_encode(sha1($signature.$xml.$signature,1));



		$form = <<<HTML
<form action="{$this->url}" method="POST">
	<input type="hidden" name="operation_xml" value="{$xml_encoded}" />
	<input type="hidden" name="signature" value="{$lqsignature}" />
	<input type="submit" value="Pay"/>
</form>
HTML;

		return $form;
	}

	function transactionResultHandler($transaction_result = '',$message = '',$source = 'frontend')
	{
		if($source != 'handler'){
			return parent::transactionResultHandler($transaction_result,$message,$source);
		}
		//DEBUG:
		$log_file = DIR_TEMP.'/'.__CLASS__.'debug.log';
		if(true&&($flog = fopen($log_file,'a'))){
			fwrite($flog,"\n==========\n");
			$args = func_get_args();
			fwrite($flog,"\nArgs:\n".var_export($args,true));
			fwrite($flog,"\nGET:\n".var_export($_GET,true));
			fwrite($flog,"\nPOST:\n".var_export($_POST,true));
		}
		$xml_decoded = isset($_POST['operation_xml'])?base64_decode($_POST['operation_xml']):false;
		$server_signature = isset($_POST['signature'])?$_POST['signature']:false;
		if($xml_decoded && ($parsed_xml = simplexml_load_string($xml_decoded))) {

			#merchant_id - id мерчанта
			#order_id - id заказа
			#amount - стоимость
			#currency - Валюта
			#description - Описание
			#status - статус транзакции
			#code - код ошибки (если есть ошибка)
			#transaction_id - id транзакции в системе LiqPay
			#pay_way - способ которым оплатит покупатель(если не указывать то он сам выбирает, с карты или с телефона(liqpay, card))
			#sender_phone - телефон оплативший заказ
			#goods_id - id товара в счетчике покупок (если был передан) NEW!
			#pays_count - число завершенных покупок данного товара (если был передан goods_id) NEW!
			#*Примеры статусов
			#status="success" - покупка совершена
			#status="failure" - покупка отклонена
			#status="wait_secure" - платеж находится на проверке

			$orderID = (int)$parsed_xml->order_id;
			$log = '';
			if($orderID &&($order = _getOrderById($orderID))){
				if($this->validateResultKey(array(__FILE__,$orderID,$order['customer_email']))){
					//check callback sign
					$sharedSecret = $this->_getSettingValue('CONF_LIQPAY_SECRET_KEY');
					if($sharedSecret){
						$signature=base64_encode(sha1($sharedSecret.$xml_decoded.$sharedSecret,1));
						if($server_signature!=$signature){
							$transaction_result = 'failure';
							$log .= ' invalid post data sign';
							if($flog){
								fwrite($flog,"\nsign:\n".var_export(array('string'=>$string,'$server_sign'=>$server_sign,'$sign'=>$sign),true));
							}
						}
					}

					if($transaction_result == 'result'){
						$statusID = $order['statusID'];
						$status = (string)$parsed_xml->status;
						$details = '';
						switch($status){
							case 'success':{//покупка совершена
								$succes_statusID = $this->_getSettingValue('CONF_LIQPAY_ORDERSTATUS');
								if($succes_statusID>0){
									$statusID = $succes_statusID;
								}
								$details .= "\n".LIQPAY_TXT_ITRANSACTION_ID.": ".(string)$parsed_xml->transaction_id;
								break;
							}
							case 'failure':{//покупка отклонена
								$details .= "\n".LIQPAY_TXT_ITRANSACTION_ERROR.": ".(string)$parsed_xml->code;
								break;
							}
							case 'wait_secure':{//платеж находится на проверке
								$details .= "\n".LIQPAY_TXT_ITRANSACTION_WAIT.": ".(string)$parsed_xml->transaction_id;
								break;
							}
							default:{
								break;
							}
						}
						if($flog){
							fwrite($flog,"\nstatus:\n".var_export(array('status'=>$status,'statusID'=>$succes_statusID,'details'=>$details),true));
						}
							
						$comment = ($statusID != $order['statusID'])?LIQPAY_TXT_ISTATUS_CHANGED:LIQPAY_TXT_ICOMMENT_ADDED;
						ostSetOrderStatusToOrder( $orderID, $statusID,$comment.$details,0,true);

					}else{
						$log .= 'transaction_result: '.$transaction_result;
					}
				}else{
					$transaction_result = 'failure';
					$statusID = 3;
					$log .= 'invalid secure key';
					//ostSetOrderStatusToOrder( $orderID, $statusID,$log,1,$force=true);
				}
			}else{
				$log = "Order with id {$orderID} not exists";
				$transaction_result = 'failure';
			}
		}else {
			$log = "Empty or invalid operation_xml";
			$transaction_result = 'failure';
		}

		if($flog){
			fwrite($flog,"\nLog:\n".$log);
			fclose($flog);
		}
		return parent::transactionResultHandler($transaction_result,$message.$log,$source);
	}



	public static function _getTransactionCurrencies()
	{
		$currencies = array();
		$currencies[] = array('title'=>LIQPAY_TXT_CURRUR,'value'=>'RUR');
		$currencies[] = array('title'=>LIQPAY_TXT_CURUSD,'value'=>'USD');
		$currencies[] = array('title'=>LIQPAY_TXT_CUREUR,'value'=>'EUR');
		$currencies[] = array('title'=>LIQPAY_TXT_CURUAH,'value'=>'UAH');
		return $currencies;
	}


	public static function _getTransactionGateway()
	{
		$gateways = array();
		$gateways[] = array('title'=>LIQPAY_TXT_GATEWAYSELECT,	'value'=>'');
		$gateways[] = array('title'=>LIQPAY_TXT_GATEWAYCARD,	'value'=>'card');
		$gateways[] = array('title'=>LIQPAY_TXT_GATEWAYPHONE,	'value'=>'liqpay');
		return $gateways;
	}

	public static function _getCustomerFields()
	{
		$fields = GetRegFields();
		$res = array(LIQPAY_TXT_NOT_DEFINED.':0');
		foreach($fields as $field){
			$res[] = xHtmlSpecialChars($field['reg_field_name'].':'.$field['reg_field_ID']);
		}
		return implode(',',$res);
	}
}
?>