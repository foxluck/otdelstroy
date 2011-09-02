<?php
define('COUPONS_PER_PAGE', 10);
define('FIRST_PAGE',1);
define('LAST_PAGE',500);

class discount_coupons extends Module
{
    function initInterfaces()
    {
        $this->Interfaces = array(
            'manage_coupons' => array(
				'name' => 'Управление купонами (админка)',
				'type' => INTDIVAVAILABLE,
            )
        );
    }
    
    function addCoupon(&$data)
    {
        $check_result = $this->_check_before_addCoupon($data);
        
        if(!empty($check_result))
        {
            return array(
                'status' => false
               ,'errors' => $check_result
            );
        };
        $data['expire_date'] = Time::timeToServerTime($data['expire_date']);
        $data['code'] = mb_strtolower($data['code'],'utf-8');
        
        $sql = "insert into ".TBL_DISCOUNT_COUPONS.
        	   " (coupon_code, is_active, coupon_type, expire_date, discount_percent, discount_absolute, discount_type, comment)".
        	   " values ('".addslashes($data['code'])."', '{$data['is_active']}', '{$data['type']}', {$data['expire_date']}, ".
               "{$data['discount_percent']}, {$data['discount_absolute']}, '{$data['discount_type']}', '".addslashes($data['comment'])."')";
        
        $res = db_query($sql);
        if($res['resource'] === false)
        {
            return array(
                'status' => false
               ,'errors' => array('sql_fail')
            );
        };
        
        return array(
            'status' => true
           ,'coupon_id' => db_insert_id()
        );
    }
    
    function delCoupon($coupon_id)
    {
        return $this->delCoupons(array($coupon_id));
    }
    
    function delCoupons($coupons_ids)
    {
        if(!is_array($coupons_ids) or empty($coupons_ids))
        {
            return array(
                'status' => true
               ,'deleted' => 0
            );
        };
        
        $coupons_ids = array_map('intval',$coupons_ids);
        
        $sql = "delete from ".TBL_ORDERS_DISCOUNT_COUPONS." where coupon_id in (".(implode(', ',$coupons_ids)).")";
        db_query($sql);
        
        $sql = "delete from ".TBL_DISCOUNT_COUPONS." where coupon_id in (".(implode(', ',$coupons_ids)).")";
        
        $res = db_query($sql);
        if($res['resource'] === false)
        {
            return array(
                'status' => false
               ,'errors' => array('sql_fail')
            );
        };

        return array(
            'status' => true
           ,'deleted' => mysql_affected_rows()
        );
    }
    
    function getCoupons($page = null)
    {
        if($page == null or $page < 1)
        {
            $page = 1;
        };
        
        $sql = "select count(*) as coupons_count from ".TBL_DISCOUNT_COUPONS." order by coupon_id desc";
        $res = db_query($sql);
        if($res['resource'] === false)
        {
            return array(
                'status' => false
               ,'errors' => array('sql_fail')
            );
        };
        
        $row = db_fetch_assoc($res);
        $coupons_count = $row['coupons_count'];
        
        if($coupons_count == 0)
        {
            return array(
                'status' => true
               ,'pagination' => array('page' => 0, 'pages' => 0, 'items_count' => 0)
               ,'coupons' => array()
            );
        };
        
        $sql = "select * from ".TBL_DISCOUNT_COUPONS." order by coupon_id desc";
        $pages = ceil($coupons_count / COUPONS_PER_PAGE); //TODO: make config!
        if($page == LAST_PAGE or $page > $pages)
        {
            $page = $pages;
        };
        $sql .= " limit ".(($page-1)*COUPONS_PER_PAGE).", ".COUPONS_PER_PAGE;
        
        $res = db_query($sql);
        if($res['resource'] === false)
        {
            return array(
                'status' => false
               ,'errors' => array('sql_fail')
            );
        };
        
        $coupons = array();
        while(($row = db_fetch_assoc($res)) != false)
        {
            $coupons[] = $row;
        };
        
        $pagination = array(
            'page' => $page
           ,'pages' => $pages
           ,'items_count' => $coupons_count
        );
        
        $status = true;
        return compact("status","pagination","coupons");
    }
    
    function getCouponInfoByID($coupon_id)
    {
        return $this->_getCouponInfoBy('id',$coupon_id);
    }
    
    function getCouponInfoByCode($coupon_code)
    {
        return $this->_getCouponInfoBy('code',$coupon_code);
    }
    
