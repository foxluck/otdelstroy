<?php
$Register = &Register::getInstance();
$smarty = &$Register->get(VAR_SMARTY);
/* @var $smarty Smarty */
$PostVars = &$Register->get(VAR_POST);
$GetVars = &$Register->get(VAR_GET);
$CurrDivision = &$Register->get(VAR_CURRENTDIVISION);
$Register = &Register::getInstance();

if(!defined('CONF_VKONTAKTE_ENABLED')||!CONF_VKONTAKTE_ENABLED||true) return '';
if(($Register->get('store_mode')!=='vkontakte')&&(!defined('CONF_PAYMENTMODULE_VKONTAKTE_PAY')||!constant('CONF_PAYMENTMODULE_VKONTAKTE_PAY')))return '';

include_once DIR_MODULES.'/payment/class.vkontaktepayment.php';

list($VKontakteInfo) = modGetModuleConfigs('vkontaktepayment');
if(!$VKontakteInfo){//Add module to module list
	$sql = 'INSERT INTO ?#MODULES_TABLE (`module_type`, `module_name`, `ModuleClassName`) VALUES (2, \'VKontakte payment\', \'vkontaktepayment\')';
	db_phquery($sql);
	list($VKontakteInfo) = modGetModuleConfigs('vkontaktepayment');
}
if($VKontakteInfo){
	$VKontakte = PaymentModule::getInstance($VKontakteInfo['ConfigID']);
}

if($VKontakte instanceof VKontaktePayment){

	$processCheckout = isset($GetVars['vkontaktecheckout']);
	renderURL('vkontaktecheckout=', '', true);

	if(!$processCheckout && xDataExists('_VKCHECKOUT_ERROR')){
		Message::raiseMessageRedirectSQ(MSG_ERROR, '',xPopData('_VKCHECKOUT_ERROR'));
	}
	$smarty->assign('VKontakteCheckout_button', $VKontakte->getCheckoutButton());
}else{
	$smarty->assign('VKontakteCheckout_button','<font color="red">Error init VKontakte</font>');
}
?>