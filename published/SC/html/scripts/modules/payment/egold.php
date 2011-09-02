<?php
	// E-Gold payment module
	// http://www.e-gold.com

/**
 * @connect_module_class_name CEGold
 * @package DynamicModules
 * @subpackage Payment
 */

class CEGold extends PaymentModule{

	var $type = PAYMTD_TYPE_ONLINE;
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/egold.gif';
	
	function _initVars(){
		
		parent::_initVars();
		$this->title 		= CEGOLD_TTL;
		$this->description 	= CEGOLD_DSCR;
		$this->sort_order 	= 1;
		
		$this->Settings = array( 
			"CONF_PAYMENTMODULE_EGOLD_MERCHANT_ACCOUNT",
			"CONF_PAYMENTMODULE_EGOLD_USD_CURRENCY"
			);
	}

	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );
		if ( $this->_getSettingValue('CONF_PAYMENTMODULE_EGOLD_USD_CURRENCY') > 0 )
		{
			$EGcurr = currGetCurrencyByID ( $this->_getSettingValue('CONF_PAYMENTMODULE_EGOLD_USD_CURRENCY') );
			$EGcurr_rate = $EGcurr["currency_value"];
		}
		if (!isset($EGcurr) || !$EGcurr)
		{
			$EGcurr_rate = 1;
		}

		$order_amount = round(100*$order["order_amount"] * $EGcurr_rate)/100;

		$res = "";

		$res .= 
			"<table width='100%'>\n".
			"	<tr>\n".
			"		<td align='center'>\n".
			"<form method='POST' action='https://www.e-gold.com/sci_asp/payments.asp'>\n".
			"<input type=\"hidden\" name=\"PAYEE_ACCOUNT\" value=\"".$this->_getSettingValue('CONF_PAYMENTMODULE_EGOLD_MERCHANT_ACCOUNT')."\">\n".
			"<input type=\"hidden\" name=\"PAYEE_NAME\" value=\"".str_replace("\"","&quot;",CONF_SHOP_NAME)."\">\n".
			"<input type=\"hidden\" name=\"PAYMENT_AMOUNT\" value=\"".$order_amount."\">\n".
			"<input type=hidden name=\"PAYMENT_UNITS\" value=1>\n". //USD; refer to http://www.e-gold.com/docs/e-gold_sci.html for more information
			"<input type=hidden name=\"PAYMENT_METAL_ID\" value=0>\n". //allow customer to select payment method (metal)
			"<input type=\"hidden\" name=\"STATUS_URL\" value=\"mailto:".CONF_ORDERS_EMAIL."\">".
			"<input type=\"hidden\" name=\"NOPAYMENT_URL\" value=\"".getTransactionResultURL('failure')."\">".
			"<input type=\"hidden\" name=\"NOPAYMENT_URL_METHOD\" value=\"LINK\">".
			"<input type=\"hidden\" name=\"PAYMENT_URL\" value=\"".getTransactionResultURL('success')."\">".
			"<input type=\"hidden\" name=\"PAYMENT_URL_METHOD\" value=\"LINK\">".
			"<input type=\"hidden\" name=\"BAGGAGE_FIELDS\" value=\"CUSTOMERID\">".
			"<input type=\"hidden\" name=\"CUSTOMERID\" value=\"\">".
			"<input type=\"hidden\" name=\"SUGGESTED_MEMO\" value=\"Thank you for shopping at ".str_replace("\"","&quot;",CONF_SHOP_NAME)." !\">".
			"<input type=\"hidden\" name=\"PAYMENT_ID\" value=\"".$orderID."\">\n".
			"<input type=\"submit\" name=\"PAYMENT_METHOD\" value=\"".CEGOLD_TXT_1."\">\n".		
			"		</td>\n".
			"	</tr>\n".
			"</table>";

		return $res;
	}

	function _initSettingFields(){
		
		$this->SettingsFields['CONF_PAYMENTMODULE_EGOLD_MERCHANT_ACCOUNT'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CEGOLD_CFG_MERCHANT_ACCOUNT_TTL, 
			'settings_description' 	=> CEGOLD_CFG_MERCHANT_ACCOUNT_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_PAYMENTMODULE_EGOLD_USD_CURRENCY'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> CEGOLD_CFG_USD_CURRENCY_TTL, 
			'settings_description' 	=> CEGOLD_CFG_USD_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 1,
		);
	}
}
?>