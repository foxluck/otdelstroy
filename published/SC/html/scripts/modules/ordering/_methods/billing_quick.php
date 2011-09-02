<?php
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();

if(!cartCheckMinTotalOrderAmount())RedirectSQ('?ukey=shopping_cart&min_order=error');
if ( CONF_ORDERING_REQUEST_BILLING_ADDRESS == '0' ){
	
	$_SESSION['billing_first_name']	= $_SESSION['receiver_first_name'];
	$_SESSION['billing_last_name']	= $_SESSION['receiver_last_name'];
	$_SESSION['billing_state']		= $_SESSION['receiver_state'];
	$_SESSION['billing_zip']		= $_SESSION['receiver_zip'];
	$_SESSION['billing_city']		= $_SESSION['receiver_city'];
	$_SESSION['billing_address']	= $_SESSION['receiver_address'];
	if ( isset($_SESSION['receiver_countryID']) )$_SESSION['billing_countryID'] = $_SESSION['receiver_countryID'];
	if ( isset($_SESSION['receiver_zoneID']) )$_SESSION['billing_zoneID'] = $_SESSION['receiver_zoneID'];
}

if ( !isset($_GET['shippingMethodID']) )RedirectSQ('?ukey=page_not_found');

$_GET['shippingMethodID'] = (int)$_GET['shippingMethodID'];

if ( !cartCheckMinOrderAmount() )RedirectSQ( '?ukey=shopping_cart' );

$moduleFiles = GetFilesInDirectory( './modules/payment', 'php' );

foreach( $moduleFiles as $fileName )include_once( $fileName );


function _getPaymentMethodsToShow( $payment_methods ){
	$payment_methodsToShow = array();
	foreach( $payment_methods as $payment_method )
	{
		if ($_GET['shippingMethodID'] == 0) //no shipping methods available => show all available payment types
		{
			$shippingMethodsToAllow = true;
		}
		else
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
	return $payment_methodsToShow;
}


if ( isset($_POST['continue_button']) )
	RedirectProtectedSQ('?ukey=order4_confirmation_quick&shippingMethodID='.$_GET['shippingMethodID'].'&paymentMethodID='.$_POST['select_payment_method'].
					(isset($_GET['shServiceID'])?'&shServiceID='.$_GET['shServiceID']:''));

$payment_methods = payGetAllPaymentMethods(true);
$payment_methodsToShow = _getPaymentMethodsToShow( $payment_methods );

if ( count($payment_methodsToShow) == 0 )
	RedirectProtectedSQ( '?ukey=order4_confirmation_quick&shippingMethodID='.$_GET['shippingMethodID'].'&paymentMethodID=0' );

$strAddress = quickOrderGetBillingAddressStr();
$smarty->assign( 'strAddress',	$strAddress );
$smarty->assign( 'payment_methods', $payment_methodsToShow );
$smarty->assign( 'main_content_template', 'order3_billing_quick.tpl.html' );
?>