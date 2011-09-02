<?php

class EditCustomerContactInfo extends ActionsController
{
	function save_contact_info()
	{
	    $ci = $this->getData('ci');

        if(!array_key_exists('subscribed4news', $ci))
        {
            $ci['subscribed4news'] = 0;
        };
        
        $ce = new Customer();
        $ce->loadByID($_GET['userID']);
        
        $ci['ActivationCode'] = ($ci['activated'] == '1' ? '' : ($ce->ActivationCode != '' ? $ce->ActivationCode : substr(md5(time()),mt_rand(0,15),16) ));
        unset($ci['activated']);
        
        Users::updateCustomerInfo($ce->customerID, $ci);
        
        RedirectSQ();
	}
	
    function main()
    {
        $smarty = &Core::getSmarty();
        Users::_initUserInfo();
        set_query('safemode=','',true);
        
        /*
        if ( isset($_POST['save']) ){
        	
        	safeMode(true);
        	regSetSubscribed4news( $_GET['userID'], isset($_POST['subscribed4news'])?1:0 );
        	regSetCustgroupID( $_GET['userID'], $_POST['custgroupID'] );
        }
        
        if ( isset($_GET['deleteCustomerID']) ){
        	
        	if (CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
        		RedirectSQ('&safemode=yes&deleteCustomerID=');
        	}
        
        	regDeleteCustomer( $_GET['deleteCustomerID'] );
        	RedirectSQ('deleteCustomerID=&ukey=admin_users_list');
        }
        
        if(isset($_GET['activateID'])){
        	
        	if (CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
        		RedirectSQ('&activateID=&safemode=yes');
        	}
        	regActivateCustomer($_GET['activateID']);
        	RedirectSQ('activateID=');
        }
        */
        $customerEntry = new Customer;
        $customerEntry->loadByID($_GET['userID']);
        
        $customer_groups = GetAllCustGroups();
        
        $cust_group_name = '-';
        foreach($customer_groups as $group_info)
        {
            if($group_info['custgroupID'] == $customerEntry->custgroupID)
            {
                $cust_group_name = $group_info['custgroup_name'];
            };
        };

        $reg_fields = GetRegFields();
        $cust_reg_fields = GetRegFieldsValuesByCustomerID($customerEntry->customerID);

        foreach($reg_fields as $key => $reg_fld)
        {
            $f = false;
            foreach($cust_reg_fields as $cfld)
            {
                if($cfld['reg_field_ID'] == $reg_fld['reg_field_ID'])
                {
                    $reg_fields[$key]['reg_field_value'] = $cfld['reg_field_value'];
                    $f = true;
                    break;
                };
            };
            if(!$f) $reg_fields[$key]['reg_field_value'] = '';
        };
        
        $smarty->assign('eLink', array('title' => translate('btn_edit'), 'href' => 'javascript: void(0);', 'onclick' => 'showEditForm();'));
        
        $smarty->assign('customer_groups', $customer_groups);
        $smarty->assign('cust_group_name', $cust_group_name);
        $smarty->assign('reg_fields', $reg_fields);
        $smarty->assign('customerInfo', $customerEntry->getVars());
        
        $smarty->assign('UserInfoFile', 'backend/user_contact.html');    
    }
};

ActionsController::exec('EditCustomerContactInfo');

?>