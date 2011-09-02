<?php 
	$PostVars = &$_POST;
	$GetVars = &$_GET;

	if(!defined('CONF_PPEXPRESSCHECKOUT_ENABLED')||!CONF_PPEXPRESSCHECKOUT_ENABLED)return '';
	if(!isset($GetVars['shopping_cart'])&&!isset($this_is_a_popup_cart_window))return '';
	
	include_once './modules/payment/class.ppexpresscheckout.php';
	
	$PPExpressCheckout = &PPExpressCheckout::getModuleInstance();
	
	$processCheckout = (isset($PostVars['ppexpresscheckout2'])||isset($GetVars['ppexpresscheckout2']));
	set_query('ppexpresscheckout2=', '', true);
	
	$error_message = '';
	
	if($processCheckout){
		
		$error_message = $PPExpressCheckout->doSetExpressCheckoutRequest();
		if(Services_PayPal::isError($error_message)){
			$error_message = $error_message->getMessage();
		}
	}elseif (xDataExists('_PPECHECKOUT_ERROR')){
		$error_message = xPopData('_PPECHECKOUT_ERROR');
	}
	
	$onsubmit = '';
	if(isset($this_is_a_popup_cart_window) && $this_is_a_popup_cart_window){
		
		$onsubmit = 'onsubmit="window.opener.location=\''.CONF_FULL_SHOP_URL.(strpos(CONF_FULL_SHOP_URL, 'index.php')===false?'index.php':'').'?shopping_cart=yes&ppexpresscheckout2=1\';window.close();"';
	}

	$smarty->assign('PPExpressCheckout_button', 
		'<form action="'.xHtmlSetQuery('').'" method="post" '.$onsubmit.'>
		<input type="hidden" name="ppexpresscheckout2" value="1" />
		'.$PPExpressCheckout->getCheckoutButton().'
		'.($error_message?'<div style="color:red;">'.$error_message.'</div>':'').'</form>');
?>