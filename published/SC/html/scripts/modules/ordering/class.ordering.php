<?php
class Ordering extends Module {
	
	function initInterfaces(){
		
		$this->Interfaces = array(
			'shipping' => array(
				'name' => 'Выбор способа доставки',
				'method' => 'methodShipping',
				),
			'billing' => array(
				'name' => 'Выбор способа оплаты',
				'method' => 'methodBilling',
				),
			'confirmation' => array(
				'name' => 'Подтверждение заказа',
				'method' => 'methodConfirmation',
				),
			'change_address' => array(
				'name' => 'Изменить адрес доставки',
				'method' => 'methodChangeAddress',
				),
			'bshipping_modules' => array(
				'name' => 'Управление модулями доставки',
				'method' => 'methodBShippingModules',
				),
			'bpayment_modules' => array(
				'name' => 'Управление модулями оплаты',
				'method' => 'methodBPaymentModules',
				),
			'bshipping_methods' => array(
				'name' => 'Настройка страницы доставки',
				'method' => 'methodBShippingMethods',
				),
			'bpayment_methods' => array(
				'name' => 'Настройка страницы выбора способа оплаты',
				'method' => 'methodBPaymentMethods',
				),
			'admin_paymentmethods' => array(
				'name' => 'Настройка страницы выбора способа оплаты 2',
				),
			'admin_addmod_pmethod' => array(
				'name' => 'Настойка/добавление метода оплаты',
				),
			'borders_list' => array(
				'name' => 'Список заказов (администрирование)',
				),
			'border_detailed' => array(
				'name' => 'Информация о заказе (администрирование)',
				),
			'ftransaction_result' => array(
				'name' => 'Обработка результатов транзакции',
				'method' => 'methodFTransactionResult',
				),
			'successful_ordering' => array(
				'name' => 'Выполнение событий в случае офрмления заказа',
				'method' => 'methodSuccessfulOrdering',
				'type' => INTCALLER
				),
			'buser_orders'=> array(
				'name' => 'История заказов пользователя (администрирование)',
				'type' => INTDIVAVAILABLE,
			),
			'b_discounts'=> array(
				'name' => 'Скидки (администрирование)',
				'type' => INTDIVAVAILABLE,
			),
			'invoice'=> array(
				'name' => 'Информация о заказе (версия для печати)',
				'type' => INTDIVAVAILABLE,
			),
			'quick_register'=> array(
				'name' => 'Быстрая регистрация',
				'type' => INTDIVAVAILABLE,
			),
			'shipping_quick' => array(
				'name' => 'Выбор способа доставки (быстрое оформление)',
				'type' => INTDIVAVAILABLE,
				),
			'billing_quick' => array(
				'name' => 'Выбор способа оплаты (быстрое оформление)',
				'type' => INTDIVAVAILABLE,
				),
			'confirmation_quick' => array(
				'name' => 'Подтверждение заказа (быстрое оформление)',
				'type' => INTDIVAVAILABLE,
				),
		);
	}
	
	function methodSuccessfulOrdering($_OrderID){		
		$order = ordGetOrder($_OrderID);
		$res = null;
		if (!CONF_BACKEND_SAFEMODE){
			
			$SMSNotify = new SMSNotify();
			$res = $SMSNotify->onEvent('new_order',array('OrderAmount'=>$order["currency_code"]." ".RoundFloatValueStr($order["currency_value"]*$order["order_amount"]), 'OrderNumber'=>$order['orderID_view']));
		}
		return $res;
	}
	
	function methodFTransactionResult(){
		
		//TODO: add automatic order processing by modules
		$Module = PaymentModule::getInstance(isset($_GET['modConfID'])?$_GET['modConfID']:0);
		if(!$Module){
			$Module = new PaymentModule();
		}	
		/* @var $Module PaymentModule */
		$Module->transactionResultHandler($_GET['transaction_result']);
		//break;
		
	}
	
	function methodBPaymentMethods(){
		
		global $smarty;
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/conf_payment.php');
	}
	
	function methodBShippingMethods(){
		
		global $smarty;
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/conf_shipping.php');
	}
	
	function methodBPaymentModules(){
		
		global $smarty;
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/modules_payment.php');
	}
	
	function methodBShippingModules(){
		
		global $smarty;
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/modules_shipping.php');
	}
	
	function methodChangeAddress(){
		
		global $smarty;
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/change_address.php');
	}
	
	function methodShipping(){
		
		global $smarty;
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/order2_shipping.php');
	}

	function getOrdersNum($_Params){
		
		/* @var $dbHandler DataBase */
		$dbHandler = &Core::getdbHandler();
		
		$Where = '';
		if(isset($_Params['orderStatuses'])){
			foreach( $_Params['orderStatuses'] as $statusID ){
				
				$Where .= ($Where!=''?' OR':'').' statusID='.xEscapeSQLstring($statusID);
			}
			if ($Where) {
				$Where = '('.$Where.')';
			}
		}
		if ( isset($_Params['customerID']) )$Where .= ($Where!=''?' AND':'').' customerID='.xEscapeSQLstring($_Params['customerID']);
		if ( isset($_Params['orderID']) )$Where .= ($Where!=''?' AND':'').' orderID='.xEscapeSQLstring($_Params['orderID']);
		
		
		$sql = '
			SELECT COUNT(*) FROM '.ORDERS_TABLE.($Where?' WHERE '.$Where:'').'
		';
		$Result = $dbHandler->query($sql);
		list($OrdersNum) = $Result->fetchRow();
		return $OrdersNum;
	}
}
?>