<?php

class order_editor extends Module
{
    function initInterfaces()
    {
        $this->Interfaces = array(
            'edit_order' => array(
				'name' => '()',
				'type' => INTDIVAVAILABLE,
            )
        );
    }
    
    
    function updateOrderInfo($order_id, $order_info)
    {
        // 1. update order
        $fields = array();
        foreach($order_info['shipping_info'] as $key => $value)
        {
            $fields['shipping_'.$key] = $value;
        };
        foreach($order_info['billing_info'] as $key => $value)
        {
            $fields['billing_'.$key] = $value;
        };
        
        foreach(array('shipping', 'billing') as $src)
        {
            if(!array_key_exists($src.'_country', $fields) and array_key_exists($src.'_country_id', $fields))
            {
                $country_info = cnGetCountryById($fields[$src.'_country_id']);
                $fields[$src.'_country'] = $country_info['country_name'];
                unset($fields[$src.'_country_id']);
            };
        };

        foreach(array('shipping', 'billing') as $src)
        {
            if(!array_key_exists($src.'_state', $fields) and array_key_exists($src.'_state_id', $fields))
            {
                $state_info =  znGetSingleZoneById($fields[$src.'_state_id']);
                $fields[$src.'_state'] = $state_info['zone_name'];
                unset($fields[$src.'_state_id']);
            };
        };

        $fields = array_merge($fields, array(
        	'payment_type' => $order_info['payment_type']
           ,'shipping_cost' => $order_info['shipping_cost']
           ,'order_discount' => $order_info['discount']
           ,'order_amount' => $order_info['amount']
           ,'customers_comment' => $order_info['comment']
           ,'discount_description' => $order_info['discount_descr']
         ));
         
         $fields = array_map('addslashes', $fields);
         $strings = array();
         foreach($fields as $fname => $fval)
         {
             $strings[] = "{$fname} = '{$fval}'";
         };
         
         $sql = "update ".ORDERS_TABLE." set ".implode(", ", $strings)." where orderID = {$order_id}";
         db_query($sql);
         
        // get current products in order
        $sql = "select itemID, Quantity from ".ORDERED_CARTS_TABLE." where orderID = {$order_id}";
        $res = db_query($sql);
        $cps = array();
        $cps_ids = array();
        while($row = db_fetch_assoc($res))
        {
            $cps_ids[] = $row['itemID'];
            $cps[$row['itemID']] = $row;
        };
        
        //split to add, update, del
        $to_add = array();
        $to_update = array();
        $u_ids = array();
        
        foreach($order_info['products'] as $info)
        {
            if(!array_key_exists('item_id', $info))
            {
                $to_add[] = $info;
                continue;
            };
            
            if(in_array($info['item_id'], $cps_ids))
            {
                $to_update[] = $info;
                $u_ids[] = $info['item_id'];
                continue;
            };
        };

        //<del:begin>
        $to_del = array_diff($cps_ids, $u_ids);
        // return products to stock
        foreach($to_del as $id)
        {
            $qty = $cps[$id]['Quantity'];
            $sql = "select productID from ".SHOPPING_CART_ITEMS_TABLE." where itemID = {$id}";
            $res = db_query($sql);
            $row = db_fetch_assoc($res);
            if(($row != false)&&$row['productID'])
            {
                $sql = "update ".PRODUCTS_TABLE." set in_stock = in_stock + {$qty} where productID = {$row['productID']}";
                db_query($sql);
            };
            
        };
        if(!empty($to_del))
        {
            $sql = "delete from ".SHOPPING_CART_ITEMS_TABLE." where itemID in (".implode(', ', $to_del).")";
            db_query($sql);
            
            $sql = "delete from ".ORDERED_CARTS_TABLE." where itemID in (".implode(', ', $to_del).")";
            db_query($sql);
        };
        //<del:end>
        
        //<update:begin>
        foreach($to_update as $info)
        {
            $qty_diff = $cps[$info['item_id']]['Quantity'] - $info['qty'];
            if($qty_diff > 0) $qty_diff = '+'.$qty_diff;
            
            if($qty_diff != 0)
            {
                $sql = "select productID from ".SHOPPING_CART_ITEMS_TABLE." where itemID = {$info['item_id']}";
                $res = db_query($sql);
                $row = db_fetch_assoc($res);
                if(($row != false)&&$row['productID'])
                {
                    $sql = "update ".PRODUCTS_TABLE." set in_stock = in_stock{$qty_diff} where productID = {$row['productID']}";
                    db_query($sql);
                };
            };
            
            $sql = "update ".ORDERED_CARTS_TABLE." set Price = '{$info['price']}', Quantity = {$info['qty']}, tax = {$info['tax']}".
            	   " where orderID = {$order_id} and itemID = {$info['item_id']}";
            db_query($sql);
        };
        //<update:end>
        
        //<add:begin>
        $strings = array();
        foreach($to_add as $info)
        {
            //insert into cart_items
            $sql = "insert into ".SHOPPING_CART_ITEMS_TABLE." (productID) values ({$info['product_id']})";
            db_query($sql);
            $item_id = db_insert_id();
            
            //update stock
            $sql = "update ".PRODUCTS_TABLE." set in_stock = in_stock - {$info['qty']} where productID = {$info['product_id']}";
            db_query($sql);
            
            // for mass insert
            //$strings[] = "({$item_id}, {$order_id}, '{$info['name']}', '{$info['price']}', {$info['qty']}, {$info['tax']})";
            $sql = "insert into ?#ORDERED_CARTS_TABLE (itemID, orderID, name, Price, Quantity, tax) values (?,?,?,?,?,?)";
            db_phquery($sql,$item_id,$order_id,$info['name'],$info['price'],$info['qty'],$info['tax']);
        };
        
        // mass insert;
        if(false&&!empty($strings))
        {
            $sql = "insert into ".ORDERED_CARTS_TABLE." (itemID, orderID, name, Price, Quantity, tax) values ".implode(', ', $strings);
            db_query($sql);
        };
        //<add:end>
         
        // 3. add changelog
        $sql = "select count(*) as cnt from ".ORDER_STATUS_CHANGE_LOG_TABLE." where orderID = {$order_id}";
        $res = db_query($sql);
        $row = db_fetch_assoc($res);
        
        $msg_name = $row['cnt'] > 0 ? 'ordr_comment_admin_modified' : 'ordr_comment_created_by_admin';
        
        $sql = "insert into ?#ORDER_STATUS_CHANGE_LOG_TABLE (orderID, status_change_time, status_comment) ".
               "values (?, ?, ?)";
        db_phquery($sql,$order_id, Time::dateTime(), str_replace('{0}', $_SESSION['wbs_username'],translate($msg_name)));
    	if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
			include_once(WBS_DIR.'/kernel/classes/class.metric.php');
			
			$DB_KEY=SystemSettings::get('DB_KEY');
			$U_ID = sc_getSessionData('U_ID');
			
			$metric = metric::getInstance();
			$metric->addAction($DB_KEY, $U_ID,'SC', 'EDITORDER', 'ACCOUNT', '');
		}
    }
};

?>