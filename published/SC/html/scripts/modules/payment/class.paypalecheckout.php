<?php
/**
 * Invisible for administration
 */
/**
 * @connect_module_class_name CPayPalECheckout
 * @package DynamicModules
 * @subpackage Payment
 */
//commented out use kernel PEAR
//ini_set('include_path', ini_get('include_path').PATH_DELIMITER.'./modules/payment/pppro/pear');
error_reporting(E_ALL & ~E_NOTICE);

class CPayPalECheckout extends PaymentModule {

	var $CertsPath = '';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/paypal.gif';
	
	function _initVars(){
		
		parent::_initVars();
		$this->CertsPath = DIR_TEMP;
		$this->title 		= CPAYPALECHECKOUT_TTL;
		$this->description 	= CPAYPALECHECKOUT_DSCR;
		$this->sort_order 	= 1;
		
		$this->Settings = array( 
				"CONF_CPAYPALECHECKOUT_USERNAME",
				"CONF_CPAYPALECHECKOUT_PASSWORD",
				'CONF_CPAYPALECHECKOUT_CERTPATH',
				'CONF_CPAYPALECHECKOUT_MODE',
				'CONF_CPAYPALECHECKOUT_PAYMENTACTION',
				'CONF_CPAYPALECHECKOUT_NOSHIPPING',
				'CONF_CPAYPALECHECKOUT_ORDERSTATUS',
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_CPAYPALECHECKOUT_USERNAME'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CPAYPALECHECKOUT_CFG_USERNAME_TTL, 
			'settings_description' 	=> CPAYPALECHECKOUT_CFG_USERNAME_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CPAYPALECHECKOUT_PASSWORD'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CPAYPALECHECKOUT_CFG_PASSWORD_TTL, 
			'settings_description' 	=> CPAYPALECHECKOUT_CFG_PASSWORD_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CPAYPALECHECKOUT_CERTPATH'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CPAYPALECHECKOUT_CFG_CERTPATH_TTL, 
			'settings_description' 	=> CPAYPALECHECKOUT_CFG_CERTPATH_DSCR, 
			'settings_html_function' 	=> 'setting_SINGLE_FILE(DIR_TEMP,',
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CPAYPALECHECKOUT_MODE'] = array(
			'settings_value' 		=> 'Sandbox', 
			'settings_title' 			=> CPAYPALECHECKOUT_CFG_MODE_TTL, 
			'settings_description' 	=> CPAYPALECHECKOUT_CFG_MODE_DSCR, 
			'settings_html_function' 	=> 'setting_RADIOGROUP(CPAYPALECHECKOUT_TXT_TEST.":Sandbox,".CPAYPALECHECKOUT_TXT_LIVE.":Live",', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CPAYPALECHECKOUT_PAYMENTACTION'] = array(
			'settings_value' 		=> 'Sale', 
			'settings_title' 			=> CPAYPALECHECKOUT_CFG_PAYMENTACTION_TTL, 
			'settings_description' 	=> CPAYPALECHECKOUT_CFG_PAYMENTACTION_DSCR, 
			'settings_html_function' 	=> 'setting_RADIOGROUP("Sale:Sale,Order:Order,Authorization:Authorization",', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CPAYPALECHECKOUT_NOSHIPPING'] = array(
			'settings_value' 		=> 1, 
			'settings_title' 			=> CPAYPALECHECKOUT_CFG_NOSHIPPING_TTL, 
			'settings_description' 	=> CPAYPALECHECKOUT_CFG_NOSHIPPING_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
		
		$this->SettingsFields['CONF_CPAYPALECHECKOUT_ORDERSTATUS'] = array(
			'settings_value' 		=> '-1', 
			'settings_title' 			=> CPAYPALECHECKOUT_CFG_ORDERSTATUS_TTL, 
			'settings_description' 	=> CPAYPALECHECKOUT_CFG_ORDERSTATUS_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),', 
			'sort_order' 			=> 1,
		);
	}
	
