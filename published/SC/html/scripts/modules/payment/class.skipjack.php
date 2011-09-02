<?php
/**
 * Invisible for administration
 */

	/**
	 * @connect_module_class_name SkipJack
	 * @package DynamicModules
	 * @subpackage Payment
	 */
	class SkipJack extends PaymentModule {
		var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/skipjack.gif';
		
		function _initVars(){
			
			parent::_initVars();
			$this->title = SKIPJACK_TTL;
			$this->description = SKIPJACK_DSCR;
			$this->sort_order = 1;
			
			$this->Settings = array( 
					'CONF_SKIPJACK_URL',
					'CONF_SKIPJACK_SERIAL',
					'CONF_SKIPJACK_USD',
				);
		}
	
		function _initSettingFields(){
	
			$this->SettingsFields['CONF_SKIPJACK_URL'] = array(
				'settings_value' 		=> 'https://developer.skipjackic.com/scripts/evolvcc.dll?Authorize', 
				'settings_title' 			=> SKIPJACK_CFG_URL_TTL,
				'settings_description' 	=> SKIPJACK_CFG_URL_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_SKIPJACK_SERIAL'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> SKIPJACK_CFG_SERIAL_TTL, 
				'settings_description' 	=> SKIPJACK_CFG_SERIAL_DSCR, 
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_SKIPJACK_USD'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> SKIPJACK_CFG_USD_TTL, 
				'settings_description' 	=> SKIPJACK_CFG_USD_DSCR, 
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
				'sort_order' 			=> 1,
			);
		}
	
		function after_processing_html( $orderID ){
		
			
			$res = '';
			
			$order = ordGetOrder( $orderID );
			$order_amount = RoundFloatValue($this->_convertCurrency($order['order_amount'],0, $this->_getSettingValue('CONF_SKIPJACK_USD')));

			$zone_iso2 = $order['billing_state'];

			$countries = cnGetCountries(array('offset'=>0,'CountRowOnPage'=>1000000), $count_row);

			foreach ($countries as $country){
				
				if($country['country_iso_2'] == 'US'){
					
					$zones = znGetZones($country['countryID']);
					
					foreach ($zones as $zone){
						
						if($zone['zone_name']==$zone_iso2){
							
							$zone_iso2 = $zone['zone_code'];
							break;
						}
					}
					break;
				}
			}
			
			$post_1=array(
				
				'sjname' => substr($order['billing_firstname'].' '.$order['billing_lastname'],0,40),
				'email' => substr($order['customer_email'],0,40),
				'streetaddress' => substr($order['billing_address'],0,40),
				'city' => substr($order['billing_city'],0,40),
				'state' => substr($zone_iso2,0,40),
				'zipcode' => substr($order['billing_zip'],0,9),
				
				'ordernumber' => $orderID,
				'orderstring' => '1~1~0.00~1~N~||',
				'shiptophone' => '111111111',
				'transactionamount' => sprintf('%.2f',$order_amount),
			
				'serialnumber' => $this->_getSettingValue('CONF_SKIPJACK_SERIAL'),
			);
			
      $hidden_fields_html = '';
      reset($post_1);
      
      while(list($k,$v)=each($post_1)){
      	
				$hidden_fields_html .= '<input type="hidden" name="'.xHtmlSpecialChars($k).'" value="'.xHtmlSpecialChars($v).'" />'."\n";
      }
       
			$CurrYear2d = date('y');
			$CurrYear4d = date('Y');
			$ExpYears = '';
			for($_Y = 0; $_Y<10; $_Y++){
			
				$_Selected = 0;
				$ExpYears .= '<option value="'.sprintf('%02d',($CurrYear2d+$_Y)).'"'.($_Selected?' selected="selected"':'').'>'.($CurrYear4d+$_Y).'</option>';
			}
			
			global $rMonths;
			$ExpMonths = '';
			for($_M = 1; $_M<=12; $_M++){
			
				$_Selected = 0;
				$ExpMonths .= '<option value="'.sprintf('%02d',$_M).'"'.($_Selected?' selected="selected"':'').'>'.$rMonths[$_M].'</option>';
			}
			
			$res = '
				<form target="_blank" method="post" action="'.xHtmlSpecialChars($this->_getSettingValue('CONF_SKIPJACK_URL')).'">
				'.$hidden_fields_html.'
				<table align="center">
				<tr>
					<td align="right">'.SKIPJACK_TXT_CCNUMBER.'</td>
					<td>
						<input type="text" name="accountnumber" />
					</td>
				</tr>
				<tr>
					<td align="right">'.SKIPJACK_TXT_CVV.'</td>
					<td>
						<input type="text" name="cvv2" />
					</td>
				</tr>
				<tr>
					<td align="right">'.SKIPJACK_TXT_EXPDATE.'</td>
					<td>
					<select name="month">'.$ExpMonths.'</select>&nbsp;<select name="year">'.$ExpYears.'</select>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<input type="submit" value="'.xHtmlSpecialChars(SKIPJACK_SUBMIT_BTN).'" />
					</td>
				</tr>
				</table>
				</form>
				';
			return $res;
		}
	}
?>