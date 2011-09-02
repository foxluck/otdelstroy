<?php
/**
 * @connect_module_class_name PPExpressCheckout
 * @package DynamicModules
 * @subpackage Payment
 */
if(defined('FILE_PPEXPRESSCHECKOUTV2')){
	return false;
}else{
	define('FILE_PPEXPRESSCHECKOUTV2', 1);
}
//commented out use kernel PEAR
//set_include_path('.'.PATH_DELIMITER.'..'.PATH_DELIMITER.DIR_MODULES.'/payment/pppro/pear');
error_reporting(E_ALL & ~E_NOTICE);

require_once 'Services/PayPal.php';

class PPExpressCheckout extends PaymentModule {

	var $type = PAYMTD_TYPE_REPLACE;
	var $language = 'eng';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/paypal.gif';
	
	var $CertsPath = '';
	
	function _initVars(){
		
		parent::_initVars();
		$this->SingleInstall = true;
		$this->CertsPath = DIR_TEMP;
		$this->title 		= PPEXPRESSCHECKOUT_TTL;
		$this->description 	= PPEXPRESSCHECKOUT_DSCR;
		$this->sort_order 	= 1;
		
		$this->Settings = array( 
			'CONF_PPEXPRESSCHECKOUT_ENABLED',
			'CONF_PPEXPRESSCHECKOUT_USERNAME',
			'CONF_PPEXPRESSCHECKOUT_PASSWORD',
			'CONF_PPEXPRESSCHECKOUT_CERTPATH',
			'CONF_PPEXPRESSCHECKOUT_MODE',
			'CONF_PPEXPRESSCHECKOUT_PAYMENTACTION',
			'CONF_PPEXPRESSCHECKOUT_TRANSCURRENCY',
			'CONF_PPEXPRESSCHECKOUT_ORDERSTATUS',
		);
	}

