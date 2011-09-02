<?php
//	include_once(DIR_CLASSES.'/class.virtual.paymentmodule.php');
	/* @var $smarty Smarty */
	$PostVars = $_POST;
	$GetVars = $_GET;

	$processCheckout = (isset($PostVars['googlecheckout2'])||isset($GetVars['googlecheckout2']));
	set_query('googlecheckout2=', '', true);
	
	if(!defined('CONF_GOOGLECHECKOUT2_ENABLED')||!CONF_GOOGLECHECKOUT2_ENABLED)return '';
	
	list($GoogleCheckout2Info) = modGetModuleConfigs('googlecheckout2');
	$GoogleCheckout2 = PaymentModule::getInstance($GoogleCheckout2Info['ConfigID']);
	/* @var $GoogleCheckout2 GoogleCheckout2 */
		
	$error_message = '';
	
	if($processCheckout){
		
		$error_message = $GoogleCheckout2->processCheckout();
	}
	
	$onsubmit = '';
	if(isset($this_is_a_popup_cart_window) && $this_is_a_popup_cart_window){
		
		$onsubmit = 'onsubmit="window.opener.location=\''.CONF_FULL_SHOP_URL.'index.php?googlecheckout2=1&shopping_cart=yes\';window.close();"';
	}
	
	$smarty->assign('GoogleCheckout_CheckoutButton', 
'
<form action="'.xHtmlSetQuery('').'" method="post" '.$onsubmit.'>
<input type="hidden" name="googlecheckout2" value="1" />
'.$GoogleCheckout2->getCheckoutButton().'
'.($error_message?'
<div style="color:red;">'.$error_message.'</div>
':'').'
</form>
');
?>