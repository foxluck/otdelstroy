<?php
define('TBL_USERS', CUSTOMERS_TABLE);

class Users extends Module {
	
	function initSettings(){
		
		$this->Settings = array(
		
			'users_per_page' => array(
				'type' 		=> SETTING_NUMBER,
				'descr' 	=> 'Количество пользователей на странице',
				'value' 	=> 20,
				),
		);
	}
	
	function initInterfaces(){
		
		$this->Interfaces = array(
			'busers_list'=> array(
				'name' => 'Список пользователей (администрирование)',
				'type' => INTDIVAVAILABLE,
			),
			'bcontact_info'=> array(
				'name' => 'Контактная информация (администрирование)',
				'type' => INTDIVAVAILABLE,
			),
			'baddress_book'=> array(
				'name' => 'Адресная книга (администрирование)',
				'type' => INTDIVAVAILABLE,
			),
			'register_authorization'=> array(
				'name' => 'Авторизация',
				'type' => INTDIVAVAILABLE,
			),
			'register_activation'=> array(
				'name' => 'Активация пользователя',
				'type' => INTDIVAVAILABLE,
			),
		);
	}
	
	function updateCustomerInfo($customer_id, $customer_info)
	{
		$customer_id = intval($customer_id);
	    // 1. update table sc_customers
        $reg_fields = (array_key_exists('reg_field', $customer_info) ? $customer_info['reg_field'] : array());
        unset($customer_info['reg_field']);
        $allowedFields = array('first_name','last_name','city','address','zoneID','countryID',);
		foreach ($customer_info as $field => $value){
			if(!in_array($field,$allowedFields)){
				unset($addr_info[$field]);
			}
		}
	    
        $strings = array();
        foreach($customer_info as $field_name => $field_value)
        {
            if(!self::_check_field($field_name, $field_value)) continue;
            $strings[] = $field_name.' = \''.mysql_real_escape_string(xStripSlashesGPC($field_value)).'\'';
        };
        
        $ce = new Customer();
        $ce->loadByID($customer_id);
        $ce->loadFromArray($customer_info,true);
        $ce->save();
        /*
        if(!empty($strings))
        {
            $sql = "update ".CUSTOMERS_TABLE." set ".implode(', ', $strings)." where customerID = {$customer_id}";
            db_query($sql);
        };
          */  
	    // 2. update table sc_customer_reg_fields_values
	    $allowedRegFields = GetRegFields();
	    foreach ($allowedRegFields as $allowedRegField){
	    	$field_id = $allowedRegField['reg_field_ID'];
			if(!isset($reg_fields[$field_id])){
				continue;
	    	}
	    	$field_value = $reg_fields[$field_id];
			$sql = "replace into ".CUSTOMER_REG_FIELDS_VALUES_TABLE." ".
	               "(reg_field_ID, customerID, reg_field_value) ".
	               "values ({$field_id}, {$customer_id}, '".mysql_real_escape_string(xStripSlashesGPC($field_value))."')";
	        db_query($sql);
		}
	}
	
	function updateCustomerAddress($addr_id, $addr_info)
	{
		$allowedFields = array('first_name','last_name','city','address','zoneID','zip','state','countryID',);
		foreach ($addr_info as $field => $value){
			if(!in_array($field,$allowedFields)){
				unset($addr_info[$field]);
			}
		}
	    $strings = array();
	    foreach($addr_info as $field_name => $field_value)
	    {
	        $strings[] = $field_name.' = \''.addslashes($field_value).'\'';
	    };
	    
	    $sql = "update ".CUSTOMER_ADDRESSES_TABLE." set ".implode(', ', $strings)." where addressID = {$addr_id}";
	    db_query($sql);
	}
	
	function addCustomerAddress($customer_id,$addr_info)
	{
		$customer_id = intval($customer_id);
		$allowedFields = array('first_name','last_name','city','address','zoneID','zip','state','countryID',);
		foreach ($addr_info as $field => $value){
			if(!in_array($field,$allowedFields)){
				unset($addr_info[$field]);
			}
		}
		
		$sql = "insert into ".CUSTOMER_ADDRESSES_TABLE." (`customerID`, `".implode('`, `', array_keys($addr_info))."`) ".
	    	   "values ({$customer_id},'".implode("', '", array_map('addslashes', $addr_info))."')";
	    db_query($sql);
	}
	
	function delCustomerAddress($customer_id, $addr_id)
	{
		$customer_id = intval($customer_id);
		$addr_id = intval($addr_id);
	    $sql = "delete from ".CUSTOMER_ADDRESSES_TABLE." where addressID = {$addr_id}";
	    db_query($sql);
	    
	    $sql = "select addressID from ".CUSTOMERS_TABLE." where customerID = {$customer_id}";
	    $res = db_query($sql);
	    $row = db_fetch_assoc($res);
	    $def_addr_id = $row['addressID'];
	    
	    if($def_addr_id == $addr_id)
	    {
	        $sql = "select addressID from ".CUSTOMER_ADDRESSES_TABLE." where customerID = {$customer_id} order by addressID limit 1";
    	    $res = db_query($sql);
    	    $row = db_fetch_assoc($res);
    	    $new_def_id = $row['addressID'];
    	    
    	    $sql = "update ".CUSTOMERS_TABLE." set addressID = {$new_def_id} where customerID = {$customer_id}";
    	    db_query($sql);
	    };
	}
	
	function setDefaultCustomerAddress($customer_id, $addr_id)
	{
		$customer_id = intval($customer_id);
		$addr_id = intval($addr_id);
	    $sql = "update ".CUSTOMERS_TABLE." set addressID = {$addr_id} where customerID = {$customer_id}";
	    db_query($sql);
	}
	
	function _check_field($field_name, $field_value)
	{
	    switch($field_name)
	    {
	        case 'first_name':
	        case 'last_name':
	            return (trim($field_value) != '');
	            break;
	        case 'email':
	            return preg_match(self::EMAIL_RX, $field_value);
	            break;
	        default:
	            return true;
	            break;
	    };
	}

	function _initUserInfo()
	{
		$smarty = &Core::getSmarty();
		$CurrentDivision = &Core::getCurrentDivision();
		
		$smarty->assign('sub_template', 'backend/user_info.html');
		$customerEntry = new Customer;
		if($customerEntry->loadByID($_GET['userID'])){
			$UserAccountDivs = &DivisionModule::getChildDivisions(DivisionModule::getDivisionIDByUnicKey('user_info'), array('xEnabled'=>1));
			$_TC = count($UserAccountDivs);
			$UserAccountDivsInfo = array();
			for($j = 0; $j<$_TC;$j++){
				
				$UserAccountDivsInfo[] = array(
					'name' => $UserAccountDivs[$j]->Name,
					'id' => $UserAccountDivs[$j]->ID,
				    'ukey' => $UserAccountDivs[$j]->UnicKey
				);
			}
			$smarty->assign('UserAccountDivs', $UserAccountDivsInfo);
			
			
			
			$smarty->assign( 'customer_name', $customerEntry->first_name.' '.$customerEntry->last_name );
			$smarty->assign('customer_orders_count', $customerEntry->getOrdersNumber());
			
			
		}else{
			$smarty->assign('user_info_not_found','1');
			$smarty->assign( 'customer_name',translate('lbl_not_found'));
			
		}
		
	}
	
	const EMAIL_RX = '/^[\-_A-Za-z0-9]+[\.\-_A-Za-z0-9]*?@[\-_A-Za-z0-9]+[\.\-_A-Za-z0-9]*?\.[A-Za-z0-9]{2,6}$/';
}
?>