	function _initSettingFields(){
		
		$this->SettingsFields['CONF_PPEXPRESSCHECKOUT_ENABLED'] = array(
			'settings_value' 		=> '1', 
			'settings_title' 			=> PPEXPRESSCHECKOUT_CFG_ENABLED_TTL, 
			'settings_description' 	=> PPEXPRESSCHECKOUT_CFG_ENABLED_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PPEXPRESSCHECKOUT_USERNAME'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> PPEXPRESSCHECKOUT_CFG_USERNAME_TTL, 
			'settings_description' 	=> PPEXPRESSCHECKOUT_CFG_USERNAME_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PPEXPRESSCHECKOUT_PASSWORD'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> PPEXPRESSCHECKOUT_CFG_PASSWORD_TTL, 
			'settings_description' 	=> PPEXPRESSCHECKOUT_CFG_PASSWORD_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PPEXPRESSCHECKOUT_CERTPATH'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> PPEXPRESSCHECKOUT_CFG_CERTPATH_TTL, 
			'settings_description' 	=> PPEXPRESSCHECKOUT_CFG_CERTPATH_DSCR, 
			'settings_html_function' 	=> 'setting_SINGLE_FILE(DIR_TEMP,',
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PPEXPRESSCHECKOUT_MODE'] = array(
			'settings_value' 		=> 'Sandbox', 
			'settings_title' 			=> PPEXPRESSCHECKOUT_CFG_MODE_TTL, 
			'settings_description' 	=> PPEXPRESSCHECKOUT_CFG_MODE_DSCR, 
			'settings_html_function' 	=> 'setting_RADIOGROUP(PPEXPRESSCHECKOUT_TXT_TEST.":Sandbox,".PPEXPRESSCHECKOUT_TXT_LIVE.":Live",', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PPEXPRESSCHECKOUT_PAYMENTACTION'] = array(
			'settings_value' 		=> 'Sale', 
			'settings_title' 			=> PPEXPRESSCHECKOUT_CFG_PAYMENTACTION_TTL, 
			'settings_description' 	=> PPEXPRESSCHECKOUT_CFG_PAYMENTACTION_DSCR, 
			'settings_html_function' 	=> 'setting_RADIOGROUP("Sale:Sale,Order:Order,Authorization:Authorization",', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PPEXPRESSCHECKOUT_TRANSCURRENCY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> PPEXPRESSCHECKOUT_CFG_TRANSCURRENCY_TTL, 
			'settings_description' 	=> PPEXPRESSCHECKOUT_CFG_TRANSCURRENCY_DSCR, 
			'settings_html_function' 	=> 'PPExpressCheckout::_settingCurrencySelect(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PPEXPRESSCHECKOUT_ORDERSTATUS'] = array(
			'settings_value' 		=> '-1', 
			'settings_title' 			=> PPEXPRESSCHECKOUT_CFG_ORDERSTATUS_TTL, 
			'settings_description' 	=> PPEXPRESSCHECKOUT_CFG_ORDERSTATUS_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),', 
			'sort_order' 			=> 1,
		);
	}
	

	/**
	 *
	 * @return CallerServices
	 */
	function &_getCaller(){

		require_once 'Services/PayPal/Profile/Handler/Array.php';
		require_once 'Services/PayPal/Profile/API.php';
		
		$handler =& ProfileHandler_Array::getInstance(array(
			'username' => $this->_getSettingValue('CONF_PPEXPRESSCHECKOUT_USERNAME'),
			'certificateFile' => realpath($this->CertsPath.'/'.$this->_getSettingValue('CONF_PPEXPRESSCHECKOUT_CERTPATH')),
			'subject' => null,
			'environment' => $this->_getSettingValue('CONF_PPEXPRESSCHECKOUT_MODE')));
		
		$profile =& APIProfile::getInstance($this->_getSettingValue('CONF_PPEXPRESSCHECKOUT_USERNAME'), $handler);
		$profile->setAPIPassword($this->_getSettingValue('CONF_PPEXPRESSCHECKOUT_PASSWORD'));
		
		$caller =& Services_PayPal::getCallerServices($profile);

		return $caller;
	}
	
	function doSetExpressCheckoutRequest($params = array()){

		if (!cartCheckMinTotalOrderAmount()) {
			return translate("cart_min_order_amount_not_reached").show_price(CONF_MINIMAL_ORDER_AMOUNT);
		}
		$caller = &$this->_getCaller();
		if(Services_PayPal::isError($caller)){
			
			return PPEXPRESSCHECKOUT_TXT_ERRORCALLER.$caller->getMessage();
		}
		

		$currency = currGetCurrencyByID($this->_getSettingValue('CONF_PPEXPRESSCHECKOUT_TRANSCURRENCY'));
		
		include_once(DIR_CLASSES.'/class.shoppingcart.php');
		$ShoppingCart = new ShoppingCart();
		$ShoppingCart->loadCurrentCart();
		$cart_amount_uc = $ShoppingCart->calculateTotalPrice();
		$resDiscount = dscCalculateDiscount( $cart_amount_uc, isset($_SESSION["log"])?$_SESSION["log"]:"" );
		$order_amount = RoundFloatValue($this->_convertCurrency($cart_amount_uc, 0, $currency['currency_iso_3'])) - 
			RoundFloatValue($this->_convertCurrency($resDiscount['discount_current_unit'], 0, $currency['currency_iso_3']));

		$amount =& Services_PayPal::getType('BasicAmountType');
		$amount->setval($order_amount);
		$amount->setattr('currencyID', $currency['currency_iso_3']);
		
		$ecd =& Services_PayPal::getType('SetExpressCheckoutRequestDetailsType');
		/* @var $ecd SetExpressCheckoutRequestDetailsType*/
		$ecd->setOrderTotal($amount);
		
		$ParsedURL = parse_url(CONF_FULL_SHOP_URL);

		$SuccessURL = set_query('?token=&PayerID=&ukey=ppexpresscheckout_orderconfirmation');
		if(isset($params['success_url_query'])){
			
			$SuccessURL = set_query($params['success_url_query'], $SuccessURL);
		}

		if(strpos($SuccessURL,'http')===false && strpos($SuccessURL,'https')===false){
		
			$SuccessURL = $ParsedURL['scheme'].'://'.$ParsedURL['host'].(isset($ParsedURL['port'])&&$ParsedURL['port']?':'.$ParsedURL['port']:'').$SuccessURL;
		}

		$FailureURL = set_query('');
		if(strpos($FailureURL,'http')===false && strpos($FailureURL,'https')===false){
		
			$FailureURL = $ParsedURL['scheme'].'://'.$ParsedURL['host'].(isset($ParsedURL['port'])&&$ParsedURL['port']?':'.$ParsedURL['port']:'').$FailureURL;
		}
		
		$ecd->setReturnURL($SuccessURL);
		$ecd->setCancelURL($FailureURL);
		$ecd->setPaymentAction($this->_getSettingValue('CONF_PPEXPRESSCHECKOUT_PAYMENTACTION'));
		if(isset($params['AddressType']))$ecd->setAddress($params['AddressType']);
		if(isset($params['AddressOverride']))$ecd->setAddressOverride($params['AddressOverride']);
		if(isset($params['NoShipping']))$ecd->setNoShipping($params['NoShipping']);
		
		$ec =& Services_PayPal::getType('SetExpressCheckoutRequestType');
		$ec->setSetExpressCheckoutRequestDetails($ecd);
		
		
		//TODO: USE GLOBAL WebAsyst Proxy settings
		
		$options = getProxySettings();
		$caller->__proxy_params = array(
					'proxy_host'=>isset($options['host'])?$options['host']:null,
					'proxy_port'=>isset($options['port'])?$options['port']:null,
					'proxy_user'=>isset($options['user'])?$options['user']:null,
					'proxy_pass'=>isset($options['password'])?$options['password']:null,);
		
		$response = $caller->SetExpressCheckout($ec);
		
		
		if(Services_PayPal::isError($response)){
			return $response;
		}
		
		$error = $this->checkResultError($response);
		if(Services_PayPal::isError($error)){
			return $error;
		}
		Redirect($this->getExpressCheckoutURL($response->Token, '', isset($params['useraction'])?$params['useraction']:null));
	}

	/**
	 * @param string $token
	 * @return getexpresscheckoutdetailsresponsetype
	 */
	function &doExpressCheckoutDetailsRequest($token){
		
		$caller = &$this->_getCaller();
		if(Services_PayPal::isError($caller)){
			
			return $caller;
		}
		
		
		$ec =& Services_PayPal::getType('GetExpressCheckoutDetailsRequestType');
		/* @var $ec GetExpressCheckoutDetailsRequestType */
		$ec->setToken($token);
		$response = $caller->GetExpressCheckoutDetails($ec);

		/* @var $response GetExpressCheckoutDetailsResponseType */
		$error = $this->checkResultError($response);

		if(Services_PayPal::isError($error)){
			return $error;
		}


		return $response;
	}

	/**
	 *
	 * @param string $token
	 * @param string $payer_id
	 * @param PaymentDetailsType $PaymentDetails
	 * @return doexpresscheckoutpaymentresponsetype | PEAR_Error
	 */
	function &doDoExpressCheckoutPaymentRequest($token, $payer_id, $PaymentDetails){
		
		$caller = &$this->_getCaller();
		if(Services_PayPal::isError($caller)){
			
			return $caller;
		}
		$caller->setOpt('curl', CURLOPT_CONNECTTIMEOUT, 20);
		$caller->setOpt('curl', CURLOPT_TIMEOUT, 20);
		
		$details =& Services_PayPal::getType('DoExpressCheckoutPaymentRequestDetailsType');
		/* @var $ec DoExpressCheckoutPaymentRequestDetailsType */
		$details->setToken($token);
		$details->setPayerID($payer_id);
		$details->setPaymentAction($this->_getSettingValue('CONF_PPEXPRESSCHECKOUT_PAYMENTACTION'));
		
		$details->setPaymentDetails($PaymentDetails);
		
		$ecprt =& Services_PayPal::getType('DoExpressCheckoutPaymentRequestType');
		$ecprt->setDoExpressCheckoutPaymentRequestDetails($details);
		
		$response = $caller->DoExpressCheckoutPayment($ecprt);
		/* @var $response doexpresscheckoutpaymentresponsetype */
		$error = $this->checkResultError($response);
		if(Services_PayPal::isError($error)){
			return $error;
		}
		return $response;
	}
	
	function _getCheckoutButtonURL(){
		
		return 'https://www.paypal.com/en_US/i/btn/btn_xpressCheckoutsm.gif';
	}
	
	function getCheckoutButton(){
		
		$html = '<input type="image" name="ppe_checkout" src="'.$this->_getCheckoutButtonURL().'" style="margin-right:7px;" />';
//		$Register = &Register::getInstance();
//		/*@var $Register Register*/
//		$currentDivision = &$Register->get(VAR_CURRENTDIVISION);
//		/*@var $currentDivision Division*/
//		if($currentDivision->getUnicKey() != 'cart'){
//			$html = '<form action="'.xHtmlSetQuery('ppexpresscheckout2=1').'" method="post">'.$html.'</form>';			
//		}
		return $html;
	}
	
	function install(){
		
		parent::install();
		$AbstractMod = &ModulesFabric::getModuleObjByKey('Abstract');
		/* @var $AbstractMod Abstract */
		$division_ukeys = array('cart','cart_popup');
		$interface = $AbstractMod->getConfigID().'_ppexpresscheckout_button';
		foreach ($division_ukeys as $division_ukey){
			
			$CDivision = &DivisionModule::getDivisionByUnicKey($division_ukey);
			if($CDivision->getID()){
				
				$CDivision->addInterface($interface);
			}
		}
		
		$PPECheckoutOrderConfirmationDivision = new Division();
		$PPECheckoutOrderConfirmationDivision->setName('PP Express Checkout - order confirmation');
		$PPECheckoutOrderConfirmationDivision->setUnicKey('ppexpresscheckout_orderconfirmation');
		$PPECheckoutOrderConfirmationDivision->setParentID(DivisionModule::getDivisionIDByUnicKey('TitlePage'));
		$PPECheckoutOrderConfirmationDivision->save();
		
		$PPECheckoutOrderConfirmationDivision->addInterface($AbstractMod->getConfigID().'_ppexpresscheckout_orderconfirmation');
		
		$PPECOrderSuccessDivision = new Division();
		$PPECOrderSuccessDivision->setName('PP Express Checkout - order success');
		$PPECOrderSuccessDivision->setUnicKey('ppec_order_success');
		$PPECOrderSuccessDivision->setParentID(DivisionModule::getDivisionIDByUnicKey('TitlePage'));
		$PPECOrderSuccessDivision->save();
		
		$PPECOrderSuccessDivision->addInterface($AbstractMod->getConfigID().'_ppec_order_success');
	}
	
	function uninstall($_ConfigID = 0){
		
		parent::uninstall($_ConfigID);
		$AbstractMod = &ModulesFabric::getModuleObjByKey('Abstract');
		/* @var $AbstractMod Abstract */
		$division_ukeys = array('cart','cart_popup');
		$interface = $AbstractMod->getConfigID().'_ppexpresscheckout_button';
		foreach ($division_ukeys as $division_ukey){
			
			$CDivision = &DivisionModule::getDivisionByUnicKey($division_ukey);
			if($CDivision->getID()){
				
				$CDivision->deleteInterface($interface);
			}
		}
		$PPECheckoutOrderConfirmationDivision = &DivisionModule::getDivisionByUnicKey('ppexpresscheckout_orderconfirmation');
		
		$PPECheckoutOrderConfirmationDivision->deleteInterface($AbstractMod->getConfigID().'_ppexpresscheckout_orderconfirmation');
		$PPECheckoutOrderConfirmationDivision->delete();
		
		$PPECOrderSuccessDivision = &DivisionModule::getDivisionByUnicKey('ppec_order_success');
		
		$PPECOrderSuccessDivision->deleteInterface($AbstractMod->getConfigID().'_ppec_order_success');
		$PPECOrderSuccessDivision->delete();
	}
	
	/**
	 * @return PPExpressCheckout
	 */
	function &getModuleInstance(){
		
		list($PPExpressCheckoutInfo) = modGetModuleConfigs('ppexpresscheckout');
		$PPExpressCheckout = PaymentModule::getInstance($PPExpressCheckoutInfo['ConfigID']);
		return $PPExpressCheckout;
	}

	/**
	 * @param mixed $response
	 * @return PEAR_Error | null
	 */
	function checkResultError($response){
		
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
			
			return Services_PayPal::raiseError($ErrorMessage);
		}else{
			return null;
		}
	}

	/**
	 * @param string $token
	 * @param string $payer_id
	 * @return string
	 */
	function getExpressCheckoutURL($token, $payer_id = '', $useraction = 'continue'){
		
		return 'https://www.'.($this->_getSettingValue('CONF_PPEXPRESSCHECKOUT_MODE')=='Live'?'':'sandbox.').'paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$token.'&useraction='.$useraction;
	}

	function _settingCurrencySelect($setting_id){
		
		return setting_CURRENCY_SELECT(array(array('title'=>PPECHECKOUT_TXT_CDCURRENCY, 'value'=>0,)), $setting_id);
	}

	function _getSettingValue($setting_name){
		
		$setting_value = parent::_getSettingValue($setting_name);
		
		if($setting_name == 'CONF_PPEXPRESSCHECKOUT_TRANSCURRENCY' && !$setting_value){
			
			$setting_value = currGetCurrentCurrencyUnitID();
		}
			
		return $setting_value;
	}
}
?>