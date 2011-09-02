<?php
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();
global $smarty_mail;
if(!cartCheckMinTotalOrderAmount() && !isset($_GET['order_success']))RedirectSQ('?ukey=cart&min_order=error');

$shServiceID = isset($_GET['shServiceID'])?$_GET['shServiceID']:0;

if ( !isset($_POST['submit']) && !isset($_GET['order_success']) )
{
	if ( !isset($_GET['shippingMethodID']) )
		RedirectSQ( '?ukey=page_not_found' );

	$_GET['shippingMethodID'] = (int)$_GET['shippingMethodID'];

	if ( !isset($_GET['paymentMethodID']) )
		RedirectSQ( '?ukey=page_not_found' );

	$_GET['paymentMethodID'] = (int)$_GET['paymentMethodID'];

	if ( $_GET['shippingMethodID'] != 0 )
		if ( !shShippingMethodIsExist($_GET['shippingMethodID']) )
			RedirectSQ( '?ukey=page_not_found' );

	if ( $_GET['paymentMethodID'] != 0 )
		if ( !payPaymentMethodIsExist($_GET['paymentMethodID']) )
			RedirectSQ( '?ukey=page_not_found' );
}

if ( !cartCheckMinOrderAmount() )
	RedirectSQ( '?ukey=cart' );


$shippingModuleFiles = GetFilesInDirectory( './modules/shipping', 'php' );
foreach( $shippingModuleFiles as $fileName )
	include( $fileName );

$paymentModuleFiles = GetFilesInDirectory( './modules/payment', 'php' );
foreach( $paymentModuleFiles as $fileName )
	include( $fileName );



if ( isset($_POST['submit']) 
		)
{
	$cc_number		= '';
	$cc_holdername	= '';
	$cc_expires		= '';
	$cc_cvv			= '';

	$orderID = ordOrderProcessing(
		$_GET['shippingMethodID'], $_GET['paymentMethodID'],
		0, 0, 
		$shippingModuleFiles, $paymentModuleFiles, $_POST['order_comment'], 
		$cc_number,		$cc_holdername, 
		$cc_expires,	$cc_cvv,
		null, $smarty_mail, $shServiceID );
		
	$_SESSION["newoid"] = $orderID;

	if ( is_bool($orderID) )
		RedirectProtectedSQ( '?ukey=order4_confirmation_quick&'.
					'&shippingMethodID='.$_GET['shippingMethodID'].
					'&paymentMethodID='.$_GET['paymentMethodID'].'&payment_error=1' );
	else
		RedirectProtectedSQ( '?ukey=order4_confirmation_quick&'.
					'order_success=yes&paymentMethodID='.
							$_GET['paymentMethodID'].'&orderID='.$orderID );
}


if ( isset($_GET['order_success']) ){
	
	if (isset($_GET["orderID"]) && isset($_SESSION["newoid"]) && (int)$_SESSION["newoid"] == (int)$_GET["orderID"]){
		
		$paymentMethod = payGetPaymentMethodById( $_GET['paymentMethodID'] );
		$currentPaymentModule = PaymentModule::getInstance($paymentMethod['module_id']);
	
		if ( $currentPaymentModule != null ){
			$after_processing_html = $currentPaymentModule->after_processing_html($_GET['orderID']);
		}else{
			$after_processing_html = '';
		}
		$smarty->assign( 'after_processing_html', $after_processing_html );
	}
	$smarty->assign( 'order_success', 1 );
}
else
{
	if ( isset($_GET['payment_error']) )
	{
		if ($_GET['payment_error'] == 1)
			$smarty->assign( 'payment_error', 1 );
		else
			$smarty->assign( 'payment_error', base64_decode($_GET['payment_error']) );
	}

	$orderSum = getOrderSummarize(
			$_GET['shippingMethodID'], $_GET['paymentMethodID'], 
			0, 0, 
			$shippingModuleFiles, $paymentModuleFiles, $shServiceID );

	$orderSum['sumOrderContent'] = xHtmlSpecialChars($orderSum['sumOrderContent'], null, 'name');
	$smarty->assign( 'orderSum',	$orderSum );
	$smarty->assign( 'totalUC',		$orderSum['totalUC'] );
}

$smarty->assign( 'main_content_template', 'order4_confirmation_quick.tpl.html' );
?>