	function payment_process($_Order){
	
		require_once 'Services/PayPal.php';
		require_once 'Services/PayPal/Profile/Handler/Array.php';
		require_once 'Services/PayPal/Profile/API.php';
		
		$handler =& ProfileHandler_Array::getInstance(array(
			'username' => $this->_getSettingValue('CONF_CPAYPALECHECKOUT_USERNAME'),
			'certificateFile' => $this->CertsPath.'/'.$this->_getSettingValue('CONF_CPAYPALECHECKOUT_CERTPATH'),
			'subject' => null,
			'environment' => $this->_getSettingValue('CONF_CPAYPALECHECKOUT_MODE')));
		
		$profile =& APIProfile::getInstance($this->_getSettingValue('CONF_CPAYPALECHECKOUT_USERNAME'), $handler);
		$profile->setAPIPassword($this->_getSettingValue('CONF_CPAYPALECHECKOUT_PASSWORD'));
		
		$caller =& Services_PayPal::getCallerServices($profile);
		
		if(Services_PayPal::isError($caller))
		{
			print "Could not create CallerServices instance: ". $caller->getMessage();
			exit;
		}

		$OrderAmount = RoundFloatValue($this->_convertCurrency($_Order['order_amount'], 0, $_Order['currency_code']));
		
		if(!isset($_GET['PayerID'])||!isset($_GET['token'])){
		
			$amount =& Services_PayPal::getType('BasicAmountType');
			$amount->setval($OrderAmount);
			$amount->setattr('currencyID', $_Order['currency_code']);
			
			$ecd =& Services_PayPal::getType('SetExpressCheckoutRequestDetailsType');
			$ecd->setOrderTotal($amount);
			if($this->_getSettingValue('CONF_CPAYPALECHECKOUT_NOSHIPPING')){
				$ecd->setNoShipping(1);
			}
			$ParsedURL = parse_url(CONF_FULL_SHOP_URL);
			$SuccessURL = set_query('&ppecheckout_failure=&token=&PayerID=');
			if(strpos($SuccessURL,'http')===false){
			
				$SuccessURL = $ParsedURL['scheme'].'://'.$ParsedURL['host'].$SuccessURL;
			}
			$FailureURL = set_query('&ppecheckout_failure=1');
			if(strpos($FailureURL,'http')===false){
			
				$FailureURL = $ParsedURL['scheme'].'://'.$ParsedURL['host'].$FailureURL;
			}
			$ecd->setReturnURL($SuccessURL);
			$ecd->setCancelURL($FailureURL);
			$ecd->setPaymentAction($this->_getSettingValue('CONF_CPAYPALECHECKOUT_PAYMENTACTION'));
			
			$ec =& Services_PayPal::getType('SetExpressCheckoutRequestType');
			$ec->setSetExpressCheckoutRequestDetails($ecd);
			
			$response = $caller->SetExpressCheckout($ec);
			
			if($response->Ack != 'Success'){
			
				$ErrorMessage = ' ';
				if(is_array($response->Errors)){
				
					foreach($response->Errors as $_Error){
					
						$ErrorMessage .= $_Error->ErrorCode.'- '.$_Error->ShortMessage.' ( '.$_Error->LongMessage.' )';
						break;
					}
				}elseif(isset($response->Errors)){
				
						$ErrorMessage .= $response->Errors->ErrorCode.'- '.$response->Errors->ShortMessage.' ( '.$response->Errors->LongMessage.' )';
				}else{
					$ErrorMessage = ' '.$response->message;
				}
				return $ErrorMessage;
	
			}else{
				$url = 'https://www.'.($this->_getSettingValue('CONF_CPAYPALECHECKOUT_MODE')=='Live'?'':'sandbox.').'paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$response->Token;
				Redirect($url);
			}
		}else{
		
			#getting shipping info
			if(!$this->_getSettingValue('CONF_CPAYPALECHECKOUT_NOSHIPPING')){
			
				$ecd =& Services_PayPal::getType('GetExpressCheckoutDetailsRequestType');
				$ecd->setToken($_GET['token']);
				
				$response = $caller->GetExpressCheckoutDetails($ecd);
				
				if($response->Ack == 'Success'){
				
					$PayerInfo = array();
					$_Address = &$response->GetExpressCheckoutDetailsResponseDetails->PayerInfo->Address;
					if($_Address->Name)$PayerInfo[] = 'Name: '.$_Address->Name;
					if($_Address->Street1)$PayerInfo[] = 'Street 1: '.$_Address->Street1;
					if($_Address->Street2)$PayerInfo[] = 'Street 2: '.$_Address->Street2;
					if($_Address->CityName)$PayerInfo[] = 'City: '.$_Address->CityName;
					if($_Address->StateOrProvince)$PayerInfo[] = 'State or province: '.$_Address->StateOrProvince;
					if($_Address->Country)$PayerInfo[] = 'Country: '.$_Address->Country;
					if($_Address->Phone)$PayerInfo[] = 'Phone: '.$_Address->Phone;
					if($_Address->PostalCode)$PayerInfo[] = 'Postal code: '.$_Address->PostalCode;
					if($_Address->InternationalName)$PayerInfo[] = 'International name: '.$_Address->InternationalName;
					if($_Address->InternationalStateAndCity)$PayerInfo[] = 'International state and city: '.$_Address->InternationalStateAndCity;
					if($_Address->InternationalStreet)$PayerInfo[] = 'International street: '.$_Address->InternationalStreet;
					if($_Address->AddressStatus)$PayerInfo[] = 'Address status: '.$_Address->AddressStatus;
					
					$PayerInfo = implode(', ', $PayerInfo);
					xSaveData('PPECHECKOUT_PAYERINFO', $PayerInfo, 300);
				}
			}
			
			
			$amount =& Services_PayPal::getType('BasicAmountType');
			$amount->setval($OrderAmount);
			$amount->setattr('currencyID', $_Order['currency_code']);

			$pdt =& Services_PayPal::getType('PaymentDetailsType');
			$pdt->setOrderTotal($amount);
			$pdt->setButtonSource('webasyst');
			
			$details =& Services_PayPal::getType('DoExpressCheckoutPaymentRequestDetailsType');
			$details->setPaymentAction($this->_getSettingValue('CONF_CPAYPALECHECKOUT_PAYMENTACTION'));
			$details->setToken($_GET['token']);
			$details->setPayerID($_GET['PayerID']);
			$details->setPaymentDetails($pdt);
			
			$ecprt =& Services_PayPal::getType('DoExpressCheckoutPaymentRequestType');
			$ecprt->setDoExpressCheckoutPaymentRequestDetails($details);
			
			$response = $caller->DoExpressCheckoutPayment($ecprt);
			
			
			if($response->Ack != 'Success'){
			
				$ErrorMessage = ' ';
				if(is_array($response->Errors)){
				
					foreach($response->Errors as $_Error){
					
						$ErrorMessage .= $_Error->ErrorCode.'- '.$_Error->ShortMessage.' ( '.$_Error->LongMessage.' )';
						break;
					}
				}elseif(isset($response->Errors)){
				
						$ErrorMessage .= $response->Errors->ErrorCode.'- '.$response->Errors->ShortMessage.' ( '.$response->Errors->LongMessage.' )';
				}else{
					$ErrorMessage = ' '.$response->message;
				}
				return $ErrorMessage;
	
			}else{
				return 1;
			}
		}
	}
	
