<?php

class discount_coupon
{
    function discount_coupon()
    {}
    
    function check($coupon_code)
    {
    	$coupon_code = mb_strtolower($coupon_code,'utf-8');
        $sql = "select coupon_id from ".TBL_DISCOUNT_COUPONS.
        	   " where LOWER(coupon_code)='".addslashes($coupon_code)."'".
               " and is_active='Y'".
               " and ( (coupon_type='SU' and expire_date!=0) or (coupon_type='MX' and expire_date>".time().") or coupon_type='MN')";
        $res = db_query($sql);
        $row = db_fetch_assoc($res);
        return ($row == false ? null : $row['coupon_id']);
    }
    
    function apply($coupon_id)
    {
        $_SESSION['discount_coupon'] = $coupon_id;
    }
    
    function remove()
    {
        if(array_key_exists('discount_coupon',$_SESSION))
        {
            unset($_SESSION['discount_coupon']);
        };
    }
    
    function getCurrentCoupon()
    {
        $coupon_code = 0;
        if(array_key_exists('discount_coupon',$_SESSION))
        {
            $coupon_id = $_SESSION['discount_coupon'];
            $sql = "select * from ".TBL_DISCOUNT_COUPONS." where coupon_id={$coupon_id}";
            $res = db_query($sql);
            $coupon_info = db_fetch_assoc($res);
            $coupon_code = $coupon_info['coupon_code'];
        };
        return $coupon_code;
    }
    
    static function getDiscount($cart_subtotal)
    {
        $discount = 0;
        
        if(array_key_exists('discount_coupon',$_SESSION))
        {
            $coupon_id = $_SESSION['discount_coupon'];
            
            $sql = "select * from ".TBL_DISCOUNT_COUPONS." where coupon_id={$coupon_id}";
            $res = db_query($sql);
            $coupon_info = db_fetch_assoc($res);
            
            switch($coupon_info['discount_type'])
            {
                case 'P': $discount = $cart_subtotal * $coupon_info['discount_percent'] / 100; break;
                case 'A': $discount = $coupon_info['discount_absolute']; break;
            };
        };
        
        return ($discount > $cart_subtotal ? $cart_subtotal : $discount);
    }
    
    function postPlaceOrder($order_id)
    {
        if(array_key_exists('discount_coupon',$_SESSION))
        {
            $coupon_id = $_SESSION['discount_coupon'];
            unset($_SESSION['discount_coupon']);
            
            $sql = "select * from ".TBL_DISCOUNT_COUPONS." where coupon_id={$coupon_id}";
            $res = db_query($sql);
            $coupon_info = db_fetch_assoc($res);
            
            if($coupon_info['coupon_type'] == 'SU')
            {
                $sql = "update ".TBL_DISCOUNT_COUPONS." set expire_date=0 where coupon_id={$coupon_id}";
                db_query($sql);
            };
            
            $sql = "insert into ".TBL_ORDERS_DISCOUNT_COUPONS." (order_id, coupon_id) values ({$order_id}, {$coupon_id})";
            db_query($sql);
        };
    }
};

?>