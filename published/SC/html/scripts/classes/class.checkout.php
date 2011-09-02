<?php
//ClassManager::includeClass('Storage');

class Checkout extends Storage {

	var $widgets = 0;
	
	function customers_comment($customers_comment = null){
		
		if(!is_null($customers_comment))$this->setData('customers_comment', $customers_comment);
		
		return $this->getData('customers_comment');
	}
	
	/**
	 * @return Customer
	 */
	function &customer($customer = null){
		
		if(!is_null($customer))$this->setData('customer', serialize($customer));
		
		if($_SESSION['log']){
			
			return Customer::getAuthedInstance();
		}
		return unserialize($this->getData('customer'));
	}
	
	/**
	 * @param Address
	 * @return Address
	 */
	function &shippingAddress($address = null){
		
		if(!is_null($address))$this->setData('shipping_address', serialize($address));
		
		$addressEntry = unserialize($this->getData('shipping_address'));
		
		if(!is_object($addressEntry) && $_SESSION['log']){
			
			$customerEntry = Customer::getAuthedInstance();
			$addressEntry = new Address();
			$res = $addressEntry->loadByID($customerEntry->addressID);
			if(!$res){
				
				$addresses = regGetAllAddressesByID($customerEntry->customerID);
				$addressEntry->loadFromArray($addresses[0]);
			}
		}
		return $addressEntry;
	}
	
	/**
	 * @return Address
	 */
	function &billingAddress($address = null){
		
		if(!is_null($address))$this->setData('billing_address', serialize($address));
		
		$addressEntry = unserialize($this->getData('billing_address'));
		if(!is_object($addressEntry) && $_SESSION['log']){
			
			$customerEntry = Customer::getAuthedInstance();
			$addressEntry = new Address();
			$res = $addressEntry->loadByID($customerEntry->addressID);
			if(!$res){
				
				$addresses = regGetAllAddressesByID($customerEntry->customerID);
				$addressEntry->loadFromArray($addresses[0]);
			}
		}
		return $addressEntry;
	}
	
	function &shippingMethodID($methodID = null){
		
		if(!is_null($methodID))$this->setData('shippingMethodID', $methodID);
		
		return $this->getData('shippingMethodID');
	}
	
	function &shippingServiceID($serviceID = null){
		
		if(!is_null($serviceID))$this->setData('shippingServiceID', $serviceID);
		
		return $this->getData('shippingServiceID');
	}
	
	function &paymentMethodID($methodID = null){
		
		if(!is_null($methodID))$this->setData('paymentMethodID', $methodID);
		
		return $this->getData('paymentMethodID');
	}
	
	/**
	 * @return Checkout
	 */
	static function &getInstance($name){
		
		$storageEntry = new Checkout();
		/*@var $storageEntry Checkout*/
		$storageEntry->init($name);
		return $storageEntry;
	}
	
	function formsData($data = null){

		if(!is_null($data))$this->setData('forms_data', is_array($this->formsData())?array_merge($this->formsData(), $data):$data);
		
		return $this->getData('forms_data',array());
	}
	
	function dropFormsData2Smarty(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/

		$smarty->assign($this->getData('forms_data'));
	}

	function emulate_getOrder(){
		
		$customerEntry = $this->customer();
		
		$order = array(
			'first_name' => $customerEntry->first_name,
			'last_name' => $customerEntry->last_name,
			'email' => $customerEntry->Email
		);
		
		$cartEntry = new ShoppingCart();
		$cartEntry->loadCurrentCart();
		$cart = $cartEntry->emulate_cartGetCartContent();
		
		$order["orderContent"]	= $cart["cart_content"];
	
		$d = oaGetDiscountValue( $cart, $_SESSION["log"] );
		$order["order_amount"] = $cart["total_price"] - $d;
		
		return $order;
	}

