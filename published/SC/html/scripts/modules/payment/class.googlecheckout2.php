<?php
if(defined('FILE_GOOGLECHECKOUT2')){
	return false;
}else{
	define('FILE_GOOGLECHECKOUT2', 1);
}
/**
 * @connect_module_class_name GoogleCheckout2
 * @package DynamicModules
 * @subpackage Payment
 * @see http://code.google.com/apis/checkout/developer/index.html#level_2_integration
 */

class GoogleCheckout2 extends PaymentModule {

	var $type = PAYMTD_TYPE_REPLACE;
	var $language = 'eng';
	
	var $SingleInstall = true;
	
	function install(){
		
		$columns = db_getColumns(ORDERS_TABLE);
		
		if(!array_key_exists('google_order_number', $columns)){

			db_query('ALTER TABLE '.ORDERS_TABLE.' ADD `google_order_number` VARCHAR( 50 ) NOT NULL');
			db_query('ALTER TABLE '.ORDERS_TABLE.' ADD INDEX ( `google_order_number` )');
		}
		parent::install();
		$AbstractMod = &ModulesFabric::getModuleObjByKey('Abstract');
		/* @var $AbstractMod Abstract */
		$CartDivision = &DivisionModule::getDivisionByUnicKey('cart');
		if($CartDivision->getID()){
			
			$CartDivision->addInterface($AbstractMod->getConfigID().'_googlecheckout_checkoutbutton');
		}
		$CartDivision = &DivisionModule::getDivisionByUnicKey('cart_popup');
		if($CartDivision->getID()){
			
			$CartDivision->addInterface($AbstractMod->getConfigID().'_googlecheckout_checkoutbutton');
		}
		
		$GoogleHndlDivision = new Division();
		$GoogleHndlDivision->setName('Google Checkout handler');
		$GoogleHndlDivision->setUnicKey('googlecheckout_handler');
		$GoogleHndlDivision->save();
		
		$GoogleHndlDivision->addInterface($AbstractMod->getConfigID().'_googlecheckout_handler');
	}
	
	function uninstall($_ConfigID = 0){
		
		parent::uninstall($_ConfigID);
		$AbstractMod = &ModulesFabric::getModuleObjByKey('Abstract');
		/* @var $AbstractMod Abstract */
		$CartDivision = &DivisionModule::getDivisionByUnicKey('cart');
		if($CartDivision->getID()){
			
			$CartDivision->deleteInterface($AbstractMod->getConfigID().'_googlecheckout_checkoutbutton');
		}
		$CartDivision = &DivisionModule::getDivisionByUnicKey('cart_popup');
		if($CartDivision->getID()){
			
			$CartDivision->deleteInterface($AbstractMod->getConfigID().'_googlecheckout_checkoutbutton');
		}
		
		$GoogleHndlDivision = &DivisionModule::getDivisionByUnicKey('googlecheckout_handler');
		
		$GoogleHndlDivision->deleteInterface($AbstractMod->getConfigID().'_googlecheckout_handler');
		$GoogleHndlDivision->delete();
	}
	
