<?php

class OrderCreateController extends ActionsController
{
    function ajax_get_customer_info()
    {
        $customer_id = $this->getData('customer_id');
        $sql = "select * from ".CUSTOMERS_TABLE." where customerID = {$customer_id}";
        $res = db_query($sql);
        $row = db_fetch_assoc($res);
        $row['reg_fields'] = GetRegFieldsValuesByCustomerID($customer_id);
        
        $GLOBALS["_RESULT"] = array(
            'customer_info' => $row
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
    
    function ajax_create_order()
    {
        $_src = $this->getData('customer_src');
        if($_src == 'ex')
        {
            $customer_id = $this->getData('customer_id');
        };
        
        if($_src == 'new')
        {
            $customer_info = $this->getData('customer_info');
            
            $info = array(
                'first_name' => $customer_info['first_name']
               ,'last_name' => $customer_info['last_name']
               ,'email' => $customer_info['email']
            );
            $reg_fields = array();
            $allowed_reg_fields = GetRegFields();
            foreach($customer_info as $fld_name => $fld_val)
            {
                if(preg_match("/^reg_field\[(\d+)$/i", $fld_name, $matches))
                {
                    if(in_array($matches[1],$allowed_reg_fields)){
                    	$reg_fields[$matches[1]] = $fld_val;
                    }
                };
            };
            $address = array(
                'country_id' => (array_key_exists('country_id', $customer_info) and $customer_info['country_id'] > 0) ? $customer_info['country_id'] : ''
               ,'state_id' => (array_key_exists('state_id', $customer_info) and $customer_info['state_id'] > 0) ? $customer_info['state_id'] : ''
               ,'zip' => $customer_info['zip']
               ,'state' => array_key_exists('state', $customer_info) ? $customer_info['state'] : ''
               ,'city' => $customer_info['city']
               ,'address' => $customer_info['address']
            );
            $customer_id = _addNewNoLoginCustomer($info, $reg_fields, $address);
        };
        
        $order_id = order_creater::createOrder($customer_id);
        
        $GLOBALS["_RESULT"] = array(
            'order_id' => $order_id
        );
        
        die();
    }
    
    function main()
    {
        $Register = &Register::getInstance();
        $smarty = &$Register->get(VAR_SMARTY);
        
        $countries = cnGetCountriesNames();
        $states = (!empty($countries) ? znGetZonesById(CONF_DEFAULT_COUNTRY) : array());
        
        $smarty->assign('olist_url', gzinflate(base64_decode($_COOKIE['olist_url'])));
        
        $smarty->assign('reg_fields', GetRegFields());
        $smarty->assign('countries', $countries);
        $smarty->assign('states', $states);
        $smarty->assign('admin_sub_dpt', 'order_editor/new_order.html');
    }
};

ActionsController::exec('OrderCreateController');

?>