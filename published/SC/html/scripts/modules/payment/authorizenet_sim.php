<?php
/**
 * Authorize.Net SIM payment module (Simple Integration Method)
 * @see http://www.authorize.net
 * @connect_module_class_name CAuthorizeNetSIM
 * @package DynamicModules
 * @subpackage Payment
 */

class CAuthorizeNetSIM extends PaymentModule
{

	var $type = PAYMTD_TYPE_CC;
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/authorizenet.gif';

	function _initVars(){

		parent::_initVars();
		$this->title 		= CAUTHORIZENETSIM_TTL;
		$this->description 	= CAUTHORIZENETSIM_DSCR;
		$this->sort_order 	= 2;

		$this->Settings = array(
				"CONF_PAYMENTMODULE_AUTHNETSIM_LOGIN",
				"CONF_PAYMENTMODULE_AUTHNETSIM_TRAN_KEY",
				"CONF_PAYMENTMODULE_AUTHNETSIM_TESTMODE",
		);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_AUTHNETSIM_LOGIN'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CAUTHORIZENETSIM_CFG_LOGIN_TTL, 
			'settings_description' 	=> CAUTHORIZENETSIM_CFG_LOGIN_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_PAYMENTMODULE_AUTHNETSIM_TRAN_KEY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CAUTHORIZENETSIM_CFG_TRAN_KEY_TTL, 
			'settings_description' 	=> CAUTHORIZENETSIM_CFG_TRAN_KEY_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_PAYMENTMODULE_AUTHNETSIM_TESTMODE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CAUTHORIZENETSIM_CFG_TESTMODE_TTL, 
			'settings_description' 	=> CAUTHORIZENETSIM_CFG_TESTMODE_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
	}

	/**
	 * Makes HMAC MD5 hash of the $data
	 * @see http://www.php.net/manual/en/function.mhash.php
	 * @param $key string
	 * @param $data string
	 * @return string hashed string
	 */
	function hmac ($key, $data)
	{
		// RFC 2104 HMAC implementation for php.
		// Creates an md5 HMAC.
		// Eliminates the need to install mhash to compute a HMAC
		// Hacked by Lance Rushing

		$b = 64; // byte length for md5
		if (strlen($key) > $b) {
			$key = pack("H*",md5($key));
		}
		$key  = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
	}

	function after_processing_html( $orderID, $active = true )
	{
		$order = ordGetOrder( $orderID );
		$order_amount = $order["order_amount"] * $order["currency_value"];

		$fp_timestamp = time();
		$fp_sequence = $orderID;
		$currency_code = $order["currency_code"];

		$testmode = $this->_getSettingValue('CONF_PAYMENTMODULE_AUTHNETSIM_TESTMODE') ? 'TRUE' : 'FALSE';
		$fp_login = $this->_getSettingValue('CONF_PAYMENTMODULE_AUTHNETSIM_LOGIN');
		$fp_hash_string = $fp_login."^".$fp_sequence."^".$fp_timestamp."^".$order_amount."^".$currency_code;
		$fp_hash = $this->hmac($this->_getSettingValue('CONF_PAYMENTMODULE_AUTHNETSIM_TRAN_KEY'),  $fp_hash_string);
		$order = xHtmlSpecialChars($order);

		$submit_title = CAUTHORIZENETSIM_TXT_1;
		$res = <<<HTML
<form method="POST" name="authSIMform" id="authSIMform" action="https://secure.authorize.net/gateway/transact.dll">
<table style="width: 100%">
	<tr>
		<td align='center'>
			
			<input type="hidden" name="x_login" value="{$fp_login}">
			<input type="hidden" name="x_test_request" value="{$testmode}">
			<input type="hidden" name="x_show_form" value="PAYMENT_FORM">
			<input type="hidden" name="x_fp_sequence" value="{$fp_sequence}">
			<input type="hidden" name="x_fp_timestamp" value="{$fp_timestamp}">
			<input type="hidden" name="x_fp_hash" value="{$fp_hash}">
			<input type="hidden" name="x_amount" value="{$order_amount}">
			<input type="hidden" name="x_currency_code" value="{$currency_code}">


			<input type="hidden" name="x_first_name" value="{$order['billing_firstname']}">
			<input type="hidden" name="x_last_name" value="{$order["billing_lastname"]}">
			<input type="hidden" name="x_address" value="{$order["billing_address"]}">
			<input type="hidden" name="x_city" value="{$order["billing_city"]}">
			<input type="hidden" name="x_state" value="{$order["billing_state"]}">
			<input type="hidden" name="x_zip" value="{$order["billing_zip"]}">
			<input type="hidden" name="x_country" value="{$order["billing_country"]}">
			<input type="hidden" name="x_email" value="{$order["customer_email"]}">
			<input type="hidden" name="x_customer_ip" value="{$order["customer_ip"]}">

			<input type="hidden" name="x_invoice_num" value="{$orderID}">
			<input type="hidden" name="x_description" value="Order #{$orderID}">
			<input type="hidden" name="x_ship_to_first_name" value="{$order["shipping_firstname"]}">
			<input type="hidden" name="x_ship_to_last_name" value="{$order["shipping_lastname"]}">
			<input type="hidden" name="x_ship_to_address" value="{$order["shipping_address"]}">
			<input type="hidden" name="x_ship_to_city" value="{$order["shipping_city"]}">
			<input type="hidden" name="x_ship_to_state" value="{$order["shipping_state"]}">
			<input type="hidden" name="x_ship_to_zip" value="{$order["shipping_zip"]}">
			<input type="hidden" name="x_ship_to_country" value="{$order["shipping_country"]}">
			<input type='hidden' name='x_relay_response' value='FALSE'>

			<input type="submit" value="{$submit_title}">
		</td>
	</tr>
</table>
</form>
HTML;

		if($active){
			$res .= <<<JS
<script type="text/javascript">
<!--
setTimeout(function(){document.getElementById("authSIMform").submit();},2000);
//-->
</script>
JS;
		}

		return $res;
	}

}
?>