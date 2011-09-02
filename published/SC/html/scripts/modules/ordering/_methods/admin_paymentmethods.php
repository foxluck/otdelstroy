<?php
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */

	$payment_methods = payGetAllPaymentMethods();
	$smarty->assign_by_ref('payment_methods_num',  count($payment_methods));
	$smarty->assign_by_ref('payment_types',  $payment_methods);
	
	//set sub-department template
	$smarty->assign('admin_sub_dpt', 'conf_payment2.tpl.html');
?>