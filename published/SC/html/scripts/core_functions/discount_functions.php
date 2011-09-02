<?php
/**
 * @param int $orderPrice: in UC
 * @param string $log: customer login
 * @return array: Example - 
		[discount_percent] => 50
    [discount_standart_unit] => 28.985
    [discount_current_unit] => 34.78
    [rest_standart_unit] => 28.985
    [rest_current_unit] => 34.78
 */
function dscCalculateDiscount($orderPrice, $log)
{
	$dsc_by = _dscGetDiscountsArray($orderPrice, $log);

    $coupon_discount = $dsc_by['coupon'];
    unset($dsc_by['coupon']);
	
	$result_discount = (CONF_DSC_CALC == 'as_max' ? max($dsc_by) : array_sum($dsc_by));
	
	if(($result_discount + $coupon_discount) < $orderPrice)
	{
    	$discount = array(
    	 	"discount_percent"		  => ($result_discount + $coupon_discount)*100/$orderPrice
           ,"discount_standart_unit"  => ($result_discount + $coupon_discount)
           ,"discount_current_unit"   => show_priceWithOutUnit($result_discount + $coupon_discount)
           ,"rest_standart_unit"      => ($orderPrice - $result_discount - $coupon_discount)
           ,"rest_current_unit"       => show_priceWithOutUnit($orderPrice - $result_discount - $coupon_discount)
    	);
	}
	else
	{
    	$discount = array(
    		"discount_percent"		  => 100
           ,"discount_standart_unit"  => $orderPrice
           ,"discount_current_unit"   => show_priceWithOutUnit($orderPrice)
           ,"rest_standart_unit"      => 0
           ,"rest_current_unit"       => show_priceWithOutUnit(0)
    	);
	};
	
	return $discount;
}

function dscGetCartDiscounts($cart_subtotal, $log)
{
    $dsc_by = _dscGetDiscountsArray($cart_subtotal, $log);
    
    $coupon_discount = $dsc_by['coupon'];
    unset($dsc_by['coupon']);
    
    $result_discount = (CONF_DSC_CALC == 'as_max' ? max($dsc_by) : array_sum($dsc_by));
    
    $r = array(
    	'discount_percent' =>$cart_subtotal?$result_discount*100/$cart_subtotal:0
       ,'coupon_discount'  => array('su' => $coupon_discount, 'cu' => show_priceWithOutUnit($coupon_discount))
       ,'other_discounts'  => array('su' => $result_discount, 'cu' => show_priceWithOutUnit($result_discount))
       ,'total' => array(
       		'su' => ($cart_subtotal - $result_discount - $coupon_discount)
           ,'cu' => show_priceWithOutUnit($cart_subtotal - $result_discount - $coupon_discount)
       )
    );
    
	if($r['total']['su'] < 0)
	{
	    $r['total']['su'] = 0;
	    $r['total']['cu'] = 0;
	};
	
	return $r;
};

function _dscGetDiscountsArray($cart_subtotal, $log)
{
    return array(
	    'coupon'     => CONF_DSC_COUPONS_ENABLED == 'Y' ? _getDiscountByCoupon($cart_subtotal) : 0
	   ,'usergroup'  => CONF_DSC_USERGROUP_ENABLED == 'Y' ? _getDiscountByCustomerGroup($cart_subtotal, $log) : 0
	   ,'amount'	 => CONF_DSC_AMOUNT_ENABLED == 'Y' ? _getDiscountByAmount($cart_subtotal) : 0
	   ,'orders'	 => CONF_DSC_ORDERS_ENABLED == 'Y' ? _getDiscountByOrdersSum($cart_subtotal, $log) : 0
	);
}

function _getDiscountByCoupon($cart_subtotal)
{
    ClassManager::includeClass('discount_coupon');
    return discount_coupon::getDiscount($cart_subtotal);
}

function _getDiscountByCustomerGroup($cart_subtotal, $log)
{
    $discount_percent = 0;
    
    if(!is_bool($customerID=regGetIdByLogin($log)))
    {
        $customer_group = GetCustomerGroupByCustomerId( $customerID );
    	$discount_percent = ($customer_group !==false ? $customer_group["custgroup_discount"] : 0);
    };
    
    return ($cart_subtotal * $discount_percent / 100);
}