	function emulate_getOrderSummarize(){
	
		// result this function
		$sumOrderContent = array();

		$payment_email_comments_text = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT '.LanguagesManager::sql_prepareField('email_comments_text').' AS email_comments_text FROM ?#PAYMENT_TYPES_TABLE WHERE PID=?',$paymentMethodID);
		$shipping_email_comments_text = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT '.LanguagesManager::sql_prepareField('email_comments_text').' FROM ?#SHIPPING_METHODS_TABLE WHERE SID=?', $shippingMethodID);

		$cartEntry = new ShoppingCart();
		$cartEntry->loadCurrentCart();
		$cartContent = $cartEntry->emulate_cartGetCartContent();
		foreach ($cartContent['cart_content'] as $k=>$_item){
			
			$thumbnail = GetThumbnail($_item['productID']);
			$cartContent['cart_content'][$k]['thumbnail_url'] = $thumbnail&&file_exists(DIR_PRODUCTS_PICTURES.'/'.$thumbnail)?URL_PRODUCTS_PICTURES.'/'.$thumbnail:'';
			if($cartContent['cart_content'][$k]['thumbnail_url']){
				
				list($thumb_width, $thumb_height) = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$thumbnail);
				list($cartContent['cart_content'][$k]['thumbnail_width'], $cartContent['cart_content'][$k]['thumbnail_height']) = shrink_size($thumb_width, $thumb_height, round(CONF_PRDPICT_THUMBNAIL_SIZE/2), round(CONF_PRDPICT_THUMBNAIL_SIZE/2));
			}
		}
		
		$pred_total = $cartEntry->calculateTotalPrice();

		$log = isset($_SESSION["log"])?$_SESSION["log"]:null;

		$d = oaGetDiscountValue( $cartContent, $log );
		$discount = $d;

		$shippingAddress = $this->shippingAddress();
		$billingAddress = $this->billingAddress();

		$addresses = array($shippingAddress->getVars(), $billingAddress->getVars());

		$shipping_method = shGetShippingMethodById( $this->shippingMethodID() );
		$shipping_name = !$shipping_method?"-":$shipping_method["Name"];
	
		$payment_method	= payGetPaymentMethodById($this->paymentMethodID());
		$payment_name = !$payment_method?"-":$payment_method["Name"];
		$calculate_tax = !(isset($payment_method["calculate_tax"]) && (int)$payment_method["calculate_tax"]==0);

		foreach( $cartContent["cart_content"] as &$cartItem ){
			
			$cartItem["tax"] = $calculate_tax?taxCalculateTax2( $cartItem["productID"], $addresses[0], $addresses[1] ):0;
			$sumOrderContent[] = $cartItem;
		}

		$orderDetails = array (
				"first_name" => $shippingAddress->first_name,
				"last_name" => $shippingAddress->last_name,
				"email" => "",
				"order_amount" => oaGetOrderAmountExShippingRate( $cartContent, $addresses, $log, FALSE, $this->shippingServiceID() ),
		);
		

		$tax = $calculate_tax?oaGetProductTax( $cartContent, $d, $addresses ):0;
		$subtotal = oaGetOrderAmountExShippingRate( $cartContent, $addresses, $log,$calculate_tax);
		
		//$total			= oaGetOrderAmount( $cartContent, $addresses, $this->shippingMethodID(), $log, $orderDetails, TRUE, $this->shippingServiceID() );
		$shipping_cost  = oaGetShippingCostTakingIntoTax( $cartContent, $this->shippingMethodID(), $addresses, $orderDetails, $calculate_tax, $this->shippingServiceID() );
		$total = $subtotal + (isset($shipping_cost[0]['rate'])?$shipping_cost[0]['rate']:0);
		//var_dump(array($cartContent,$calculate_tax,'tax'=>$tax,'subtotal'=>$subtotal,$shipping_cost,$total));
		
