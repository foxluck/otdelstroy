<?php
class CustomerGroup extends DBObject {
	
	var $custgroupID;
	var $custgroup_discount;
	var $sort_order;
	var $custgroup_name;
	
	var $__primary_key = 'custgroupID';
	var $__db_table = CUSTGROUPS_TABLE;	
}

class Customer extends DBObject {
	
	var $customerID;
	var $Login;
	var $cust_password;
	var $Email;
	var $first_name;
	var $last_name;
	var $subscribed4news;
	var $custgroupID;
	/**
	 * Default address id
	 *
	 * @var int
	 */
	var $addressID;
	var $reg_datetime;
	/**
	 * Customer selected currency id
	 *
	 * @var int
	 */
	var $CID;
	var $affiliateID;
	var $affiliateEmailOrders;
	var $affiliateEmailPayments;
	var $ActivationCode;
	var $vkontakte_id;

	var $_custom_fields = array();
	
	var $__primary_key = 'customerID';
	var $__db_table = CUSTOMERS_TABLE;	
	
	function loadByID($customerID){
		
		$res = parent::loadByID($customerID);
		
		$this->cust_password = Crypt::PasswordDeCrypt( $this->cust_password, null );
		
		if (CONF_BACKEND_SAFEMODE)$this->Email = translate("msg_safemode_info_blocked");
		
		return $res;
	}
	
