<?php
// *****************************************************************************
// Purpose			select shipping address and shipping method
// Call condition   
//					index.php?order2=yes&shippingAddressID=<address ID>
// Include PHP		index.php -> [order2.php]
// Uses TPL			order2.tpl.html
// Remarks
if(!cartCheckMinTotalOrderAmount())	RedirectSQ('?ukey=cart&min_order=error');

if (!isset($_GET["shippingAddressID"]))RedirectSQ('?ukey=page_not_found&debug=1' );

$_GET["shippingAddressID"] = (int)$_GET["shippingAddressID"];

if ($_GET["shippingAddressID"] == 0){ //no default address specified
	$addrs = regGetAllAddressesByLogin($_SESSION["log"]);
}
elseif ( !regAddressBelongToCustomer(regGetIdByLogin($_SESSION["log"]), $_GET["shippingAddressID"]) ){
	RedirectSQ('?ukey=page_not_found&debug=2' );
}

if ( !cartCheckMinOrderAmount() )RedirectSQ('?ukey=cart');

function _getOrder()
{
	$cust_password		= "";
	$Email				= "";
	$first_name			= "";
	$last_name			= "";
	$subscribed4news	= "";
	$additional_field_values = "";
	$countryID			= "";
	$zoneID				= "";
	$state				= "";
	$zip				= "";
	$city				= "";
	$address			= "";

	regGetCustomerInfo($_SESSION["log"], 
			$cust_password, $Email, $first_name, 
			$last_name, $subscribed4news, $additional_field_values, 
			$countryID, $zoneID, $state, $zip, $city, $address );


	$order["first_name"]	= $first_name;
	$order["last_name"]		= $last_name;
	$order["email"]			= $Email;

	$res = cartGetCartContent();
	$order["orderContent"]	= $res["cart_content"];

	$d = oaGetDiscountValue( $res, $_SESSION["log"] );
	$order["order_amount"] = $res["total_price"] - $d;

	return $order;
}

if ( isset($_GET['selectedNewAddressID']) )
{
	if ( !isset($_GET['defaultBillingAddressID']) )
		RedirectSQ( '?ukey=order2_shipping&shippingAddressID='.$_GET['selectedNewAddressID'] );
	else
		RedirectSQ( '?ukey=order2_shipping&shippingAddressID='.$_GET['selectedNewAddressID'].'&defaultBillingAddressID='.$_GET['defaultBillingAddressID'] );
}

if ( isset($_POST['continue_button']) ){
	
	$_POST['shServiceID'] = isset($_POST['shServiceID'][$_POST['select_shipping_method']]) ? $_POST['shServiceID'][$_POST['select_shipping_method']]:0;
	if ( !isset($_GET['defaultBillingAddressID']) )
		RedirectProtectedSQ('ukey=order3_billing&shippingMethodID='.$_POST['select_shipping_method'].'&billingAddressID='.regGetDefaultAddressIDByLogin($_SESSION['log']).'&shServiceID='.$_POST['shServiceID']);
	else
		RedirectProtectedSQ('ukey=order3_billing&shippingMethodID='.$_POST['select_shipping_method'].'&billingAddressID='.$_GET['defaultBillingAddressID'].'&shServiceID='.$_POST['shServiceID']);
}
$shippingAddressID		= $_GET['shippingAddressID'];
$order					= _getOrder();

$strAddress = regGetAddressStr( $shippingAddressID );

$moduleFiles = GetFilesInDirectory( "./modules/shipping", "php" );
foreach( $moduleFiles as $fileName )
	include_once( $fileName );
	
$shipping_methods	= shGetAllShippingMethods( true );
$shipping_costs		= array();

$res			= cartGetCartContent();

$sh_address = regGetAddress( $shippingAddressID );
$addresses = array( $sh_address, $sh_address );

$j = 0;
foreach( $shipping_methods as $key => $shipping_method )
{
	$_ShippingModule = ShippingRateCalculator::getInstance($shipping_method["module_id"]);
	/*@var $_ShippingModule ShippingRateCalculator*/
	if($_ShippingModule){
		
		if ( $_ShippingModule->allow_shipping_to_address( regGetAddress($shippingAddressID) ) )
		{
			$shipping_costs[$j] = oaGetShippingCostTakingIntoTax( $res, $shipping_method["SID"], $addresses, $order );
		}
		else
		{

			$shipping_costs[$j] = array(array('rate'=>-1));
		}
	}else //rate = freight charge
	{
		$shipping_costs[$j] = oaGetShippingCostTakingIntoTax( $res, $shipping_method["SID"], $addresses, $order );
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

if ( count($shipping_methods) == 0 )
		RedirectProtectedSQ("?ukey=order3_billing&".
					"shippingAddressID=".regGetDefaultAddressIDByLogin($_SESSION["log"])."&".
					"shippingMethodID=0&".
					"billingAddressID=".regGetDefaultAddressIDByLogin($_SESSION["log"]) );


if ( isset($_GET["defaultBillingAddressID"]) )
	$smarty->assign( "defaultBillingAddressID", $_GET["defaultBillingAddressID"] );
$smarty->assign( "shippingAddressID",	$_GET["shippingAddressID"] );
$smarty->assign( "strAddress",			$strAddress );
$smarty->assign( "shipping_costs",		$shipping_costs );
$smarty->assign( "shipping_methods",	$shipping_methods );		
$smarty->assign( "shipping_methods_count",  count($shipping_methods) );
$smarty->assign( "main_content_template", "order2_shipping.tpl.html" );
?>