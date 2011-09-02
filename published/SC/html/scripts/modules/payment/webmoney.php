<?php
/**
 * @connect_module_class_name CWebMoney
 * @package DynamicModules
 * @subpackage Payment
 */
// WebMoney method implementation
// see also
//		http://www.webmoney.ru
//		https://merchant.webmoney.ru/conf/guide.asp#properties

class CWebMoney extends PaymentModule {

	var $type = PAYMTD_TYPE_ONLINE;
	var $language = 'rus';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/webmoney.gif';

	function _initVars(){

		parent::_initVars();
		$this->title 		= "WebMoney";
		$this->description 	= "WebMoney Merchant Interface (www.webmoney.ru)<br>ВНИМАНИЕ: После того, как модуль будет установлен, вам необходимо включить опцию приема платежей через Merchant Interface в вашей учетной записи WebMoney";
		$this->sort_order 	= 0;
		$this->Settings = array(
				"CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_PURSE", 
				"CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_EXCHANGERATE",
				"CONF_PAYMENTMODULE_WEBMONEY_TESTMODE",
				"CONF_PAYMENTMODULE_WEBMONEY_PAYMENTS_DESC",
				"CONF_PAYMENTMODULE_WEBMONEY_SHARED_SECRET",
				"CONF_PAYMENTMODULE_WEBMONEY_ORDERSTATUS",  
		);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_PURSE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Номер кошелька, на который будут приниматься деньги в Вашем магазине', 
			'settings_description' 	=> 'Формат - буква и 12 цифр', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_EXCHANGERATE'] = array(
			'settings_value' 		=> '1', 
			'settings_title' 			=> 'Курс у.е. магазина по отношению к валюте Web-Money', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(1,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_WEBMONEY_TESTMODE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Тестовый режим', 
			'settings_description' 	=> 'Используйте тестовый режим для проверки модуля', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_WEBMONEY_PAYMENTS_DESC'] = array(
			'settings_value' 		=> 'Оплата заказа #[orderID]', 
			'settings_title' 			=> 'Назначение платежей', 
			'settings_description' 	=> 'Укажите описание платежей. Вы можете использовать строку [orderID] - она автоматически будет заменена на номер заказа', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_WEBMONEY_SHARED_SECRET'] = array(
			'settings_value' 		=> '',
			'settings_title' 			=> 'Secret Key',
			'settings_description' 	=> 'Строка символов, добавляемая к реквизитам платежа, высылаемым продавцу вместе с оповещением. Эта строка используется для повышения надежности идентификации высылаемого оповещения. Содержание строки известно только сервису Web Merchant Interface и продавцу',
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_WEBMONEY_ORDERSTATUS'] = array(
			'settings_value' 		=> '-1',
			'settings_title' 			=> 'Статус заказа',
			'settings_description' 	=> 'Статус, который будет автоматически установлен для заказа после успешной оплаты',
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),',
			'sort_order' 			=> 1,
		);
	}

	function after_processing_html( $orderID )
	{
		$order = ordGetOrder( $orderID );
		$order_amount = $order["order_amount"];

		$exhange_rate = (float)$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_EXCHANGERATE');
		if ( (float)$exhange_rate == 0 )
		$exhange_rate = 1;

		$order_amount = round($order_amount/$exhange_rate,2);

		$is_MSIE = (isset($_SERVER['HTTP_USER_AGENT'])&&(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false))?true:false;

		$res = "";

		$description = str_replace("[orderID]",$orderID,$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_PAYMENTS_DESC'));
		if($is_MSIE){
			$description = translit($description);
		}
		$res .=
			"<table width='100%'>\n".
			"	<tr>\n".
			"		<td align='center'>\n".
			"<form method='POST' action='https://merchant.webmoney.ru/lmi/payment.asp'".($is_MSIE?">\n":" accept-charset='windows-1251'>\n").
			"	<input type='hidden' name='LMI_PAYMENT_AMOUNT' value='".$order_amount."'>\n".
			"	<input type='hidden' name='LMI_PAYMENT_DESC' value='".$description."'>\n".
			"	<input type='hidden' name='LMI_PAYMENT_NO' value='".$orderID."'>\n".
			"	<input type='hidden' name='LMI_PAYEE_PURSE' value=".$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_PURSE').">\n".
			"	<input type='hidden' name='LMI_MODE' value=".$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_TESTMODE').">\n".
			"	<input type='hidden' name='LMI_RESULT_URL' value='".$this->getDirectTransactionResultURL('success',array($orderID,$order['customer_email']))."'>".
			"	<input type='hidden' name='LMI_SUCCESS_URL' value='".$this->getTransactionResultURL('success')."'>".
			"	<input type='hidden' name='LMI_FAIL_URL' value='".$this->getTransactionResultURL('failure')."'>".
			"	<input type='submit' value='Оплатить по WebMoney сейчас!' align='center'>\n".
			"</form>\n".
			"		</td>\n".
			"	</tr>\n".
			"</table>";
		return $res;
	}

	/*
	 * Check user sign
	 * md5
	 * 1.Кошелек продавца (LMI_PAYEE_PURSE);
	 * 2.Сумма платежа (LMI_PAYMENT_AMOUNT);
	 * 3.Внутренний номер покупки продавца (LMI_PAYMENT_NO);
	 * 4.Флаг тестового режима (LMI_MODE);
	 * 5.Внутренний номер счета в системе WebMoney Transfer (LMI_SYS_INVS_NO);
	 * 6.Внутренний номер платежа в системе WebMoney Transfer (LMI_SYS_TRANS_NO);
	 * 7.Дата и время выполнения платежа (LMI_SYS_TRANS_DATE);
	 * 8.Secret Key (LMI_SECRET_KEY);
	 * 9.Кошелек покупателя (LMI_PAYER_PURSE);
	 * 10.WMId покупателя (LMI_PAYER_WM).
	 */

	function transactionResultHandler($transaction_result = '',$message = '',$source = 'frontend')
	{
		$log = '';
		if($source == 'handler'){
			$orderID = isset($_POST['LMI_PAYMENT_NO'])?$_POST['LMI_PAYMENT_NO']:0;
			if($orderID &&($order = _getOrderById($orderID))){
				if($this->validateResultKey(array($orderID,$order['customer_email']))){

					$sharedSecret = $this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_SHARED_SECRET');
					if($sharedSecret){//check callback sign
						// ...
						$transaction_sign = isset($_POST['LMI_HASH'])?strtolower($_POST['LMI_HASH']):null;
						$transaction_hash = strtolower(md5(
						//Кошелек продавца
						($payee_purse = isset($_POST['LMI_PAYEE_PURSE'])?$_POST['LMI_PAYEE_PURSE']:'')
						//Сумма платежа
						.($payment_amount = isset($_POST['LMI_PAYMENT_AMOUNT'])?$_POST['LMI_PAYMENT_AMOUNT']:'')
						//Внутренний номер покупки продавца
						.$orderID
						//Флаг тестового режима
						.($mode = isset($_POST['LMI_MODE'])?$_POST['LMI_MODE']:'')
						//Внутренний номер счета в системе WebMoney Transfer
						.($sys_invs_no = isset($_POST['LMI_SYS_INVS_NO'])?$_POST['LMI_SYS_INVS_NO']:'')
						//Внутренний номер платежа в системе WebMoney Transfer
						.($sys_trans_no = isset($_POST['LMI_SYS_TRANS_NO'])?$_POST['LMI_SYS_TRANS_NO']:'')
						//Дата и время выполнения платежа
						.($sys_trans_date = isset($_POST['LMI_SYS_TRANS_DATE'])?$_POST['LMI_SYS_TRANS_DATE']:'')
						//Secret Key
						.$sharedSecret
						//Кошелек покупателя
						.($payer_purse = isset($_POST['LMI_PAYER_PURSE'])?$_POST['LMI_PAYER_PURSE']:'')
						//WMId покупателя
						.($payer_wm = isset($_POST['LMI_PAYER_WM'])?$_POST['LMI_PAYER_WM']:'')
						));
						$prerequest = (isset($_POST['LMI_PREREQUEST'])&&$_POST['LMI_PREREQUEST'])?true:false;
						if((!$prerequest)&&(!$transaction_sign||$transaction_sign!=$transaction_hash)){
							$transaction_result = 'failure';
							$log .= ' invalid fields sign';
						}
						$exhange_rate = (float)$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_EXCHANGERATE');
						if ( (float)$exhange_rate == 0 ){
							$exhange_rate = 1;
						}
						$order_amount = round($order["order_amount"]/$exhange_rate,2);
						if($order_amount!=floatval($payment_amount)){
							$transaction_result = 'failure';
							$log .= ' wrong LMI_PAYMENT_AMOUNT recieved';
						}
						
						if($payee_purse !=$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_PURSE')){
							$transaction_result = 'failure';
							$log .= ' wrong LMI_PAYEE_PURSE recieved';
						}
						
						if($mode != $this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_TESTMODE')){
							$transaction_result = 'failure';
							$log .= ' wrong LMI_PAYEE_PURSE recieved';
						}
						
						if($transaction_result == 'success'){
							//change order status on setted at module settings
							if($prerequest){
								die('YES');
							}
							$statusID = $this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_ORDERSTATUS');
							if($statusID!=-1){
								$comment = $sys_invs_no?sprintf("Заказ оплачен по Webmoney%s. Номер счета — %s, номер платежа — %s.",($mode?' (тестовый режим)':''),$sys_invs_no,$sys_trans_no):'Заказ оплачен по WebMoney';
								ostSetOrderStatusToOrder( $orderID, $statusID,$comment,0,true);
							}
						}elseif($transaction_result == 'failure'){
							$statusID = $order['statusID'];
							$remote_ip = false;
							if(function_exists("getallheaders")){
								$request_headers = getallheaders();
								if(isset($request_headers['X-Real-IP'])){
									$remote_ip = $request_headers['X-Real-IP'];
								}
							}
							if(!$remote_ip){
								$remote_ip = $_SERVER["REMOTE_ADDR"];
							}

							$comment = sprintf("Ошибочный запрос от WebMoney;\nIP: %s\n",$remote_ip);
							$this->log_mode |= LOGMODE_MSG|LOGMODE_ERROR;
							$this->LogFile = DIR_LOG.'/webmoney.log';
							$this->_log(LOGMODE_MSG,$comment."\r\nПараметры неверного запроса:\r\n".var_export(array('GET'=>$_GET,'POST'=>$_POST,'REQUEST'=>$_REQUEST,'log'=>$log),true));
							ostSetOrderStatusToOrder( $orderID, $statusID,$comment,0,true);
							if($prerequest){
								die($log?$log:"Ошибочный запрос от WebMoney");
							}

						}
					}elseif($transaction_result == 'failure'){
						//log at order processing history
						$statusID = 3;
						//ostSetOrderStatusToOrder($orderID, $statusID, translate('ordr_added_comment').': '.$comment, ($this->getData('notify_customer')?1:0), true);
					}elseif($transaction_result == 'success'){
						//change order status on setted at module settings without secure key
						$statusID = $this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_ORDERSTATUS');
						if($statusID!=-1){
							$mode = $this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_TESTMODE');
							$sys_invs_no = isset($_POST['LMI_SYS_INVS_NO'])?$_POST['LMI_SYS_INVS_NO']:'';
							$sys_trans_no = isset($_POST['LMI_SYS_TRANS_NO'])?$_POST['LMI_SYS_TRANS_NO']:'';

							$comment = $sys_invs_no?sprintf("Заказ оплачен по Webmoney%s. Номер счета — %s, номер платежа — %s.",($mode?' (тестовый режим)':''),$sys_invs_no,$sys_trans_no):'Заказ оплачен по WebMoney';
							ostSetOrderStatusToOrder( $orderID, $statusID,$comment,0,true);
						}
					}
				}else{
					$transaction_result = 'failure';
					$statusID = 3;
					//ostSetOrderStatusToOrder( $orderID, $statusID,$log,1,$force=true);
				}
			}else{
				$log = "Order with id {$orderID} not exists";
				$orderID = false;
				$transaction_result = 'failure';
			}
		}

		return parent::transactionResultHandler($transaction_result,$message.$log,$source);
	}

}
?>