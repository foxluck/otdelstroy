<?php
/**
 * @connect_module_class_name GSPay
 * @package DynamicModules
 * @subpackage Payment
 */
	class GSPay extends PaymentModule {
		
		var $type = PAYMTD_TYPE_CC;
		var $language = 'eng';
		var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/gspay.gif';
		
		function _initVars(){
			
			$this->title = GSPAY_TTL;
			$this->description = GSPAY_DSCR;
			
			$this->Settings = array( 
					'CONF_GSPAY_SITE_ID',
					'CONF_GSPAY_TRANS_CURRENCY',
				);
		}
	
		function _initSettingFields(){
	
			$this->SettingsFields['CONF_GSPAY_SITE_ID'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> GSPAY_CFG_SITE_ID_TTL,
				'settings_description' 	=> GSPAY_CFG_SITE_ID_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			);
			$this->SettingsFields['CONF_GSPAY_TRANS_CURRENCY'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> GSPAY_CFG_TRANS_CURRENCY_TTL,
				'settings_description' 	=> GSPAY_CFG_TRANS_CURRENCY_DSCR,
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			);
		}
		
		function after_processing_html($order_id){
			
			$order = ordGetOrder($order_id);
			$currency = currGetCurrencyByID($this->_getSettingValue('CONF_GSPAY_TRANS_CURRENCY'));
			
			$countries = cnGetCountries(array('offset'=>0,'CountRowOnPage'=>1000000), $count_row);
			$order_country = array(
				'shipping' => null,
				'billing' => null
			);
			foreach ($countries as $country){

				if($country['country_name'] == $order['billing_country']){
					$order_country['billing'] = $country;
				}
				if($country['country_name'] == $order['shipping_country']){
					$order_country['shipping'] = $country;
				}
			}
			
			$order_state = array(
				'billing' => array('zone_code'=>'XX'),
				'shipping' => array('zone_code'=>'XX'),
			);
			
			foreach ($order_country as $type=>$country){
				
				if(is_null($country))continue;
				$states = znGetZones($country['countryID']);
				foreach ($states as $state){
					
					if($state['zone_name'] == $order[$type.'_state']){
						$order_state[$type] = $state;
					}
				}
			}
			
			$processing_data = array(
				'siteID' => $this->_getSettingValue('CONF_GSPAY_SITE_ID'),
				'orderID' => $order_id,
				'returnUrl' => getTransactionResultURL('success'),
				/* Billing info */
				'customerFullName' => $order['billing_firstname'].' '.$order['billing_lastname'],
				'customerAddress' => $order['billing_address'],
				'customerCity' => $order['billing_city'],
				'customerStateCode' => $order_state['billing']['zone_code'],
				'customerZip' => $order['billing_zip'],
				'customerCountry' => $order['billing_country'],
				'customerEmail' => $order['customer_email'],
				'customerPhone' => '',
				/* Shipping info */
				'customerShippingFullName' => $order['shipping_firstname'].' '.$order['shipping_lastname'],
				'customerShippingAddress' => $order['shipping_address'],
				'customerShippingCity' => $order['shipping_city'],
				'customerShippingStateCode' => $order_state['shipping']['zone_code'],
				'customerShippingZip' => $order['shipping_zip'],
				'customerShippingCountry' => $order['shipping_country'],
				'customerShippingEmail' => $order['customer_email'],
				'customerShippingPhone' => '',
			);
			$processing_data['OrderDescription[0]'] = sprintf(GSPAY_TXT_ORDERFROM, $order_id, CONF_SHOP_NAME);
			$processing_data['Qty[0]'] = 1;
			$processing_data['Amount[0]'] = RoundFloatValue($this->_convertCurrency($order['order_amount'],0, $currency['CID']));
/* Not tested

			$order_cart = ordGetOrderContent($order_id);
			$order_amount = 0;
			$order_tax = 0;
			$item_cnt = 0;
			foreach ($order_cart as $item){
				
				$processing_data['OrderDescription['.$item_cnt.']'] = $item['name'];
				$processing_data['Qty['.$item_cnt.']'] = $item['Quantity'];
				$processing_data['Amount['.$item_cnt.']'] = $this->_convertCurrency($item['Price'], 0, $currency['CID']);
				$order_amount += $processing_data['Amount['.$item_cnt.']'];
				$order_tax += $processing_data['Amount['.$item_cnt.']']*$item['tax']/100;
				$item_cnt++;
			}
			
			if($order['order_discount']){
				
			$processing_data['OrderDescription['.$item_cnt.']'] = 'Discount';
			$processing_data['Qty['.$item_cnt.']'] = 1;
			$processing_data['Amount['.$item_cnt.']'] = -1*RoundFloatValue($order_amount*$order['order_discount']/100);
			$item_cnt++;
			}
			
			if($order_tax){
				
			$processing_data['OrderDescription['.$item_cnt.']'] = 'Tax';
			$processing_data['Qty['.$item_cnt.']'] = 1;
			$processing_data['Amount['.$item_cnt.']'] = RoundFloatValue($order_tax);
			$item_cnt++;
			}
			
			if($order['shipping_cost']){
				
			$processing_data['OrderDescription['.$item_cnt.']'] = 'Shipping';
			$processing_data['Qty['.$item_cnt.']'] = 1;
			$processing_data['Amount['.$item_cnt.']'] = RoundFloatValue($this->_convertCurrency($order['shipping_cost'], 0, $currency['CID']));
			$item_cnt++;
			}
*/		

			$fields_html = '';
			foreach ($processing_data as $name=>$value){
				
				$fields_html .= '<input type="hidden" name="'.xHtmlSpecialChars($name).'" value="'.xHtmlSpecialChars($value).'">';
			}
			return '
			<form method="post" action="https://secure.paymenter.com/payment/pay.php" style="text-align:center;">
			'.$fields_html.'
			<input type="submit" value="'.xHtmlSpecialChars(GSPAY_TXT_PAYBUTTON).'">
			</form>
			';
		}
	}
?>