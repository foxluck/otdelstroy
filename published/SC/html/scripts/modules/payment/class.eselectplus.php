<?php
/**
 * @connect_module_class_name eSelectPLUS
 * @package DynamicModules
 * @subpackage Payment
 */
	class eSelectPLUS extends PaymentModule {
		
		var $type = PAYMTD_TYPE_CC;
		var $language = 'eng';
		var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/eselectplus.gif';
			
		function _initVars(){
			
			$this->title = ESELECTPLUS_TTL;
			$this->description = ESELECTPLUS_DSCR;
			$this->sort_order = 1;
			
			$this->Settings = array( 
					'CONF_ESELECTPLUS_TESTMODE',
					'CONF_ESELECTPLUS_PS_STORE_ID',
					'CONF_ESELECTPLUS_HPP_KEY',
					'CONF_ESELECTPLUS_USD_CURRENCY',
				);
		}
	
		function _initSettingFields(){
	
			$this->SettingsFields['CONF_ESELECTPLUS_TESTMODE'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> ESELECTPLUS_CFG_TESTMODE_TTL,
				'settings_description' 	=> ESELECTPLUS_CFG_TESTMODE_DSCR,
				'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			);
			$this->SettingsFields['CONF_ESELECTPLUS_PS_STORE_ID'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> ESELECTPLUS_CFG_PS_STORE_ID_TTL,
				'settings_description' 	=> ESELECTPLUS_CFG_PS_STORE_ID_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			);
			$this->SettingsFields['CONF_ESELECTPLUS_HPP_KEY'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> ESELECTPLUS_CFG_HPP_KEY_TTL,
				'settings_description' 	=> ESELECTPLUS_CFG_HPP_KEY_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			);
			$this->SettingsFields['CONF_ESELECTPLUS_USD_CURRENCY'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> ESELECTPLUS_CFG_USD_CURRENCY_TTL, 
				'settings_description' 	=> ESELECTPLUS_CFG_USD_CURRENCY_DSCR, 
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			);
		}
	
		function after_processing_html( $orderID ){
		
			$order = ordGetOrder( $orderID );

			$post_1=array(
				'ps_store_id' => $this->_getSettingValue('CONF_ESELECTPLUS_PS_STORE_ID'),
				'hpp_key' => $this->_getSettingValue('CONF_ESELECTPLUS_HPP_KEY'),
				'charge_total' => sprintf('%.2f',RoundFloatValue(PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_ESELECTPLUS_USD_CURRENCY')))),
				
				'order_id' => $orderID,
				'email' => $order['customer_email'],
			);
			
      $hidden_fields_html = '';
      reset($post_1);
      
      while(list($k,$v)=each($post_1)){
      	
				$hidden_fields_html .= '<input type="hidden" name="'.xHtmlSpecialChars($k).'" value="'.xHtmlSpecialChars($v).'" />'."\n";
      }
       
			$res = '
				<form method="post" action="'.xHtmlSpecialChars($this->_getSettingValue('CONF_ESELECTPLUS_TESTMODE')?
				'https://esqa.moneris.com/HPPDP/index.php':
				'https://www3.moneris.com/HPPDP/index.php').'" style="text-align:center;">
					'.$hidden_fields_html.'
					<input type="submit" value="'.xHtmlSpecialChars(ESELECTPLUS_TXT_SUBMIT).'" />
				</form>
				';
			
			return $res;
		}
	}
?>