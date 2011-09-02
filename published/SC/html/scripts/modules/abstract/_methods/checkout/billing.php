<?php
class BillingController extends ActionsController {

	function change_address(){
		
		$addressEntry = new Address();
		$addressEntry->loadByID($this->getData('addressID'));
		
		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		/*@var $checkoutEntry Checkout*/
		$customerEntry = &$checkoutEntry->customer();
		
		if($addressEntry->belong2Customer($customerEntry)){
			
			$checkoutEntry->billingAddress($addressEntry);
		}
		
		RedirectSQ('action=&addressID');
	}
	
	function select_payment(){
		
		$checkoutEntry = &Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		$checkoutEntry->paymentMethodID($this->getData('paymentMethodID'));
		$checkoutEntry->formsData($this->getData());
		
		RedirectSQ('step=confirmation');
	}
	
	function main(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		$checkoutEntry = Checkout::getInstance(_CHECKOUT_INSTANCE_NAME);
		/*@var $checkoutEntry Checkout*/

		if($checkoutEntry->shippingMethodID() == 0){//no shipping methods available => show all available payment types
			
			$payment_methods = payGetAllPaymentMethods(true);
		}else{
			$_payment_methods = payGetAllPaymentMethods(true);
			$payment_methods = array();
			foreach( $_payment_methods as $payment_method ){
				
				$shippingMethodsToAllow = false;
				foreach( $payment_method['ShippingMethodsToAllow'] as $ShippingMethod ){
					if ( ((int)$checkoutEntry->shippingMethodID() == (int)$ShippingMethod['SID']) && $ShippingMethod['allow'] ){
						
						$shippingMethodsToAllow = true;
						break;
					}
				}
		
				if ( $shippingMethodsToAllow )$payment_methods[] = $payment_method;
			}
		}
		
		if(count($payment_methods)==0){
			$stepManager = $Register->get('__STEPMANAGER');
			/*@var $stepManager StepManager */
			$stepManager->unregisterStep('billing');
			storeWData(_CHECKOUT_STEPMANAGER, serialize($stepManager));
			$this->__exec_cust(array('action'=>'select_payment', 'paymentMethodID' => null));
			//RedirectSQ('step=confirmation');
		}
		
		if(count($payment_methods)==1){
			
			$stepManager = $Register->get('__STEPMANAGER');
			/*@var $stepManager StepManager */
			$stepManager->unregisterStep('billing');
			storeWData(_CHECKOUT_STEPMANAGER, serialize($stepManager));
			$this->__exec_cust(array('action'=>'select_payment', 'paymentMethodID' => $payment_methods[0]['PID']));
		}
		
		$customerEntry = Customer::getAuthedInstance();
		
		$checkoutEntry->dropFormsData2Smarty();
		$billingAddress = &$checkoutEntry->billingAddress();
		$smarty->assign('change_address_url', set_query(!($customerEntry instanceof Customer)||!$customerEntry->customerID?'step=your_info':"?ukey=change_address&view=&addressID={$billingAddress->addressID}&return=".base64_encode(set_query(''))));
		$smarty->assign('billing_address_str', $billingAddress->getHTMLString());
		$smarty->assign('billing_addressID', $billingAddress->addressID);
		$smarty->assign('payment_methods', $payment_methods);
		$smarty->assign('main_content_template', 'checkout.billing.html');
	}
}
?>