<?php
/**
 * @connect_module_class_name iDEAL_Basic
 * @package DynamicModules
 * @subpackage Payment
 */

class iDEAL_Basic extends PaymentModule{

	var $type = PAYMTD_TYPE_CC;
//	var $language = 'eng';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/ing.gif';
	
	var $test_uri = array(
			'ing'=>"https://idealtest.secure-ing.com/ideal/mpiPayInitIng.do",
			'rabobank'=>"https://idealtest.rabobank.nl/ideal/mpiPayInitRabo.do"
	);
	var $uri = array(
			'ing'=>"https://ideal.secure-ing.com/ideal/mpiPayInitIng.do",
			'rabobank'=>"https://idealtest.rabobank.nl/ideal/mpiPayInitRabo.do"
	);
	
	function _initVars(){
		
		$this->title 		= IDEALBASIC_TTL;
		$this->description 	= IDEALBASIC_DSCR;
		$this->sort_order 	= 2;
		
		$this->Settings = array( 
				"CONF_IDEALBASIC_TEST",
				"CONF_IDEALBASIC_SECRET_KEY",
				"CONF_IDEALBASIC_MERCHANT_ID",
				"CONF_IDEALBASIC_EUR_CURRENCY",
				"CONF_IDEALBASIC_BANK",
			);
	}

	function _initSettingFields(){
		
		$this->SettingsFields['CONF_IDEALBASIC_TEST'] = array(
			'settings_value' 			=> '1', 
			'settings_title' 			=> IDEALBASIC_TEST_TTL, 
			'settings_description' 		=> IDEALBASIC_TEST_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
		);
		
		$this->SettingsFields['CONF_IDEALBASIC_SECRET_KEY'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> IDEALBASIC_SECRET_KEY_TTL, 
			'settings_description' 		=> IDEALBASIC_SECRET_KEY_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
		);
		
		$this->SettingsFields['CONF_IDEALBASIC_MERCHANT_ID'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> IDEALBASIC_MERCHANT_ID_TTL, 
			'settings_description' 		=> IDEALBASIC_MERCHANT_ID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
		);

		$this->SettingsFields['CONF_IDEALBASIC_EUR_CURRENCY'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> IDEALBASIC_EUR_CURRENCY_TTL, 
			'settings_description' 		=> IDEALBASIC_EUR_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
		);
		$this->SettingsFields['CONF_IDEALBASIC_BANK'] = array(
			'settings_value' 			=> 'ing', 
			'settings_title' 			=> IDEALBASIC_BANK_TTL, 
			'settings_description' 		=> IDEALBASIC_BANK_TTL_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(iDEAL_Basic::listBank(),', 
		);
	}

	function __hash($data, $prd_num){

		$data['merchantKey'] = $this->_getSettingValue('CONF_IDEALBASIC_SECRET_KEY');
		$concatStr = $data['merchantKey'].$data['merchantID'].$data['subID'].$data['amount'].$data['purchaseID'].$data['paymentType'].$data['validUntil'];
		
		for ($i=1;$i<=$prd_num;$i++){
			
			$concatStr .= $data['itemNumber'.$i].substr($data['itemDescription'.$i],0,32).$data['itemQuantity'.$i].$data['itemPrice'.$i];
		}
		
		$concatStr = html_entity_decode($concatStr);
	    $not_allowed = array("\t", "\n", "\r", " ");
	    $concatStr = str_replace($not_allowed, "",$concatStr);
		return sha1($concatStr);
	}
	
	function after_processing_html( $orderID, $active = true  ) 
	{
		$order = ordGetOrder( $orderID );

		$res = "";
		$post_fields = array(
			'merchantID' => $this->_getSettingValue('CONF_IDEALBASIC_MERCHANT_ID'),
			'subID' => 0,
			'amount' => RoundFloatValue($this->_convertCurrency($order['order_amount'], 0, $this->_getSettingValue('CONF_IDEALBASIC_EUR_CURRENCY')))*100,
			'purchaseID' => $orderID,
			'language' => 'nl',
			'currency' => 'EUR',
			'description' => substr(CONF_SHOP_NAME,0,32),
			'hash' => '',
			'paymentType' => 'ideal',
			'validUntil' => date("Y-m-d\TG:i:s\Z",strtotime ("+1 week")),
			'urlSuccess' => getTransactionResultURL('success'),
			'urlCancel' => getTransactionResultURL('failure'),
			'urlError' => getTransactionResultURL('failure'),
		);

		$order_content = ordGetOrderContent($orderID);
		$cart_amount = 0;
		for ($i=0, $length = count($order_content); $i<$length; $i++){
			
			$post_fields['itemNumber'.($i+1)] = $order_content[$i]['itemID'];
			$post_fields['itemDescription'.($i+1)] = substr($order_content[$i]['name'], 0, 32);
			$post_fields['itemQuantity'.($i+1)] = $order_content[$i]['Quantity'];
			$post_fields['itemPrice'.($i+1)] = RoundFloatValue($this->_convertCurrency($order_content[$i]['Price'], $order['currency_code'], $this->_getSettingValue('CONF_IDEALBASIC_EUR_CURRENCY'))*($order['order_discount']?((100-$order['order_discount'])/100):1))*100;
			$cart_amount += $post_fields['itemPrice'.($i+1)]*$order_content[$i]['Quantity'];
		}

		if($post_fields['amount']>$cart_amount){
			
			$post_fields['itemNumber'.($i+1)] = 'SHIPPING';
			$post_fields['itemDescription'.($i+1)] = substr(IDEALBASIC_TXT_SHIPPINGTAX,0,32);
			$post_fields['itemQuantity'.($i+1)] = 1;
			$post_fields['itemPrice'.($i+1)] = $post_fields['amount'] - $cart_amount;
		}

		$post_fields['hash'] = $this->__hash($post_fields, count($order_content));

		$post_fields_html = '';
		foreach ($post_fields as $field_name=>$field_value)$post_fields_html .= "\n<input type='hidden' name='{$field_name}' value='".xHtmlSpecialChars($field_value)."'>";
		$url_id = $this->_getSettingValue('CONF_IDEALBASIC_BANK');
		$url = ($this->_getSettingValue('CONF_IDEALBASIC_TEST')?$this->test_uri[$url_id]:$this->uri[$url_id]);
		$res = 
"<table width='100%'><tr><td align='center'>
<form name='ideal_basic' method='post' action='".$url."'>
{$post_fields_html}
<input type='submit' value='".IDEALBASIC_TXT_SUBMIT."'>
</form>".
($active?
//<center><h1>".translate('lbl_redirecting_to_idealbasic')."</h1></center>
"<script language=\"JavaScript\">
<!--
var redirect_win_ol_back = window.onload;
window.onload = function() {
    if(redirect_win_ol_back) redirect_win_ol_back();
    setTimeout(\"document.ideal_basic.submit();\",2000);
};
// -->
</script>":'').
"</td></tr></table>";
return $res;
	}
	
	static function listBank()
	{
		$bank = 'ING:ing,Rabobank:rabobank';
		return $bank;
	}
}
?>