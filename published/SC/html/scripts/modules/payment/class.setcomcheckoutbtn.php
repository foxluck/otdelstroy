<?php
	/**
	 * @connect_module_class_name SetcomCheckoutBTN
	 * @package DynamicModules
	 * @subpackage Payment
	 */
	class SetcomCheckoutBTN extends PaymentModule {

		var $type = PAYMTD_TYPE_CC;
		var $language = 'eng';
		
		function _initVars(){
			
			parent::_initVars();
			$this->title = SETCOMCHECKOUTBTN_TTL;
			$this->description = SETCOMCHECKOUTBTN_DSCR;
			
			$this->Settings = array( 
					'CONF_SETCOMCHECKOUTBTN_MERCHANTID',
					'CONF_SETCOMCHECKOUTBTN_CURRENCY',
				);
		}
	
		function _initSettingFields(){
	
			$this->SettingsFields['CONF_SETCOMCHECKOUTBTN_MERCHANTID'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> SETCOMCHECKOUTBTN_CFG_MERCHANTID_TTL,
				'settings_description' 	=> SETCOMCHECKOUTBTN_CFG_MERCHANTID_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			);
			$this->SettingsFields['CONF_SETCOMCHECKOUTBTN_CURRENCY'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> SETCOMCHECKOUTBTN_CFG_CURRENCY_TTL,
				'settings_description' 	=> SETCOMCHECKOUTBTN_CFG_CURRENCY_DSCR,
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			);
		}
	
		function after_processing_html( $orderID ){
		
			$res = '';
			
			$order = ordGetOrder( $orderID );
			$order_amount = RoundFloatValue(PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_SETCOMCHECKOUTBTN_CURRENCY')));

			$currency = currGetCurrencyByID($this->_getSettingValue('CONF_SETCOMCHECKOUTBTN_CURRENCY'));
			
			$r_country = cnGetCountries(array('offset'=>0,'CountRowOnPage'=>1000000), $count_row);
			$country_iso2 = $order['billing_country'];
			foreach ($r_country as $country){
				
				if($country['country_name']==$order['billing_country']){
					
					$country_iso2 = $country['country_iso_2'];
					break;
				}
			}
			
			$post_1=array(
				'ButtonAction' => 'checkout',
				'MerchantIdentifier' => $this->_getSettingValue('CONF_SETCOMCHECKOUTBTN_MERCHANTID'),
				
				'CurrencyAlphaCode' => $currency['currency_iso_3'],
				
				'BuyerInformation' => 1,
				'FirstName' => $order['billing_firstname'],
				'LastName' => $order['billing_lastname'],
				'Address1' => $order['billing_address'],
				'City' => $order['billing_city'],
				'State' => $order['billing_state'],
				'PostalCode' => $order['billing_zip'],
				'Country' => $country_iso2,
				'Email' => $order['customer_email'],
			);
			
			
			$post_1['LIDSKU'] = $orderID;
			$post_1['LIDDesc'] = '';
			
			$products = ordGetOrderContent($orderID);
			foreach ($products as $product){
				$post_1['LIDDesc'] .= ', '.$product['name'];
			}
			$post_1['LIDDesc'] = SETCOMCHECKOUTBTN_TXT_ORDER.substr($post_1['LIDDesc'], 1);
			
			$post_1['LIDPrice'] = $order_amount;
			$post_1['LIDQty'] = 1;
			
      $hidden_fields_html = '';
      reset($post_1);
      
      while(list($k,$v)=each($post_1)){
      	
				$hidden_fields_html .= '<input type="hidden" name="'.xHtmlSpecialChars($k).'" value="'.xHtmlSpecialChars($v).'" />'."\n";
      }
       
			$res = '
				<form method="post" action="https://www.setcom.com/secure/" style="text-align:center;">
					'.$hidden_fields_html.'
					<input type="submit" value="'.xHtmlSpecialChars(SETCOMCHECKOUTBTN_TXT_PAYNOW).'" />
				</form>
				';

			return $res;
		}
	}
?>