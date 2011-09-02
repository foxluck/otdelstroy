<?php

	$_GET['ukey'] = 'googlecheckout_handler';
	include 'index.php';
	include(DIR_FUNC.'/search_function.php');
	include(DIR_FUNC.'/discount_functions.php'); 
	include(DIR_FUNC.'/custgroup_functions.php'); 
	include(DIR_FUNC.'/shipping_functions.php');
	include(DIR_FUNC.'/payment_functions.php');
	include(DIR_FUNC.'/tax_function.php'); 
	include(DIR_FUNC.'/currency_functions.php');
	include(DIR_FUNC.'/module_function.php');
	include(DIR_FUNC.'/quick_order_function.php'); 
	include(DIR_FUNC.'/setting_functions.php');
	include(DIR_FUNC.'/subscribers_functions.php');
	include(DIR_FUNC.'/discussion_functions.php');
	include(DIR_FUNC.'/order_amount_functions.php'); 
	include(DIR_FUNC.'/linkexchange_functions.php'); 
	include_once(DIR_FUNC.'/affiliate_functions.php');
//	include_once(DIR_CLASSES.'/class.virtual.paymentmodule.php');
	include_once(DIR_CLASSES.'/class.virtual.shippingratecalculator.php');
	include_once(DIR_ROOT.'smarty/smarty.class.php'); 

	session_start();

	//connect to the database
	db_connect(SystemSettings::get('DB_HOST'),SystemSettings::get('DB_USER'),SystemSettings::get('DB_PASS')) or die (db_error());
	db_select_db(SystemSettings::get('DB_NAME')) or die (db_error());
	
	settingDefineConstants();
	
	if(isset($_GET['success_order'])){
		
		cartClearCartContet();
		Redirect(getTransactionResultURL('success'));
		die;
	}
	
	//include current language file
	$_SESSION['current_language'] = 0;
	if (file_exists('./languages/'.$lang_list[$_SESSION['current_language']]->filename)) {
		
		include_once('languages/'.$lang_list[$_SESSION['current_language']]->filename);
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
	
	if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
		
		if($_SERVER['PHP_AUTH_USER']==$merchant_id&& $_SERVER['PHP_AUTH_PW']==$merchant_key){
			
			$AuthOK = true;
		}
	}
	
	if(!$AuthOK)die;
	
	$Request = new xmlNodeX();
	$Request->renderTreeFromInner($reqXML);

	switch ($Request->getName()){
		case 'new-order-notification':
			$GooglePaymentModule->hndl_NewOrderNotification($Request);
			break;
		case 'merchant-calculation-callback':
			$GooglePaymentModule->hndl_MerchantCalculationCallback($Request);
			break;
		case 'order-state-change-notification':
			$GooglePaymentModule->hndl_OrderStateChangeNotification($Request);
			break;
	}

?>