function _getDiscountByAmount($cart_subtotal)
{
    $sql = "select * from ".ORDER_PRICE_DISCOUNT_TABLE." where price_range<={$cart_subtotal} and discount_type='A' order by price_range desc limit 1";
    $res = db_query($sql);
    $row = db_fetch_assoc($res);
    $discount_percent = ($row !== false ? $row['percent_discount'] : 0);
    return ($cart_subtotal * $discount_percent / 100);
}

function _getDiscountByOrdersSum($cart_subtotal, $log)
{
    $discount_percent = 0;
	$customerEntry = Customer::getAuthedInstance();
	if(!is_null($customerEntry) && $customerEntry->Login === $log)
	{
	    $orders_sum = $customerEntry->getOrdersSum(CONF_ORDSTATUS_DELIVERED);
        $sql = "select * from ".ORDER_PRICE_DISCOUNT_TABLE." where price_range<={$orders_sum} and discount_type='O' order by price_range desc limit 1";
        $res = db_query($sql);
        $row = db_fetch_assoc($res);
        $discount_percent = ($row !== false ? $row['percent_discount'] : 0);
	};

    return ($cart_subtotal * $discount_percent / 100);
}

// *****************************************************************************
// Purpose	gets all order price discounts
// Inputs       
// Remarks		
// Returns	
function dscGetAllOrderPriceDiscounts()
{
    return _dscGetAllDiscounts('A');
}                                                                           

function dscGetAllOrderSumDiscounts()
{
    return _dscGetAllDiscounts('O');
}                                                                           

// *****************************************************************************
// Purpose	add order price discount
// Inputs   
// Remarks		
// Returns	if discount with $price_range already exists this function returns false and does not add new discount
//			otherwise true
function dscAddOrderPriceDiscount( $price_range, $percent_discount )
{
    return _dscAddDiscount($price_range, $percent_discount, 'A');
}

function dscAddOrderSumDiscount( $price_range, $percent_discount )
{
    return _dscAddDiscount($price_range, $percent_discount, 'O');
}

// *****************************************************************************
// Purpose	delete discount
// Inputs   
// Remarks		
// Returns	
function dscDeleteOrderPriceDiscount( $discount_id )
{
    return dscDeleteDiscount($discount_id);
}
           
// *****************************************************************************
// Purpose	update discount
// Inputs   
// Remarks		
// Returns	
function dscUpdateOrderPriceDiscount( $discount_id, $price_range, $percent_discount )
{
    return dscUpdateDiscount($discount_id, $price_range, $percent_discount);
}

function _dscGetAllDiscounts($dsc_type = 'A')
{
	$q = db_query( "select discount_id, price_range, percent_discount from ".ORDER_PRICE_DISCOUNT_TABLE.
			" where discount_type='{$dsc_type}' order by price_range" );	
	$data = array();
	while( $row = db_fetch_row($q) )
		$data[] = $row;
	return $data;
}

function _dscAddDiscount($price_range, $percent_discount, $dsc_type = 'A')
{
	$q=db_query( "select price_range, percent_discount from ".ORDER_PRICE_DISCOUNT_TABLE.
			" where discount_type='{$dsc_type}' and price_range={$price_range}" );
	if (($row=db_fetch_row($q)))
	{
		return false;
	}
	else
	{
		db_query("insert into ".ORDER_PRICE_DISCOUNT_TABLE." ( price_range, percent_discount, discount_type ) ".
			 " values( {$price_range}, {$percent_discount}, '{$dsc_type}' )");
		return true; 
	}
}

function dscDeleteDiscount($discount_id)
{
	db_query( "delete from ".ORDER_PRICE_DISCOUNT_TABLE." where discount_id=$discount_id " );
}

function dscUpdateDiscount($discount_id, $price_range, $percent_discount)
{
	$q=db_query( "select price_range, percent_discount from ".ORDER_PRICE_DISCOUNT_TABLE.
			" where price_range=$price_range AND discount_id <> $discount_id" );
	if ( ($row=db_fetch_row($q)) )
		return false;
	else
	{	
		db_query("update ".ORDER_PRICE_DISCOUNT_TABLE.
			" set price_range=$price_range, percent_discount=$percent_discount ".
			" where discount_id=$discount_id ");
		return true; 
	}
}

?>