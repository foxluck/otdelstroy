<?php
// WorldPay payment module
// http://www.worldpay.com

/**
 * @connect_module_class_name CWorldPay
 * @package DynamicModules
 * @subpackage Payment
 */
class CWorldPay extends PaymentModule {

	var $type = PAYMTD_TYPE_CC;
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/worldpay.gif';
	
	#old url was https://select.worldpay.com/wcc/purchase
	private $url = 'https://secure.wp3.rbsworldpay.com/wcc/purchase';
	
	function _initVars(){
		
		parent::_initVars();
		$this->title 		= CWORLDPAY_TTL;
		$this->description 	= CWORLDPAY_DSCR;
		$this->sort_order 	= 2;
		
		$this->Settings = array( 
				"CONF_PAYMENTMODULE_WORLDPAY_INSTID"
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_WORLDPAY_INSTID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CWORLDPAY_CFG_INSTID_TTL, 
			'settings_description' 	=> CWORLDPAY_CFG_INSTID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
	}

	function after_processing_html( $orderID, $active = true ) 
	{
		$order = ordGetOrder( $orderID );
		$order_amount = round(100*$order["order_amount"] * $order["currency_value"])/100;

		$res = "";
		$country = "US";
		
		$fields = LanguagesManager::ml_getLangFieldNames('country_name');
		$where_clause = '';
		foreach($fields as $field){
			if($field && isset($order["billing_country"])&&$order["billing_country"])
			$where_clause .= (strlen($where_clause)?' OR ':'')."{$field} = ?billing_country";
		}
		if(strlen($where_clause)){
			$sql  = 'SELECT country_iso_2 FROM ?#COUNTRIES_TABLE WHERE '.$where_clause;
			//$q = db_query("select country_iso_2 from ".COUNTRIES_TABLE." where ".LanguagesManager::ml_getLangFieldName('country_name')." = '".$order["billing_country"]."';") or die (db_error());
			$q = db_phquery($sql,$order);
			if ($row = db_fetch_row($q)) //country is not defined
			{
				$country = $row[0];
				
			}
		}

		$res .= 
			"<table width='100%'>\n".
			"	<tr>\n".
			"		<td align='center'>\n".
			"<form method='POST' action='{$this->url}' id='worldpay_form'>\n".
			"<input type=\"hidden\" name=\"instId\" value=\"".$this->_getSettingValue('CONF_PAYMENTMODULE_WORLDPAY_INSTID')."\">\n".
			"<input type=\"hidden\" name=\"desc\" value=\"".CONF_SHOP_NAME." - Order #".$orderID."\">\n".
			"<input type=\"hidden\" name=\"cartId\" value=\"".$orderID."\">\n".
			"<input type=\"hidden\" name=\"amount\" value=\"".$order_amount."\">\n".
			"<input type=\"hidden\" name=\"currency\" value=\"".$order["currency_code"]."\">\n".
			"<input type=\"hidden\" name=\"testMode\" value=\"0\">\n".
			"<input type=\"hidden\" name=\"country\" value=\"".$country."\">\n".
			"<input type=\"hidden\" name=\"postcode\" value=\"".$order["billing_zip"]."\">\n".
			"<input type=\"hidden\" name=\"address\" value=\"".str_replace("\n","&#10;",$order["billing_address"])."\">\n".
			"<input type=\"hidden\" name=\"email\" value=\"".$order["customer_email"]."\">\n".
			"<input type=\"submit\" value=\"".CWORLDPAY_TXT_AFTER_PROCESSING_HTML_1."\"></form>\n".		
			"		</td>\n".
			"	</tr>\n".
			"</table>";
	if($active){
		$res .= '<script type="text/javascript">
<!--
setTimeout(\'document.getElementById("worldpay_form").submit();\',2000);
//-->
</script>';
		}

		return $res;
	}
}
?>