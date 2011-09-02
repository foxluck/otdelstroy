<?php
	$Register = &Register::getInstance();
	$PostVars = &$Register->get(VAR_POST);
	$GetVars = &$Register->get(VAR_GET);
	
	if(isset($GetVars['success_order'])){
		
		cartClearCartContet();
		Redirect(getTransactionResultURL('success'));
	}
	
	list($GoogleCheckout2Info) = modGetModuleConfigs('googlecheckout2');
	$GooglePaymentModule = PaymentModule::getInstance($GoogleCheckout2Info['ConfigID']);
	/* @var $GooglePaymentModule GoogleCheckout2 */
	
	$merchant_id = $GooglePaymentModule->_getSettingValue('CONF_GOOGLECHECKOUT2_MERCHANTID');
	$merchant_key = $GooglePaymentModule->_getSettingValue('CONF_GOOGLECHECKOUT2_MERCHANTKEY');
	$sandbox = $GooglePaymentModule->_getSettingValue('CONF_GOOGLECHECKOUT2_SANDBOX');
	$currency = currGetCurrencyByID($GooglePaymentModule->_getSettingValue('CONF_GOOGLECHECKOUT2_TRANSCURR'));
	
	$reqXML = $GLOBALS['HTTP_RAW_POST_DATA'];
	$AuthOK = false;
	
	if(!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])){
		/*
		 * #Add support on FastCGI mode
		 * RewriteEngine On
		 * RewriteCond %{HTTP:Authorization} !^$
		 * RewriteCond %{REQUEST_URI} !(http_auth)
		 * RewriteRule ^googlecheckout_handler/$ googlecheckout_handler/?http_auth=%{HTTP:Authorization}
		 * 
		 * RewriteCond %{HTTP:Authorization} !^$
		 * RewriteCond %{REQUEST_URI} !(http_auth)
		 * RewriteRule ^(.*)$ $1?http_auth=%{HTTP:Authorization} [QSA]
		 */
		if(isset($_GET['http_auth'])){
			$d = base64_decode(substr($_GET['http_auth'],6) );
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $d,2);
			//log_error(E_ERROR,'GoogleCheckout authorization via CGI '.var_export(array($_SERVER['PHP_AUTH_USER']=>$_SERVER['PHP_AUTH_PW']),true),__FILE__,__LINE__);
		}else{
			log_error(E_ERROR,'GoogleCheckout authorization error (CGI mode) '.var_export($_REQUEST,true),__FILE__,__LINE__);
		}
	}
	
	
	if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
		//DEBUG
		//log_error(E_ERROR,var_export(array($_SERVER['PHP_AUTH_USER']=>$_SERVER['PHP_AUTH_PW']),true),__FILE__,__LINE__);
		if(($_SERVER['PHP_AUTH_USER']==$merchant_id)&& ($_SERVER['PHP_AUTH_PW']==$merchant_key)){
			
			$AuthOK = true;
		}else{
			log_error(E_ERROR,'GoogleCheckout authorization error (Invalid merchant_id and/or merchant_key) '.var_export(array($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']),true),__FILE__,__LINE__);
		}
	}
	
	if($AuthOK /*||true*/) {  //UNCOMMENT condition to disable authorization check
		if(!$AuthOK){
			log_error(E_ERROR,'GoogleCheckout authorization error [1] (Checking auth disabled)',__FILE__,__LINE__);
		}
		
		$Request = new xmlNodeX();
		$Request->renderTreeFromInner($reqXML);
		$RequestName = $Request->getName();
		//DEBUG
		//log_error(E_ERROR,'GoogleCheckout request '. $RequestName,__FILE__,__LINE__);
		switch ($RequestName){
			case 'new-order-notification':
				$GooglePaymentModule->hndl_NewOrderNotification($Request);
				break;
			case 'merchant-calculation-callback':
				$GooglePaymentModule->hndl_MerchantCalculationCallback($Request);
				break;
			case 'order-state-change-notification':
				$GooglePaymentModule->hndl_OrderStateChangeNotification($Request);
				break;
			default :
				log_error(E_ERROR,'GoogleCheckout unknown action: '.$Request->Name,__FILE__,__LINE__);
				break;
		}
	}else{
		log_error(E_ERROR,'GoogleCheckout authorization error [2]',__FILE__,__LINE__);
	}
	exit;
?>