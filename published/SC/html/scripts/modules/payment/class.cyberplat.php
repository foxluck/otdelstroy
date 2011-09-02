<?php
/**
 * @connect_module_class_name Cyberplat
 * @package DynamicModules
 * @subpackage Payment
 */
	class Cyberplat extends PaymentModule {

		var $type = PAYMTD_TYPE_CC;
		var $language = 'rus';
		
		function _initVars(){
			
			parent::_initVars();
			$this->title = CYBERPLAT_TTL;
			$this->description = CYBERPLAT_DSCR;
			$this->sort_order = 1;
			
			$this->Settings = array( 
					'CONF_CYBERPLAT_TRANS_CURRENCY',
				);
		}
	
		function _initSettingFields(){
	
			$this->SettingsFields['CONF_CYBERPLAT_TRANS_CURRENCY'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> CYBERPLAT_CFG_TRANS_CURRENCY_TTL, 
				'settings_description' 	=> CYBERPLAT_CFG_TRANS_CURRENCY_DSCR, 
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			);
		}
	
		function after_processing_html( $orderID ){
		
			$order = ordGetOrder( $orderID );
			$currency = currGetCurrencyByID($this->_getSettingValue('CONF_CYBERPLAT_TRANS_CURRENCY'));

			$post_1=array(
				
				'OrderID' => $orderID,
				'PaymentDetails' => 'Оплата заказа №'.$orderID,
				'Amount' => sprintf('%.2f', RoundFloatValue($this->_convertCurrency($order['order_amount'], 0, $currency['currency_iso_3']))),
				'Currency' => $currency['currency_iso_3'],
				'FirstName' => $order['customer_firstname'],
				'MiddleName' => '',
				'LastName' => $order['customer_lastname'],
				'Email' => $order['customer_email'],
			);
			
      $hidden_fields_html = '';
      reset($post_1);
      
      while(list($k,$v)=each($post_1)){
      	
				$hidden_fields_html .= '<input type="hidden" name="'.xHtmlSpecialChars($k).'" value="'.xHtmlSpecialChars($v).'" />'."\n";
      }
       
			$res = '
				<form method="post" action="'.CONF_FULL_SHOP_URL.'modules/payment/cyberplat/cybercrd.cgi" style="text-align:center;">
					'.$hidden_fields_html.'
					<input type="submit" value="'.xHtmlSpecialChars(CYBERPLAT_TXT_SUBMIT).'" />
				</form>
				';
			
			return $res;
		}
	}
?>