	function _initVars(){
		
		$this->SingleInstall = true;
		$this->title 		= GOOGLECHECKOUT2_TTL;
		$this->description 	= GOOGLECHECKOUT2_DSCR;
		$this->LogFile = DIR_TEMP.'/googlecheckout2.log';
		
		$this->Settings = array( 
				'CONF_GOOGLECHECKOUT2_ENABLED',
				'CONF_GOOGLECHECKOUT2_SANDBOX',
				'CONF_GOOGLECHECKOUT2_CALCULATESHIPTAX',
				'CONF_GOOGLECHECKOUT2_SENDORDERNOTIFYCATION',
				'CONF_GOOGLECHECKOUT2_MERCHANTID',
				'CONF_GOOGLECHECKOUT2_MERCHANTKEY',
				'CONF_GOOGLECHECKOUT2_TRANSCURR',
				'CONF_GOOGLECHECKOUT2_CHARGEORDER',
				'CONF_GOOGLECHECKOUT2_CHARGEDORDERSTATUS',
				'CONF_GOOGLECHECKOUT2_SHIPPEDORDERSTATUS',
				//'CONF_GOOGLECHECKOUT2_SHIPPIGNRESCTRICTIONS',
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_GOOGLECHECKOUT2_MERCHANTID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> GOOGLECHECKOUT2_CFG_MERCHANTID_TTL, 
			'settings_description' 	=> GOOGLECHECKOUT2_CFG_MERCHANTID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
		);
		$this->SettingsFields['CONF_GOOGLECHECKOUT2_MERCHANTKEY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> GOOGLECHECKOUT2_CFG_MERCHANTKEY_TTL, 
			'settings_description' 	=> GOOGLECHECKOUT2_CFG_MERCHANTKEY_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
		);
		$this->SettingsFields['CONF_GOOGLECHECKOUT2_SANDBOX'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> GOOGLECHECKOUT2_CFG_SANDBOX_TTL, 
			'settings_description' 	=> GOOGLECHECKOUT2_CFG_SANDBOX_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
		);
		$this->SettingsFields['CONF_GOOGLECHECKOUT2_CALCULATESHIPTAX'] = array(
			'settings_value' 		=> 1, 
			'settings_title' 			=> GOOGLECHECKOUT2_CFG_CALCULATESHIPTAX_TTL, 
			'settings_description' 	=> GOOGLECHECKOUT2_CFG_CALCULATESHIPTAX_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
		);
		$this->SettingsFields['CONF_GOOGLECHECKOUT2_SENDORDERNOTIFYCATION'] = array(
			'settings_value' 		=> 0, 
			'settings_title' 			=> GOOGLECHECKOUT2_CFG_SENDORDERNOTIFYCATION_TTL, 
			'settings_description' 	=> GOOGLECHECKOUT2_CFG_SENDORDERNOTIFYCATION_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
		);
		$this->SettingsFields['CONF_GOOGLECHECKOUT2_ENABLED'] = array(
			'settings_value' 		=> 1, 
			'settings_title' 			=> GOOGLECHECKOUT2_CFG_ENABLED_TTL, 
			'settings_description' 	=> GOOGLECHECKOUT2_CFG_ENABLED_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
		);
		$this->SettingsFields['CONF_GOOGLECHECKOUT2_TRANSCURR'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> GOOGLECHECKOUT2_CFG_TRANSCURR_TTL, 
			'settings_description' 	=> GOOGLECHECKOUT2_CFG_TRANSCURR_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
		);
		$this->SettingsFields['CONF_GOOGLECHECKOUT2_CHARGEORDER'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> GOOGLECHECKOUT2_CFG_CHARGEORDER_TTL, 
			'settings_description' 	=> GOOGLECHECKOUT2_CFG_CHARGEORDER_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
		);
		$this->SettingsFields['CONF_GOOGLECHECKOUT2_CHARGEDORDERSTATUS'] = array(
			'settings_value' 		=> '-1', 
			'settings_title' 			=> GOOGLECHECKOUT2_CFG_CHARGEDORDERSTATUS_TTL, 
			'settings_description' 	=> GOOGLECHECKOUT2_CFG_CHARGEDORDERSTATUS_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),', 
		);
		$this->SettingsFields['CONF_GOOGLECHECKOUT2_SHIPPEDORDERSTATUS'] = array(
			'settings_value' 		=> '-1', 
			'settings_title' 			=> GOOGLECHECKOUT2_CFG_SHIPPEDORDERSTATUS_TTL, 
			'settings_description' 	=> GOOGLECHECKOUT2_CFG_SHIPPEDORDERSTATUS_DSCR, 
			'settings_html_function' 	=> 'setting_SELECT_BOX(PaymentModule::_getStatuses(),', 
		);
	/*	$this->SettingsFields['CONF_GOOGLECHECKOUT2_SHIPPIGNRESCTRICTIONS'] = array(
			'settings_value' 		=> '-1', 
			'settings_title' 			=> GOOGLECHECKOUT2_CFG_SHIPPIGNRESCTRICTIONS_TTL, 
			'settings_description' 	=> GOOGLECHECKOUT2_CFG_SSHIPPIGNRESCTRICTIONS_DSCR, 
			'settings_html_function' 	=> 'setting_RADIOGROUP(GOOGLECHECKOUT2_CFG_SSHIPPIGNRESCTRICTIONS_ENTIRE_WORLD.":ALL,".GOOGLECHECKOUT2_CFG_SSHIPPIGNRESCTRICTIONS_US_ONLY.":US",', 
		);*/
	}

	function payment_process($order){
		
		return 'You cannt use it so.';
	}
	
	function processCheckout(){

		if (!cartCheckMinTotalOrderAmount()) {
			return translate("cart_min_order_amount_not_reached").show_price(CONF_MINIMAL_ORDER_AMOUNT);
		}
		$currency = currGetCurrencyByID($this->_getSettingValue('CONF_GOOGLECHECKOUT2_TRANSCURR'));

		$CheckoutShoppingCart = new xmlNodeX('checkout-shopping-cart');
		$CheckoutShoppingCart->attribute('xmlns', 'http://checkout.google.com/schema/2');
		$ShoppingCart = &$CheckoutShoppingCart->child('shopping-cart');
		$Items = &$ShoppingCart->child('items');

		$cart_content = cartGetCartContent();

		if(!count($cart_content['cart_content']))return 'Empty cart';
		
		foreach ($cart_content['cart_content'] as $cart_item){
			
			$Item = &$Items->child('item');
			
			$ItemName = &$Item->child('item-name');
			$ItemName->setData($cart_item['name']);
			
			$product = GetProduct($cart_item['productID']);
			
			$ItemDescription = &$Item->child('item-description');
			$ItemDescription->setData(strip_tags($product['brief_description']?$product['brief_description']:$product['description']));
			
			$UnitPrice = &$Item->child('unit-price');
			$UnitPrice->attribute('currency', $currency['currency_iso_3']);
			$UnitPrice->setData(RoundFloatValue(PaymentModule::_convertCurrency($cart_item['costUC'],0,$currency['currency_iso_3'])));
			
			$Quantity = &$Item->child('quantity');
			$Quantity->setData($cart_item['quantity']);
		}
		
		/**
		 * Discount
		 */
		$resDiscount = dscCalculateDiscount( $cart_content["total_price"], isset($_SESSION["log"])?$_SESSION["log"]:"" );
		//FIXME: 
		if($resDiscount['discount_standart_unit']>0){
			
			$discount_amount = PaymentModule::_convertCurrency($resDiscount['discount_standart_unit'],0, $currency['currency_iso_3']);
			$discount_amount = RoundFloatValue($discount_amount);
			
			$Item = &$Items->child('item');
			
			$ItemName = &$Item->child('item-name');
			$ItemName->setData(GOOGLECHECKOUT2_TXT_DISCOUNT);
			
			$ItemDescription = &$Item->child('item-description');
			$discountDescription = '';
			if(CONF_DSC_COUPONS_ENABLED){
				ClassManager::includeClass('discount_coupon');
				$discountDescription =discount_coupon::getCurrentCoupon();
			}
			$ItemDescription->setData($discountDescription?$discountDescription:'');
			
			$UnitPrice = &$Item->child('unit-price');
			$UnitPrice->attribute('currency', $currency['currency_iso_3']);
			$UnitPrice->setData(-1*$discount_amount);
			
			$Quantity = &$Item->child('quantity');
			$Quantity->setData(1);
		}
		
		/**
		 * Some ss-info about shopping cart
		 */
		if ( isset($_SESSION['log']) && $_SESSION['log'] != null )$customerID = regGetIdByLogin( $_SESSION['log'] );
		else $customerID = 0;
	
		if ( isset($_SESSION['log']) && $_SESSION['log'] != null )$customerInfo = regGetCustomerInfo2( $_SESSION['log'], true );
		else{
			
			$cust_fields = array('first_name'=>'first_name', 'last_name'=>'last_name', 'email'=>'Email');
			$cust_fields['affiliationLogin'] = 'affiliationLogin';
			foreach ($cust_fields as $sess_field=>$cust_field){
				
				$customerInfo[$cust_field] = isset($_SESSION[$sess_field])?$_SESSION[$sess_field]:'';
			}
			
		}
		$MerchantPrivateData = &$ShoppingCart->child('merchant-private-data');
		$OrderData = &$MerchantPrivateData->child('order-data');
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$OrderData->child('source', null, $Register->get('widgets')?'widgets':'storefront');
		//FIXME: check it
		$OrderData->child('discount-percent', null, $resDiscount['discount_percent']);
		$OrderData->child('customer-firstname', null, $customerInfo["first_name"]);
		$OrderData->child('customer-lastname', null, $customerInfo["last_name"]);
		$OrderData->child('customer-email', null, $customerInfo["Email"]);
		$OrderData->child('customer-ip', null, stGetCustomerIP_Address());
		$OrderData->child('customer-id', null, $customerID);
		$OrderData->child('freight-cost', null, sprintf('%.2f', RoundFloatValue(PaymentModule::_convertCurrency($cart_content['freight_cost'], 0, $currency['currency_iso_3']))));
		$OrderData->child('affiliate-id', null, (isset($_SESSION['refid'])?$_SESSION['refid']:regGetIdByLogin(isset($customerInfo["affiliationLogin"])?$customerInfo["affiliationLogin"]:'')));
		
		$CartContent = &$MerchantPrivateData->child('cart-content');
		
		$aShoppingCart = new ShoppingCart();
		$customerID = isset($_SESSION["log"])?regGetIdByLogin($_SESSION["log"]):0;
		if($customerID){
			
			$aShoppingCart->loadCurrentCustomerCart($customerID);
		}else{
			
			$aShoppingCart->loadCurrentCartFromSession();
		}
		
		$CartContent->setData(base64_encode($aShoppingCart->exportInXML()));
		
		$CheckoutFlowSupport = &$CheckoutShoppingCart->child('checkout-flow-support');
		$MerchantCheckoutFlowSupport = &$CheckoutFlowSupport->child('merchant-checkout-flow-support');

		$PlatformID = &$MerchantCheckoutFlowSupport->child('platform-id');
		$PlatformID->setData('626424857959005');
		
		$EditCartURL = &$MerchantCheckoutFlowSupport-> child('edit-cart-url');
		$EditCartURL->setData(set_query('?ukey=cart', CONF_FULL_SHOP_URL));
	
		$ContinueShoppingURL = &$MerchantCheckoutFlowSupport-> child('continue-shopping-url');
		$ContinueShoppingURL->setData(set_query('?ukey=googlecheckout_handler&success_order=1', CONF_FULL_SHOP_URL));
		
		/**
		 * Shipping
		 */
		$ShippingMethods = &$MerchantCheckoutFlowSupport->child('shipping-methods');
		
		if(!$this->_getSettingValue('CONF_GOOGLECHECKOUT2_CALCULATESHIPTAX')){
			
			$FlatRateShipping = &$ShippingMethods->child('flat-rate-shipping');
			$FlatRateShipping->attribute('name','Call');
			$Price = &$FlatRateShipping->child('price');
			$Price->attribute('currency',$currency['currency_iso_3']);
			$Price->setData(sprintf('%.2f', RoundFloatValue(PaymentModule::_convertCurrency($cart_content['freight_cost'], 0, $currency['currency_iso_3']))));
			//$ShippingRestrictions = &$FlatRateShipping->child('shipping-restrictions');
			$AddressFilters = &$FlatRateShipping->child('address-filters');
			$AllowedAreas = &$AddressFilters->child('allowed-areas');
			$AllowedAreas->child('world-area');
			//address-filters
			//$AllowedAreas = &$ShippingRestrictions->child('allowed-areas');
			//$AllowedAreas->child('world-area');
//			$USZipArea = &$AllowedAreas->child('us-zip-area');
//			$USZipArea->child('zip-pattern', null, '*');
		}else{

			$MerchantCalculations = &$MerchantCheckoutFlowSupport->child('merchant-calculations');
			$MerchantCalculations->child('merchant-calculations-url', null, set_query('?ukey=googlecheckout_handler',str_replace('http://','https://',CONF_FULL_SHOP_URL)));
			
			$shipping_methods	= shGetAllShippingMethods( true );
	
			/**
			 * Add supporting of shipping sevices
			 */
			foreach ($shipping_methods as $shipping_method){
				
				$ShippingModule = ShippingRateCalculator::getInstance($shipping_method['module_id']);
				/* @var $ShippingModule ShippingRateCalculator */
				
				$module_services = is_object($ShippingModule)?$ShippingModule->getAvailableServices('', true):array();
				
				$no_services = false;
				
				if(!count($module_services)){
					
					$no_services = true;
					$module_services[] = 1;
				}
				
				foreach ($module_services as $module_service){
					
					$MerchantCalculatedShipping = &$ShippingMethods->child('merchant-calculated-shipping');
					$MerchantCalculatedShipping->attribute('name',$shipping_method['Name'].($no_services?'':(' - '.$module_service['name'])).' ['.$shipping_method['SID'].($no_services?'':(':'.$module_service['id'])).']');
					
					//$ShippingRestrictions = &$MerchantCalculatedShipping->child('shipping-restrictions');
					$AddressFilters = &$MerchantCalculatedShipping->child('address-filters');
					$AllowedAreas = &$AddressFilters->child('allowed-areas');
					$AllowedAreas->child('world-area');
					//address-filters
					//$AllowedAreas = &$ShippingRestrictions->child('allowed-areas');
					//$AllowedAreas->child('world-area');
//					$USZipArea = &$AllowedAreas->child('us-zip-area');
//					$USZipArea->child('zip-pattern', null, '*');
					
					$Price = &$MerchantCalculatedShipping->child('price');
					$Price->attribute('currency', $currency['currency_iso_3']);
					$Price->setData('0.00');
				}
				
				/**
				 * Only freight cost if free shipping
				 */
				if(!$no_services){
					
					$MerchantCalculatedShipping = &$ShippingMethods->child('merchant-calculated-shipping');
					$MerchantCalculatedShipping->attribute('name',$shipping_method['Name']. '['.$shipping_method['SID'].']');
					
					//$ShippingRestrictions = &$MerchantCalculatedShipping->child('shipping-restrictions');
					$AddressFilters = &$MerchantCalculatedShipping->child('address-filters');
					$AllowedAreas = &$AddressFilters->child('allowed-areas');
					$AllowedAreas->child('world-area');
					//address-filters
					//$AllowedAreas = &$ShippingRestrictions->child('allowed-areas');
					//$AllowedAreas->child('world-area');
//					$USZipArea = &$AllowedAreas->child('us-zip-area');
//					$USZipArea->child('zip-pattern', null, '*');
				
					$Price = &$MerchantCalculatedShipping->child('price');
					$Price->attribute('currency', $currency['currency_iso_3']);
					$Price->setData('0.00');
				}
			}
			
			$MerchantCalculatedShipping = &$ShippingMethods->child('merchant-calculated-shipping');
			$MerchantCalculatedShipping->attribute('name',GOOGLECHECKOUT2_TXT_FREIGHT);
			
			//$ShippingRestrictions = &$MerchantCalculatedShipping->child('shipping-restrictions');
			$AddressFilters = &$MerchantCalculatedShipping->child('address-filters');
			$AllowedAreas = &$AddressFilters->child('allowed-areas');
			$AllowedAreas->child('world-area');
			//address-filters
			//$AllowedAreas = &$ShippingRestrictions->child('allowed-areas');
			//$AllowedAreas->child('world-area');
//			$USZipArea = &$AllowedAreas->child('us-zip-area');
//			$USZipArea->child('zip-pattern', null, '*');
		
			$Price = &$MerchantCalculatedShipping->child('price');
			$Price->attribute('currency', $currency['currency_iso_3']);
			$Price->setData(sprintf('%.2f', RoundFloatValue(PaymentModule::_convertCurrency($cart_content['freight_cost'], 0, $currency['currency_iso_3']))));
		}
		
		/**
		 * Tax
		 */
		$TaxTables = &$MerchantCheckoutFlowSupport->child('tax-tables');
		if(!$this->_getSettingValue('CONF_GOOGLECHECKOUT2_CALCULATESHIPTAX')){
			
			$TaxTables->attribute('merchant-calculated','false');
		}else{
			
			$TaxTables->attribute('merchant-calculated','true');
		}
		
		$DefaultTaxTable = &$TaxTables->child('default-tax-table');
		$TaxRules = &$DefaultTaxTable->child('tax-rules');
		$DefaultTaxRule = &$TaxRules->child('default-tax-rule');
		$DefaultTaxRule->child('shipping-taxed', array(),'true');
		$Rate = &$DefaultTaxRule->child('rate');
		$Rate->setData('0.00');
		$TaxArea = &$DefaultTaxRule->child('tax-area');
		//$TaxArea->child('us-country-area', array('country-area'=>'ALL'));
//<tax-area>
//                  <world-area/>
//                </tax-area>
		$TaxArea->child('world-area');
		
		$result = $this->sendData($CheckoutShoppingCart->getNodeXML());

		$CheckoutRedirect = new xmlNodeX();
		$CheckoutRedirect->renderTreeFromInner($result);
		if($CheckoutRedirect->getName() != 'checkout-redirect'){
			
			$ErrorMessage = $CheckoutRedirect->getChildData('error-message');
			return $ErrorMessage;
		}
		
		$redirect_url = str_replace('&amp;','&',$CheckoutRedirect->getChildData('redirect-url'));

		Redirect($redirect_url);
		die;
	}
	
	function sendData($xml){
		
		if(strpos($xml,'<?xml version="1.0" encoding="'.translate("str_default_charset").'"?>')===false){
			
			$xml = '<?xml version="1.0" encoding="'.translate("str_default_charset").'"?>'.$xml;
		}
		
		
	/*	if(file_exists(DIR_TEMP.'/google_request_default.xml')){
			if($fp=fopen(DIR_TEMP.'/google_request_default.xml','rb')){
				$xml = fread($fp,filesize(DIR_TEMP.'/google_request_default.xml'));
				fclose($fp);
			}
		}*/
		
		/*if($fp=fopen(DIR_TEMP.'/google_request.xml','wb')){
			fwrite($fp,$xml);
			fclose($fp);
		}*/
		
		//print htmlentities($xml);exit;
		$merchant_id = trim($this->_getSettingValue('CONF_GOOGLECHECKOUT2_MERCHANTID'));
		$merchant_key = trim($this->_getSettingValue('CONF_GOOGLECHECKOUT2_MERCHANTKEY'));
		$sandbox = $this->_getSettingValue('CONF_GOOGLECHECKOUT2_SANDBOX');
		$url = $sandbox?'https://sandbox.google.com/checkout/cws/v2/Merchant/'.$merchant_id.'/request':'https://checkout.google.com/cws/v2/Merchant/'.$merchant_id.'/request';
		
		
		
		if(!extension_loaded('curl'))
			return "Curl: PHP module isn't loaded";
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Basic '.base64_encode($merchant_id.':'.$merchant_key),
			'Content-Type: application/xml', 'Accept: application/xml'));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_TIMEOUT, 25);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		if ($sandbox) {
			
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
		initCurlProxySettings($ch);

		$result=curl_exec ($ch);
		

		if(curl_errno($ch)){
			$result = "Curl: #".curl_errno($ch).(curl_error($ch)?' - '.curl_error($ch):'');
		}
		curl_close ($ch); 
		
		
		return $result;
	}
	
	function getCheckoutButtonURL(){
		
		return $this->_getSettingValue('CONF_GOOGLECHECKOUT2_SANDBOX')?
			'http://sandbox.google.com/checkout/buttons/checkout.gif':
			'http://checkout.google.com/buttons/checkout.gif';
	}
	
	function getCheckoutButton($name = 'Google Checkout', $width = 180, $height = 46){
		return '<input type="image" name="google_checkout" alt="Fast checkout through Google"
        src="'.xHtmlSpecialChars(set_query('?merchant_id='.$this->_getSettingValue('CONF_GOOGLECHECKOUT2_MERCHANTID').'&w='.$width.'&h='.$height.'&style=white&variant=text&loc=en_US', $this->getCheckoutButtonURL(),false,true)).'"  height="'.$height.'" width="'.$width.'" />';
	}
	
	/**
	 * @param xmlNodeX $Request
	 */
	function hndl_NewOrderNotification(&$Request){
		//uncomment next line for debug purpose
		//$this->_writeLogMessage(null,var_export($Request,true));
		/**
		 * Defence from order duplication
		 */
		if($this->_getSSOrderID($Request->getChildData('google-order-number'))){
			$this->notificationAcknowledgment(true);
			die;
		}
		$OrderTotal = &$Request->getFirstChildByName('order-total');
		$currency = currGetCurrencyByISO3($OrderTotal->attribute('currency'));
		
		$OrderAdjustment = &$Request->getFirstChildByName('order-adjustment');
		$Shipping = &$OrderAdjustment->getFirstChildByName('shipping');
		if(!$this->_getSettingValue('CONF_GOOGLECHECKOUT2_CALCULATESHIPTAX')){
			
			$ShippingInfo = &$Shipping->getFirstChildByName('flat-rate-shipping-adjustment');
		}else{
			
			$ShippingInfo = &$Shipping->getFirstChildByName('merchant-calculated-shipping-adjustment');
		}
		
		$ShoppingCart = &$Request->getFirstChildByName('shopping-cart');
		$MerchantPrivateData = &$ShoppingCart->getFirstChildByName('merchant-private-data');
		
		$OrderData = &$MerchantPrivateData->getFirstChildByName('order-data');

		$CartContent = &$MerchantPrivateData->getFirstChildByName('cart-content');
		
		include_once(DIR_CLASSES.'/class.shoppingcart.php');
		$aShoppingCart = new ShoppingCart();
		$aShoppingCart->loadFromXML(base64_decode($CartContent->getData()));
		
		$BuyerBillingAddress = &$Request->getFirstChildByName('buyer-billing-address');
		$BuyerShippingAddress = &$Request->getFirstChildByName('buyer-shipping-address');
		
		$billing_name = explode(' ',$BuyerBillingAddress->getChildData('contact-name'),2);
		if(!isset($billing_name[1])){
			$billing_name[1] = '';
		}
		$shipping_name = explode(' ',$BuyerShippingAddress->getChildData('contact-name'),2);
		if(!isset($shipping_name[1])){
			$shipping_name[1] = '';
		}
		
		$billing_country = cnGetCountryByAlphaISO($BuyerBillingAddress->getChildData('country-code'));
		$billing_zone = znGetZoneByAlphaISO($billing_country['countryID'], $BuyerBillingAddress->getChildData('region'));
		
		$shipping_country = cnGetCountryByAlphaISO($BuyerShippingAddress->getChildData('country-code'));
		$shipping_zone = znGetZoneByAlphaISO($shipping_country['countryID'], $BuyerShippingAddress->getChildData('region'));
		
		$billing_info = array(
			'countryID' => $billing_country['countryID'],
			'zoneID' => $billing_zone['zoneID'],
			'zip' => $BuyerBillingAddress->getChildData('postal-code'),
		);
		
		$shipping_info = array(
			'countryID' => $shipping_country['countryID'],
			'zoneID' => $shipping_zone['zoneID'],
			'zip' => $BuyerShippingAddress->getChildData('postal-code'),
		);
		$order_info = array(
			'order_time' => Time::dateTime(),
			'statusID' => CONF_ORDSTATUS_PENDING,
			'order_discount' => $OrderData->getChildData('discount-percent'),
			'order_amount' => PaymentModule::_convertCurrency($OrderTotal->getData(), $OrderTotal->attribute('currency'), 0),
			'currency_code' => $OrderTotal->attribute('currency'),
			'currency_value' => $currency['currency_value'],
			
			'customer_firstname' => $OrderData->getChildData('customer-firstname')?$OrderData->getChildData('customer-firstname'):$billing_name[0],
			'customer_lastname' => $OrderData->getChildData('customer-lastname')?$OrderData->getChildData('customer-lastname'):$billing_name[1],
			'customers_comment' => '',
			'customer_email' => $OrderData->getChildData('customer-email')?$OrderData->getChildData('customer-email'):$BuyerBillingAddress->getChildData('email'),
			'customer_ip' => $OrderData->getChildData('customer-ip'),
			'customerID' => $OrderData->getChildData('customer-id'),
			'affiliateID' => $OrderData->getChildData('affiliate-id'),
			'shipping_type' => $ShippingInfo->getChildData('shipping-name'),
			'shipping_cost' => virtualModule::_convertCurrency($ShippingInfo->getChildData('shipping-cost'),$OrderTotal->attribute('currency'),0),
			'shipping_firstname' => $shipping_name[0],
			'shipping_lastname' => $shipping_name[1],
			'shipping_country' => $shipping_country['country_name']?$shipping_country['country_name']:$BuyerShippingAddress->getChildData('country-code'),
			'shipping_state' => $shipping_zone['zone_name']?$shipping_zone['zone_name']:$BuyerShippingAddress->getChildData('region'),
			'shipping_zip' => $BuyerShippingAddress->getChildData('postal-code'),
			'shipping_city' => $BuyerShippingAddress->getChildData('city'),
			'shipping_address' => trim(
				$BuyerShippingAddress->getChildData('address1')
				.' '
				.$BuyerShippingAddress->getChildData('address2')
				.' '.
				$BuyerShippingAddress->getChildData('company-name')
				.' '.
				$BuyerShippingAddress->getChildData('phone')
				.' '.
				$BuyerShippingAddress->getChildData('fax')),
				//address1, address2?, city, company-name?, contact-name?, country-code, email?, fax?, phone?, postal-code, region
			'shippingServiceInfo' => '',
			
			'payment_type' => 'Google ('.$Request->getChildData('google-order-number').')',
			'billing_firstname' => $billing_name[0],
			'billing_lastname' => $billing_name[1],
			'billing_country' => $billing_country['country_name']?$billing_country['country_name']:$BuyerBillingAddress->getChildData('country-code'),
			'billing_state' => $billing_zone['zone_name']?$billing_zone['zone_name']:$BuyerBillingAddress->getChildData('region'),
			'billing_zip' => $BuyerBillingAddress->getChildData('postal-code'),
			'billing_city' => $BuyerBillingAddress->getChildData('city'),
			'billing_address' => trim(
				$BuyerBillingAddress->getChildData('address1')
				.' '
				.$BuyerBillingAddress->getChildData('address2')
				.' '.
				$BuyerBillingAddress->getChildData('phone')
				.' '.
				$BuyerBillingAddress->getChildData('fax')),
				//address1, address2?, city, company-name?, contact-name, country-code, email?, fax?, phone?, postal-code, region
			
			'google_order_number' => $Request->getChildData('google-order-number'),
			'source' => $OrderData->getChildData('source'),
		);
		
		$dbq = '
			INSERT ?#ORDERS_TABLE (?&) VALUES (?@)
		';

		db_phquery($dbq, array_keys($order_info), $order_info);
		
		$orderID = db_insert_id( ORDERS_TABLE );
		stChangeOrderStatus($orderID, $order_info['statusID'], translate('ordr_comment_orderplaced'));

		$aShoppingCart->saveToOrderedCarts($orderID, $shipping_info, $billing_info);

		$this->ggl_setMerchantOrderNumber($Request->getChildData('google-order-number'),$orderID);
		
		$smarty_mail = new ViewSC();
		$smarty_mail->template_dir = DIR_TPLS."/email";
		
		$TotalTax = &$OrderAdjustment->getFirstChildByName('total-tax');
		$tax_uc = virtualModule::_convertCurrency($TotalTax->getData(), $TotalTax->attribute('currency'),0);
		if(function_exists('sc_registerOrder2MT')){
			$res = sc_registerOrder2MT($orderID);
		}
		_sendOrderNotifycationToAdmin( $orderID, $smarty_mail, $tax_uc);

		if($this->_getSettingValue('CONF_GOOGLECHECKOUT2_SENDORDERNOTIFYCATION')){
			
			$shipping_info = $this->_extractInfoFromShippingName($ShippingInfo->getChildData('shipping-name'));
			if(!is_null($shipping_info)){
				
				$shipping_method = shGetShippingMethodById($shipping_info['method_id']);
				_sendOrderNotifycationToCustomer( $orderID, $smarty_mail, $order_info['customer_email'], regGetLoginById($order_info['customerID']),'', $shipping_method['email_comments_text'], $tax_uc );
			}
		}
	
		$OrderingModule = ModulesFabric::getModuleObjByKey('Ordering');
		$OrderingModule->getInterface('successful_ordering', $orderID);
		$this->notificationAcknowledgment();
	}

	/**
	 * @param xmlNodeX $Request
	 */
	function hndl_MerchantCalculationCallback(&$Request){

		list($xnCartContent) = $Request->xPath('/merchant-calculation-callback/shopping-cart/merchant-private-data/cart-content');
		list($xnOrderData) = $Request->xPath('/merchant-calculation-callback/shopping-cart/merchant-private-data/order-data');
		/* @var $xnOrderData xmlNodeX */
		$xnFreightCost = &$xnOrderData->getFirstChildByName('freight-cost');
		$xnDiscountPercent = &$xnOrderData->getFirstChildByName('discount-percent');
		
		$currency = currGetCurrencyByID($this->_getSettingValue('CONF_GOOGLECHECKOUT2_TRANSCURR'));
		
		include_once(DIR_CLASSES.'/class.shoppingcart.php');
		$aShoppingCart = new ShoppingCart();
		$aShoppingCart->loadFromXML(base64_decode($xnCartContent->getData()));
		
		$xnCalculate = &$Request->getFirstChildByName('calculate');
		$xnTax = &$xnCalculate->getFirstChildByName('tax');
		$xnShipping = &$xnCalculate->getFirstChildByName('shipping');
		if(!is_null($xnShipping)){
		/**
		 * Shipping calculation
		 */
			$xnrMerchantCalculationResults = new xmlNodeX('merchant-calculation-results');
			$xnrMerchantCalculationResults->attribute('xmlns','http://checkout.google.com/schema/2');
			$xnrResults = &$xnrMerchantCalculationResults->child('results');
			
			$r_xnMethod = $xnShipping->getChildrenByName('method');
			$xnAddresses = &$xnCalculate->getFirstChildByName('addresses');
			$r_xnAnonymousAddress = $xnAddresses->getChildrenByName('anonymous-address');
			
			$cart_content = $aShoppingCart->emulate_cartGetCartContent();
			
			$order_details = array (
				'first_name' => '',
				'last_name' => '',
				'email' => '',
			);
			$order_details['order_amount'] = $cart_content['total_price'] - $cart_content['total_price']*$xnOrderData->getChildData('discount-percent')/100;
			
			/**
			 * Cach of addresses
			 */
			$prepared_addresses = array();
			$prepared_methods = array();
			
			foreach ($r_xnMethod as $xnMethod){
				/*@var $xnMethod xmlNodeX */
				$shipping_info = $this->_extractInfoFromShippingName($xnMethod->attribute('name'));
				if(!is_null($shipping_info)){
					
					$shipping_method_id = $shipping_info['method_id'];
					$shipping_service_id = $shipping_info['service_id'];
				}else{
					continue;						
				}
				$prepared_methods[$shipping_method_id][$shipping_service_id] = array(
					'name' => $xnMethod->attribute('name')
					);
			}
			
			foreach ($r_xnAnonymousAddress as $xnAnonymousAddress){
				/*@var $xnAnonymousAddress xmlNodeX */
					
					if(!array_key_exists($xnAnonymousAddress->attribute('id'), $prepared_addresses)){
						
						$country = cnGetCountryByAlphaISO($xnAnonymousAddress->getChildData('country-code'));
						$zone = znGetZoneByAlphaISO($country['countryID'], $xnAnonymousAddress->getChildData('region'));
						
						$prepared_addresses[$xnAnonymousAddress->attribute('id')] = array(
							'first_name' => '',
							'last_name' => '', 
							'countryID' => $country['countryID'],
							'zoneID' => $zone['zoneID'], 
							'zip' => $xnAnonymousAddress->getChildData('postal-code'), 
							'state' => $zone['zone_name'], 
							'city' => $xnAnonymousAddress->getChildData('city'), 
							'address' => ''
						);
					}
			}
			
			foreach ($prepared_addresses as $address_id=>$address){
				
				/**
				 * Tax
				 */
				if($xnTax->getData()=='true'){
					$tax_amount = RoundFloatValue(virtualModule::_convertCurrency(oaGetProductTax( $cart_content, floatval($xnDiscountPercent->getData()), array($address, $address)), 0,
						$currency['currency_iso_3']));
				}
				
				$shipping_methods_cnt = 0;
				foreach ($prepared_methods as $shipping_method_id=>$shipping_services){

					$shipping_method = shGetShippingMethodById($shipping_method_id);
					$ShippingModule = ShippingRateCalculator::getInstance($shipping_method['module_id']);
					/* @var $ShippingModule ShippingRateCalculator */
					$shipping_available = true;
					if(!is_null($ShippingModule)){
						
						$shipping_available = $ShippingModule->allow_shipping_to_address($address);
					}

					if($shipping_available){
						$shipping_rates = oaGetShippingCostTakingIntoTax($cart_content, $shipping_method_id, array($address, $address), $order_details);
					}else{
						$shipping_rates = array();
					}
					
					$shipping_services_cnt = 0;
					foreach ($shipping_rates as $shipping_rate){
						
						if(!array_key_exists('id', $shipping_rate))continue;
						if(!array_key_exists($shipping_rate['id'], $shipping_services))continue;
						
						$xnrResult = &$xnrResults->child('result');
						$xnrResult->attribute('shipping-name', $shipping_services[$shipping_rate['id']]['name']);
						$xnrResult->attribute('address-id', $address_id);
						
						if($xnTax->getData()=='true'){
							
							$xnrTotalTax = &$xnrResult->child('total-tax');
							$xnrTotalTax->attribute('currency', $currency['currency_iso_3']);
							$xnrTotalTax->setData(sprintf('%.2f',$tax_amount));
						}
						
						$xnrShippingRate = &$xnrResult->child('shipping-rate');
						$xnrShippingRate->attribute('currency', $currency['currency_iso_3']);
						$xnrShippingRate->setData(sprintf('%.2f',(
							RoundFloatValue(virtualModule::_convertCurrency($shipping_rate['rate'],0,$currency['currency_iso_3']))
							)));
						unset($shipping_services[$shipping_rate['id']]);
						$shipping_services_cnt++;
						$shipping_methods_cnt++;
					}
					
					foreach ($shipping_services as $shipping_service_id=>$shipping_service){
						
						if($shipping_service['name']==GOOGLECHECKOUT2_TXT_FREIGHT){
							continue;
						}
						$xnrResult = &$xnrResults->child('result');
						$xnrResult->attribute('shipping-name', $shipping_service['name']);
						$xnrResult->attribute('address-id', $address_id);
						
						if($xnTax->getData()=='true'){
							
							$xnrTotalTax = &$xnrResult->child('total-tax');
							$xnrTotalTax->attribute('currency', $currency['currency_iso_3']);
							$xnrTotalTax->setData(sprintf('%.2f',$tax_amount));
						}
						
						$xnrShippingRate = &$xnrResult->child('shipping-rate');
						$xnrShippingRate->attribute('currency', $currency['currency_iso_3']);
						if($shipping_available && $shipping_service_id == '_none_' && !$shipping_services_cnt){
							
							$xnrShippingRate->setData(sprintf('%.2f',(
								RoundFloatValue(virtualModule::_convertCurrency($shipping_rates[0]['rate'],0,$currency['currency_iso_3']))
								)));
							$shipping_methods_cnt++;
						}else{
							
							$xnrResult->child('shippable', null, 'false');
							$xnrShippingRate->setData('0.00');
						}
					}
				}
				
				$xnrResult = &$xnrResults->child('result');
				$xnrResult->attribute('shipping-name', GOOGLECHECKOUT2_TXT_FREIGHT);
				$xnrResult->attribute('address-id', $address_id);
				
				if($xnTax->getData()=='true'){
					
					$xnrTotalTax = &$xnrResult->child('total-tax');
					$xnrTotalTax->attribute('currency', $currency['currency_iso_3']);
					$xnrTotalTax->setData(sprintf('%.2f',$tax_amount));
				}
				
				$xnrShippingRate = &$xnrResult->child('shipping-rate');
				$xnrShippingRate->attribute('currency', $currency['currency_iso_3']);
				if(!$shipping_methods_cnt){
					
					$xnrShippingRate->setData(sprintf('%.2f',(
						RoundFloatValue(virtualModule::_convertCurrency($xnFreightCost->getData(),0,$currency['currency_iso_3']))
						)));
				}else{
					
					$xnrResult->child('shippable', null, 'false');
					$xnrShippingRate->setData('0.00');
				}
			}
			
			print 
			'<?xml version="1.0" encoding="'.translate("str_default_charset").'"?>'.
			$xnrMerchantCalculationResults->getNodeXML();
			die;
		}
	}
	/**
	 * @param xmlNodeX $Request
	 */
	function hndl_OrderStateChangeNotification(&$Request){
		
		$xnGoogleOrderNumber = &$Request->getFirstChildByName('google-order-number');
		if($Request->getChildData('new-financial-order-state')=='CHARGEABLE' && $this->_getSettingValue('CONF_GOOGLECHECKOUT2_CHARGEORDER')){
			
			$order_id = $this->_getSSOrderID($xnGoogleOrderNumber->getData());
			$order = ordGetOrder($order_id);
			
			$xnChargeOrder = new xmlNodeX('charge-order');
			$xnChargeOrder->attribute('xmlns', 'http://checkout.google.com/schema/2');
			$xnChargeOrder->attribute('google-order-number', $xnGoogleOrderNumber->getData());
			
			$currency = currGetCurrencyByID($this->_getSettingValue('CONF_GOOGLECHECKOUT2_TRANSCURR'));
			
			$xnAmount = &$xnChargeOrder->child('amount');
			$xnAmount->attribute('currency', $currency['currency_iso_3']);
			$xnAmount->setData(RoundFloatValue(PaymentModule::_convertCurrency($order['order_amount'], 0, $this->_getSettingValue('CONF_GOOGLECHECKOUT2_TRANSCURR'))));
			$this->sendData($xnChargeOrder->getNodeXML());
		}
		
		if($Request->getChildData('new-financial-order-state')=='CHARGED'
				&& $Request->getChildData('previous-financial-order-state')=='CHARGING'
			 && $this->_getSettingValue('CONF_GOOGLECHECKOUT2_CHARGEDORDERSTATUS') != -1){
			
			$order_id = $this->_getSSOrderID($xnGoogleOrderNumber->getData());
			$order = ordGetOrder($order_id);
			if($order_id && $order['statusID']!=$this->_getSettingValue('CONF_GOOGLECHECKOUT2_CHARGEDORDERSTATUS'))
				ostSetOrderStatusToOrder($order_id, $this->_getSettingValue('CONF_GOOGLECHECKOUT2_CHARGEDORDERSTATUS'));
		}
		
		if($Request->getChildData('new-financial-order-state')=='CANCELLED'){
			$order_id = $this->_getSSOrderID($xnGoogleOrderNumber->getData());
			if($order_id){
				ostSetOrderStatusToOrder($order_id, CONF_ORDSTATUS_CANCELLED,'The buyer or the merchant canceled the order');
			}
		}
		
		if($Request->getChildData('new-financial-order-state')=='CANCELLED_BY_GOOGLE'){
			$order_id = $this->_getSSOrderID($xnGoogleOrderNumber->getData());
			if($order_id){
				ostSetOrderStatusToOrder($order_id, CONF_ORDSTATUS_CANCELLED,'Google cancel an order because the credit card authorization fails and the customer does not provide a new credit card within seven days');
			}
		}
		
		if($Request->getChildData('new-fulfillment-order-state')=='DELIVERED' && $this->_getSettingValue('CONF_GOOGLECHECKOUT2_SHIPPEDORDERSTATUS') != -1){
			
			$order_id = $this->_getSSOrderID($xnGoogleOrderNumber->getData());
			$order = ordGetOrder($order_id);
			if($order_id && $order['statusID']!=$this->_getSettingValue('CONF_GOOGLECHECKOUT2_SHIPPEDORDERSTATUS'))
				ostSetOrderStatusToOrder($order_id, $this->_getSettingValue('CONF_GOOGLECHECKOUT2_SHIPPEDORDERSTATUS'));
		}
		
		$this->notificationAcknowledgment();
	}

	function ggl_setMerchantOrderNumber($google_order_number, $order_id){
		
		$xnAddMerchantOrderNumber = new xmlNodeX('add-merchant-order-number');
		$xnAddMerchantOrderNumber->attribute('xmlns','http://checkout.google.com/schema/2');
		$xnAddMerchantOrderNumber->attribute('google-order-number', $google_order_number);
		
		$xnMerchantOrderNumber = &$xnAddMerchantOrderNumber->child('merchant-order-number');
		$xnMerchantOrderNumber->setData($order_id);
		$this->sendData($xnAddMerchantOrderNumber->getNodeXML());
	}
	
	/**
	 * Get order id by google order number
	 *
	 * @param string $google_order_number
	 * @return int
	 */
	function _getSSOrderID($google_order_number){
		
			$dbq = 'SELECT orderID FROM ?#ORDERS_TABLE WHERE google_order_number=?';
			$dbr = db_phquery($dbq, $google_order_number);
			if(!db_num_rows($dbr['resource'])){
				return 0;
			}
			list($order_id) = db_fetch_row($dbr);
			return $order_id;
	}

	/**
	 * @param string $shipping_name
	 * @return null|array: (method_id,service_id)
	 */
	function _extractInfoFromShippingName($shipping_name){
		
		if($shipping_name == GOOGLECHECKOUT2_TXT_FREIGHT){
			
			return array(
				'method_id' => 0,
				'service_id' => '_none_',
			);
		}
		if(!preg_match('/\[([^\[]*)\]$/msi',$shipping_name,$subpatterns))return null;
		$shipping_method_id = explode(':',$subpatterns[1],2);
		$shipping_service_id = '_none_';
		if(isset($shipping_method_id[1])){
			$shipping_service_id = $shipping_method_id[1];
		}
		$shipping_method_id = $shipping_method_id[0];
		
		return array(
			'method_id' => $shipping_method_id,
			'service_id' => $shipping_service_id,
		);
	}

	function notificationAcknowledgment($state = true){
		
		if($state){
			
			header('HTTP/1.1 200');
			print '<?xml version="1.0" encoding="'.translate("str_default_charset").'"?><notification-acknowledgment xmlns="http://checkout.google.com/schema/2"/>';
			flush();
		}else{
			
			header('HTTP/1.1 500');
			flush();
		}
	}
}
?>