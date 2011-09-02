<?php
	define('CHECKOUTVIEW_FADE', 'fade');
	define('CHECKOUTVIEW_FRAME', 'frame');
	define('CHECKOUTVIEW_WIDGET', 'widget');
	$Register = &Register::getInstance();
	/*@var $Register Register*/

	if(CONF_PROTECTED_CONNECTION){
		
		$urlEntry = &$Register->get(VAR_URL);
		/*@var $urlEntry Url*/
		if($urlEntry->getScheme() !== 'https'){
			
			$urlEntry->setScheme('https');
			$urlEntry->redirect();
		}
	}

	$smarty = &$Register->get(VAR_SMARTY);
	/*@var $smarty Smarty*/
	
	$cartEntry = new ShoppingCart();
	$cartEntry = &$cartEntry;
	$cartEntry->loadCurrentCart();
	
	$current_dir = dirname(__FILE__);
	
	// Все проверки происходят в class ShoppingCartController
	//if(!cartCheckMinOrderAmount())RedirectSQ('?ukey=cart&cartCheckMinOrderAmount=1');//&view=frame
	//if(!cartCheckMinTotalOrderAmount())RedirectSQ('?ukey=cart&cartCheckMinTotalOrderAmount=');//&view=frame
	
	if(issetWData(_CHECKOUT_STEPMANAGER)){
		$stepManager = unserialize(loadWData(_CHECKOUT_STEPMANAGER));
	}else{
		$stepManager = new StepManager;
	}
	/*@var $stepManager StepManager*/
	
	$stepManager->StepDir = $current_dir.'/checkout';
	$stepManager->default_step = 'your_info';
	$stepManager->allowed_steps = array('your_info', 'shipping', 'billing', 'confirmation', 'success');
	$stepManager->init();
	
	if($cartEntry->isEmpty() && $stepManager->getStepStatus('success') !== 'current')RedirectSQ('?ukey=cart&view=frame');
	
	$customerEntry = Customer::getAuthedInstance();
	$is_quick_checkout = is_null($customerEntry);
	$steps_chain = array();

	$currentStep = $stepManager->getCurrentStep();
	$current_step_key = is_object($currentStep)?$currentStep->getKey():'';
	$current_step_key_index = $current_step_key?array_search($current_step_key, $stepManager->allowed_steps):-1;
	
	$chain_links_titles = array('your_info' => 'checkout_yourinfo_header', 'shipping' => translate("checkout_shipping"), 'billing' => translate("ordr_payment_type"), 'confirmation' => translate("ordr_order_confirmation"));

	$payment_methods = payGetAllPaymentMethods(true);

	foreach($stepManager->allowed_steps as $_i => $step_key){
		 
		if($step_key == 'your_info' && !$is_quick_checkout)continue;
		$step_status = $stepManager->getStepStatus($step_key);

		if($step_status == 'ahead' && $_i<$current_step_key_index)continue;
		if($step_status == 'passed' && $_i>$current_step_key_index)$step_status = 'ahead';
		if(count($payment_methods)<=1 && $step_key=='billing')continue;
		
		if($step_status == 'current'){
			
			$steps_names = array(
				'your_info' => 'checkout_yourinfo_header',
				'shipping' => 'checkout_shipping',
				'billing' => 'ordr_payment_type',
				'confirmation' => 'ordr_order_confirmation',
				'success' => 'checkout_success_title',
				);
			$smarty->assign('step_title', $steps_names[$step_key]);
		}
		if($step_key == 'success')continue;
		
		$chain_link = array(
			'image' => URL_IMAGES.'/order_'.$step_key.'_'.($step_status == 'current' || ($step_status=='passed'&&$step_key=='confirmation')?'colored':($step_status == 'ahead'?'ahead':'passed')).'.gif',
			'url' => $current_step_key!='success'&&$step_status == 'passed'?renderURL('step='.$step_key):null,
			'status' => $step_status,
			'title' => $chain_links_titles[$step_key]
		);
		
		$steps_chain[] = $chain_link;
	}
	storeWData('__CHECKOUT_CHAIN', $steps_chain);
	$smarty->assign('steps_chain', $steps_chain);
	
	$Register->assign('__STEPMANAGER', $stepManager);
	function _detect_cart_view(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		if($smarty->get_template_vars('PAGE_VIEW') !== 'noframe')return CHECKOUTVIEW_FRAME;
		if($Register->is_set('widgets')&&$Register->get('widgets'))return CHECKOUTVIEW_WIDGET;
		if($smarty->get_template_vars('PAGE_VIEW') == 'noframe')return CHECKOUTVIEW_FADE;
		
		return CHECKOUTVIEW_FRAME;
	}
	
	
	$stepManager->exec();
	$cart_view = _detect_cart_view();
	$smarty->assign('checkout_template', $smarty->get_template_vars('main_content_template'));
	$smarty->assign('main_content_template', 'checkout.frame.html');
	$smarty->assign('main_body_style','style="'.(((CONF_SHOPPING_CART_VIEW==2)||($cart_view==CHECKOUTVIEW_FRAME))?'':'background:#FFFFFF;').'min-width:auto;width:auto;_width:auto;"');
	
	storeWData(_CHECKOUT_STEPMANAGER, serialize($stepManager));
?>