<?php
/**
 * @connect_module_class_name Invoice
 * @package DynamicModules
 * @subpackage PrintForms
 * @_type generic
 * @_name PRINTFORMS_INVOICE_NAME
 * @_description PRINTFORMS_INVOICE_DESCRIPTION
 */
class Invoice extends Forms
{
	function _initVars()
	{
		parent::_initVars();


	}

	function _initSettingFields()
	{
		parent::_initSettingFields();

		$this->SettingsFields['COMPANYNAME'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> constant('PRINTFORMS_INVOICE_COMPANYNAME'),
		'settings_description' 	=> constant('PRINTFORMS_INVOICE_COMPANYNAME_DESCRIPTION'),
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['COMPANYADDRESS'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> constant('PRINTFORMS_INVOICE_COMPANYADDRESS'),
		'settings_description' 	=> constant('PRINTFORMS_INVOICE_COMPANYADDRESS_DESCRIPTION'),
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['COMPANYPHONE'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> constant('PRINTFORMS_INVOICE_COMPANYPHONE'),
		'settings_description' 	=> constant('PRINTFORMS_INVOICE_COMPANYPHONE_DESCRIPTION'),
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['CUSTOMER_COMPANY_FIELD'] = array(
		'settings_value' 		=> false,
		'settings_title' 			=> constant('PRINTFORMS_INVOICE_CUSTOMER_COMPANY_FIELD'),
		'settings_description' 	=> constant('PRINTFORMS_INVOICE_CUSTOMER_COMPANY_FIELD_DESCRIPTION'),
		'settings_html_function' 	=> 'select Forms::select_customer_fields',
		'sort_order' 			=> 1,
		);/*
		$this->SettingsFields['CUSTOMER_PHONE_FIELD'] = array(
		'settings_value' 		=> false,
		'settings_title' 			=> 'Телефон покупателя',
		'settings_description' 	=> 'Поле "телефон" в форме регистрации',
		'settings_html_function' 	=> 'select Forms::select_customer_fields',
		'sort_order' 			=> 1,
		);*/
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
			foreach($order['reg_fields_values'] as $customer_field){
				switch($customer_field['reg_field_ID']){
					case $this->CUSTOMER_COMPANY_FIELD:
						$customer['company'] = $customer_field['reg_field_value'];
						break;
				}
			}
			ordPrepareOrderInfo($order);
			$order_content = ordGetOrderContent( $orderID );
			ordCalculateOrderTax($order,$order_content);
				
			$order['date_print'] = Time::standartTime($order['order_time_mysql'],false);

			$order['shipping_address'] = str_replace(array("\n","\r"),array('',''),$order['shipping_address']);
			$order['shipping_name'] = $order['shipping_firstname'].' '.$order['shipping_lastname'];
			$order['billing_name'] = $order['billing_firstname'].' '.$order['billing_lastname'];
			$order['billing_address'] = str_replace(array("\n","\r"),array('',''),$order['billing_address']);
			$order['paid_date'] = '&nbsp;';	
			if(($order['statusID'] == 5)){
				
				if(count($order_status_report = stGetOrderStatusReport( $orderID,false))){
					$order['paid_date'] = $order_status_report[0]['status_change_time'];
				}
			}
			
			if((strpos(CONF_SHOP_URL,'http://')===false)&&(strpos(CONF_SHOP_URL,'https://')===false)){
				$store_url = 'http://'.CONF_SHOP_URL;
			}else{
				$store_url = CONF_SHOP_URL;
			}

			$smarty = &Core::getSmarty();
			/*@var $smarty Smarty */
			$smarty->assign('order',$order);
			$smarty->assign('store_url',$store_url);
			$smarty->assign('order_content',$order_content);
			$smarty->assign('customer',$customer);

		}
		parent::display($strict);
	}
}
?>