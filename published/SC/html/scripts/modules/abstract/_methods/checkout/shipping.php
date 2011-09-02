<?php
class ShippingController extends ActionsController {

	function change_address(){
		
		$addressEntry = new Address();
		$addressEntry->loadByID($this->getData('addressID'));
		
		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		$customerEntry = &$checkoutEntry->customer();
		
		if($addressEntry->belong2Customer($customerEntry)){
			
			$checkoutEntry->shippingAddress($addressEntry);
			if(!CONF_ORDERING_REQUEST_BILLING_ADDRESS){
				$checkoutEntry->billingAddress($addressEntry);
			}
		}
		
		RedirectSQ('action=&addressID');
	}
	
	function select_shipping(){

		$checkoutEntry = Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		/*@var $checkoutEntry Checkout*/
		$checkoutEntry->formsData(array_merge($checkoutEntry->formsData(),$this->getData()));
		$checkoutEntry->shippingMethodID($this->getData('shippingMethodID'));
		$checkoutEntry->shippingServiceID($this->getData('shippingServiceID', $this->getData('shippingMethodID')));

		RedirectSQ('step=billing');
	}
	
	function main(){

		$Register = &Register::getInstance();

		$cartEntry = new ShoppingCart();
		$cartEntry->loadCurrentCart();
		
		$shipping_methods = shGetAllShippingMethods( true );
		if(count($shipping_methods) == 0){
			$stepManager = $Register->get('__STEPMANAGER');
			/*@var $stepManager StepManager */
			$stepManager->unregisterStep('shipping');
			storeWData(_CHECKOUT_STEPMANAGER, serialize($stepManager));
			RedirectSQ('step=billing');
		}
		
		$shipping_costs = array();

		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		
		$shippingAddress = $checkoutEntry->shippingAddress();
		$shipping_address = $shippingAddress->getVars();
		
		$addresses = array($shipping_address, $shipping_address);
		
		$order = $checkoutEntry->emulate_getOrder();
		$cart = $cartEntry->emulate_cartGetCartContent();

		$j = 0;
		foreach( $shipping_methods as $key => $shipping_method ){
			$_ShippingModule = ShippingRateCalculator::getInstance($shipping_method["module_id"]);
			/*@var $_ShippingModule ShippingRateCalculator*/
								
			if($_ShippingModule){
				
				if( $_ShippingModule->allow_shipping_to_address($shipping_address)){
					$shipping_costs[$j] = oaGetShippingCostTakingIntoTax( $cart, $shipping_method["SID"], $addresses, $order );
				}else
					$shipping_costs[$j] = array(array('rate'=>-1));
			}else{ //rate = freight charge
				$shipping_costs[$j] = oaGetShippingCostTakingIntoTax( $cart, $shipping_method["SID"], $addresses, $order );
			}
			$j++;
		}
		
		for($_i=count($shipping_costs)-1; $_i>=0; $_i-- ){
			for($_t = count($shipping_costs[$_i])-1; $_t>=0; $_t-- ){
				if($shipping_costs[$_i][$_t]['rate']>0){
					$shipping_costs[$_i][$_t]['rate'] = show_price($shipping_costs[$_i][$_t]['rate']);
				}else {
					if(count($shipping_costs[$_i]) == 1 && $shipping_costs[$_i][$_t]['rate']<0)
						$shipping_costs[$_i] = 'n/a';
					else
						$shipping_costs[$_i][$_t]['rate'] = '';
				}
			}
		}
		if( count($shipping_costs)==1 && is_array($shipping_costs[0]) && count($shipping_costs[0])==1 && !$shipping_costs[0][0]['rate']){
			
			$stepManager = $Register->get('__STEPMANAGER');
			/*@var $stepManager StepManager */
			$stepManager->unregisterStep('shipping');
			storeWData(_CHECKOUT_STEPMANAGER, serialize($stepManager));
			$this->__exec_cust(array(
				'action'=>'select_shipping', 
				'shippingMethodID' => $shipping_methods[0]['SID'], 
				'shippingServiceID' => array($shipping_methods[0]['SID'] =>  $shipping_costs[0][0]['id'])));
		}elseif(count($shipping_methods)&&!count($shipping_costs)){
			$shipping_methods = array();
		}
		

		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/

		$checkoutEntry->dropFormsData2Smarty();
		
		$customerEntry = &$checkoutEntry->customer();
	
		$smarty->assign('change_address_url', set_query(!$customerEntry->customerID?'step=your_info':"?ukey=change_address&view=&addressID={$shippingAddress->addressID}&return=".base64_encode(set_query(''))));
		$smarty->assign('shipping_address_str', $shippingAddress->getHTMLString());
		$smarty->assign('shipping_addressID', $shippingAddress->addressID);
		$smarty->assign("shipping_costs", $shipping_costs );
		$smarty->assign("shipping_methods", $shipping_methods );		
		$smarty->assign("shipping_methods_count", count($shipping_methods) );
		$smarty->assign('main_content_template', 'checkout.shipping.html');
	}
}
?>