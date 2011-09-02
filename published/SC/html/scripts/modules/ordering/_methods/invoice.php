<?php
$Register = &Register::getInstance();
/*@var $Register Register*/
$smarty = &$Register->get(VAR_SMARTY);
/*@var $smarty Smarty*/
$currentDivision = &$Register->get(VAR_CURRENTDIVISION);
/*@var $currentDivision Division*/
$currentDivision->MainTemplate = 'invoice.html';

do{
	
	if (!isset($_GET['orderID'])){
		
		$error = translate("err_forbidden");break;
	}
	
	$orderID = (int) $_GET['orderID'];
	$order = ordGetOrder( $orderID );
	
	$order['discount_value'] = $order['order_discount'];
	
	if (!$order){
		$error = translate("err_cant_find_required_page"); break;
	}

	$customerEntry = Customer::getAuthedInstance();
	if(is_null($customerEntry))$customerEntry = new Customer;
	
	if ( !wbs_auth() && $order['customerID'] != $customerEntry->customerID){
		$error = translate("err_forbidden");break;
	}
	
	if($order['customerID'] != $customerEntry->customerID){
		
		$admin_orders_listDivision = new Division(DivisionModule::getDivisionIDByUnicKey('admin_orders_list'));
		sc_checkLoggedUserAccess2Division($admin_orders_listDivision);
	}
	
	$orderContent = ordGetOrderContent( $orderID );
	ordCalculateOrderTax($order, $orderContent);
		
	$smarty->hassign( 'orderContent', $orderContent );
	$smarty->hassign( 'order', $order );
	
}while(0);
if (isset($error))$smarty->assign('error', $error);


?>