		$shServiceInfo = '';
		if(is_array($shipping_cost)){
			
			$_T = array_shift($shipping_cost);
			$shipping_cost = $_T['rate'];
			$shServiceInfo = $_T['name'];
		}
		$paymentModule = PaymentModule::getInstance(isset($payment_method["module_id"])?$payment_method["module_id"]:null);
		$payment_form_html = is_object($paymentModule)?$paymentModule->payment_form_html((array)$this->billingAddress(),$this->customer()):"";
		return array(
			"sumOrderContent"	=> $sumOrderContent, 
			"discount"			=> $discount,
			"discount_percent"	=> (100*$discount/$pred_total),
			"discount_str"		=> show_price($discount),
			"pred_total_disc"	=> show_price(($pred_total*((100-$d)/100))),
			"pred_total"		=> show_price($pred_total),
			"totalTax"			=> show_price($tax),
			"totalTaxUC"		=> $tax,
			"shippingAddress"	=> $this->shippingAddress(), 
			"billingAddress"	=> $this->billingAddress(),
			"shipping_name"		=> $shipping_name,
			"shippingServiceInfo" => $shServiceInfo,
			"payment_name"		=> $payment_name,
			"shipping_cost"		=> show_price($shipping_cost),
			"shipping_costUC"	=> $shipping_cost,
			"payment_form_html"	=> $payment_form_html,
			"total"				=> show_price($total),
			"totalUC"			=> $total,
			"payment_email_comments_text"		=> $payment_email_comments_text, 
			"shipping_email_comments_text"		=> $shipping_email_comments_text,
			"orderContentCartProductsCount"	=> count($sumOrderContent));			
	}
	
	function emulate_ordOrderProcessing(){
		
		$customerEntry = &$this->customer();
		$order_time = Time::dateTime();
		$customer_ip = stGetCustomerIP_Address();
		$statusID = CONF_ORDSTATUS_PENDING;
		$checkoutforms_data = $this->formsData();
		
		if(!$customerEntry->customerID){
			
			if(!isset($checkoutforms_data['permanent_registering']) || !$checkoutforms_data['permanent_registering']){
				
				$customerEntry->Login = null;
				$customerEntry->cust_password = null;
			}
			if(isset($_SESSION['refid']) && $_SESSION['refid'])$customerEntry->affiliateID = $_SESSION['refid'];
			$res = $customerEntry->save();
			$customerEntry->saveCustomFields();
		}

		$currencyEntry = Currency::getSelectedCurrencyInstance();
		$currencyID = $currencyEntry->CID;
		if ( $currencyID != 0 ){
			$currentCurrency = currGetCurrencyByID( $currencyID );
			$currency_code	 = $currentCurrency["currency_iso_3"];
			$currency_value	 = $currentCurrency["currency_value"];
		}else{
			$currency_code	= "";
			$currency_value = 1;
		}

		$cartEntry = new ShoppingCart();
		$cartEntry->loadCurrentCart();
		
		$cartContent = $cartEntry->emulate_cartGetCartContent();
		$shippingAddress = &$this->shippingAddress();
		if(!$shippingAddress->customerID){
			
			$shippingAddress->customerID = $customerEntry->customerID;
			$shippingAddress->save();
		}
		
		$billingAddress = &$this->billingAddress();
		if(!$billingAddress->customerID && (!isset($checkoutforms_data['billing_as_shipping']) || !$checkoutforms_data['billing_as_shipping'])){
			
			$billingAddress->customerID = $customerEntry->customerID;
			$billingAddress->save();
		}
		
		$addresses = array($shippingAddress->getVars(), $billingAddress->getVars());

		$orderDetails = array (
			"first_name" => $shippingAddress->first_name,
			"last_name" => $shippingAddress->last_name,
			"email" => $customerEntry->Email,
			"order_amount" => oaGetOrderAmountExShippingRate( $cartContent, $addresses, $customerEntry->Login, FALSE )
		);

		$shippingMethod = shGetShippingMethodById( $this->shippingMethodID() );
		$shipping_email_comments_text = $shippingMethod["email_comments_text"];
		$shippingName = $shippingMethod["Name"];
	
		$paymentMethod = payGetPaymentMethodById( $this->paymentMethodID() );
		$paymentName = $paymentMethod["Name"];
		$payment_email_comments_text = $paymentMethod["email_comments_text"];
		
		$calculate_tax = !(isset($paymentMethod["calculate_tax"]) && (int)$paymentMethod["calculate_tax"]==0);
		
		$order_sub_amount = oaGetOrderAmountExShippingRate($cartContent, $addresses, $customerEntry->Login,$calculate_tax);
//		$order_amount = oaGetOrderAmount( $cartContent, $addresses, $this->shippingMethodID(), $customerEntry->Login, $orderDetails, TRUE, $this->shippingServiceID() );
		$d = oaGetDiscountValue( $cartContent, $customerEntry->Login );
		$tax = $calculate_tax?oaGetProductTax( $cartContent, $d, $addresses ):0;
		$shipping_costUC = oaGetShippingCostTakingIntoTax( $cartContent, $this->shippingMethodID(), $addresses, $orderDetails, $calculate_tax, $this->shippingServiceID(), $calculate_tax );
		$order_amount = $order_sub_amount + (isset($shipping_costUC[0]['rate'])?$shipping_costUC[0]['rate']:0);

		$discount = oaGetDiscountValue( $cartContent, $customerEntry->Login);
		$discount_array = _dscGetDiscountsArray(oaGetClearPrice($cartContent), $customerEntry->Login);
		$discount_descr = array();
		ClassManager::includeClass('discount_coupon');
	    $curr_coupon = discount_coupon::getCurrentCoupon();
	    if($curr_coupon !== 0)
	    {
	        $discount_descr[] = translate('lbl_order_dsc_by_coupon').': #'.$curr_coupon.', '.$currencyEntry->getUnitsView($discount_array['coupon']);
	    };
		if(CONF_DSC_CALC == 'as_sum') // by sum
		{
		    foreach(array('usergroup', 'amount', 'orders') as $dsc_by)
		    {
		        if(isset($discount_array[$dsc_by])&&$discount_array[$dsc_by] > 0)
		        {
		            $discount_descr[] = translate('lbl_order_dsc_by_'.$dsc_by).': '.$currencyEntry->getUnitsView($discount_array[$dsc_by]);
		        };
		    };
		}
		else // by max
		{
		    //$dsc_by = array_search($discount, $discount_array);
		    $_tmp = $discount_array;
		    unset($_tmp['coupon']);
		    $dsc_by = array_search(max($_tmp),$discount_array);
		    if(isset($discount_array[$dsc_by])&&$discount_array[$dsc_by] > 0)
	        	$discount_descr[] = translate('lbl_order_dsc_by_'.$dsc_by).': '.$currencyEntry->getUnitsView($discount_array[$dsc_by]);
		};
		
		$discount_descr = implode("\n", $discount_descr);
		
		$shServiceInfo = '';
		if(is_array($shipping_costUC)){
			
			list($shipping_costUC) = $shipping_costUC;
			$shServiceInfo = $shipping_costUC['name'];
			$shipping_costUC = $shipping_costUC['rate'];
		}

		$currentPaymentModule = isset($paymentMethod['module_id']) && $paymentMethod['module_id']?PaymentModule::getInstance( $paymentMethod["module_id"]):null;
		/*@var $currentPaymentModule PaymentModule*/
		$temp_orderID = null;

		if(!is_null($currentPaymentModule)){
			
			//define order details for payment module
			$order_payment_details = array(
				'customer_email' => $customerEntry->Email,
				'customer_ip' => $customer_ip,
				'order_amount' => $order_amount,
				'currency_code' => $currency_code,
				'currency_value' => $currency_value,
				'shipping_cost' => $shipping_costUC,
				'order_tax' => $tax,
				'shipping_info' => $shippingAddress->getVars(),
				'billing_info' => $billingAddress->getVars(),
				'discount' => $discount,
				'statusID' => $statusID,
				'customer_firstname' => $customerEntry->first_name,
				'customer_lastname' => $customerEntry->last_name,
				'customers_comment' => $this->customers_comment(),
				'customerID' => $customerEntry->customerID,
				'shipping_type' => $shippingName,
				'payment_type' => $paymentName,
				'shippingServiceInfo' => $shServiceInfo,
				'affiliateID' => $customerEntry->affiliateID,
			);
			//HACK for CPayPalDirect, PPExpressCheckout
			if(in_array(get_class($currentPaymentModule),array('CPayPalDirect','PPExpressCheckout'))){
				$dbq = "INSERT `?#ORDERS_TABLE` (`order_time`,`statusID`) VALUES(?,?)";
				db_phquery($dbq,$order_time,-1);
				$temp_orderID = db_insert_id( ORDERS_TABLE );
				$order_payment_details['orderID'] = $temp_orderID;
			}
	
			$process_payment_result = $currentPaymentModule->payment_process( $order_payment_details ); //gets payment processing result
			$statusID = $order_payment_details['statusID'];
	
			if($process_payment_result !== 1){ //error on payment processing
	
				if (isset($_POST)){
					
					$_SESSION["order4confirmation_post"] = $_POST;
				}
				xSaveData('PaymentError', $process_payment_result);
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', $process_payment_result);
			}
		}

		// prepare states names
		if($shippingAddress->zoneID != null)
		{
		    $zone_info = znGetSingleZoneById($shippingAddress->zoneID);
		    $shippingAddress->state = $zone_info['zone_name'];
		};
		if($billingAddress->zoneID != null)
		{
		    $zone_info = znGetSingleZoneById($billingAddress->zoneID);
		    $billingAddress->state = $zone_info['zone_name'];
		};
		
		
		$dbq = "
			INSERT ?#ORDERS_TABLE (customerID, order_time, customer_ip, shipping_type, payment_type, customers_comment, statusID,
				shipping_cost, order_discount, order_amount, currency_code, currency_value, customer_firstname, customer_lastname, 
				customer_email, shipping_firstname, shipping_lastname, shipping_country, shipping_state, shipping_zip, shipping_city,
				shipping_address, billing_firstname, billing_lastname, billing_country, billing_state, billing_zip, billing_city,
				billing_address, cc_number, cc_holdername, cc_expires, cc_cvv, shippingServiceInfo".
				",affiliateID".
				", payment_module_id, shipping_module_id, source, discount_description)
			VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?".
				",?".
				",?,?,?,?)
		";
		db_phquery($dbq, $customerEntry->customerID, $order_time, $customer_ip, $shippingName, $paymentName, $this->customers_comment(),
			$statusID, $shipping_costUC, $discount, $order_amount, $currency_code, $currency_value, $customerEntry->first_name,
			$customerEntry->last_name, $customerEntry->Email, $shippingAddress->first_name, $shippingAddress->last_name, 
			$shippingAddress->getCountryName(), $shippingAddress->state, $shippingAddress->zip, $shippingAddress->city,
			$shippingAddress->address, $billingAddress->first_name, $billingAddress->last_name, $billingAddress->getCountryName(),
			$billingAddress->state, $billingAddress->zip, $billingAddress->city, $billingAddress->address,
			$cc_number, $cc_holdername, $cc_expires, $cc_cvv, $shServiceInfo
			,$customerEntry->affiliateID
			,isset($paymentMethod['module_id'])?$paymentMethod['module_id']:0 ,isset($shippingMethod['module_id'])?$shippingMethod['module_id']:0, $this->widgets?'widgets':'storefront'
			,$discount_descr
		);
		
		$orderID = db_insert_id( ORDERS_TABLE );
		if($temp_orderID){
			
			db_phquery('DELETE FROM `?#ORDERS_TABLE` WHERE `orderID`=?',$temp_orderID);
			db_phquery('UPDATE `?#ORDERS_TABLE` SET `orderID`=? WHERE `orderID`=?',$temp_orderID,$orderID);
			$orderID = $temp_orderID;
			$autoincrement = db_phquery_fetch(DBRFETCH_FIRST,'SELECT MAX(`OrderID`)+1 FROM `?#ORDERS_TABLE`');
			db_phquery('ALTER TABLE  `?#ORDERS_TABLE` AUTO_INCREMENT = ?',$autoincrement);
			//TODO remove all old temporal order records
		}
		
			if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
			include_once(WBS_DIR.'/kernel/classes/class.metric.php');
			
			$DB_KEY=strtoupper(SystemSettings::get('DB_KEY'));
			$U_ID = sc_getSessionData('U_ID');
			
			$metric_data = array(
			/*	$currency_code,
				$order_amount,
				$paymentName,
				$shippingName,
				$billingAddress->getCountryName(),
				$billingAddress->state,
				$billingAddress->city,*/				
			);
			
			$metric = metric::getInstance();
			$metric->addAction($DB_KEY, $U_ID,'SC', 'ORDER', isset($_GET['widgets'])?'WIDGET':'STOREFRONT', implode(':',$metric_data));
		}
	
		stChangeOrderStatus($orderID, $statusID, translate('ordr_comment_orderplaced'));
		if(function_exists('sc_registerOrder2MT')){
			$res = sc_registerOrder2MT($orderID);
		}
		$cartEntry->saveToOrderedCarts($orderID, $shippingAddress->getVars(), $billingAddress->getVars(),$calculate_tax);

		global $smarty_mail;
		_sendOrderNotifycationToAdmin( $orderID, $smarty_mail, $tax );
		_sendOrderNotifycationToCustomer( $orderID, $smarty_mail, $customerEntry->Email, $customerEntry->Login, $payment_email_comments_text, $shipping_email_comments_text, $tax );

		$OrderingModule = ModulesFabric::getModuleObjByKey('Ordering');
		$OrderingModule->getInterface('successful_ordering', $orderID);
		if ($currentPaymentModule != null){
			$currentPaymentModule->after_processing_php( $orderID );
		}

		unset($_SESSION["order4confirmation_post"]);

		discount_coupon::postPlaceOrder($orderID);
		
		return $orderID;
	}
}
?>