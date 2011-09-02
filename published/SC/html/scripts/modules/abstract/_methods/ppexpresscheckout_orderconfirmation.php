<?php 
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	$GetVars = &$Register->get(VAR_GET);
	$PostVars = &$Register->get(VAR_POST);
	
	if(!isset($GetVars['token']) || !isset($GetVars['PayerID']))RedirectSQ('?ukey=cart');
	
	include_once DIR_MODULES.'/payment/class.ppexpresscheckout.php';
	
	$PPExpressCheckout = &PPExpressCheckout::getModuleInstance();
	
	$result = &$PPExpressCheckout->doExpressCheckoutDetailsRequest($GetVars['token']);
	
	if(Services_PayPal::isError($result)){
		
		xSaveData('_PPECHECKOUT_ERROR', $result->getMessage());
		RedirectSQ('?ukey=cart');
	}
	
	$ec = &$result->getGetExpressCheckoutDetailsResponseDetails();
	/* @var $ec GetExpressCheckoutDetailsResponseDetailsType */
	
	$PayerInfoType = $ec->getPayerInfo();
	/* @var $PayerInfoType PayerInfoType */
	$AddressType = $PayerInfoType->getAddress();
	/* @var $AddressType AddressType */
	
	@list($first_name, $last_name) = explode(' ', $AddressType->getName(), 2);
	$country = cnGetCountryByAlphaISO($AddressType->getCountry());
	$state = znGetZoneByAlphaISO($country['countryID'], $AddressType->getStateOrProvince());

	$shipping_info = array(
		'first_name' => $first_name,
		'last_name' => $last_name, 
		'country_name' => $country['country_name'],
		'state_name' => $state['zoneID']?$state['zone_name']:$AddressType->getStateOrProvince(),
		'countryID' => $country['countryID'],
		'zoneID' => $state['zoneID'], 
		'zip' => $AddressType->getPostalCode(), 
		'state' => $state['zoneID']?'':$AddressType->getStateOrProvince(), 
		'city' => $AddressType->getCityName(), 
		'address' => trim($AddressType->getStreet1().' '.$AddressType->getStreet2())
		);
	$addresses = array( $shipping_info, $shipping_info );
				
	$ShoppingCart = &ClassManager::getInstance('ShoppingCart');
	/* @var $ShoppingCart ShoppingCart */
	$ShoppingCart->loadCurrentCart();
	$cart = $ShoppingCart->emulate_cartGetCartContent();

	foreach ($cart['cart_content'] as $k=>$v){
		
		$cart['cart_content'][$k]['tax_percent'] = taxCalculateTax2($cart['cart_content'][$k]['productID'], $shipping_info, $shipping_info);
	}
	
	$discount_info = dscCalculateDiscount($cart['total_price'], isset($_SESSION['log'])?$_SESSION['log']:'');
	
	$tax_amount_uc = oaGetProductTax( $cart, $discount_info['discount_percent'], array($shipping_info, $shipping_info));

	$customerInfo = array();
	if (isset($_SESSION['log'])&&$_SESSION['log'])$customerInfo = regGetCustomerInfo2($_SESSION['log'], true );
	else{
			$customerInfo['first_name'] 	= $_SESSION['first_name'];
			$customerInfo['last_name']	= $_SESSION['last_name'];
			$customerInfo['Email']		= $_SESSION['email'];
	}
	$order = array(
		'first_name' => $customerInfo['first_name'],
		'last_name' => $customerInfo['last_name'],
		'email' => $customerInfo['Email'],
		'orderContent' => $cart['cart_content'],
		'order_amount' => $cart['total_price'] - $discount_info['discount_standart_unit'],
	);

	/**
	 * Billing info
	 */
	$billing_country = cnGetCountryByAlphaISO($PayerInfoType->getPayerCountry());
	$PayerName = $PayerInfoType->getPayerName();
	/* @var $PayerName personnametype */
	$billing_info = array(
		'payment_method' => $PPExpressCheckout->title,
		'first_name' => $PayerName->getFirstName(),
		'last_name' => $PayerName->getLastName(),
		'countryID' => $billing_country['countryID'],
		'country_name' => $billing_country['country_name'],
		'email' => $PayerInfoType->getPayer(),
	);
	
	/**
	 * Place order
	 */
	$pay_now_flag = ( isset($GetVars['useraction']) && $GetVars['useraction']=='commit' );
	if (isset($PostVars['submit']) || $pay_now_flag){

		if($pay_now_flag){
			
			$shippingMethodID = xGetData('_PPECHECKOUT_SHIPPINGMETHOD_ID');
			$shServiceID = xGetData('_PPECHECKOUT_SHIPPINGSERVICE_ID');
			set_query('useraction=','',true);
		}else{
			
			$shippingMethodID = isset($PostVars['select_shipping_method'])?$PostVars['select_shipping_method']:0;
			$shServiceID = isset($PostVars['shServiceID'][$shippingMethodID])?$PostVars['shServiceID'][$shippingMethodID]:0;
		}

		$shipping_methods = shGetAllShippingMethods(true);
		if(count($shipping_methods) && !(int)$shippingMethodID){

			RedirectSQ();
		}

		$trans_currency = currGetCurrencyByID($PPExpressCheckout->_getSettingValue('CONF_PPEXPRESSCHECKOUT_TRANSCURRENCY'));
		
		$customer_loggin = isset($_SESSION['log'])&&$_SESSION['log']?$_SESSION['log']:'';
		
		if ( $customer_loggin )$customerID = regGetIdByLogin($customer_loggin);
		else $customerID = 0;
	
		if ($customer_loggin)$customerInfo = regGetCustomerInfo2($customer_loggin, true );
		else{
			$customerInfo['first_name'] 	= $_SESSION['first_name'];
			$customerInfo['last_name']	= $_SESSION['last_name'];
			$customerInfo['Email']		= $_SESSION['email'];
			$customerInfo['affiliationLogin'] = $_SESSION['affiliationLogin'];
		}
		
		$shipping_method	= shGetShippingMethodById( $shippingMethodID );
		$shipping_email_comments_text	= $shipping_method['email_comments_text'];
		$shippingName	= $shipping_method['Name'];

		$order_amount = oaGetOrderAmount( $cart, $addresses, $shippingMethodID, $customer_loggin, $order, TRUE, $shServiceID );

		$shipping_costUC = oaGetShippingCostTakingIntoTax( $cart, $shippingMethodID, $addresses, $order, TRUE, $shServiceID, TRUE );

		$shServiceInfo = '';
		if(is_array($shipping_costUC)){
			
			list($shipping_costUC) = $shipping_costUC;
			$shServiceInfo = $shipping_costUC['name'];
			$shipping_costUC = $shipping_costUC['rate'];
		}
		
		/**
		 * Process transaction
		 */
		$pdt =& Services_PayPal::getType('PaymentDetailsType');
		/* @var $pdt PaymentDetailsType */
		$pdt->setButtonSource('shopscript-EC-webasyst');
		
		/* Order total */
		$amount =& Services_PayPal::getType('BasicAmountType');
		$amount->setval(RoundFloatValue(virtualModule::_convertCurrency($order_amount,0,$trans_currency['currency_iso_3'])));
		$amount->setattr('currencyID', $trans_currency['currency_iso_3']);
		$pdt->setOrderTotal($amount);
//		
//		/* Shipping total */
//		$amount =& Services_PayPal::getType('BasicAmountType');
//		$amount->setval(RoundFloatValue(virtualModule::_convertCurrency($shipping_costUC,0,$trans_currency['currency_iso_3'])));
//		$amount->setattr('currencyID', $trans_currency['currency_iso_3']);
//		$pdt->setShippingTotal($amount);
//		
//		/* Item total */
//		$amount =& Services_PayPal::getType('BasicAmountType');
//		$amount->setval(RoundFloatValue(virtualModule::_convertCurrency($discount_info['rest_standart_unit'],0,$trans_currency['currency_iso_3'])));
//		$amount->setattr('currencyID', $trans_currency['currency_iso_3']);
//		$pdt->setItemTotal($amount);
//		
//		/* Tax total */
//		$amount =& Services_PayPal::getType('BasicAmountType');
//		$amount->setval(RoundFloatValue(virtualModule::_convertCurrency($tax_amount_uc,0,$trans_currency['currency_iso_3'])));
//		$amount->setattr('currencyID', $trans_currency['currency_iso_3']);
//		$pdt->setTaxTotal($amount);

	//HACK
		$dbq = "INSERT `?#ORDERS_TABLE` (`order_time`,`statusID`) VALUES(?,?)";
		db_phquery($dbq,$order_time,-1);
		$temp_orderID = db_insert_id( ORDERS_TABLE );
		$pdt->setInvoiceID($temp_orderID);
				
		
		$result = $PPExpressCheckout->doDoExpressCheckoutPaymentRequest($GetVars['token'], $GetVars['PayerID'], $pdt);
		db_phquery('DELETE FROM `?#ORDERS_TABLE` WHERE `orderID`=?',$temp_orderID);
			
		if(Services_PayPal::isError($result)){
			
			xSaveData('_PPECHECKOUT_ERROR', $result->getMessage());
			xSaveData('_PPECHECKOUT_SHIPPINGMETHOD_ID', $shippingMethodID);
			xSaveData('_PPECHECKOUT_SHIPPINGSERVICE_ID', $shServiceID);
			RedirectSQ('__t=1');
		}

		$decprd = $result->getDoExpressCheckoutPaymentResponseDetails();
		/* @var $decprd DoExpressCheckoutPaymentResponseDetailsType */
		$PaymentInfoType = $decprd->getPaymentInfo();
		/* @var $PaymentInfoType PaymentInfoType */
		
		$order_info = array(
			'order_time' => Time::dateTime(),
			'statusID' => CONF_ORDSTATUS_PENDING,
			'order_discount' => $discount_info['discount_percent'],
			'order_amount' => $order_amount,
			'currency_code' => $trans_currency['currency_iso_3'],
			'currency_value' => $trans_currency['currency_value'],
			
			'customer_firstname' => $customerInfo["first_name"]?$customerInfo["first_name"]:$billing_info['first_name'],
			'customer_lastname' => $customerInfo['last_name']?$customerInfo['last_name']:$billing_info['last_name'],
			'customers_comment' => sprintf(PPECHECKOUT_TXT_CUSTCOMMENT, $billing_info['email'], $PaymentInfoType->getTransactionID()),
			'customer_email' => $customerInfo['Email']?$customerInfo['Email']:$billing_info['email'],
			'customer_ip' => stGetCustomerIP_Address(),
			'customerID' => $customerID,
			'affiliateID' => (isset($_SESSION['refid'])?$_SESSION['refid']:regGetIdByLogin(isset($customerInfo['affiliationLogin'])?$customerInfo['affiliationLogin']:'')),
			'shipping_type' => $shippingName,
			'shipping_module_id' => $shipping_method['module_id'],
			'shipping_cost' => $shipping_costUC,
			'shipping_firstname' => $shipping_info['first_name'],
			'shipping_lastname' => $shipping_info['last_name'],
			'shipping_country' => $shipping_info['country_name'],
			'shipping_state' => $shipping_info['state_name'],
			'shipping_zip' => $shipping_info['zip'],
			'shipping_city' => $shipping_info['city'],
			'shipping_address' => $shipping_info['address'],
			'shippingServiceInfo' => $shServiceInfo,
			
			'payment_type' => $PPExpressCheckout->title,
			'billing_firstname' => $billing_info['first_name'],
			'billing_lastname' => $billing_info['last_name'],
			'billing_country' => $billing_info['country_name'],
			'payment_module_id' => $PPExpressCheckout->get_id(),
			'source' => $Register->get('widgets')?'widgets':'storefront',
		);
		
		$dbq = '
			INSERT ?#ORDERS_TABLE (?&) VALUES (?@)
		';

		db_phquery($dbq, array_keys($order_info), $order_info);
		
		$orderID = db_insert_id( ORDERS_TABLE );
		db_phquery('UPDATE `?#ORDERS_TABLE` SET `orderID`=? WHERE `orderID`=?',$temp_orderID,$orderID);
		$orderID = $temp_orderID;
		//TODO remove all old temporal order records
		
		if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
			include_once(WBS_DIR.'/kernel/classes/class.metric.php');
			
			$DB_KEY=SystemSettings::get('DB_KEY');
			$U_ID = sc_getSessionData('U_ID');
			
			$metric_data = array(
				/*$order_info['currency_code'],
				$order_info['order_amount'],
				$order_info['payment_type'],
				$order_info['shipping_type'],
				$order_info['shipping_country'],
				$order_info['shipping_state'],
				$order_info['shipping_city'],	*/			
			);
			
			$metric = metric::getInstance();
			$metric->addAction($DB_KEY, $U_ID,'SC', 'ORDER', isset($_GET['widgets'])?'WIDGET':'STOREFRONT', implode(':',$metric_data));
		}		
		
		stChangeOrderStatus($orderID, $order_info['statusID'], translate('ordr_comment_orderplaced'));
		
		$ShoppingCart->saveToOrderedCarts($orderID, $shipping_info, $billing_info);

		$smarty_mail = new ViewSC();
		$smarty_mail->template_dir = DIR_TPLS."/email";
		
		if(function_exists('sc_registerOrder2MT')){
			$res = sc_registerOrder2MT($orderID);
		}
		
		_sendOrderNotifycationToAdmin( $orderID, $smarty_mail, $tax_amount_uc);
		_sendOrderNotifycationToCustomer( $orderID, $smarty_mail, $order_info['customer_email'], regGetLoginById($order_info['customerID']),'', $shipping_method['email_comments_text'], $tax_amount_uc );
	
		$OrderingModule = ModulesFabric::getModuleObjByKey('Ordering');
		$OrderingModule->getInterface('successful_ordering', $orderID);
		if($PPExpressCheckout->_getSettingValue('CONF_PPEXPRESSCHECKOUT_ORDERSTATUS')!=-1){
			ostSetOrderStatusToOrder($orderID, $PPExpressCheckout->_getSettingValue('CONF_PPEXPRESSCHECKOUT_ORDERSTATUS'));
		}

		cartClearCartContet();
		
		xSaveData('PPEC_ORDER_ID', $orderID);
		xSaveData('PPEC_TRANSACTION_ID', $PaymentInfoType->getTransactionID());
		
		RedirectSQ('?ukey=ppec_order_success');
	}
	
	/**
	 * Shipping methods
	 */
	$shipping_methods	= shGetAllShippingMethods( true );
	$shipping_costs		= array();

	$j = 0;
	foreach( $shipping_methods as $key => $shipping_method ){
	
		$_ShippingModule = ShippingRateCalculator::getInstance($shipping_method['module_id']);
		if($_ShippingModule){
			
			if($_ShippingModule->allow_shipping_to_address($shipping_info)){

				$shipping_costs[$j] = oaGetShippingCostTakingIntoTax( $cart, $shipping_method['SID'], $addresses, $order );
			}else{
	
				$shipping_costs[$j] = array(array('rate'=>-1));
			}
		}else{ //rate = freight charge
			
			$shipping_costs[$j] = oaGetShippingCostTakingIntoTax( $cart, $shipping_method['SID'], $addresses, $order );
		}
		$j++;
	}
			
	$_i = count($shipping_costs)-1;
	for ( ; $_i>=0; $_i-- ){
		
		$_t = count($shipping_costs[$_i])-1;
		for ( ; $_t>=0; $_t-- ){
			
			if($shipping_costs[$_i][$_t]['rate']>0){
				$shipping_costs[$_i][$_t]['_total_price'] = show_price($shipping_costs[$_i][$_t]['rate']+$tax_amount_uc+$cart['total_price']-$discount_info['discount_standart_unit']);
				$shipping_costs[$_i][$_t]['rate'] = show_price($shipping_costs[$_i][$_t]['rate']);
			}else {
			
				if(count($shipping_costs[$_i]) == 1 && $shipping_costs[$_i][$_t]['rate']<0){
				
					$shipping_costs[$_i] = 'n/a';
				}else{
				
					$shipping_costs[$_i][$_t]['rate'] = '';
				}
			}
		}
	}
	$order_totals = array(
		'discount_percent'=> $discount_info['discount_percent'],
		'pred_total_disc'=> show_price($discount_info['discount_standart_unit']),
		'total_tax'=> show_price($tax_amount_uc),
		'order_amount'=> show_price($tax_amount_uc+$cart['total_price']-$discount_info['discount_standart_unit']),
	);
	
	$avmethod_cnt = 0;
	foreach ($shipping_costs as $shipping_cost){
		
		if($shipping_cost == 'n/a')continue;
		$avmethod_cnt++;
	}
	
	if(xDataExists('_PPECHECKOUT_ERROR')){
		$smarty->assign('ppec_error_message', xPopData('_PPECHECKOUT_ERROR'));
	}
	$smarty->assign('shipping_method_id', xPopData('_PPECHECKOUT_SHIPPINGMETHOD_ID'));
	$smarty->assign('shipping_service_id', xPopData('_PPECHECKOUT_SHIPPINGSERVICE_ID'));
	
	$smarty->assign('billing_info', $billing_info);
	$smarty->assign('shipping_info', $shipping_info);
	$smarty->assign('shipping_costs',		$shipping_costs );
	$smarty->assign('shipping_methods',	$shipping_methods );
	$smarty->assign('shipping_methods_count',  $avmethod_cnt );
	$smarty->assign('shipping_price',  show_price(0) );
	$smarty->assign($order_totals);
	$smarty->assign('cart_content', $cart['cart_content']);
	$smarty->assign('main_content_template', 'ppecheckout_orderconfirmation.html');
?>