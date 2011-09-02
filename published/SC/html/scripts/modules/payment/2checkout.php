<?php
/**
 * 2checkout payment module
 * @connect_module_class_name C2checkout
 * @see www.2checkout.com
 *
 */

class C2checkout extends PaymentModule
{

	var $type = PAYMTD_TYPE_CC;
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/2co.gif';

	function _initVars()
	{

		parent::_initVars();
		$this->title 		= C2CHECKOUT_TTL;
		$this->description 	= C2CHECKOUT_DSCR;
		$this->sort_order 	= 5;
		$this->Settings[] = "CONF_PAYMENTMODULE_2CHECKOUT_ID";
		$this->Settings[] = "CONF_PAYMENTMODULE_2CHECKOUT_SECRET";
		$this->Settings[] = "CONF_PAYMENTMODULE_2CO_USD_CURRENCY";
		$this->Settings[] = "CONF_PAYMENTMODULE_2CO_DEMO";

	}

	function _initSettingFields()
	{

		$this->SettingsFields['CONF_PAYMENTMODULE_2CHECKOUT_ID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> C2CHECKOUT_CFG_ID_TTL, 
			'settings_description' 	=> C2CHECKOUT_CFG_ID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_PAYMENTMODULE_2CHECKOUT_SECRET'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> C2CHECKOUT_CFG_SECRET_TTL, 
			'settings_description' 	=> C2CHECKOUT_CFG_SECRET_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_2CO_USD_CURRENCY'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> C2CHECKOUT_CFG_USD_CURRENCY_TTL, 
			'settings_description' 	=> C2CHECKOUT_CFG_USD_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_2CO_DEMO'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> C2CHECKOUT_CFG_DEMO_TTL, 
			'settings_description' 	=> C2CHECKOUT_CFG_DEMO_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
	}
	


	function getCustomProperties()
	{
		$customProperties = array();
		$customProperties[] = array (
			'settings_title'=>C2CHECKOUT_CUST_RESULT_URL_TTL,
			'settings_description'=>C2CHECKOUT_CUST_RESULT_URL_DSCR,
			'control'=>'<input type="text" onclick="this.select();" onfocus="this.select();" readonly="readonly" size="40" value="'
			.xHtmlSpecialChars($this->getTransactionResultURL('success'))
			.'">',
			);
		return $customProperties;
	}

	function after_processing_html( $orderID, $active = true )
	{
		$order = ordGetOrder( $orderID );

		if ( $this->_getSettingValue('CONF_PAYMENTMODULE_2CO_USD_CURRENCY') > 0 )
		{
			$TWOCOcurr = currGetCurrencyByID ( $this->_getSettingValue('CONF_PAYMENTMODULE_2CO_USD_CURRENCY') );
			$TWOCOcurr_rate = $TWOCOcurr["currency_value"];
		}
		if (!isset($TWOCOcurr) || !$TWOCOcurr)
		{
			$TWOCOcurr_rate = 1;
		}

		$order_amount = round( 100 * $order["order_amount"] * $TWOCOcurr_rate ) / 100;

		$res = "";
		$form_fields = array();
		$form_fields['sid'] = $this->_getSettingValue('CONF_PAYMENTMODULE_2CHECKOUT_ID');
		$form_fields['total'] = $order_amount;
		$form_fields['cart_order_id'] = $orderID;
		$form_fields['card_holder_name'] = $order["billing_firstname"]." ".$order["billing_lastname"];
		$form_fields['street_address'] = $order["billing_address"];


		$form_fields['city'] = $order["billing_city"];
		$form_fields['state'] = $order["billing_state"];
		$form_fields['zip'] = $order["billing_zip"];
		$form_fields['country'] = $order["billing_country"];
		$form_fields['email'] = $order["customer_email"];
		$form_fields['ship_street_address'] = $order["shipping_address"];
		$form_fields['ship_city'] = $order["shipping_city"];
		$form_fields['ship_state'] = $order["shipping_state"];
		$form_fields['ship_zip'] = $order["shipping_zip"];
		$form_fields['ship_country'] = $order["shipping_country"];
		if($this->_getSettingValue('CONF_PAYMENTMODULE_2CO_DEMO')){
			$form_fields['demo'] = 'Y';
		}

		$form_fields['c_prod'] = "Shop-Script order";
		$form_fields['id_type'] = 2;
		$string = '';
		$string .= $this->_getSettingValue('CONF_PAYMENTMODULE_2CHECKOUT_SECRET');
		$string .= $form_fields['sid'];
		$string .= $form_fields['cart_order_id'];
		$string .= $form_fields['total'];
		$form_fields['key'] = strtoupper(md5($string));

		$form_fields = xHtmlSpecialChars($form_fields);

		$res = <<<HTML
<table width='100%'>
	<tr>
		<td align='center'>
			<form method='POST' name='two_check_out_form' action='https://www.2checkout.com/2co/buyer/purchase'>
HTML;

		foreach($form_fields as $field=>$value){
			$res .= <<<HTML
				<input type="hidden" name="{$field}" value="{$value}">
HTML;
		}
		$submit = xHtmlSpecialChars(C2CHECKOUT_TXT_1);
		$res .= <<<HTML
				<input type="submit" value="{$submit}">
			</form>
		</td>
	</tr>
</table>
HTML;
		return $res;
	}
}
?>