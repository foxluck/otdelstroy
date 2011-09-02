<?php
define('DIR_ROOT', str_replace("\\","/",realpath(dirname(__FILE__).'/../')));
$DebugMode = false;
$Warnings = array();
// -------------------------INITIALIZATION-----------------------------//
include_once(DIR_ROOT.'/includes/init.php');
include_once(DIR_CFG.'/connect.inc.wa.php');

$DB_tree = new DataBase();
$DB_tree->connect(SystemSettings::get('DB_HOST'), SystemSettings::get('DB_USER'), SystemSettings::get('DB_PASS'));
$DB_tree->selectDB(SystemSettings::get('DB_NAME'));
define('VAR_DBHANDLER','DBHandler');
$Register = &Register::getInstance();
$Register->set(VAR_DBHANDLER, $DB_tree);

include(DIR_FUNC.'/setting_functions.php' );
settingDefineConstants();




/*require_once(DIR_CFG.'/language_list.php');*/
require_once(DIR_FUNC.'/category_functions.php');
require_once(DIR_FUNC.'/product_functions.php');
require_once(DIR_FUNC.'/statistic_functions.php');/*
require_once(DIR_FUNC.'/country_functions.php' );
require_once(DIR_FUNC.'/zone_functions.php' );*/
require_once(DIR_FUNC.'/datetime_functions.php' );/*
require_once(DIR_FUNC.'/picture_functions.php' );
require_once(DIR_FUNC.'/configurator_functions.php' );
require_once(DIR_FUNC.'/option_functions.php' );
require_once(DIR_FUNC.'/discount_functions.php' );
require_once(DIR_FUNC.'/custgroup_functions.php' );
require_once(DIR_FUNC.'/currency_functions.php' );*/
require_once(DIR_FUNC.'/module_function.php' );
require_once(DIR_FUNC.'/registration_functions.php' );/*
require_once(DIR_FUNC.'/order_amount_functions.php' );
require_once(DIR_FUNC.'/catalog_import_functions.php');*/
require_once(DIR_FUNC.'/cart_functions.php');/*
require_once(DIR_FUNC.'/subscribers_functions.php' );
require_once(DIR_FUNC.'/discussion_functions.php' );*/
require_once(DIR_FUNC.'/order_status_functions.php' );
require_once(DIR_FUNC.'/order_functions.php' );

 require_once(DIR_FUNC.'/shipping_functions.php' );
 require_once(DIR_FUNC.'/payment_functions.php' );
 require_once(DIR_FUNC.'/reg_fields_functions.php' );
 require_once(DIR_FUNC.'/tax_function.php' );
/* require_once(DIR_CLASSES.'/class.virtual.shippingratecalculator.php');
require_once(DIR_CLASSES.'/class.virtual.paymentmodule.php');
 require_once(DIR_FUNC.'/search_function.php' );
 */

$LanguageEntry = &LanguagesManager::getCurrentLanguage();
$locals = $LanguageEntry->getLocals(array(LOCALTYPE_FRONTEND, LOCALTYPE_GENERAL, LOCALTYPE_HIDDEN), false, false);
$Register->set('CURRLANG_LOCALS', $locals);
$Register->set('CURR_LANGUAGE', $LanguageEntry);

foreach($_GET as $key=>$value){//chronopay bugfix :)
	if(strpos($key,'amp;')===0){
		$key = substr($key,4);
		if(!isset($_GET[$key])){
			$_GET[$key] = $value;
		}
	}
}
settingDefineMLConstants();

$Module = PaymentModule::getInstance(isset($_GET['modConfID'])?$_GET['modConfID']:0);
if(!$Module){
	$Module = new PaymentModule();
}
/* @var $Module PaymentModule */
$Module->transactionResultHandler($_GET['transaction_result'],'','handler');
