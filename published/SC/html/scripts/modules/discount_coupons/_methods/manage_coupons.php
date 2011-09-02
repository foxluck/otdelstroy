<?php
    $Register = &Register::getInstance();
    $smarty = &$Register->get(VAR_SMARTY);
    $PV = $Register->get(VAR_POST);
    $GV = $Register->get(VAR_GET);

    $page = (array_key_exists('offset',$GV) ? $GV['offset'] / COUPONS_PER_PAGE + 1 : null);
    
    $new_coupon_data = array(
        'code' => $this->genRandomCouponCode()
       ,'is_active' => 'Y'
       ,'type' => 'SU'
       ,'expire_date' => time()
       ,'discount_type' => 'P'
       ,'discount_percent' => '0.00'
       ,'discount_absolute' => '0.00'
       ,'comment' => ''
    );
    
    $_errors = array();
    
    switch($PV['coupon_action'])
    {
        case 'add_new':
            $new_coupon_data = $PV['new_coupon'];
            $new_coupon_data['is_active'] = array_key_exists('is_active',$new_coupon_data) ? 'Y' : 'N';
            
            $add_result = $this->addCoupon($new_coupon_data);
            
            if(!$add_result['status'])
            {
                $_errors = array_merge($_errors, $add_result['errors']);
            }
            else
            {
                $new_coupon_data['code'] = $this->genRandomCouponCode();
                $new_coupon_data['comment'] = '';
                $page = FIRST_PAGE;
            };
            break;
        case 'del_coupons':
            $coupons_ids = explode('|',$PV['coupons_ids']);
            $del_result = $this->delCoupons($coupons_ids);
            if(!$del_result['status'])
            {
                $_errors = array_merge($_errors, $del_result['errors']);
            };
            break;
        case 'upd_coupons':
            list($active_ids_str, $inactive_ids_str) = explode('-',$PV['coupons_ids']);
            $active_coupons = explode('|',$active_ids_str);
            $inactive_coupons = explode('|',$inactive_ids_str);
            $coupons_data = array();
            $PV['coupons_discounts'] = explode('|',$PV['coupons_discounts']);
            foreach($PV['coupons_discounts'] as $dsc)
            {
                list($coupon_id, $discount_value) = explode('-', $dsc);
                $coupons_data[$coupon_id] = array(
                    'is_active' => in_array($coupon_id, $active_coupons)
                   ,'discount_value' => $discount_value
                );
            };
            $PV['discount_types'] = explode('|', $PV['discount_types']);
            foreach($PV['discount_types'] as $dsc)
            {
                list($coupon_id, $discount_type) = explode('-', $dsc);
                $coupons_data[$coupon_id]['discount_type'] = $discount_type;
            };
            $this->updateCoupons($coupons_data);
            break;
    };
    
    $currencyEntry = Currency::getDefaultCurrencyInstance();
    $currency_sign = trim(str_replace('{value}','',$currencyEntry->display_template));

    $new_coupon_data['expire_date'] = Time::standartTime($new_coupon_data['expire_date'],false);

    $get_res = $this->getCoupons($page);
    if(!$get_res['status'])
    {
        $_errors = array_merge($_errors, $get_res['errors']);
        $coupons = array();
        $pagination = array('page' => 0, 'pages' => 0);
    }
    else
    {
        $coupons = $get_res['coupons'];
        $pagination = $get_res['pagination'];
    };
    
    foreach($coupons as $k => $coupon)
    {
        $coupons[$k]['show_abs_discount'] = $currencyEntry->getView($coupon['discount_absolute']);
        if($coupon['coupon_type'] != 'SU')
        {
            $coupons[$k]['expire_info'] = translate($coupon['expire_date'] > time() ? 'lbl_valid_to' : 'lbl_expired').' '.Time::standartTime($coupon['expire_date']);
            $coupons[$k]['is_disabled'] = ($coupon['coupon_type'] != 'MN' and $coupon['expire_date'] < time());
        }
        else
        {
            $coupons[$k]['expire_info'] = '';
            $coupons[$k]['is_disabled'] = ($coupon['expire_date'] == 0);
        };
        
        $coupons[$k]['orders'] = $this->getCouponOrders($coupon['coupon_id']);
        $coupons[$k]['orders_count'] = count($coupons[$k]['orders']);
    };

    $smarty->assign('base_uri', preg_replace("/&page=\d+/","",$_SERVER["QUERY_STRING"]));
    $smarty->assign('_errors', array_map(array(&$this,'_add_prefix_to_errors'),$_errors));
    $smarty->assign('new_coupon_data', $new_coupon_data);
    $smarty->assign('currency_sign', $currency_sign);
    $smarty->assign('coupons_list', $coupons);
    $smarty->assign('coupons_pagination', $pagination);
    
    ShowNavigator($pagination['items_count'], ($pagination['page']-1)*COUPONS_PER_PAGE, COUPONS_PER_PAGE, preg_replace("/&offset=\d+/","",$_SERVER["QUERY_STRING"]), $coupons_nav);
    $smarty->assign('coupons_nav', $coupons_nav);
    
    $smarty->assign('coupons_tpls_dir', $this->getTemplatePath(''));
    $smarty->assign('sub_template', $this->getTemplatePath('manage_coupons.html'));
?>