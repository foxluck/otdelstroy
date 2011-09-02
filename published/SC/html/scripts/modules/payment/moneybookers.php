<?php
	// Moneybookers payment module
	// http://www.moneybookers.com

/**
 * @connect_module_class_name CMoneybookers
 * @package DynamicModules
 * @subpackage Payment
 */
class CMoneybookers extends PaymentModule {

	var $type = PAYMTD_TYPE_ONLINE;
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/moneybookers.gif';
	
	function _initVars(){
		
		parent::_initVars();
		$this->title 		= CMONEYBOOKERS_TTL;
		$this->description 	= CMONEYBOOKERS_DSCR;
		$this->sort_order 	= 1;
		
		$this->Settings = array( 
				"CONF_PAYMENTMODULE_MONEYBOOKERS_MERCHANT_EMAIL"
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_MONEYBOOKERS_MERCHANT_EMAIL'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CMONEYBOOKERS_CFG_MERCHANT_EMAIL_TTL, 
			'settings_description' 	=> CMONEYBOOKERS_CFG_MERCHANT_EMAIL_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
	}
	
	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );
		$order_amount = round(100*$order["order_amount"] * $order["currency_value"])/100;

		$res = "";

		$res .= 
			"<table width='100%'>\n".
			"	<tr>\n".
			"		<td align='center'>\n".
			"<form method='POST' action='https://www.moneybookers.com/app/payment.pl'>\n".
			"<input type=\"hidden\" name=\"pay_to_email\" value=\"".$this->_getSettingValue('CONF_PAYMENTMODULE_MONEYBOOKERS_MERCHANT_EMAIL')."\">\n".
			"<input type=\"hidden\" name=\"return_url\" value=\"".getTransactionResultURL('success')."\">\n".
			"<input type=\"hidden\" name=\"cancel_url\" value=\"".getTransactionResultURL('failure')."\">\n".
			"<input type=\"hidden\" name=\"status_url\" value=\"mailto:".$this->_getSettingValue('CONF_PAYMENTMODULE_MONEYBOOKERS_MERCHANT_EMAIL')."\">\n".
			"<input type=\"hidden\" name=\"language\" value=\"EN\">\n".
			"<input type=\"hidden\" name=\"detail1_description\" value=\"Order #\">\n".
			"<input type=\"hidden\" name=\"detail1_text\" value=\"".$orderID."\">\n".
			"<input type=\"hidden\" name=\"transaction_id\" value=\"".$orderID."\">\n".
			"<input type=\"hidden\" name=\"amount\" value=\"".$order_amount."\">\n".
			"<input type=\"hidden\" name=\"currency\" value=\"".$order["currency_code"]."\">\n".
			"<input type=\"hidden\" name=\"firstname\" value=\"".$order["billing_firstname"]."\">\n".
			"<input type=\"hidden\" name=\"lastname\" value=\"".$order["billing_lastname"]."\">\n".
			"<input type=\"hidden\" name=\"address\" value=\"".$order["billing_address"]."\">\n".
			"<input type=\"hidden\" name=\"postal_code\" value=\"".$order["billing_zip"]."\">\n".
			"<input type=\"hidden\" name=\"City\" value=\"".$order["billing_city"]."\">\n".
			"<input type=\"hidden\" name=\"confirmation_note\" value=\"Thank you for your order!\">\n".
			"<input type=\"submit\" value=\"".CMONEYBOOKERS_TXT_AFTER_PROCESSING_HTML_1."\">\n".		
			"		</td>\n".
			"	</tr>\n".
			"</table>";

		return $res;
	}
}
?>