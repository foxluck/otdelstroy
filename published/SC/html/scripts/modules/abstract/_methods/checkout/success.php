<?php
class SuccessController extends ActionsController {

	function main(){

		//$cartEntry = new ShoppingCart();
		//$cartEntry->cleanCurrentCart();

		if(!(isset($_GET["orderID"]) && isset($_SESSION["newoid"]) && (int)$_SESSION["newoid"] == (int)$_GET["orderID"])) {
			RedirectSQ('?');
		}

		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		$paymentMethod = payGetPaymentMethodById($checkoutEntry->paymentMethodID());
		$currentPaymentModule = PaymentModule::getInstance($paymentMethod['module_id']);
		if(!isset($_SESSION['log']) || !$_SESSION['log']) {
			$mode = defined('CONF_STRICT_ACCESS')?constant('CONF_STRICT_ACCESS'):'lastname';
			if($mode != 'auth') {//auth only|status only|full
				$orderID = intval($_GET['orderID']);
				if($order = ordGetOrder( $orderID )) {
					$storage = Cache::getInstance('order_status',Cache::SESSION);
					$storage->set($orderID,$order["customerID"],1200);
				}
			}
		}

		if ( $currentPaymentModule != null ){
			$after_processing_html = $currentPaymentModule->after_processing_html($_GET['orderID']);
		}else{
			$after_processing_html = '';
		}
		$Register = &Register::getInstance();
		/*@var $Register Register*/

		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/

		$smarty->assign( 'after_processing_html', $after_processing_html );

		$smarty->assign( 'order_success', 1 );

		$smarty->assign('main_content_template', 'checkout.success.html');
	}
}
?>