	function loadByLogin($Login){

		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/*@var $DBHandler DataBase*/

		$DBRes = $DBHandler->ph_query('SELECT * FROM ?#CUSTOMERS_TABLE WHERE Login=?', $Login);
		if(!$DBRes->getNumRows())return false;

		$this->loadFromArray($DBRes->fetchAssoc());
		
		$this->cust_password = Crypt::PasswordDeCrypt( $this->cust_password, null );
		
		if (CONF_BACKEND_SAFEMODE)$this->Email = translate("msg_safemode_info_blocked");
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	function save(){
		
		$_cust_password = $this->cust_password;
		$this->cust_password = Crypt::PasswordCrypt($this->cust_password, null);
		
		if(!$this->customerID){
			
			if(is_null($this->reg_datetime))$this->reg_datetime = date('Y-m-d H:i:s');
			if(is_null($this->custgroupID))$this->custgroupID = CONF_DEFAULT_CUSTOMER_GROUP;
			
		}
		if($this->subscribed4news){
			subscrAddUnRegisteredCustomerEmail($this->Email);
		}elseif($this->customerID){
			subscrUnsubscribeSubscriberByEmail(base64_encode($this->Email));
		}
		$res = parent::save();
		
		$this->cust_password = $_cust_password;
		
		return $res;
	}
	
	/**
	 * @return PEAR_Error
	 */
	function checkInfo($scheme = null){
		
		$res = parent::checkInfo($scheme);
		if(PEAR::isError($res))return $res;
		
		if(!$this->first_name)
			return PEAR::raiseError('err_input_name', null, null, null, 'first_name');
		if(!$this->last_name)
			return PEAR::raiseError('err_input_name', null, null, null, 'last_name');
		if(!$this->Email)
			return PEAR::raiseError('err_input_email', null, null, null, 'Email');
		if(!valid_email($this->Email))
			return PEAR::raiseError('err_input_email', null, null, null, 'Email');
		
			
		switch ($scheme){
			case 'required_loginpass':
				if(!$this->Login)
					return PEAR::raiseError('err_input_login', null, null, null, 'Login');
				if(!$this->cust_password)
					return PEAR::raiseError('err_input_password', null, null, null, 'cust_password');

				if(!$this->customerID && $this->Login){
					
					$replCustomer = new Customer();
					$replCustomer->loadByLogin($this->Login);
					if($replCustomer->loadByLogin($this->Login)){
						return PEAR::raiseError(translate("err_user_already_exists"));
					}
				}
				
				$dbq = 'SELECT 1 FROM ?#CUSTOMERS_TABLE WHERE Email=? AND login<>"" AND login<>?';
			
				$is_free_email = !db_phquery_fetch(DBRFETCH_FIRST, $dbq, $this->Email, $this->Login);
				if(!$is_free_email){
					return PEAR::raiseError(translate('err_occupied_email'));
				}
				
				break;
		}
		
		$custom_fields = GetRegFields();

		foreach ($custom_fields as $_field){
			
			if(!$_field['reg_field_required'])continue;
			if($this->_custom_fields[$_field['reg_field_ID']])continue;
				
			return PEAR::raiseError('err_input_all_required_fields', null, null, null, "_custom_fields[{$_field['reg_field_ID']}]");
		}
	}

	/**
	 * @return Customer | null
	 */
	static function getAuthedInstance(){

		static $customerEntry = null;
		if(!is_object($customerEntry) && isset($_SESSION['log'])&&$_SESSION['log']){
			 
			$customerEntry = new Customer;
			if(!$customerEntry->loadByLogin($_SESSION['log']))return null;
		}
		
		return $customerEntry;
	}

	function getAddressesNumber(){
		
		return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(addressID) FROM ?#CUSTOMER_ADDRESSES_TABLE WHERE customerID=?', $this->customerID);
	}

	function getOrdersNumber(){
		
		return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(orderID) FROM ?#ORDERS_TABLE WHERE customerID=?', $this->customerID);
	}

	function getOrdersSum($order_status = null)
	{
	    $sql = "select sum(order_amount) as orders_sum from ?#ORDERS_TABLE where customerID=?";
	    $sql .= ($order_status? ' and statusID=?':'');
	    $res = db_phquery($sql,$this->customerID,$order_status);
	    $row = db_fetch_assoc($res);
	    return floatval($row['orders_sum']);
	}
	
	function saveCustomFields(){

		$custom_fields = GetRegFields();
		foreach ($custom_fields as $_field){
			
			$this->setCustomField($_field['reg_field_ID'], isset($this->_custom_fields[$_field['reg_field_ID']])?$this->_custom_fields[$_field['reg_field_ID']]:'');
		}
		
	}
	
	function setCustomField($reg_field_ID, $reg_field_value){
		
		$sql = '
			SELECT COUNT(*) FROM ?#CUSTOMER_REG_FIELDS_VALUES_TABLE WHERE reg_field_ID=? AND customerID=?
		';
	
		$q=db_phquery( $sql, $reg_field_ID, $this->customerID );
		$r=db_fetch_row($q);
		if ( $r[0] == 0 ){
			
			if ( trim($reg_field_value) == "" )return;
			
			$sql = '
				INSERT ?#CUSTOMER_REG_FIELDS_VALUES_TABLE (reg_field_ID, customerID, reg_field_value) VALUES(?,?,?)
			';
			db_phquery($sql,$reg_field_ID,$this->customerID,$reg_field_value);
		}else{
			
			if ( trim($reg_field_value) == "" ){
				
				$sql = '
					DELETE FROM ?#CUSTOMER_REG_FIELDS_VALUES_TABLE WHERE reg_field_ID=? AND customerID=?
				';
				db_phquery($sql,$reg_field_ID,$this->customerID);
			}else{
				
				$sql = '
					UPDATE ?#CUSTOMER_REG_FIELDS_VALUES_TABLE SET reg_field_value=? WHERE reg_field_ID=? AND customerID=?
				';
				db_phquery($sql,$reg_field_value,$reg_field_ID,$this->customerID);
			}
		}
	}
	
	public static function confOrderStatusAccess()
	{
		$types = array();
		$types[] = array('title'=>translate('cfg_strict_access_lastname'),'value'=>'lastname');
		//$types[] = array('title'=>translate('cfg_strict_access_captcha'),'value'=>'captcha');
		$types[] = array('title'=>translate('cfg_strict_access_code'),'value'=>'code');
		$types[] = array('title'=>translate('cfg_strict_access_auth'),'value'=>'auth');
		return $types;
	}
}
?>