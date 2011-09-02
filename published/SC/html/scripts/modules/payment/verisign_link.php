<?php
/**
 * VeriSign Link payment module
 *
 * @connect_module_class_name CVeriSignLink
 * @link http://www.verisign.com
 * @package DynamicModules
 * @subpackage Payment
 */
class CVeriSignLink extends PaymentModule
{

	var $type = PAYMTD_TYPE_CC;
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/paypal.gif';

	private $url = 'https://payflowlink.paypal.com';//'https://payments.verisign.com/payflowlink';


	function _initVars()
	{

		parent::_initVars();
		$this->title 		= CVERISIGNLINK_TTL;
		$this->description 	= CVERISIGNLINK_DSCR;
		$this->sort_order 	= 5;

		$this->Settings = array(
				"CONF_PAYMENTMODULE_VERISIGNLINK_LOGIN",
				"CONF_PAYMENTMODULE_VERISIGNLINK_PARTNER",
				"CONF_PAYMENTMODULE_VERISIGNLINK_TRANSTYPE",
				"CONF_PAYMENTMODULE_VERISIGNLINK_USD_CURRENCY",
		);
	}

	function _initSettingFields()
	{

		$this->SettingsFields['CONF_PAYMENTMODULE_VERISIGNLINK_LOGIN'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> CVERISIGNLINK_CFG_LOGIN_TTL, 
			'settings_description'	 	=> CVERISIGNLINK_CFG_LOGIN_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 				=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_VERISIGNLINK_PARTNER'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> CVERISIGNLINK_CFG_PARTNER_TTL, 
			'settings_description' 		=> CVERISIGNLINK_CFG_PARTNER_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 				=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_VERISIGNLINK_TRANSTYPE'] = array(
			'settings_value' 			=> 'S', 
			'settings_title' 			=> CVERISIGNLINK_CFG_TRANSTYPE_TTL, 
			'settings_description' 		=> CVERISIGNLINK_CFG_TRANSTYPE_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(CVeriSignLink::getTranstypeOptions(),', 
			'sort_order' 				=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_VERISIGNLINK_USD_CURRENCY'] = array(
			'settings_value' 			=> '0', 
			'settings_title' 			=> CVERISIGNLINK_CFG_USD_CURRENCY_TTL, 
			'settings_description' 		=> CVERISIGNLINK_CFG_USD_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 				=> 1,
		);
	}

	function getTranstypeOptions()
	{
		$options = array();
		$options[] = array('title' => CVERISIGNLINK_TXT_GETTRANSTYPEOPTIONS_1,'value' => 'S');
		$options[] = array('title' => CVERISIGNLINK_TXT_GETTRANSTYPEOPTIONS_2,'value' => 'A');
		return $options;
	}

	function after_processing_html( $orderID, $active = true)
	{
		$order = ordGetOrder( $orderID );

		#get order amount
		if ( $this->_getSettingValue('CONF_PAYMENTMODULE_VERISIGNLINK_USD_CURRENCY') > 0 ){
			$curr = currGetCurrencyByID ( $this->_getSettingValue('CONF_PAYMENTMODULE_VERISIGNLINK_USD_CURRENCY') );
			$curr_rate = $curr["currency_value"];
		}

		if (!isset($curr) || !$curr){
			$curr_rate = 1;
		}

		$order_amount = RoundFloatValue( $order["order_amount"] * $curr_rate );

		#get billing country ISO 2-chars code
		$sql = "select country_iso_3 from ?#COUNTRIES_TABLE where country_name_en = ?";
		$q = db_phquery($sql,$order["billing_country"]);
		if ($row = db_fetch_row($q)){
			$bcountry = $row[0];
		}else{
			$bcountry = "";
		}

		$order["billing_address"] = str_replace("\n","",$order["billing_address"]);
		$order['submit'] = CVERISIGNLINK_TXT_AFTER_PROCESSING_HTML_1;

		$order = xHtmlSpecialChars($order);

		$res = <<<HTML
<table width='100%'>
	<tr>
		<td align='center'>
		<form method="POST" name="verisignLINKform" id="verisignLINKform" action="{$this->url}">
			<input type="hidden" name="LOGIN"		value="{$this->_getSettingValue('CONF_PAYMENTMODULE_VERISIGNLINK_LOGIN')}">
			<input type="hidden" name="PARTNER"	 	value="{$this->_getSettingValue('CONF_PAYMENTMODULE_VERISIGNLINK_PARTNER')}">
			<input type="hidden" name="AMOUNT"		value="{$order_amount}">
			<input type="hidden" name="TYPE"		value="{$this->_getSettingValue('CONF_PAYMENTMODULE_VERISIGNLINK_TRANSTYPE')}">
			<input type="hidden" name="DESCRIPTION" value="Order #{$orderID}">
			<input type="hidden" name="NAME"		value="{$order["billing_firstname"]} {$order["billing_lastname"]}">
			<input type="hidden" name="ADDRESS"	 	value="{$order["billing_address"]}">
			<input type="hidden" name="CITY"	 	value="{$order["billing_city"]}">
			<input type="hidden" name="STATE"	 	value="{$order["billing_state"]}">
			<input type="hidden" name="ZIP"		 	value="{$order["billing_zip"]}">
			<input type="hidden" name="COUNTRY"	 	value="{$bcountry}">
			<input type="hidden" name="EMAIL"	 	value="{$order["customer_email"]}">
		<!--
			<input type="hidden" name="PHONE"	 	value="{$order["billing_city"]}>
			<input type="hidden" name="FAX"		 	value="{$order["billing_state"]}">
		
		-->
			<input type="submit"					value="{$order['submit']}">
		</form>
		</td>
	</tr>
</table>
HTML;
		if($active){
			$res .= <<<JS
<script type="text/javascript">
<!--
setTimeout('document.getElementById("verisignLINKform").submit();',2000);
//-->
</script>
JS;
		}

		return $res;
	}
}
?>