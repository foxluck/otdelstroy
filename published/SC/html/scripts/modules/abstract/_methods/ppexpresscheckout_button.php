<?php
$Register = &Register::getInstance();
$smarty = &$Register->get(VAR_SMARTY);
/* @var $smarty Smarty */
$PostVars = &$Register->get(VAR_POST);
$GetVars = &$Register->get(VAR_GET);
$CurrDivision = &$Register->get(VAR_CURRENTDIVISION);

if(!defined('CONF_PPEXPRESSCHECKOUT_ENABLED')||!CONF_PPEXPRESSCHECKOUT_ENABLED)return '';

include_once DIR_MODULES.'/payment/class.ppexpresscheckout.php';

list($$PPExpressCheckoutInfo) = modGetModuleConfigs('ppexpresscheckout');
if(!$PPExpressCheckoutInfo){//Add module to module list
	$sql = 'INSERT INTO ?#MODULES_TABLE (`module_type`, `module_name`, `ModuleClassName`) VALUES (2, \'PayPal Website Payments Pro - Express Checkout\', \'ppexpresscheckout\')';
	db_phquery($sql);
	list($PPExpressCheckoutInfo) = modGetModuleConfigs('ppexpresscheckout');
}
if($PPExpressCheckoutInfo){
	$PPExpressCheckout = PaymentModule::getInstance($PPExpressCheckoutInfo['ConfigID']);
}

if($PPExpressCheckout instanceof PPExpressCheckout){

	$processCheckout = isset($GetVars['ppexpresscheckout2']);
	renderURL('ppexpresscheckout2=', '', true);

	$error_message = '';

	if($processCheckout){

		$error_message = $PPExpressCheckout->doSetExpressCheckoutRequest();
		if(Services_PayPal::isError($error_message)){
			$error_message = $error_message->getMessage();
		}
	}elseif (xDataExists('_PPECHECKOUT_ERROR')){
		$error_message = xPopData('_PPECHECKOUT_ERROR');
	}

	if($error_message){
		Message::raiseMessageRedirectSQ(MSG_ERROR, '', $error_message);
	}

	$smarty->assign('PPExpressCheckout_button', $PPExpressCheckout->getCheckoutButton());
}else{
	$smarty->assign('PPExpressCheckout_button','<font color="red">Error init PPExpressCheckout</font>');
}
?>