	function payment_form_html(){
	
		if(isset($_GET['ppecheckout_failure'])){
			return '<div class="error_msg_f">'.CPAYPALECHECKOUT_TXT_CHECKOUT_CANCELED.'</div>';
		}
		
		if(isset($_GET['PayerID'])&&isset($_GET['token'])){
		
			return '<div class="ok_msg_f">'.CPAYPALECHECKOUT_TXT_CHECKOUT_SUCCESS.'</div>';
		}
		
		return '';
	}
	
	function after_processing_php($_OrderID){
	
		$StatusName = '';
		
		if($this->_getSettingValue('CONF_CPAYPALECHECKOUT_ORDERSTATUS') != -1){
		
			ostSetOrderStatusToOrder($_OrderID, $this->_getSettingValue('CONF_CPAYPALECHECKOUT_ORDERSTATUS'));
			$StatusName = ostGetOrderStatusName( $this->_getSettingValue('CONF_CPAYPALECHECKOUT_ORDERSTATUS') );
		}
		
		$PayerInfo = xPopData('PPECHECKOUT_PAYERINFO');
		if(isset($PayerInfo) && $PayerInfo){
		
			$sql = '
				SELECT status_change_time FROM '.ORDER_STATUS_CHANGE_LOG_TABLE.'
				WHERE orderID="'.xEscapeSQLstring($_OrderID).'"
				ORDER BY status_change_time DESC
				LIMIT 1
			';
			list($StatusChangeTime) = db_fetch_row(db_query($sql));
			if($StatusChangeTime){
			
				$sql = '
					UPDATE '.ORDER_STATUS_CHANGE_LOG_TABLE.' SET status_comment = "'.xEscapeSQLstring(CPAYPALECHECKOUT_USERINFO_PREFIX.$PayerInfo).'"
					WHERE orderID="'.xEscapeSQLstring($_OrderID).'" 
					AND status_change_time="'.$StatusChangeTime.'"
					'.($StatusName?'AND status_name="'.xEscapeSQLstring($StatusName).'"':'').'
				';
				db_query($sql);
			}
		}
	}
}
?>