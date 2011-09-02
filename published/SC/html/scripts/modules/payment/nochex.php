<?php
// NOchex payment module
// http://www.paypal.com

/**
 * @connect_module_class_name CNOCHEX
 * @package DynamicModules
 * @subpackage Payment
 */
class CNOCHEX extends PaymentModule {

	var $type = PAYMTD_TYPE_ONLINE;
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/nochex.gif';
	
	function _initVars(){
		
		parent::_initVars();
		$this->title 		= CNOCHEX_TTL;
		$this->description 	= CNOCHEX_DSCR;
		$this->sort_order 	= 3;
		
		$this->Settings = array( 
				"CONF_PAYMENTMODULE_NOCHEX_MERCHANT_EMAIL",
				"CONF_PAYMENTMODULE_NOCHEX_GBP_EXCHANGE_RATE"
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_NOCHEX_MERCHANT_EMAIL'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CNOCHEX_CFG_MERCHANT_EMAIL_TTL, 
			'settings_description' 	=> CNOCHEX_CFG_MERCHANT_EMAIL_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_NOCHEX_GBP_EXCHANGE_RATE'] = array(
			'settings_value' 		=> '1', 
			'settings_title' 			=> CNOCHEX_CFG_GBP_EXCHANGE_RATE_TTL, 
			'settings_description' 	=> CNOCHEX_CFG_GBP_EXCHANGE_RATE_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
	}
	
	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );

		$exhange_rate = (float)$this->_getSettingValue('CONF_PAYMENTMODULE_NOCHEX_GBP_EXCHANGE_RATE');
		if ( (float)$exhange_rate == 0 )
			$exhange_rate = 1;

		$order_amount = round(100 * $order["order_amount"] * $exhange_rate)/100;
		if ($order_amount == round($order_amount))
		{ //add .00
			$order_amount = (string) $order_amount;
			$order_amount .= ".00";
		}
		else
			if ($order_amount*10 == round($order_amount*10))
			{ //add 0
				$order_amount = (string) $order_amount;
				$order_amount .= ".00";
			}
		$res = "";

		$res .= 
			"<table width='100%'>\n".
			"	<tr>\n".
			"		<td align='center'>\n".
			"<form method='POST' action='https://www.nochex.com/nochex.dll/checkout'>\n".
			"<input type=\"hidden\" name=\"email\" value=\"".$this->_getSettingValue('CONF_PAYMENTMODULE_NOCHEX_MERCHANT_EMAIL')."\">\n".
			"<input type=\"hidden\" name=\"amount\" value=\"".$order_amount."\">\n".
			"<input type=\"hidden\" name=\"ordernumber\" value=\"".$orderID."\">\n".
			"<input type=\"hidden\" name=\"description\" value=\"".CONF_SHOP_NAME." - Order #".$orderID."\">\n".
			"<input type=\"hidden\" name=\"returnurl\" value=\"".getTransactionResultURL('success')."\">\n".
			"<input type=\"hidden\" name=\"firstname\" value=\"".$order["billing_firstname"]."\">\n".
			"<input type=\"hidden\" name=\"lastname\" value=\"".$order["billing_lastname"]."\">\n".
			"<input type=\"hidden\" name=\"town\" value=\"".$order["billing_city"]."\">\n".
			"<input type=\"hidden\" name=\"postcode\" value=\"".$order["billing_zip"]."\">\n".
			"<input type=\"hidden\" name=\"email_address_sender\" value=\"".$order["customer_email"]."\">\n".
			"<input type=\"submit\" value=\"".CNOCHEX_TXT_AFTER_PROCESSING_HTML_1."\"></form>\n".		
			"		</td>\n".
			"	</tr>\n".
			"</table>";

		return $res;
	}
}
?>