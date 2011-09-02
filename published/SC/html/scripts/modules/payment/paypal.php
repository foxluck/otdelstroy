<?php
// PayPal payment module
// http://www.paypal.com

/**
 * @connect_module_class_name CPayPal
 * @package DynamicModules
 * @subpackage Payment
 */
class CPayPal extends PaymentModule {

	var $type = PAYMTD_TYPE_ONLINE;
	
	private $validate_url ='https://www.paypal.com/cgi-bin/webscr';
	private $submit_url ='https://www.paypal.com/cgi-bin/webscr';
	private $submit_test_url ='https://www.sandbox.paypal.com/cgi-bin/webscr';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/paypal.gif';
	
	function _initVars(){
		
		parent::_initVars();
		$this->title 		= CPAYPAL_TTL;
		$this->description 	= CPAYPAL_DSCR;
		$this->sort_order 	= 1;
		
		$this->Settings = array( 
				"CONF_PAYMENTMODULE_PAYPAL_MERCHANT_EMAIL",
				"CONF_PAYMENTMODULE_PAYPAL_CHECKOUT_MODE",
				"CONF_PAYMENTMODULE_PAYPAL_TRANSCURR",
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_PAYPAL_MERCHANT_EMAIL'] = array(
			'settings_value' 		=> '', 
			'settings_title' 		=> CPAYPAL_CFG_MERCHANT_EMAIL_TTL, 
			'settings_description' 	=> CPAYPAL_CFG_MERCHANT_EMAIL_DSCR, 
			'settings_html_function'=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_PAYPAL_CHECKOUT_MODE'] = array(
			'settings_value' 		=> 'Live',
			'settings_title' 		=> CPAYPAL_CFG_MODE_TTL,
			'settings_description' 	=> CPAYPAL_CFG_MODE_DSCR,
			'settings_html_function'=> 'setting_RADIOGROUP(CPAYPAL_TXT_TEST.":Sandbox,".CPAYPAL_TXT_LIVE.":Live",',
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_PAYPAL_TRANSCURR'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CPAYPAL_CFG_CURRENCY_TTL, 
			'settings_description' 	=> CPAYPAL_CFG_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
		);
	}

	function after_processing_html($orderID, $active = true  )
	{
	    $Register = &Register::getInstance();
	    $smarty = &$Register->get(VAR_SMARTY);
	    
		$order = ordGetOrder($orderID);
		$products = ordGetOrderContent($orderID);
        ordCalculateOrderTax($order, $products);
        $currency = currGetCurrencyByID($this->_getSettingValue('CONF_PAYMENTMODULE_PAYPAL_TRANSCURR'));
        $currency_iso3 = $currency['currency_iso_3'];
        
        foreach($products as $key => &$product)
        {
            $product['ItemPrice'] = number_format(self::_convertCurrency($product['Price'],0,$currency_iso3), 2, '.', '');
            $product['name'] = translit($product['name']); 
        };
        unset($product);

        $order['order_discount'] = number_format(self::_convertCurrency($order['order_discount'],0,$currency_iso3), 2, '.', '');
        $order['shipping_cost'] = number_format(self::_convertCurrency($order['shipping_cost'],0,$currency_iso3), 2, '.', '');
        $order['tax'] = number_format(self::_convertCurrency($order['tax'],0,$currency_iso3), 2, '.', '');
        $order['currency_code'] = $currency_iso3;
        $smarty->assign('order', $order);
        $smarty->assign('active', $active);
		$smarty->assign('names_str', $names_str);
		$smarty->assign('products', $products);
		$smarty->assign('merchant', $this->_getSettingValue('CONF_PAYMENTMODULE_PAYPAL_MERCHANT_EMAIL'));
		$smarty->assign('return_url', getTransactionResultURL('success'));
		$smarty->assign('cancel_url', getTransactionResultURL('failure'));
		$smarty->assign('paypal_url', ($this->_getSettingValue('CONF_PAYMENTMODULE_PAYPAL_CHECKOUT_MODE')=='Live'?$this->submit_url:$this->submit_test_url ));
		//$smarty->assign('notify_url', $this->getDirectTransactionResultURL('success',array($orderID,$order['customer_email'])));
		
		return $smarty->fetch('../payment/paypal-cart-mode.html');
	}
	
	function _transactionResultHandler($transaction_result = '',$message = '',$source = 'frontend')
	{
		$orderID = isset($_POST[''])?$_POST['']:0;
		$log = '';
		if($orderID &&($order = _getOrderById($orderID))){
			$log = 'log';
			if($this->validateResultKey(array($orderID,$order['customer_email']))){
				
				$sharedSecret = $this->_getSettingValue('');
				if($this->validateIPNResponce()){//check callback sign
					// ...
					$transaction_sign = isset($_POST[''])?$_POST['']:null;
					$transaction_hash = md5();
					if(!$transaction_sign||$transaction_sign!=$transaction_hash){
						$transaction_result = 'failure';
						$log .= ' invalid cs fields sign';
					}
				
					if($transaction_result == 'success'){
						//change order status on setted at module settings
						$statusID = $this->_getSettingValue('');
						if($statusID!=-1){
							$comment = isset($_POST['transaction_id'])?sprintf('WebMoney номер счета: %s; номер платежа: %s',$sys_invs_no,$sys_trans_no):'auto status changed';
							ostSetOrderStatusToOrder( $orderID, $statusID,$comment,0);
						}
					}
				}elseif($transaction_result == 'failure'){
					//log at order processing history
					$statusID = 3;
					//ostSetOrderStatusToOrder($orderID, $statusID, translate('ordr_added_comment').': '.$comment, ($this->getData('notify_customer')?1:0), true);
				}
			}else{
				$transaction_result = 'failure';
				$statusID = 3;
				//ostSetOrderStatusToOrder( $orderID, $statusID,$log,1,$force=true);
			}
		}else{
			$log = "Order with id {$orderID} not exists";
			$orderID = false;
			$transaction_result = 'failure';
		}
		return parent::transactionResultHandler($transaction_result,$message.$log,$source);
	}
	
	private function validateIPNResponce()
	{
		// Post the data back to paypal
		$fields = $_POST;
		$result = false;
		$fields['cmd']='_notify-validate';
		
		$ch = curl_init();
		@curl_setopt( $ch, CURLOPT_URL, $this->processing_url );
		@curl_setopt( $ch, CURLOPT_POST, 1);
		@curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields );
		@curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		@curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		@curl_setopt( $ch, CURLOPT_TIMEOUT, 120 );
		@curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 120 );
		@curl_setopt( $ch, CURLE_OPERATION_TIMEOUTED, 120 );

		initCurlProxySettings($ch);

		$result = curl_exec($ch);
		//parse responce
		if(curl_errno($ch)!=0){
			$this->_log(LOGTYPE_ERROR, 'Curl error #'.curl_errno($ch).' '.curl_error($ch));
		}elseif(preg_match('/VERIFIED/',$result)){
			$result = true;
		}
		curl_close ($ch);
		//VERIFIED
	}
}
?>