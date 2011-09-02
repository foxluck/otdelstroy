<?php

class EditCustomerAddressBook extends ActionsController
{
    function ajax_get_states()
    {
        $country_id = $this->getData('country_id');
        $states = znGetZones($country_id);
        $GLOBALS['_RESULT'] = array(
            'states' => $states
        );
        die();
    }
    
    function save_address()
    {
        $a = $this->getData('addr');
        $addr_id = array_shift(array_keys($a));
        $addr_info = array_shift($a);
        $customer_id = $_GET['userID'];
        //Add strong check for input data
        
        if(!array_key_exists('state', $addr_info))
        {
            $addr_info['state'] = '';
        };
        
        if(!array_key_exists('zoneID', $addr_info))
        {
            $addr_info['zoneID'] = 0;
        };
        
        if($addr_id == 0)
        {
            $addr_info['customerID'] = $_GET['userID'];
            Users::addCustomerAddress($customer_id,$addr_info);
        }
        else
        {
            Users::updateCustomerAddress($addr_id, $addr_info);
        };
        
        RedirectSQ();
    }
    
    function del_address()
    {
        $addr_id = $this->getData('addr_id');
        $customer_id = $_GET['userID'];
        Users::delCustomerAddress($customer_id, $addr_id);
        RedirectSQ();
    }
    
    function set_default_address()
    {
        $addr_id = $this->getData('addr_id');
        $customer_id = $_GET['userID'];
        Users::setDefaultCustomerAddress($customer_id, $addr_id);
        RedirectSQ();
    }
    
    function main()
    {
        $smarty = &Core::getSmarty();
        Users::_initUserInfo();
        
        $smarty->assign('UserInfoFile','backend/user_addresses.html');
        $customerEntry = new Customer();
        $customerEntry->loadByID($_GET['userID']);
        
        $addresses = regGetAllAddressesByID($_GET['userID']);
        for($i=0; $i<count($addresses); $i++)
        	$addresses[$i]['addressStr'] = 	regGetAddressStr( $addresses[$i]['addressID'] );
        
        $def_addr = null;
        foreach($addresses as $k => $addr)
        {
            if($addr['addressID'] == $customerEntry->addressID)
            {
                $def_addr = array_splice($addresses, $k ,1);
                break;
            };
        };
        
        if($def_addr !== null)
        {
            array_unshift($addresses, $def_addr[0]);
        };

        foreach($addresses as $k => $addr)
        {
            $addresses[$k]['address_js'] = str_replace(array("\n","\r"), " ", $addr['address']);
        };
        
        $smarty->assign( 'addresses', $addresses );
        $smarty->assign('addresses_count', count($addresses));

        $smarty->assign( 'defaultAddressID', $customerEntry->addressID);

        $countries = cnGetCountriesNames();
        $states = array();
        foreach($addresses as $key => $addr)
        {
            $states[$addr['addressID']] = znGetZones($addr['countryID']);
        };
        
        $states[0] = znGetZones(array_shift(array_keys($countries)));
        
        $smarty->assign('countries', $countries);
        $smarty->assign('states', $states);
    }
};

ActionsController::exec('EditCustomerAddressBook');

?>