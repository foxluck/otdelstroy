<?php
// Модуль интеграции с платежной системой Яндекс.Деньги
// http://money.yandex.ru

/**
 * Модуль интеграции с платежной системой Яндекс.Деньги
 * @link http://money.yandex.ru
 * @connect_module_class_name CYandexMoney
 * @package DynamicModules
 * @subpackage Payment
 */

class CYandexMoney extends PaymentModule
{

	var $type = PAYMTD_TYPE_ONLINE;
	var $language = 'obsolete_module';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/yandexmoney.gif';
	
	function _initVars()
	{
		
		parent::_initVars();
		$this->title 		= "Яндекс.Деньги";
		$this->description 	= "Модуль интеграции с платежной системой Яндекс.Деньги (http://money.yandex.ru) по схеме 'Прямой платеж на счет'";
		$this->sort_order 	= 2;
		
		$this->Settings = array( 
				"CONF_PAYMENTMODULE_YANDEXMONEY_MERCHANT_ID",
				"CONF_PAYMENTMODULE_YANDEXMONEY_MERCHANT_ADDRESS", 
				"CONF_PAYMENTMODULE_YANDEXMONEY_MERCHANT_EXCHANGERATE",
				"CONF_PAYMENTMODULE_YANDEXMONEY_PAYMENTS_DESC"  
			);
	}

	function _initSettingFields()
	{
		
		$this->SettingsFields['CONF_PAYMENTMODULE_YANDEXMONEY_MERCHANT_ID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Номер счета в Яндекс.Деньги', 
			'settings_description' 	=> 'Указите номер Вашего счета в системе Яндекс.Деньги', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_YANDEXMONEY_MERCHANT_ADDRESS'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Адрес продавца / магазина', 
			'settings_description' 	=> 'В качестве адреса может быть указан IP, доменный или e-mail адрес (используется для отправки уведомления об оплате)', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 2,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_YANDEXMONEY_MERCHANT_EXCHANGERATE'] = array(
			'settings_value' 		=> '1', 
			'settings_title' 			=> 'Курс у.е. магазина по отношению к валюте в системе Яндекс.Деньги', 
			'settings_description' 	=> 'Платеж в системе Яндекс.Деньги<br>осуществляется <u>в рублях</u>', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(1,', 
			'sort_order' 			=> 3,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_YANDEXMONEY_PAYMENTS_DESC'] = array(
			'settings_value' 		=> 'Оплата заказа №[orderID]', 
			'settings_title' 			=> 'Назначение платежа', 
			'settings_description' 	=> 'Укажите описание платежей. Вы можете использовать строку [orderID] - она автоматически будет заменена на номер заказа', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 4,
		);
	}

	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );
		$order_amount = $order["order_amount"];

		$exhange_rate = (float)$this->_getSettingValue('CONF_PAYMENTMODULE_YANDEXMONEY_MERCHANT_EXCHANGERATE');
		if ( (float)$exhange_rate == 0 )
			$exhange_rate = 1;

		$order_amount = $order_amount*((float)$this->_getSettingValue('CONF_PAYMENTMODULE_YANDEXMONEY_MERCHANT_EXCHANGERATE'));

		$res = "";
		$res .= 
			"<table width='100%'>\n".
			"	<tr>\n".
			"		<td align='center'>\n".
			"		<form method=\"POST\" action=\"http://money.yandex.ru/select-wallet.xml\">\n".
			"		<input type=hidden name=\"wbp_Version\" value=\"2\">\n".
			"		<input type=\"hidden\" name=\"wbp_MessageType\" value=\"DirectPaymentIntoAccountRequest\">\n".
			"		<input type=\"hidden\" name=\"wbp_ShopAddress\" value=\"".$this->_getSettingValue('CONF_PAYMENTMODULE_YANDEXMONEY_MERCHANT_ADDRESS')."\">\n".
			"		<input type=\"hidden\" name=\"wbp_accountid\" value=\"".$this->_getSettingValue('CONF_PAYMENTMODULE_YANDEXMONEY_MERCHANT_ID')."\">\n".
			"		<input type=\"hidden\" name=\"wbp_currencyamount\" value=\"643;".$order_amount."\">\n".
			"		<input type=\"hidden\" name=\"wbp_shortdescription\" value=\"".str_replace("[orderID]",$orderID,$this->_getSettingValue('CONF_PAYMENTMODULE_YANDEXMONEY_PAYMENTS_DESC'))."\">\n".
			"		<input type=\"hidden\" name=\"wbp_ShopErrorInfo\" value=\"\">\n".
			"		<input type=\"hidden\" name=\"wbp_template_1\" value=\"\">\n".
			"		<input type=\"hidden\" name=\"wbp_template_2\" value=\"\">\n".
			"		<input type=submit value=\"Оплатить заказ в системе Яндекс.Деньги сейчас!\"><br>\n".
			"		(Вы должны запустить программу Интернет.Кошелек ДО нажатия на эту кнопку)\n".
			"		</form>\n".
			"		</td>\n".
			"	</tr>\n".
			"</table>";

		return $res;
	}
	
	function transactionResultHandler($transaction_result = '',$message = '',$source = 'frontend')
	{
		$orderID = 0;
		$log = '';
		if($orderID &&($order = _getOrderById($orderID))){
			$log = 'log';
			if($this->validateResultKey(array($orderID,$order['customer_email']))){
				//check callback sign
				// ...
				//
				if($transaction_result == 'success'){
					//change order status on setted at module settings
					$statusID = $this->_getSettingValue('CONF_CHRONOPAY_ORDERSTATUS');
					if($statusID!=-1){
						$comment = isset($_POST['transaction_id'])?sprintf('ChronoPay transaction ID: %d',intval($_POST['transaction_id'])):'auto status changed';
						ostSetOrderStatusToOrder( $orderID, $statusID,$comment,0);
						
					}
				}elseif($transaction_result == 'failure'){
					//log at order processing history
					$statusID = 3;
					//ostSetOrderStatusToOrder( $orderID, $statusID,$log,1,$force=true);
					//ostSetOrderStatusToOrder($orderID, $statusID, translate('ordr_added_comment').': '.$comment, ($this->getData('notify_customer')?1:0), true);
				}
			}else{
				$transaction_result = 'failure';
				$statusID = 3;
				//ostSetOrderStatusToOrder( $orderID, $statusID,$log,1,$force=true);
			}
		}else{
			$log = "Order with id {$orderID} not exists";
			$transaction_result = 'failure';
		}
		return parent::transactionResultHandler($transaction_result,$message.$log,$source);
	}
}
?>