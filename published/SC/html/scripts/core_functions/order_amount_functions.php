<?php
// *****************************************************************************
// Purpose	get "clear" price as Sum( Price[i]*Quantity[i] ) 
// Inputs   $cartContent is result of cartGetCartContent function
// Remarks	
// Returns	price in universal unit
function oaGetClearPrice( $cartContent )
{
	$res = 0;
	for( $i=0; $i<count($cartContent["cart_content"]); $i++ ) 
	{
		$cartItem = $cartContent["cart_content"][$i];
		$res += $cartItem["quantity"]*$cartItem["costUC"];
	}
	return $res;
}


// *****************************************************************************
// Purpose	get product tax in univesal unit
// Inputs   
//				$cartContent is result of cartGetCartContent function
//				$d is discount in percent
//				$addresses array of
//						$shippingAddressID, 
//						$billingAddressID
//					OR
//						$shippingAddress - array of 
//							"countryID"
//							"zoneID"
//							"zip"
//						$billingAddress - array of
//							"countryID"
//							"zoneID"
//							"zip"
// Remarks	
// Returns	
function oaGetProductTax( $cartContent, $d, $addresses )
{
	$res = 0;
	for( $i=0; $i<count($cartContent["cart_content"]); $i++ ) 
	{
		$cartItem = $cartContent["cart_content"][$i];
		$q = db_query( "select count(*) from ".PRODUCTS_TABLE.
			" where productID=".$cartItem["productID"] );
		$count = db_fetch_row($q);
		if ( $count[0] == 0 )
			continue;

		$cartItem = $cartContent["cart_content"][$i];
		$price = $cartItem["costUC"];

		$price = $price*$cartItem["quantity"];
		if ( is_array($addresses[0]) )
			$tax = taxCalculateTax2( $cartItem["productID"], 
				$addresses[0], $addresses[1] );
		else
			$tax = taxCalculateTax( $cartItem["productID"], 
				$addresses[0], $addresses[1] );
		$res += ($price/100)*$tax;
		//DEBUG:
		//var_dump($tax);
	}
	//DEBUG:	
	//var_dump(array($tax,$res,func_get_args()));
	return $res;
}

// *****************************************************************************
// Purpose	get product tax in univesal unit
// Inputs   
//				$cartContent is result of cartGetCartContent function
// Remarks	
// Returns	
function oaGetShippingCostTakingIntoTax( $cartContent, $shippingMethodID, $addresses, $orderDetails, $CALC_TAX = TRUE, $shServiceID = 0, $shServiceFull = FALSE )
{
	$Rates = array();
	$SimpleFormat = false;
		
	$shipping_method	= shGetShippingMethodById( $shippingMethodID );
	
	if ( $shipping_method )
	{
		$shippingModule = ShippingRateCalculator::getInstance($shipping_method["module_id"]);
		/*@var $shippingModule ShippingRateCalculator*/
		
		if ( $shippingModule )
		{
			//shipping address
			if ( !is_array($addresses[0]) )
			{
				$shippingAddress	= regGetAddress( $addresses[0] );
			}
			else
			{
				$shippingAddress	= $addresses[0] ;
			}

			//order content
			$order = array (
				"first_name" => $orderDetails["first_name"],
				"last_name" => $orderDetails["last_name"],
				"email" => $orderDetails["email"],
				"orderContent" => $cartContent,
				"order_amount" => $orderDetails["order_amount"]
			);

			$Rates = $shippingModule->calculate_shipping_rate( $order, $shippingAddress,  $shServiceID );

			if(!is_array($Rates)){
				
				$Rates = array(array('name'=>'','rate'=>$Rates));
			}
		}
	}

	if(!count($Rates)){
		
		$Rates[] = array('rate'=>'0','name'=>'');
	}
	
	foreach ($Rates as $_ind=>$_Rate)
		$Rates[$_ind]['rate'] += $cartContent["freight_cost"];
	if ($CALC_TAX)
	{
		if ( is_array($addresses[0]) ){
			$rate = taxCalculateTaxByClass2( CONF_CALCULATE_TAX_ON_SHIPPING, $addresses[0], $addresses[1] );
		}else{
			$rate = taxCalculateTaxByClass( CONF_CALCULATE_TAX_ON_SHIPPING, $addresses[0], $addresses[1] );
		}
		foreach ($Rates as $_ind=>$_Rate){
			if(is_int($Rates[$_ind]['rate'])||is_float($Rates[$_ind]['rate'])){
				$Rates[$_ind]['rate'] += ($Rates[$_ind]['rate']/100)*$rate;
			}
		}
	}
		
	return $Rates;
}

// *****************************************************************************
// Purpose	get discount percent
// Inputs   
//				$cartContent is result of cartGetCartContent function
// Remarks	
// Returns	
function oaGetDiscountValue( $cartContent, $log )
{
	$price = oaGetClearPrice( $cartContent );
	$res = dscCalculateDiscount( $price, $log  );
	return $res['discount_standart_unit'];
}

// *****************************************************************************
// Purpose	get order amount (with discount) excluding shipping rate
// Inputs   
//				$cartContent is result of cartGetCartContent function
//				$addresses array of
//						$shippingAddressID, 
//						$billingAddressID
//					OR
//						$shippingAddress - array of 
//							"countryID"
//							"zoneID"
//							"zip"
//						$billingAddress - array of
//							"countryID"
//							"zoneID"
//							"zip"
// Remarks	
// Returns	
function oaGetOrderAmountExShippingRate( $cartContent, $addresses, $log, $CALC_TAX = TRUE )
{
	$clearPrice = oaGetClearPrice( $cartContent );
	$d = oaGetDiscountValue( $cartContent, $log );
	$res = $clearPrice - $d;
	if ($CALC_TAX)
	{
		$res += oaGetProductTax( $cartContent, $d, $addresses );
	}
	return $res;
}



// *****************************************************************************
// Purpose	get order amount
// Inputs   
//				$cartContent is result of cartGetCartContent function
//				$addresses array of
//						$shippingAddressID, 
//						$billingAddressID
//					OR
//						$shippingAddress - array of 
//							"countryID"
//							"zoneID"
//							"zip"
//						$billingAddress - array of
//							"countryID"
//							"zoneID"
//							"zip"
// Remarks	
// Returns	
function oaGetOrderAmount( $cartContent, $addresses, $shippingMethodID, $log, $orderDetails, $CALC_TAX = TRUE, $shServiceID = 0 )
{
	$Rate = oaGetShippingCostTakingIntoTax( $cartContent, $shippingMethodID, $addresses, $orderDetails, $CALC_TAX, $shServiceID );
	$res = oaGetOrderAmountExShippingRate( $cartContent, $addresses, $log, $CALC_TAX ) + $Rate[0]['rate'];
	return $res;
}

?>