    function getTemplatePath($template)
    {
        return DIR_TPLS.'/backend/discount_coupons/'.$template;
    }
    
    function genRandomCouponCode()
    {
        return strtoupper(substr(md5(time()),mt_rand(0,15),10));
    }
    
    function updateCouponsStatus($coupons_ids, $is_active)
    {
        if(!is_array($coupons_ids) or empty($coupons_ids))
        {
            return array(
                'status' => true
               ,'updated' => 0
            );
        };
        
        $sql = "update ".TBL_DISCOUNT_COUPONS." set is_active='".($is_active ? 'Y' : 'N')."' where ".
               "coupon_id in (".implode(", ", $coupons_ids).")";
        
        $res = db_query($sql);
        if($res['resource'] === false)
        {
            return array(
                'status' => false
               ,'errors' => array('sql_fail')
            );
        };

        return array(
            'status' => true
           ,'updated' => mysql_affected_rows()
        );
    }
    
    function updateCoupons($data)
    {
        foreach($data as $coupon_id => $coupon_data)
        {
            $this->updateCoupon($coupon_id, $coupon_data);
        };
    }
    
    function updateCoupon($coupon_id, $data)
    {
        $sql = "update ".TBL_DISCOUNT_COUPONS." set is_active='".($data['is_active'] ? 'Y' : 'N')."', ".
               "discount_type = '{$data['discount_type']}', ".
               ($data['discount_type'] == 'P' ? 'discount_percent' : 'discount_absolute')."={$data['discount_value']} ".
               "where coupon_id={$coupon_id}";
        db_query($sql);
    }
    
    function getCouponOrders($coupon_id)
    {
        $sql = "select * from ".TBL_ORDERS_DISCOUNT_COUPONS." where coupon_id={$coupon_id} order by order_id desc";
        $res = db_query($sql);
        $orders = array();
        while(($row = db_fetch_assoc($res)) !== false)
        {
            $orders[] = $row['order_id'];
        };
        return $orders;
    }
    
    function _check_before_addCoupon(&$data)
    {
        $return = array();
        
        $data['code'] = trim($data['code']);
        if($data['code'] == '' or !preg_match("/^[a-z0-9]{1,10}$/i",$data['code']))
        {
            $return[] = 'invalid_code';
        };
        
        $check_for_exists = $this->getCouponInfoByCode($data['code']);
        if($check_for_exists['status'] and $check_for_exists['coupon_info'] !== false)
        {
            $return[] = 'code_exists';
        };
        
        $data['is_active'] = ($data['is_active'] != 'Y' ? 'N' : 'Y');
        $data['type'] = (!in_array($data['type'], array('SU','MX','MN')) ? 'SU' : $data['type']);
        
        if(Time::isValidSatandartTime($data['expire_date']))
        {
        	$day 	= substr($data['expire_date'], strpos(CONF_DATE_FORMAT, 'DD'),2);
        	$month 	= substr($data['expire_date'], strpos(CONF_DATE_FORMAT, 'MM'),2);
        	$year 	= substr($data['expire_date'], strpos(CONF_DATE_FORMAT, 'YYYY'),4);
        	$data['expire_date'] = mktime(23, 59, 59, $month, $day, $year);
        }
        else
        {
            $data['expire_date'] = time();
            if($data['type'] == 'MX')
            {
                $return[] = 'invalid_date';
            };
        };
        	
        $data['discount_percent'] = number_format(abs($data['discount_percent']),2,'.','');
        if($data['discount_percent'] > 100)
        {
            $return[] = 'invalid_discount_percent';
        };
        
        $data['discount_absolute'] = number_format(abs($data['discount_absolute']),2,'.','');
        
        return $return;
    }
    
    function _getCouponInfoBy($get_by, $data)
    {
        $sql = "select * from ".TBL_DISCOUNT_COUPONS." where ";
        $sql .= ($get_by == 'id' ? 'coupon_id' : 'coupon_code') . " = '{$data}'";
        
        $res = db_query($sql);
        if($res['resource'] === false)
        {
            return array(
                'status' => false
               ,'errors' => array('sql_fail')
            );
        };
        
        $row = db_fetch_assoc($res);
        return array(
            'status' => true
           ,'coupon_info' => $row
        );
    }
    
    function _add_prefix_to_errors($a)
    {
        return 'err_dc_'.$a;
    }
   
};
?>