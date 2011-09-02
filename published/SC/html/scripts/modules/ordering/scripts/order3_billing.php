<?php
if(!cartCheckMinTotalOrderAmount())RedirectSQ('?ukey=cart&min_order=error');

if ( !regAddressBelongToCustomer(regGetIdByLogin($_SESSION['log']), $_GET['shippingAddressID']) ||
	!regAddressBelongToCustomer(regGetIdByLogin($_SESSION['log']), $_GET['billingAddressID']) ||
	!shShippingMethodIsExist($_GET['shippingMethodID']) ){
	RedirectSQ( '?ukey=page_not_found');
}

if ( !cartCheckMinOrderAmount() )RedirectSQ( '?ukey=cart' );

if ( isset($_POST['continue_button']) ){
	RedirectProtectedSQ('ukey=order4_confirmation&paymentMethodID='.$_POST['select_payment_method'].(isset($_GET['shServiceID'])?'&shServiceID='.$_GET['shServiceID']:'') );
}

if ( isset($_GET['selectedNewAddressID']) ){
	RedirectSQ('selectedNewAddressID=&billingAddressID='.$_GET['selectedNewAddressID']);
}

$moduleFiles = GetFilesInDirectory( './modules/payment', 'php' );
foreach( $moduleFiles as $fileName )
	include_once( $fileName );

$payment_methods = payGetAllPaymentMethods(true);
$payment_methodsToShow = array();
foreach( $payment_methods as $payment_method )
{
	if ($_GET['shippingMethodID'] == 0) //no shipping methods available => show all available payment types
	{
		$shippingMethodsToAllow = true;
	}
	else // list of payment options depends on selected shipping method
	{
		$shippingMethodsToAllow = false;
		foreach( $payment_method['ShippingMethodsToAllow'] as $ShippingMethod )
			if ( ((int)$_GET['shippingMethodID'] == (int)$ShippingMethod['SID']) &&
							 $ShippingMethod['allow'] )
			{
				$shippingMethodsToAllow = true;
				break;
			}
	}

	if ( $shippingMethodsToAllow )
		$payment_methodsToShow[] = $payment_method;
}

if ( count($payment_methodsToShow) == 0 )
	RedirectProtectedSQ( 'ukey=order4_confirmation&billingAddressID='.regGetDefaultAddressIDByLogin($_SESSION['log']).'&paymentMethodID=0' );

$smarty->assign( 'shippingAddressID',	$_GET['shippingAddressID'] );
$smarty->assign( 'billingAddressID',	$_GET['billingAddressID'] );
$smarty->assign( 'shippingMethodID',	$_GET['shippingMethodID'] );
$smarty->assign( 'strAddress', regGetAddressStr($_GET['billingAddressID']) );
$smarty->assign( 'payment_methods', $payment_methodsToShow );
$smarty->assign( 'main_content_template', 'order3_billing.tpl.html' );
?>