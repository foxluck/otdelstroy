<?php
//TODO: finalize method for this class (use functions from order_functions.php
ClassManager::includeClass('shoppingcart');

class Order extends DBObject{

	var $orderID;
	var $statusID;
	var $payment_module_id;
	var $shipping_module_id;
	var $order_amount;
	var $currency_code;
	var $currency_value;
	var $payment_type;
	var $shipping_type;
	var $shipping_country;
	var $shipping_zip;
	var $shipping_city;
	var $source;

	var $__primary_key = 'orderID';
	var $__db_table = ORDERS_TABLE;

	/**
	 * @return PaymentModule
	 */
	function getPaymentModule(){
			
		$paymentModule = PaymentModule::getInstance($this->payment_module_id);
		if(!is_object($paymentModule))$paymentModule = new PaymentModule();
			
		return $paymentModule;
	}

	/**
	 * @return PaymentModule
	 */
	function getShippingModule(){
			
		$shippingtModule = ShippingRateCalculator::getInstance($this->shipping_module_id);
		if(!is_object($shippingtModule))$shippingtModule = new ShippingRateCalculator();
			
		return $shippingtModule;
	}

	function set_status($status_id, $source){
			
		;
	}

	function add_comment($comment,$notify_customer = false){
			
		;
	}

	function exec_action($action, $source){
			
		$args = func_get_args();
		$action = array_shift($args);
			
		$paymentModule = $this->getPaymentModule();
		if(!$paymentModule->isAllowedOrderAction($action, $this))
		return PEAR::raiseError('ordr_forbidden_order_action');

		//			$res = call_user_method_array('on'.$action, $paymentModule, $args);
		$res = call_user_func_array(array(&$paymentModule,'on'.$action),$args);
			
		if(PEAR::isError($res))return $res;
			
		//PHP 4.10			$res = call_user_method_array($action, &$this, $args);
		//print "Action = {$action}<br>";
		$res = call_user_func_array(array(&$this,$action),$args);
		if(PEAR::isError($res))return $res;
	}

	function cancel($source, $comment = '',$notify = true){
			
		ostSetOrderStatusToOrder($this->orderID, CONF_ORDSTATUS_CANCELLED, translate('ordr_comment_canceled_by').' '.translate('ordr_action_source_'.$source, false).$comment,$notify);
	}

	function process($source, $comment = '',$notify = true){
			
		ostSetOrderStatusToOrder($this->orderID, CONF_ORDSTATUS_PROCESSING, translate('ordr_comment_processing_order').$comment,$notify);
	}

	function restore($source, $comment = '',$notify = true){
			
		ostSetOrderStatusToOrder($this->orderID, CONF_ORDSTATUS_PROCESSING, translate('ordr_comment_restore').$comment,$notify);
	}

	function deliver($source, $comment = '',$notify = true){
			
		ostSetOrderStatusToOrder($this->orderID, CONF_ORDSTATUS_DELIVERED, translate('ordr_comment_delivered').$comment,$notify);
	}

	function refund($source, $refund_amount = 0, $comment = '',$notify = true){
			
		ostSetOrderStatusToOrder($this->orderID, CONF_ORDSTATUS_REFUNDED, str_replace('%REFUND_AMOUNT%', $refund_amount, translate('ordr_comment_refund')).$comment,$notify);
	}

	function charge($source, $charge_amount = 0, $comment = '',$notify = true){
			
		ostSetOrderStatusToOrder($this->orderID, CONF_ORDSTATUS_CHARGED, str_replace('%CHARGE_AMOUNT%', $charge_amount, translate('ordr_comment_charge')).$comment,$notify);
	}
}
?>