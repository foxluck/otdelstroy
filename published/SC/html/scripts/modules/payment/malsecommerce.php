<?php
	// Mal's e-commerce payment module
	// http://www.mals-e.com

/**
 * @connect_module_class_name CMalsE
 * @package DynamicModules
 * @subpackage Payment
 */


class CMalsE extends PaymentModule {

	var $type = PAYMTD_TYPE_CC;
	var $language = 'eng';
	
	function _initVars(){
		
		parent::_initVars();
		$this->title 		= CMALSE_TTL;
		$this->description 	= CMALSE_DSCR;
		$this->sort_order 	= 7;
		
		$this->Settings = array( 
				"CONF_PAYMENTMODULE_MALSE_USERID",
				"CONF_PAYMENTMODULE_MALSE_CURR_TYPE"
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_MALSE_USERID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CMALSE_CFG_USERID_TTL, 
			'settings_description' 	=> CMALSE_CFG_USERID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_MALSE_CURR_TYPE'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> CMALSE_CFG_CURR_TYPE_TTL, 
			'settings_description' 	=> CMALSE_CFG_CURR_TYPE_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 1,
		);
	}

	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );

		if ($this->_getSettingValue('CONF_PAYMENTMODULE_MALSE_CURR_TYPE') > 0)
		{
			$MCcurr = currGetCurrencyByID($this->_getSettingValue('CONF_PAYMENTMODULE_MALSE_CURR_TYPE'));
		}
		else
		{
			$MCcurr = array( "currency_value" => 1 );
		}
		$order_amount = round(100*$order["order_amount"] * $MCcurr["currency_value"])/100;


		$res = "";

		$res .= 
			"<table width='100%'>\n".
			"	<tr>\n".
			"		<td align='center'>\n".
			"<form method='POST' action='http://www.aitsafe.com/cf/addmulti.cfm'>\n".
			"<input type=\"hidden\" name=\"userid\" value=\"".$this->_getSettingValue('CONF_PAYMENTMODULE_MALSE_USERID')."\">\n".
			"<INPUT TYPE=\"HIDDEN\" NAME=\"qty1\">".
			"<INPUT TYPE=\"HIDDEN\" NAME=\"noqty1\" VALUE=1>".
			"<INPUT TYPE=\"HIDDEN\" NAME=\"product1\" VALUE=\"Order #".$orderID." (".show_price($order_amount).")\">".
			"<INPUT TYPE=\"HIDDEN\" NAME=\"price1\" VALUE=\"".$order_amount."\">".
			"<input type=\"submit\" name=\"submit\" value=\"".CMALSE_TXT_AFTER_PROCESSING_HTML_1."\">\n".		
			"		</td>\n".
			"	</tr>\n".
			"</table>";

		return $res;
	}
}
?>