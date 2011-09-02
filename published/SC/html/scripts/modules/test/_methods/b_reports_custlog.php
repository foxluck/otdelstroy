<?php
/* @vars $smarty Smarty */
$smarty = &Core::getSmarty();
set_query('safemode=','',true);

if ( isset($_POST['clear']) ){
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		RedirectSQ( 'safemode=yes' );
	}
	stClearCustomerLogReport();
}

$customer_log_report = stGetCustomerLogReport();

$smarty->assign('customer_log_report', $customer_log_report );

//set sub-department template
$smarty->assign('admin_sub_dpt', 'reports_customer_log.tpl.html');
?>