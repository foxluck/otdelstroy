<?php
	include_once './cfg/connect.inc.php';
	include_once './cfg/language_list.php';
	include_once './core_functions/db_functions.php';
	include_once './core_functions/functions.php';
	include_once './core_functions/setting_functions.php';
	include_once './core_functions/payment_functions.php';

	db_connect(SystemSettings::get('DB_HOST'),SystemSettings::get('DB_USER'),SystemSettings::get('DB_PASS')) or die (db_error());
	db_select_db(SystemSettings::get('DB_NAME')) or die (db_error());
	settingDefineConstants();
	MagicQuotesRuntimeSetting();

	//current language session variable
	if (!isset($_SESSION['current_language']) || $_SESSION['current_language'] < 0 || $_SESSION['current_language'] > count($lang_list)){
			$_SESSION['current_language'] = 0; //set default language
	}
	//include a language file
	if (isset($lang_list[$_SESSION['current_language']]) &&	file_exists('languages/'.$lang_list[$_SESSION['current_language']]->filename)){
		//include current language file
		include('languages/'.$lang_list[$_SESSION['current_language']]->filename);
	}

	$redirect_url = getTransactionResultURL('failure');
	if(isset($_POST['ResponseCode']) && isset($_POST['OrderID'])){
		
		$message = '';
		switch ($_POST['ResponseCode']){
			case 1:
				$message = 'Payment for order #'.$_POST['OrderID'].' has been ACCEPTED.';
				$redirect_url = getTransactionResultURL('success');
				break;
			case 2:
				$message = 'Payment for order #'.$_POST['OrderID'].' has been DECLINED.';
				$redirect_url = getTransactionResultURL('failure');
				break;
			case 3:
				$message = 'Error processing of payment for order #'.$_POST['OrderID'].' ('.(isset($_POST['ReasonCode'])?' ReasonCode: '.$_POST['ReasonCode']:'').(isset($_POST['ReasonCodeDesc'])?' ReasonCodeDesc: '.$_POST['ReasonCodeDesc']:'');
				$redirect_url = getTransactionResultURL('failure');
				break;
		}
		mail(CONF_ORDERS_EMAIL,'JCC transaction for order #'.$_POST['OrderID'], $message,"From: \"".
				CONF_SHOP_NAME."\"<".CONF_GENERAL_EMAIL.">\n".stripslashes(translate("email_message_parameters"))."\nReturn-path: <".CONF_GENERAL_EMAIL.">");
	}
	
	if($redirect_url)
		Redirect($redirect_url);
?>