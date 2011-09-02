<?php
class ChangeAddressController extends ActionsController{
	
	function select_address(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$GetVars = &$Register->get(VAR_GET);
		
		if(!$this->getData('addressID')){
			
			$addressEntry = new Address();
			$addressEntry->loadFromArray($this->getData('address'), true);
			
			$res = $addressEntry->checkInfo();
			if(PEAR::isError($res)){
				
				storeWData('__address', $this->getData('address'));
				storeWData('__addressID', $this->getData('addressID'));
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
			}
			
			$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
			$customerEntry = &$checkoutEntry->customer();
			
			$addressEntry->customerID = $customerEntry->customerID;
			$addressEntry->save();
			$this->setData('addressID', $addressEntry->addressID); 
		}
		
		unsetWData('__address');
		unsetWData('__addressID');
		Redirect(set_query('action=change_address&addressID='.$this->getData('addressID'), base64_decode($GetVars['return'])));
	}
	
	function change_country(){
		
		storeWData('__address', $this->getData('address'));
		storeWData('__addressID', $this->getData('addressID'));
		RedirectSQ();
	}
	
	function main(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		$GetVars = &$Register->get(VAR_GET);

		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		$customerEntry = &$checkoutEntry->customer();
		
		if(!$customerEntry->customerID){
			Redirect(base64_decode($GetVars['return']));			
		}
		
		$t_addresses = regGetAllAddressesByID($customerEntry->customerID);
		$addresses = array();

		if(is_array($t_addresses))foreach( $t_addresses as $_address ){
			
			$addressEntry = new Address();
			$addressEntry->loadFromArray($_address);
			$addresses[] = array(
				'strAddress'	=> $addressEntry->getHTMLString(),
				'addressID'	=> $addressEntry->addressID
			);
		}

		$smarty->assign( 'addresses', $addresses );
		
		$address = issetWData('__address')?loadWData('__address'):array('countryID' => CONF_DEFAULT_COUNTRY);
		$addressID = $this->getData('addressID');
		$addressID = issetWData('__addressID')?loadWData('__addressID'):$addressID;
		
		$zones = array('address' => znGetZonesById( $address['countryID']));
		$smarty->assign('zones',$zones);
		$count_row = 0;
		$smarty->assign('countries', cnGetCountries( array(), $count_row, null));
		$smarty->assign('addressID', $addressID);
		$smarty->assign('address', $address);
		$smarty->assign('steps_chain', loadWData('__CHECKOUT_CHAIN'));
		
		$smarty->assign('main_content_template', 'change_address.html');
		unsetWData('__address');
		unsetWData('__addressID');
	}
}

ActionsController::exec('ChangeAddressController');
?>