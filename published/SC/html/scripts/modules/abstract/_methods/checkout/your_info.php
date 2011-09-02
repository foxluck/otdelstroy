<?php
//looks like unused code
class YourInfoController extends ActionsController {
	
	/**
	 * @return PEAR_Error
	 */
	function __test_data(){
		
		/**
		* @features "Trial product"
		*/
//		if(isOverflowedCustomersNum())
//			PEAR::raiseError(str_replace('[NUM]', TRIAL_MAX_CUSTOMERS_NUM, TRIAL_STRING_CUSTOMERS_OVERFLOW).'<br />'.TRIAL_STRING_LIMITATIONS);
		/**
		* @features
		*/
		
		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		$forms_data = $checkoutEntry->formsData();
		
		$customerEntry = new Customer();
		$customerEntry->loadFromArray($forms_data['customer_info'], true);
		
		if(!$forms_data['permanent_registering']){
			$customerEntry->Login = '';
			$customerEntry->cust_password = '';
		}

		if($forms_data['permanent_registering'] && customerWithEmailExists($customerEntry->Email))RedirectSQ('email_exists='.$customerEntry->Email);
		
		$res = $customerEntry->checkInfo($forms_data['permanent_registering']?'required_loginpass':null);

		if(PEAR::isError($res))return $res;
		
		if($forms_data['permanent_registering'] && $customerEntry->cust_password != $forms_data['customer_info']['cust_password1'])
			return PEAR::raiseError(translate('err_password_confirm_failed'));
		
		$shippingAddress = new Address();
		$shippingAddress->loadFromArray($forms_data['shipping_address'], true);
		$res = $shippingAddress->checkInfo();
		if(PEAR::isError($res))return $res;

		if(CONF_ORDERING_REQUEST_BILLING_ADDRESS){
			$billingAddress = new Address();
			$billingAddress->loadFromArray($forms_data['billing_as_shipping']?$forms_data['shipping_address']:$forms_data['billing_address'], true);
			$res = $billingAddress->checkInfo();
			if(PEAR::isError($res))return $res;
		}
		
		if (isset($forms_data['customer_info']['affiliationLogin']) && $forms_data['customer_info']['affiliationLogin'] 
			&& !regIsRegister($forms_data['customer_info']['affiliationLogin']))return PEAR::raiseError(translate("err_wrong_referrer"), null, null, null, 'affiliationLogin');
	}
	
	function update_form(){
		
		$this->setData('billing_as_shipping', $this->getData('billing_as_shipping'));
		$this->setData('permanent_registering', $this->getData('permanent_registering'));
		
		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		$checkoutEntry->formsData($this->getData());
		
		RedirectSQ();
	}
	
	function process_customer_info(){
		
		$this->setData('billing_as_shipping', $this->getData('billing_as_shipping'));
		$this->setData('permanent_registering', $this->getData('permanent_registering'));
		
		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		$checkoutEntry->formsData($this->getData());
	
		$res = $this->__test_data();
		if(PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
		
		$i = new IValidator();
		if(CONF_ENABLE_CONFIRMATION_CODE && !$i->checkCode($this->getData('confirmation_code')))
			Message::raiseMessageRedirectSQ(MSG_ERROR, '', translate("err_wrong_ccode"));
		
		$customerEntry = new Customer();
		
		$customerEntry->loadFromArray($this->getData('customer_info'), true);
/**
 * @features "Affiliate program"
 */
		$customerEntry->affiliateID = 0;
		
		if($this->getData('customer_info', 'affiliationLogin')){
		
			$customerEntry->affiliateID = regGetIdByLogin($this->getData('customer_info', 'affiliationLogin'));
		}
/**
 * @features
 */
		
		$shippingAddress = new Address();
		$shippingAddress->loadFromArray($this->getData('shipping_address'), true);
		
		if(CONF_ORDERING_REQUEST_BILLING_ADDRESS){
			$billingAddress = new Address();
			$billingAddress->loadFromArray($this->getData('billing_as_shipping')?$this->getData('shipping_address'):$this->getData('billing_address'), true);
		}else{
			$billingAddress = clone($shippingAddress);
		}
		
		if(!$this->getData('permanent_registering')){
			$customerEntry->Login = '';
			$customerEntry->cust_password = '';
		}
		
		$checkoutEntry->customer($customerEntry);
		$checkoutEntry->shippingAddress($shippingAddress);
		$checkoutEntry->billingAddress($billingAddress);
		
		RedirectSQ('step=shipping');
	}
	
	function auth(){

		if ( regAuthenticate($this->getData('auth','Login'), $this->getData('auth','cust_password')) ){
			
			$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
			$checkoutEntry->shippingAddress('');
			$checkoutEntry->billingAddress('');
			RedirectSQ('step=shipping');
		}else{
			
			Message::raiseMessageRedirectSQ(MSG_ERROR, 'login_form=1', 'err_wrong_password', '', array('name' => 'auth', 'Data' => $this->getData()));
		}
	}
	
	function main(){
		
		if(isset($_SESSION['log'])&&$_SESSION['log'])RedirectSQ('step=shipping');
		
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		$messageEntry = &$Register->get(VAR_MESSAGE);
		/*@var $messageEntry Message*/

		$smarty->assign('additional_fields', GetRegFields());
		$smarty->assign('billing_as_shipping', 1);

		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		$checkoutEntry->dropFormsData2Smarty();
		
		$shipping_address = $smarty->get_template_vars('shipping_address');
		$billing_address = $smarty->get_template_vars('billing_address');
		$callBackParam = array();
		$count_row = 0;
		$smarty->assign('countries', cnGetCountries($callBackParam, $count_row) );
		$zones = array(
			'shipping_address' => znGetZonesById( isset($shipping_address['countryID'])&&$shipping_address['countryID']?$shipping_address['countryID']:CONF_DEFAULT_COUNTRY),
			'billing_address' => znGetZonesById( isset($billing_address['countryID'])&&$billing_address['countryID']?$billing_address['countryID']:CONF_DEFAULT_COUNTRY)
			);
		foreach($zones as $k=>$v)if(!count($v))unset($zones[$k]);
		$smarty->assign('zones', $zones);
		
		if(!Message::loadData2Smarty()){
			
			$smarty->assign('subscribed4news' ,1);
		}
		
		$smarty->assign('email_exists', $this->getData('email_exists'));
		$smarty->assign('login_form', $this->getData('login_form'));
		
		set_query('email_exists=&login_form=','',true);
		
/**
* @features "Affiliate program"
*/
		if(isset($_SESSION['s_RefererLogin']))$smarty->assign('SessionRefererLogin', $_SESSION['s_RefererLogin']);
/**
 * @features
 */
		
		$smarty->assign('main_content_template', 'checkout.your_info.html');
	}
}
?>