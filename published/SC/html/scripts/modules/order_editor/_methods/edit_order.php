<?php

class OrderEditController extends ActionsController
{
    const CUSTOMERS_PER_PAGE = 20;
    
    function search_products()
    {
        $params = array(
            'name' => array($this->getData('search_string')),
            'product_code' => array($this->getData('search_string')),
            'search_tags' => array($this->getData('search_string')),
        );
        $count = 0;
        $row_products = prdSearchProductByTemplate($params,$count);
        $products = array();
        
        $order_id = $this->getData('order_id');
        $order_info = ordGetOrder($order_id);
        
        foreach($row_products as $p)
        {
            $products[] = array(
                'product_id' => $p['productID']
               ,'price'		 => number_format($p['Price'] * $order_info['currency_value'], 2, '.', '')
               ,'name'		 => ((/*CONF_ENABLE_PRODUCT_SKU&&*/$p['product_code'])?"[{$p['product_code']}] ":'').$p['name']
               ,'in_stock'	 => $p['in_stock']
               ,'have_options' => (haveProductSelectableOptions($p['productID']) ? '1' : '0')
            );
        };
        
        $GLOBALS['_RESULT'] = array(
            'products' => $products
        );
        
        die();
    }
    
    function ajax_get_states()
    {
        $country_id = $this->getData('country_id');
        
        $states = znGetZones($country_id);
        
        $GLOBALS['_RESULT'] = array(
            'states' => $states
        );
        
        die();
    }
    
    function save_order_info()
    {
        $order_id = $this->getData('order_id');
        
        $order_info = ordGetOrder($order_id);
        
        $shipping_info = $this->getData('shipping');
        $billing_info = $this->getData('billing');
        $products = $this->getData('order_content');
        foreach($products as $k => $v)
        {
            $products[$k]['price'] = $v['price'] / $order_info['currency_value'];
        };
        $discount = $this->getData('order_discount') / $order_info['currency_value'];
        $shipping_cost = $this->getData('order_shipping_cost') / $order_info['currency_value'];
        $payment_type = $this->getData('payment_type');
        $comment = $this->getData('order_comment');
        $discount_descr = $this->getData('order_discount_description');
        
        $amount = array_sum(array_map(
            create_function('$a', 'return ($a["price"] * $a["qty"] + ($a["price"] * $a["qty"] * $a["tax"] / 100));')
           ,$products
        )) - $discount + $shipping_cost;
        
        $info = compact('shipping_info', 'billing_info', 'products', 'discount', 'shipping_cost', 'payment_type', 'amount', 'comment', 'discount_descr');
        
        order_editor::updateOrderInfo($order_id, $info);
        
        Message::raiseMessageRedirectSQ(MSG_SUCCESS, gzinflate(base64_decode($_COOKIE['odet_url'])), 'ordr_order_changed');
    }
    
    function show_customer_search()
    {
        $Register = &Register::getInstance();
        $smarty = &$Register->get(VAR_SMARTY);
        $PV = $Register->get(VAR_POST);
        $GV = $Register->get(VAR_GET);
        
        if((array_key_exists('search', $PV) and $PV['search'] == 'go') or (array_key_exists('oe_ss', $_SESSION) and array_key_exists('page', $GV)))
        {
            $search_string = array_key_exists('search_string', $PV) ? $PV['search_string'] : $_SESSION['oe_ss'];
            
            $_SESSION['oe_ss'] = $search_string;
            
            $_for_count = "count(customerid) as cnt";
            $_for_res = "customerid, login, email, first_name, last_name";
            
            $sql = "from ".CUSTOMERS_TABLE;
            
            $words = array_filter(array_map("trim", explode(" ", $search_string)));
            
            if(!empty($words))
            {
                $strs = array();
                foreach($words as $word)
                {
                    $strs[] = "(login like '%{$word}%' or email like '%{$word}%' or first_name like '%{$word}%' or last_name like '%{$word}%')";
                };
                
                $sql .= " where ".implode(" and ", $strs);
            };
            
            $res = db_query("select {$_for_count} ".$sql);
            $row = db_fetch_assoc($res);
            $customers_count = $row['cnt'];
            
            $customers = array();
            
            $page = ((array_key_exists('page', $GV) and !array_key_exists('search_string', $PV)) ? $GV['page'] : 1); 
            $pages = ceil($customers_count / self::CUSTOMERS_PER_PAGE);
            if($page > $pages) $page = $pages;
            if($page < 1) $page = 1;
            
            if($customers_count > 0)
            {
                $res = db_query("select {$_for_res} ".$sql." limit ".(($page-1)*self::CUSTOMERS_PER_PAGE).", ".self::CUSTOMERS_PER_PAGE);
                while($row = db_fetch_assoc($res))
                {
                    $customers[] = $row;
                };
            };
            
            $smarty->assign('search_string', $search_string);
            $smarty->assign('customers', $customers);
            $smarty->assign('empty_result', empty($customers));
            $smarty->assign('pagination', array(
            	'pages'     => $pages
               ,'page'      => $page
               ,'base_vars' => preg_replace("/&page=\d+/","",$_SERVER["QUERY_STRING"])));
        };
        
        $smarty->assign('admin_sub_dpt', 'order_editor/search_customer'.@$GV['suff'].'.html');
    }
    
