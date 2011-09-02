<?php
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();

if(!cartCheckMinTotalOrderAmount())RedirectSQ('?ukey=cart&min_order=error');
if(!cartCheckMinOrderAmount())RedirectSQ('ukey=cart');

$moduleFiles = GetFilesInDirectory( './modules/shipping', 'php' );
foreach( $moduleFiles as $fileName )
	include_once( $fileName );

function _getOrder(){
	
	if (!isset($_SESSION['first_name']) || !isset($_SESSION['last_name']) || !isset($_SESSION['email']))return NULL;

	$order['first_name']	= $_SESSION['first_name'];
	$order['last_name'] = $_SESSION['last_name'];
	$order['email'] = $_SESSION['email'];

	$res = cartGetCartContent();
	$order['orderContent'] = $res['cart_content'];
	$d = oaGetDiscountValue( $res, '' );
	$order['order_amount'] = $res['total_price'] - $d;
	return $order;
}

function _getShippingCosts( $shipping_methods, $order, $moduleFiles )
{
	if (!isset($_SESSION['receiver_countryID']) || !isset($_SESSION['receiver_zoneID']) || !isset($_SESSION['receiver_zip']))
		return NULL;

	$shipping_modules	= modGetModules( $moduleFiles );
	$shippingAddressID = 0;
	$shipping_costs = array();

	$res			= cartGetCartContent();

	$sh_address = array(
		'countryID' => $_SESSION['receiver_countryID'],
		'zoneID' => $_SESSION['receiver_zoneID'],
		'zip' => $_SESSION['receiver_zip']
	);
	$addresses = array( $sh_address, $sh_address );

	$j = 0;
	foreach( $shipping_methods as $shipping_method )
	{
		$_ShippingModule = ShippingRateCalculator::getInstance($shipping_method['module_id']);
		if($_ShippingModule){
			
			if ( $_ShippingModule->allow_shipping_to_address( quickGetShippingAddress() ) )
			{
				$shipping_costs[$j] = oaGetShippingCostTakingIntoTax( $res, $shipping_method['SID'], $addresses, $order );
			}
			else
			{

				$shipping_costs[$j] = array(array('rate'=>-1));
			}
		}else //rate = freight charge
		{
			$shipping_costs[$j] = oaGetShippingCostTakingIntoTax( $res, $shipping_method['SID'], $addresses, $order );
		}
		$j++;
	}

	$_i = count($shipping_costs)-1;
	for ( ; $_i>=0; $_i-- ){
		
		$_t = count($shipping_costs[$_i])-1;
		for ( ; $_t>=0; $_t-- ){
			
			if($shipping_costs[$_i][$_t]['rate']>0){
				$shipping_costs[$_i][$_t]['rate'] = show_price($shipping_costs[$_i][$_t]['rate']);
			}else {
			
				if(count($shipping_costs[$_i]) == 1 && $shipping_costs[$_i][$_t]['rate']<0){
				
					$shipping_costs[$_i] = 'n/a';
				}else{
				
					$shipping_costs[$_i][$_t]['rate'] = '';
				}
			}
		}
	}

	return $shipping_costs;
}

$order	 = _getOrder();
$strAddress = quickOrderGetReceiverAddressStr();
$shipping_methods	= shGetAllShippingMethods( true );

if ( isset($_POST['continue_button']) ){
	
	$_POST['shServiceID'] = isset($_POST['shServiceID'][$_POST['select_shipping_method']]) ? $_POST['shServiceID'][$_POST['select_shipping_method']]:0;
	RedirectProtectedSQ('?ukey=order3_billing_quick&shippingMethodID='.$_POST['select_shipping_method'].'&shServiceID='.$_POST['shServiceID']);
}

if ( count($shipping_methods) == 0 )RedirectProtectedSQ( '?ukey=order3_billing_quick&shippingMethodID=0' );

$shipping_costs = _getShippingCosts( $shipping_methods, $order, $moduleFiles );

$avmethod_cnt = 0;

foreach ($shipping_costs as $shipping_cost){
	
	if($shipping_cost == 'n/a')continue;
	$avmethod_cnt++;
}

$smarty->assign( 'strAddress',			$strAddress );
$smarty->assign( 'shipping_costs',		$shipping_costs );
$smarty->assign( 'shipping_methods',	$shipping_methods );
$smarty->assign( 'shipping_methods_count',  $avmethod_cnt );

$smarty->assign( 'main_content_template', 'order2_shipping_quick.tpl.html' );
?>