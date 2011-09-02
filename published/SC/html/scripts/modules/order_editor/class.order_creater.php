<?php

class order_creater extends Module
{
    function initInterfaces()
    {
        $this->Interfaces = array(
            'create_order' => array(
               'name' => '()'
              ,'type' => INTDIVAVAILABLE
            )
        );
    }
    
    function createOrder($customer_id)
    {
        $order_id = null;
        $_info = _getBaseCustomerInfo($customer_id);
        $_info = array_map('mysql_real_escape_string', $_info);
        $addr = regGetAddress($_info['addressID']);
        $addr = array_map('mysql_real_escape_string', $addr);
        $c = currGetCurrencyByID(CONF_DEFAULT_CURRENCY);
        
        $cnt = cnGetCountryById($addr['countryID']);
        $country_name = mysql_real_escape_string($cnt['country_name']);
        
        if($addr['zoneID']){
            $st = znGetSingleZoneById($addr['zoneID']);
            $state_name = mysql_real_escape_string($st['zone_name']);
        }else{
            $state_name = mysql_real_escape_string($addr['state']);
        }
            
        $sql = "insert into ".ORDERS_TABLE.
               " (customerID, order_time, customer_ip, statusID, order_amount, currency_code, currency_value, ".
               "customer_firstname, customer_lastname, customer_email, ".
               "shipping_firstname, shipping_lastname, shipping_country, shipping_state, shipping_zip, shipping_city, shipping_address, ".
               "billing_firstname, billing_lastname, billing_country, billing_state, billing_zip, billing_city, billing_address,".
               "source)".
        	   " values ({$customer_id}, '".Time::dateTime()."', '".stGetCustomerIP_Address()."', ".CONF_ORDSTATUS_PENDING.", 0.00, '".$c['currency_iso_3']."', 1, ".
               "'{$_info['first_name']}', '{$_info['last_name']}', '{$_info['Email']}', ".
               "'{$_info['first_name']}', '{$_info['last_name']}', '{$country_name}', '{$state_name}', '{$addr['zip']}', '{$addr['city']}', '{$addr['address']}', ".
               "'{$_info['first_name']}', '{$_info['last_name']}', '{$country_name}', '{$state_name}', '{$addr['zip']}', '{$addr['city']}', '{$addr['address']}', ".
               "'backend')";
        db_query($sql);
        $order_id = db_insert_id();
        
	    if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
			include_once(WBS_DIR.'/kernel/classes/class.metric.php');
			
			$DB_KEY=SystemSettings::get('DB_KEY');
			$U_ID = sc_getSessionData('U_ID');
			
			$metric = metric::getInstance();
			$metric->addAction($DB_KEY, $U_ID,'SC', 'ORDER', 'ACCOUNT', '');
		}
        
        return $order_id;
    }
};

?>