    function set_customer()
    {
        $order_id = $this->getData('order_id');
        $customer_id = $this->getData('customer_id');
        
        $sql = "select email, first_name, last_name from ".CUSTOMERS_TABLE." where customerID = {$customer_id}";
        $res = db_query($sql);
        $row = db_fetch_assoc($res);
        
        $sql = "update ".ORDERS_TABLE." set customerID = {$customer_id}, ".
        	   "customer_firstname = '{$row['first_name']}', customer_lastname = '{$row['last_name']}', ".
        	   "customer_email = '{$row['email']}' where orderID = {$order_id}";
        db_query($sql);

        $sql = "select count(*) as cnt from ".ORDER_STATUS_CHANGE_LOG_TABLE." where orderID = {$order_id}";
        $res = db_query($sql);
        $row = db_fetch_assoc($res);
        
        $msg_name = $row['cnt'] > 0 ? 'ordr_comment_admin_modified' : 'ordr_comment_created_by_admin';
        
        $sql = "insert into ".ORDER_STATUS_CHANGE_LOG_TABLE." (orderID, status_change_time, status_comment) ".
            "values ({$order_id}, '".Time::dateTime()."', '".addslashes(str_replace('{0}', $_SESSION['wbs_username'], translate($msg_name)))."')";
        db_query($sql);

        Message::raiseMessageRedirectSQ(MSG_SUCCESS, gzinflate(base64_decode($_COOKIE['odet_url'])), 'ordr_order_changed');
    }
    
    function show_options_form()
    {
        $Register = &Register::getInstance();
        $smarty = &$Register->get(VAR_SMARTY);
        
        $product_id = $this->getData('product_id');
        $options = GetExtraParametrs($product_id);
        $product_info = GetProduct($product_id);
        
        $smarty->assign('options', $options);
        $smarty->assign('product_info', $product_info);
        $smarty->assign('admin_sub_dpt', 'order_editor/show_options_form.html');
    }
    
    function main()
    {
        $Register = &Register::getInstance();
        $smarty = &$Register->get(VAR_SMARTY);
        
        $order_id = $this->getData('order_id');
        $order_info = ordGetOrder($order_id);
        
        $countries = cnGetCountriesNames();
        $shipping_country_id = array_search($order_info['shipping_country'], $countries);
        $billing_country_id = array_search($order_info['billing_country'], $countries);
        $shipping_state_id = 0;
        $billing_state_id = 0;
        
        $shipping_states = znGetZones($shipping_country_id);
        foreach($shipping_states as $state_info)
        {
            if($state_info['zone_name'] == $order_info['shipping_state'])
            {
                $shipping_state_id = $state_info['zoneID'];
            };
        };

        $billing_states = znGetZones($billing_country_id);
        foreach($billing_states as $state_info)
        {
            if($state_info['zone_name'] == $order_info['billing_state'])
            {
                $billing_state_id = $state_info['zoneID'];
            };
        };
        
		$order_content = ordGetOrderContent($order_id);
		ordCalculateOrderTax($order_info, $order_content);

		$olist_url = gzinflate(base64_decode($_COOKIE['olist_url']));
		$odet_url = $olist_url.'&ukey=admin_order_detailed&orderID='.$order_id;
		
		setcookie('odet_url',base64_encode(gzdeflate($odet_url, 9)));

		// convert prices
		$order_info['cnv'] = array(
		    'shipping_cost' => $order_info['shipping_cost'] * $order_info['currency_value']
		   ,'order_discount' => $order_info['order_discount'] * $order_info['currency_value']
		   ,'order_amount' => $order_info['order_amount'] * $order_info['currency_value']
		);
		
        $smarty->assign('order_info', $order_info);
        $smarty->assign('olist_url', $olist_url);
        $smarty->assign('odet_url', $odet_url);
        
        $smarty->assign('countries', $countries);
        $smarty->assign('states', array('shipping' => $shipping_states, 'billing' => $billing_states));
        $smarty->assign('shipping_country_id', $shipping_country_id);
        $smarty->assign('billing_country_id', $billing_country_id);
        $smarty->assign('shipping_state_id', $shipping_state_id);
        $smarty->assign('billing_state_id', $billing_state_id);
        
        $smarty->assign('order_content', $order_content);
        $smarty->assign('customer_login', regGetLoginById($order_info['customerID']));
        
        $smarty->assign('admin_sub_dpt', 'order_editor/order.html');
    }
};

ActionsController::exec('OrderEditController');

?>