<?php
/**
 * @connect_module_class_name NAB_NSIPS
 * @package DynamicModules
 * @subpackage Payment
 */
	class NAB_NSIPS extends PaymentModule {

		var $type = PAYMTD_TYPE_OBSOLETE;//PAYMTD_TYPE_CC;
		var $language = 'eng';
		
		function _initVars(){
			
			parent::_initVars();
			$this->title = NAB_NSIPS_TTL;
			$this->description = NAB_NSIPS_DSCR;
			$this->sort_order = 1;
			
			$this->Settings = array( 
					'CONF_NAB_NSIPS_URL',
					'CONF_NAB_NSIPS_MERCHID',
					'CONF_NAB_NSIPS_CURCODE',
				);
		}
	
		function _initSettingFields(){
	
			$this->SettingsFields['CONF_NAB_NSIPS_URL'] = array(
				'settings_value' 		=> 'http://203.63.249.148/utci_v1.1.5/utci.nsa', 
				'settings_title' 			=> NAB_NSIPS_CFG_URL_TTL,
				'settings_description' 	=> NAB_NSIPS_CFG_URL_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_NAB_NSIPS_MERCHID'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> NAB_NSIPS_CFG_MERCHID_TTL,
				'settings_description' 	=> NAB_NSIPS_CFG_MERCHID_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_NAB_NSIPS_CURCODE'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> NAB_NSIPS_CFG_CURRENCY_TTL, 
				'settings_description' 	=> NAB_NSIPS_CFG_CURRENCY_DSCR, 
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
				'sort_order' 			=> 1,
			);
		}
	
		function after_processing_html( $orderID ){
		
			$res = '';
			
			$order = ordGetOrder( $orderID );
			$order_amount = RoundFloatValue(PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_NAB_NSIPS_CURCODE')));

			$post_1=array(
				'MERCHID' => $this->_getSettingValue('CONF_NAB_NSIPS_MERCHID'),
				'AMT' => $order_amount,
				'MTID' => $orderID,
				'SUCCESSURL' => substr(getTransactionResultURL('success'),0,255),
				'FAILURL' => substr(getTransactionResultURL('failure'),0,255),
				'NAME' => substr($order['billing_firstname'].' '.$order['billing_lastname'],0,30),
				'EMAIL' => substr($order['customer_email'],0,64),
			);
			
      $hidden_fields_html = '';
      reset($post_1);
      
      while(list($k,$v)=each($post_1)){
      	
				$hidden_fields_html .= '<input type="hidden" name="'.xHtmlSpecialChars($k).'" value="'.xHtmlSpecialChars($v).'" />'."\n";
      }
       
			$res = '
				<form method="post" action="'.xHtmlSpecialChars($this->_getSettingValue('CONF_NAB_NSIPS_URL')).'" style="text-align:center;">
					<img src="'.xHtmlSpecialChars(URL_IMAGES.'/nsips2.gif').'" alt="NSIPS" />
					<br />
					<br />
					'.$hidden_fields_html.'
					<input type="image" src="'.xHtmlSpecialChars(URL_IMAGES.'/NSIPS_Pay_Now_button.gif').'" />
				</form>
				';
			
			return $res;
		}
	}
?>