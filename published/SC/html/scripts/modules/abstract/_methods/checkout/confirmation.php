<?php
class ConfirmationController extends ActionsController {

	function process_order(){
		
		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$checkoutEntry->widgets = $Register->get('widgets');
		$checkoutEntry->customers_comment($this->getData('order_comment'));
		$orderID = $checkoutEntry->emulate_ordOrderProcessing();
		$cartEntry = new ShoppingCart();
		$cartEntry->cleanCurrentCart();
		$_SESSION["newoid"] = $orderID;

		RedirectSQ('step=success&orderID='.$orderID);
	}
	
	function main(){

		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);

		$orderSum = $checkoutEntry->emulate_getOrderSummarize();
	
		$smarty->assign( 'orderSum', $orderSum );
		$smarty->assign( 'totalUC',	$orderSum['totalUC'] );
		
		$smarty->assign('main_content_template', 'checkout.confirmation.html');
	}
}
?>