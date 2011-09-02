<?php
// RUPAY payment module
// http://www.rupay.com

/**
 * @connect_module_class_name CRUpay
 * @package DynamicModules
 * @subpackage Payment
 */
class CRUpay extends PaymentModule{

	var $type = PAYMTD_TYPE_ONLINE;
	var $language = 'obsolete_module';
	
	function _initVars(){
		
		parent::_initVars();
		$this->title 		= "RUpay (On-line касса)";
		$this->description 	= "Оплата в системе RUpay - метод интеграции \"On-line касса\". Подробнее: http://www.rupay.com";
		$this->sort_order 	= 1;
		
		$this->Settings = array( 
				"CONF_PAYMENTMODULE_RUPAY_MERCHANT_EMAIL",
				"CONF_PAYMENTMODULE_RUPAY_PAYMENTS_DESC",
				"CONF_PAYMENTMODULE_RUPAY_USD_CURRENCY"
			);
	}

	function _initSettingFields(){
		
		$this->SettingsFields['CONF_PAYMENTMODULE_RUPAY_MERCHANT_EMAIL'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Email (идентификатор в системе RUpay)', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 2,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_RUPAY_PAYMENTS_DESC'] = array(
			'settings_value' 		=> 'Оплата заказа №[orderID]', 
			'settings_title' 			=> 'Назначение платежа', 
			'settings_description' 	=> 'Укажите описание платежей. Вы можете использовать строку [orderID] - она автоматически будет заменена на номер заказа', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 2,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_RUPAY_USD_CURRENCY'] = array(
			'settings_value' 		=> CONF_DEFAULT_CURRENCY, 
			'settings_title' 			=> 'Валюта \"Доллары США\" в Вашем магазине', 
			'settings_description' 	=> 'Сумма к оплате, отправляемая на сервер RUpay, указывается в долларах США (USD). Выберите из списка доллары США в Вашем магазине - это необходимо для верного пересчета суммы (по курсу доллара). Если тип вылюты не определен, курс считается равным 1', 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 3,
		);
	}
	
	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );

		//посчитать order amount в USD
		if ( $this->_getSettingValue('CONF_PAYMENTMODULE_RUPAY_USD_CURRENCY') > 0 )
		{
			$RUpay_curr = currGetCurrencyByID ( $this->_getSettingValue('CONF_PAYMENTMODULE_RUPAY_USD_CURRENCY') );
			$RUpay_curr_rate = $RUpay_curr["currency_value"];
		}
		if (!isset($RUpay_curr) || !$RUpay_curr)
		{
			$RUpay_curr_rate = 1;
		}
		$order_amount = round(100*$order["order_amount"] * $RUpay_curr_rate)/100;

		$res = "";
		$res .= 
			"<table width='100%'>\n".
			"	<tr>\n".
			"		<td align='center'>\n".
			"		<form action=\"https://rupay.com/pay.php\" name=\"pay\" method=\"POST\">\n".
			"		<input type=\"hidden\" name=\"in_email\" value=\"".$this->_getSettingValue('CONF_PAYMENTMODULE_RUPAY_MERCHANT_EMAIL')."\">\n".
			"		<input type=\"hidden\" name=\"send_sum\" value=\"".$order_amount."\">\n".
			"		<input type=\"hidden\" name=\"name_service\" value=\"".str_replace("[orderID]",$orderID,$this->_getSettingValue('CONF_PAYMENTMODULE_RUPAY_PAYMENTS_DESC'))."\">\n".
			"		<input type=\"hidden\" name=\"order_id\" value=\"".$orderID."\">\n".
			"		<input type=\"hidden\" name=\"success_url\" value=\"".getTransactionResultURL('success')."\">\n".
			"		<input type=\"hidden\" name=\"fail_url\" value=\"".getTransactionResultURL('failure')."\">\n".
			"		<input type=\"submit\" name=\"button\" value=\"Оплатить заказ в системе RUpay сейчас!\">\n".
			"		</form>\n".
			"		</td>\n".
			"	</tr>\n".
			"</table>";

		return $res;
	}
}
?>