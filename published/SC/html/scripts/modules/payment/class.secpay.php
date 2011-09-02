<?php
/**
 * @connect_module_class_name SecPay
 * @package DynamicModules
 * @subpackage Payment
 */
	class SecPay extends PaymentModule {

		var $type = PAYMTD_TYPE_CC;
		var $language = 'eng';
		
		function _initVars(){
			
			parent::_initVars();
			$this->title = SECPAY_TTL;
			$this->description = SECPAY_DSCR;
			$this->sort_order = 1;
			
			$this->Settings = array( 
					'CONF_SECPAY_MERCHANT',
					'CONF_SECPAY_REMOTEPASSWORD',
					'CONF_SECPAY_CURRENCY'
				);
		}
	
		function _initSettingFields(){
	
			$this->SettingsFields['CONF_SECPAY_MERCHANT'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> SECPAY_CFG_MERCHANT_TTL,
				'settings_description' 	=> SECPAY_CFG_MERCHANT_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_SECPAY_REMOTEPASSWORD'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> SECPAY_CFG_REMOTEPASSWORD_TTL,
				'settings_description' 	=> SECPAY_CFG_REMOTEPASSWORD_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_SECPAY_CURRENCY'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> SECPAY_CFG_CURRENCY_TTL, 
				'settings_description' 	=> SECPAY_CFG_CURRENCY_DSCR, 
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
				'sort_order' 			=> 1,
			);
		}
	
		function after_processing_html( $orderID ){
		
			$res = '';
			
			$order = ordGetOrder( $orderID );
			$order_amount = PaymentModule::_convertCurrency($order['order_amount'], 0, $this->_getSettingValue('CONF_SECPAY_CURRENCY'));
			$currency = currGetCurrencyByID($this->_getSettingValue('CONF_SECPAY_CURRENCY'));

			$post_1=array(
				'merchant' => $this->_getSettingValue('CONF_SECPAY_MERCHANT'),
				'trans_id' => $orderID,
				'amount' => sprintf('%.2f', $order_amount),
				'callback' => getTransactionResultURL('success').';'.getTransactionResultURL('failure'),
				'cb_post' => 'true',
				'currency' => $currency['currency_iso_3'],
				
				'customer' => $order['customer_firstname'].' '.$order['customer_lastname'],
				'bill_email' => $order['customer_email'],
			);
			
			$post_1['digest'] = md5($post_1['trans_id'].$post_1['amount'].$this->_getSettingValue('CONF_SECPAY_REMOTEPASSWORD'));
			
			if(preg_match('/^https/msi',getTransactionResultURL('success'))){
				
				$post_1['ssl_cb'] = 'true';
			}
			
      $hidden_fields_html = '';
      reset($post_1);
      
      while(list($k,$v)=each($post_1)){
      	
				$hidden_fields_html .= '<input type="hidden" name="'.xHtmlSpecialChars($k).'" value="'.xHtmlSpecialChars($v).'" />'."\n";
      }
			$res = '
				<form method="post" action="https://www.secpay.com/java-bin/ValCard" style="text-align:center;">
					'.$hidden_fields_html.'
					<input type="submit" value="'.xHtmlSpecialChars(SECPAY_SUBMIT_BTN).'" />
				</form>
				';
			
			return $res;
		}
	}
?>