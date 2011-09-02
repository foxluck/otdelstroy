<?php
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	
	$GetVars = &$Register->get(VAR_GET);
	$PostVars = &$Register->get(VAR_POST);
	
	$PID = isset($GetVars['PID'])?$GetVars['PID']:'';
	
	if (isset($GetVars['delete'])){ //delete payment type

		if (CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
			RedirectSQ( 'delete=&safemode=yes' );
		}
		payDeletePaymentMethod($PID);
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?ukey=pmethod_list', '__Method was deleted__');
	}
	
	$shipping_methods = shGetAllShippingMethods();
	
	if(isset($PostVars['save'])){
		
		if (CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
			RedirectSQ( 'safemode=yes' );
		}
		$new_method = false;
		if($PID){
			
			payUpdatePaymentMethod( $PID, $PostVars['name'], $PostVars['description'], isset($PostVars['enabled'])?1:0, 
				(int)$PostVars['sort_order'], $PostVars['module_id'], $PostVars['email_comments_text'], isset($PostVars['calculate_tax'])?1:0 );
			payResetPaymentShippingMethods( $PID );
			
		}else{
			
			$new_method = true;
		 	$PID = payAddPaymentMethod($PostVars['name'], $PostVars['description'], isset($PostVars['enabled'])?1:0, 
				(int)$PostVars['sort_order'], $PostVars['email_comments_text'], $PostVars['module_id'], isset($PostVars['calculate_tax'])?1:0 );
		}
		
		foreach( $shipping_methods as $shipping_method ){
			
			if ( isset($PostVars['ShippingMethodsToAllow_'.$shipping_method['SID']]) )
				paySetPaymentShippingMethod( $PID, $shipping_method['SID'] );
		}
		
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?ukey=pmethod_list', $new_method?'__Method was created__':'__Method was saved__');
	}
	
	$payment_method = payGetPaymentMethodById($PID);
	
	if($payment_method['PID']!=$PID){
		$PID = $payment_method['PID'];
		renderURL('PID='.$PID,'',true);
	}
	
	$payment_method['ShippingMethodsToAllow'] = _getShippingMethodsToAllow( $payment_method['PID'] );

	$smarty->assign('payment_method', $payment_method);
	$smarty->assign('admin_sub_dpt', 'admin_addmod_pmethod.html');
	$smarty->assign('shipping_methods', $shipping_methods );
	$smarty->assign('payment_modules', modGetAllInstalledModuleObjs(PAYMENT_MODULE) );
?>