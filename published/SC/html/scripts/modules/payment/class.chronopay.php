<?php
/**
 * @connect_module_class_name Chronopay
 * @package DynamicModules
 * @subpackage Payment
 */
class Chronopay extends PaymentModule {

	var $type = PAYMTD_TYPE_CC;

	var $processing_url = 'https://secure.chronopay.com/index_shop.cgi';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/chronopay.gif';

	function _initVars(){
			
		parent::_initVars();
		$this->title = CHRONOPAY_TTL;
		$this->description = CHRONOPAY_DSCR;
		$this->sort_order = 1;
			
		$this->Settings = array(
			'CONF_CHRONOPAY_PRODUCT_ID',
			'CONF_CHRONOPAY_CURCODE',
			'CONF_CHRONOPAY_LANG',
			'CONF_CHRONOPAY_SHARED_SECRET',
			'CONF_CHRONOPAY_ORDERSTATUS',
			
		);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_CHRONOPAY_PRODUCT_ID'] = array(
			'settings_value' 		=> '',
			'settings_title' 			=> CHRONOPAY_CFG_PRODUCT_ID_TTL,
			'settings_description' 	=> CHRONOPAY_CFG_PRODUCT_ID_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CHRONOPAY_CURCODE'] = array(
			'settings_value' 		=> '',
			'settings_title' 			=> CHRONOPAY_CFG_CURCODE_TTL,
			'settings_description' 	=> CHRONOPAY_CFG_CURCODE_DSCR,
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(',
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CHRONOPAY_LANG'] = array(
				'settings_value' 		=> 'En',
				'settings_title' 			=> CHRONOPAYE_CFG_LANG_TTL,
				'settings_description' 	=> CHRONOPAY_CFG_LANG_DSCR,
				'settings_html_function' 	=> 'setting_SELECT_BOX(Chronopay::_getLanguages(),',
				'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CHRONOPAY_SHARED_SECRET'] = array(
			'settings_value' 		=> '',
			'settings_title' 			=> CHRONOPAY_CFG_SHAREDSECRET_TTL,
			'settings_description' 	=> CHRONOPAY_CFG_SHAREDSECRET_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,',
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CHRONOPAY_ORDERSTATUS'] = array(
			'settings_value' 		=> '-1',
			'settings_title' 			=> CHRONOPAY_CFG_ORDERSTATUS_TTL,
			'settings_description' 	=> CHRONOPAY_CFG_ORDERSTATUS_DSCR,
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),',
			'sort_order' 			=> 1,
		);
	}

	function after_processing_html( $orderID ){

		$res = '';
		$order = ordGetOrder( $orderID );
		$order_amount = RoundFloatValue(PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_CHRONOPAY_CURCODE')));
		$currency = currGetCurrencyByID($this->_getSettingValue('CONF_CHRONOPAY_CURCODE'));
		$zone_iso2 = $order['billing_state'];
		$countries = cnGetCountries(array('offset'=>0,'CountRowOnPage'=>1000000), $count_row);

		foreach ($countries as $country){
			if($country['country_name'] == $order['billing_country']){
				$country_iso3 = $country['country_iso_3'];
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
			
			
		$content = ordGetOrderContent( $orderID );
		$content_name ='';
		foreach($content as $content_item){
			$content_name .= str_replace(' ','&nbsp;',$content_item['name'].'&times;'.$content_item['Quantity']."\r\n");
		}
		$language_iso2 = $this->_getSettingValue('CONF_CHRONOPAY_LANG');
		if(!$language_iso2){
			$language = LanguagesManager::getCurrentLanguage();
			/*@var $language Language*/
			if(in_array(strtolower($language->iso2),array('en','ru','nl','de','lv','es'))){
				$language_iso2 = preg_replace('/^([\w])/e',"strtoupper('\\1')",$language->iso2);
			}
		}
		$orderID = intval($orderID);	
		$post_1=array(
				'product_id' => $this->_getSettingValue('CONF_CHRONOPAY_PRODUCT_ID'),
				'product_name' => str_replace('"','\"',translit($content_name)),//translit(CONF_SHOP_NAME),
				'product_price' => sprintf('%0.2f',ceil($order_amount*100)/100),
				'product_price_currency' => $currency['currency_iso_3'],
				
				'f_name' => translit($order['billing_firstname']),
				's_name' => translit($order['billing_lastname']),
				'street' => translit($order['billing_address']),
				'city' => translit($order['billing_city']),
				'state' => $zone_iso2,
				'zip' => $order['billing_zip'],
				'country' => $country_iso3,
				'email' => $order['customer_email'],
				'cs1' => $orderID,
				
				'cb_url' => $this->getDirectTransactionResultURL('success',array($orderID,$order['customer_email'])),
				//'cb_url' => 'http://webasyst.net.ru/test.php?transaction_result=success',
				'cb_type' => 'P',
				'decline_url' => $this->getTransactionResultURL('failure',array($orderID,$order['customer_email'])),
		);
		
		if($language_iso2){
			$post_1['language'] = $language_iso2;
		}
		$sharedSecret = $this->_getSettingValue('CONF_CHRONOPAY_SHARED_SECRET');
		if($sharedSecret){
			$post_1['sign'] = md5(implode('-',array(
			$post_1['product_id'],
			$post_1['product_price'],
			$sharedSecret
			)));
			$post_1['cs2'] = $post_1['sign'];
			$post_1['cs3'] = md5(sprintf('%s-%s-%s',$post_1['cs1'],$post_1['cs2'],$sharedSecret));
		}
			
		$hidden_fields_html = '';
		reset($post_1);

		while(list($k,$v)=each($post_1)){
			$v = translit($v);
    		$hidden_fields_html .= '<input type="hidden" name="'.xHtmlSpecialChars($k).'" value="'.xHtmlSpecialChars($v).'" />'."\n";
		}
		 
		$res .= '<form method="post" action="'.xHtmlSpecialChars($this->processing_url).'" style="text-align:center;"  accept-charset="iso-8859-1">'."\n";
		$res .= $hidden_fields_html;
		$res .= '<input type="submit" value="'.CHRONOPAY_TXT_SUBMIT.'" />'."\n";
		$res .= '</form>'."\n";
			
		return $res;
	}
	function transactionResultHandler($transaction_result = '',$message = '',$source = 'frontend'){
		$orderID = intval(isset($_POST['cs1'])?$_POST['cs1']:0);
		$log = '';
		if($orderID &&($order = _getOrderById($orderID))){
			$log = 'log';
			if($this->validateResultKey(array($orderID,$order['customer_email']))){
				//check callback sign
				$sharedSecret = $this->_getSettingValue('CONF_CHRONOPAY_SHARED_SECRET');
				if($sharedSecret){
					$value = implode('',array(
							$sharedSecret,
							$_POST['customer_id'],
							$_POST['transaction_id'],
							$_POST['transaction_type'],
							$_POST['total']
					));
					$sign = md5($value);
					
					if($_POST['sign']!=$sign){
						$transaction_result = 'failure';
						$log .= ' invalid post data sign';
					}
					$sign_cs = md5(sprintf('%s-%s-%s',$_POST['cs1'],$_POST['cs2'],$sharedSecret));
					if($sign_cs!=$_POST['cs3']){
						$transaction_result = 'failure';
						$log .= ' invalid cs fields sign';
					}
				}
				if($transaction_result == 'success'){
					//change order status on setted at module settings
					$statusID = $this->_getSettingValue('CONF_CHRONOPAY_ORDERSTATUS');
					if($statusID!=-1){
						$comment = isset($_POST['transaction_id'])?sprintf('ChronoPay transaction ID: %d',intval($_POST['transaction_id'])):'auto status changed';
						ostSetOrderStatusToOrder( $orderID, $statusID,$comment,0);
						
					}
				}elseif($transaction_result == 'failure'){
					//log at order processing history
					$statusID = 3;
					//ostSetOrderStatusToOrder( $orderID, $statusID,$log,1,$force=true);
					//ostSetOrderStatusToOrder($orderID, $statusID, translate('ordr_added_comment').': '.$comment, ($this->getData('notify_customer')?1:0), true);
				}
			}else{
				$transaction_result = 'failure';
				$statusID = 3;
				//ostSetOrderStatusToOrder( $orderID, $statusID,$log,1,$force=true);
			}
		}else{
			$log = "Order with id {$orderID} not exists";
			$transaction_result = 'failure';
		}
		return parent::transactionResultHandler($transaction_result,$message.$log,$source);
	}
	
	static function _getLanguages(){
		return 	CHRONOPAY_TXT_LANGEN.':En,'
				.CHRONOPAY_TXT_LANGRU.':Ru,'
				.CHRONOPAY_TXT_LANGNL.':Nl,'
				.CHRONOPAY_TXT_LANGDE.':De,'
				.CHRONOPAY_TXT_LANGLV.':Lv,'
				.CHRONOPAY_TXT_LANGES.':Es,'
				.CHRONOPAY_TXT_LANUSER.':';
	}
}
?>