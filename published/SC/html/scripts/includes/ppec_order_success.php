<?php
	include_once DIR_MODULES.'/payment/class.ppexpresscheckout.php';
	
	$PPExpressCheckout = &PPExpressCheckout::getModuleInstance();
	
	$orderID = xGetData('PPEC_ORDER_ID');

	$order = ordGetOrder( $orderID );
			
	$orderContent = ordGetOrderContent( $orderID );
	ordCalculateOrderTax($order, $orderContent);
	
	$smarty->assign( "orderContent", $orderContent );
	$smarty->assign( "order", $order );
	$smarty->assign( "order_detailed", 1 );
	$smarty->assign('ppec_transaction_id', xGetData('PPEC_TRANSACTION_ID'));

	$smarty->assign('main_content_template', 'ppec_order_success.tpl.html' );
?>