<?php
class OrderDetailedController extends ActionsController{
	
	function post_comment(){

		$orderEntry = new Order();
		$res = $orderEntry->loadByID($this->getData('orderID'));
		if($res !== true)RedirectSQ('did='.$_GET['rdid'].'&orders_detailed=&orderID=');

		$comment = trim($this->getData('comment'));
		if(!$comment)RedirectSQ('');
		ostSetOrderStatusToOrder($orderEntry->orderID, $orderEntry->statusID, translate('ordr_added_comment').': '.$comment, ($this->getData('notify_customer')?1:0), true);
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'order_action_id=&status_comments=&notify_customer=', 'ordr_order_changed');
	}
	
	function exec_order_action(){
		
		$orderEntry = new Order();
		$res = $orderEntry->loadByID($this->getData('orderID'));
		if($res !== true)RedirectSQ('did='.$_GET['rdid'].'&orders_detailed=&orderID=');
		$notify_customer = true;
		$notify_customer = (intval($this->getData('notify_customer')) == 1);
		if($notify_customer){
			$status_comments = trim($this->getData('status_comments'));
			if(strlen($status_comments))$status_comments = " \n".translate('ordr_added_comment').': '.$status_comments;
			if(!$status_comments)$status_comments='';
		}else{
			$status_comments = '';
		}
		$res = $orderEntry->exec_action($this->getData('order_action_id'), ORDACTION_SOURCE_ADMIN, $status_comments,$notify_customer);
		if(PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, 'order_action_id=', $res->getMessage());
		
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'order_action_id=&status_comments=&notify_customer=', 'ordr_order_changed');
	}
	
	function set_custom_status(){
		
		$custom_order_statuses = ost_getOrderStatuses(false);
		
		foreach ($custom_order_statuses as $_status){
			
			if($_status['statusID'] != $this->getData('statusID'))continue;
				
			$notify_customer = (intval($this->getData('notify_customer')) == 1);
			if($notify_customer){
				$status_comments = trim($this->getData('status_comments'));
				if(strlen($status_comments))$status_comments = " \n".translate('ordr_added_comment').': '.$status_comments;
				if(!$status_comments)$status_comments='';
			}else{
				$status_comments = '';
			}	
			ostSetOrderStatusToOrder($this->getData('orderID'), $this->getData('statusID'),str_replace('%STATUS_NAME%', ostGetOrderStatusName($this->getData('statusID')), translate('ordr_set_custom_status_comment')).$status_comments,$notify_customer);
			Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'order_action_id=&status_comments=&notify_customer=', 'ordr_order_changed');
		}
		
		RedirectSQ('');
	}
	
	function main(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		
		$orderID = $this->getData('orderID');
		if(!$orderID)RedirectSQ('did='.$_GET['rdid'].'&orders_detailed=&orderID=');

		$olist_url = xHtmlSetQuery('ukey=admin_orders_list&rdid=');
		$oedit_url = xHtmlSetQuery('','index.php?did='.$_GET['rdid'].'&ukey=order_editor&order_id='.$orderID);

		setcookie('olist_url',base64_encode(gzdeflate($olist_url, 9)));
		
		$order = ordGetOrder($orderID);
		$order['discount_description'] = preg_replace_callback("/\{\%(\w+)\%\}/"
		    ,create_function('$matches','return translate($matches[1]);')
		    ,$order['discount_description']);
		
		$order_status_report = stGetOrderStatusReport( $orderID );
		
		$paymentModule = PaymentModule::getInstance($order['payment_module_id']);
		/*@var $paymentModule PaymentModule*/
		if(!is_object($paymentModule)){
			
			$paymentModule = new PaymentModule();
		}
		
		
		$shippingtModule = ShippingRateCalculator::getInstance($order['shipping_module_id']);
		/*@var $shippingtModule ShippingRateCalculator*/
		if(!is_object($shippingtModule)){
			$shippingtModule = new ShippingRateCalculator();
		}
		$curr_language = LanguagesManager::getCurrentLanguage();
		/*@var $curr_language Language*/ 
		$lang_iso2 = $curr_language->iso2;//sc_getSessionData('LANGUAGE_ISO3');
		$_language = '';
		if($lang_iso2 == 'ru'){
			$_language = 'rus';
		}elseif($lang_iso2 == 'en'){
			$_language = 'eng';
		}
		
		$properties = array('language'=>$_language);
		$print_forms = array_merge(
			Forms::listConnectedModules(array('type'=>Forms::GENERIC_FORM,'language'=>$_language)),
			Forms::listConnectedModules(array('type'=>Forms::MODULE_FORM,'sub_type'=>$paymentModule->getConnectedPrintforms())),//$properties),
			Forms::listConnectedModules(array('type'=>Forms::MODULE_FORM,'sub_type'=>$shippingtModule->getConnectedPrintforms()))//$properties)
			);
		$available_actions_ids = $paymentModule->getAllowedOrderActions($order);
		
		$order_actions = ord_getOrderActionsInfo($available_actions_ids);
		$custom_order_statuses = ost_getOrderStatuses(false);
		
		$predefined_statuses = ost_getOrderStatuses(true);
		foreach($order_actions as $k => $v)
		{
		    if(array_key_exists($v['status_style'], $predefined_statuses))
		    {
		        $order_actions[$k]['_style'] = $predefined_statuses[$v['status_style']]['_style'];
		    };
		};
		
		$order_content = ordGetOrderContent($orderID);
		ordCalculateOrderTax($order, $order_content);
		
		$order['shipping_address_js'] = str_replace(array("\n","\r"), " ", $order['shipping_address']);
		$order['billing_address_js'] = str_replace(array("\n", "\r"), " ", $order['billing_address']);
		
		$smarty->assign( 'orderContent', $order_content);
		$smarty->assign( 'order', $order );
		$smarty->assign( 'order_status_report', $order_status_report );
		$smarty->assign( 'custom_order_statuses', $custom_order_statuses );
		$smarty->assign( 'order_actions', $order_actions);
		$smarty->assign( 'order_detailed', 1 );
		$smarty->assign('print_forms',$print_forms);
		
		$currentLanguage = LanguagesManager::getCurrentLanguage();
		$smarty->assign('invoice_lang', $currentLanguage->iso2);
		
		$smarty->assign('olist_url', $olist_url);
		$smarty->assign('edit_url', $oedit_url);
		$smarty->assign('customer_login', regGetLoginById($order['customerID']));
		
		$smarty->assign('admin_sub_dpt', 'order_detailed.html');		
	}
}

ActionsController::exec('OrderDetailedController');
?>