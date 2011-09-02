<?php
//payment types list

set_query('safe_mode=&save_successful=','',true);

$shipping_methods = shGetAllShippingMethods();

$moduleFiles = GetFilesInDirectory( './modules/payment', 'php' );

foreach( $moduleFiles as $fileName )
	include( $fileName );


if (isset($_GET['save_successful'])) //show successful save confirmation message
	$smarty->assign('configuration_saved', 1);

if (isset($_GET['delete'])) //delete payment type
{
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		RedirectSQ( 'delete=&safemode=yes' );
	}
	payDeletePaymentMethod( $_GET['delete'] );
	RedirectSQ( 'delete=' );
}

if (isset($_POST['save_payment'])) //save payment and payment types
{
	safeMode(true);
	
	$values = scanArrayKeysForID($_POST, array( 'Enabled', 'Name_\w{2}', 'description_\w{2}', 'email_comments_text_\w{2}', 'module', 'sort_order', 'calculate_tax' ) );

	foreach( $values as $PID => $value )
	{
		payUpdatePaymentMethod( $PID, $value, $value, isset($value['Enabled']), $value['sort_order'], $value['module'], $value, isset($value['calculate_tax']) );

		payResetPaymentShippingMethods( $PID );
		foreach( $shipping_methods as $shipping_method )
		{
			if ( isset($_POST['ShippingMethodsToAllow_'.$PID.'_'.$shipping_method['SID']]) )
				paySetPaymentShippingMethod( $PID, $shipping_method['SID'] );
		}
	}


	if ( !LanguagesManager::ml_isEmpty('Name', $_POST) ){
		
	 	$PID = payAddPaymentMethod( $_POST, $_POST, isset($_POST['Enabled']), $_POST['sort_order'], $_POST, $_POST['module'], isset($_POST['calculate_tax']) );
	 	
		foreach( $shipping_methods as $shipping_method )
		{
			if ( isset($_POST['ShippingMethodsToAllow_'.$shipping_method['SID']]) )
				paySetPaymentShippingMethod( $PID, $shipping_method['SID'] );
		}
	}

	Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', translate("msg_update_successful"));
}

$smarty->assign('payment_types', payGetAllPaymentMethods() );
$smarty->assign('payment_modules', modGetAllInstalledModuleObjs(PAYMENT_MODULE) );
$smarty->assign('shipping_methods', $shipping_methods );


//set sub-department template
$smarty->assign('admin_sub_dpt', 'conf_payment.tpl.html');
?>