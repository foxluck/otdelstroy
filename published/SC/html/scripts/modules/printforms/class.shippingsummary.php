<?php
/**
 * @connect_module_class_name ShippingSummary
 * @package DynamicModules
 * @subpackage PrintForms
 * @_type generic
 * @_side backend
 * @_name PRINTFORMS_SHIPPING_SUMMARY_NAME
 * @_description PRINTFORMS_SHIPPING_SUMMARY_DESCRIPTION
 */
class ShippingSummary extends Forms
{
	function _initSettingFields()
	{
		parent::_initSettingFields();
	}

	function display($strict = true)
	{
		if($orderID = intval($_GET["orderID"])){
			$order = ordGetOrder($orderID);
		}else{
			$order = false;
		}
		if($order&&$strict&&!$this->verifyOrderData($order)){
			unset($order);
		}
		if($order){
			$customer = array();
			foreach($order['reg_fields_values'] as $customer_fields){
				$customer[$customer_fields['reg_field_name']] = $customer_fields['reg_field_value'];
			}
			ordPrepareOrderInfo($order);
			foreach($order as $id=>$order_){
				if(is_int($id)){
					unset($order[$id]);
				}
			}
			$order['date_print'] = Time::standartTime($order['order_time_mysql'],false);
			$order['current_date_print'] = Time::standartTime(null,false);
			$order['paid_date'] = $order['current_date_print'];	
			if(($order['statusID'] == 5)){
				
				if(count($order_status_report = stGetOrderStatusReport( $orderID,false))){
					$order['paid_date'] = $order_status_report[0]['status_change_time'];
				}
			}
				
			$order['shipping_address'] = str_replace(array("\n","\r"),array('',''),$order['shipping_address']);
			$order['shipping_name'] = $order['shipping_firstname'].' '.$order['shipping_lastname'];
			$order['billing_name'] = $order['billing_firstname'].' '.$order['billing_lastname'];
			$order['billing_address'] = str_replace(array("\n","\r"),array('',''),$order['billing_address']);
			$order_content = ordGetOrderContent($orderID);
			
			$smarty = &Core::getSmarty();
			/*@var $smarty Smarty */


			$smarty->assign('order',$order);
			$smarty->assign('order_content',$order_content);
			$smarty->assign('customer',$customer);

		}
		parent::display($strict);//use shippingsummary.html
	}
}
?>