<?php
/**
 * @connect_module_class_name ConsignmentNote
 * @package DynamicModules
 * @subpackage PrintForms
 * @_type generic
 * @_language rus
 * @_side backend
 * @_name Товарная накладная
 * @_description Для использования на территории РФ. Накладную может распечатать только администратор интернет-магазина.
 */
class ConsignmentNote extends Forms
{
	function _initSettingFields()
	{
		parent::_initSettingFields();

		$this->SettingsFields['CURRENCY'] = array(
		'settings_value' 		=> '0',
		'settings_title' 			=> 'Валюта - рубли',
		'settings_description' 	=> 'Счета на оплату выписываются в рублях. Выберите из списка валют магазина рубль. При формировании счета будет использоваться значение курса рубля. Если валюта не определена, будет использован курс выбранной пользователем валюты',
		'settings_html_function' 	=> 'select Forms::select_currency',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['NDS'] = array(
		'settings_value' 		=> '0',
		'settings_title' 			=> 'Ставка НДС (%)',
		'settings_description' 	=> 'Укажите ставку НДС в процентах. Если Вы работаете по упрощенной системе налогообложения, укажите 0',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['NDS_IS_INCLUDED_IN_PRICE'] = array(
		'settings_value' 		=> '1',
		'settings_title' 			=> 'НДС уже включен в стоимость товаров',
		'settings_description' 	=> 'Включите эту опцию, если налог уже включен в стоимость товаров в Вашем магазине. Если же НДС не включен в стоимость и должен прибавляться дополнительно, выключите эту опцию',
		'settings_html_function' 	=> 'checkbox',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['COMPANYNAME'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> 'Название компании',
		'settings_description' 	=> 'Укажите название организации, от имени которой выписывается счет',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['COMPANYADDRESS'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> 'Адрес компании',
		'settings_description' 	=> 'Укажите адрес организации, от имени которой выписывается счет',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['COMPANYPHONE'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> 'Телефон компании',
		'settings_description' 	=> 'Укажите телефон организации',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['CEO_NAME'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> 'Директор компании',
		'settings_description' 	=> 'Укажите Фамилию И.О.',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['BUH_NAME'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> 'Бухгалтер компании',
		'settings_description' 	=> 'Укажите Фамилию И.О.',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['BANK_ACCOUNT_NUMBER'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> 'Расчетный счет',
		'settings_description' 	=> 'Номер расчетного счета организации',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['INN'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> 'ИНН',
		'settings_description' 	=> 'ИНН организации',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['KPP'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> 'КПП',
		'settings_description' 	=> '',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['BANKNAME'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> 'Наименование банка',
		'settings_description' 	=> '',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['BANK_KOR_NUMBER'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> 'Корреспондентский счет',
		'settings_description' 	=> '',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['BIK'] = array(
		'settings_value' 		=> '',
		'settings_title' 			=> 'БИК',
		'settings_description' 	=> '',
		'settings_html_function' 	=> 'text',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['CUSTOMER_COMPANY_FIELD'] = array(
		'settings_value' 		=> false,
		'settings_title' 			=> 'Компания покупателя',
		'settings_description' 	=> 'Поле "Компания" в форме регистрации',
		'settings_html_function' 	=> 'select Forms::select_customer_fields',
		'sort_order' 			=> 1,
		);
		$this->SettingsFields['CUSTOMER_PHONE_FIELD'] = array(
		'settings_value' 		=> false,
		'settings_title' 			=> 'Телефон покупателя',
		'settings_description' 	=> 'Поле "телефон" в форме регистрации',
		'settings_html_function' 	=> 'select Forms::select_customer_fields',
		'sort_order' 			=> 1,
		);
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
					case $this->CUSTOMER_PHONE_FIELD:
						$customer['phone'] = $customer_field['reg_field_value'];
						break;
							
				}
			}
			//$currency_rate = Currency::

			$rur_currency = new Currency();
			$rur_currency->loadByCID($this->CURRENCY);
			$currency_rate = $rur_currency->currency_value?$rur_currency->currency_value:1;
				
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
			$order['total_quantity'] =0;
			$order['total_nds'] = 0;
			$order['total_nds_price'] = 0;
			$order['total_price'] = 0;
			$order['total_record'] = 0;
			$order['clear_total_price'] *=$currency_rate/$order['currency_value'];
			$order['order_discount'] *=$currency_rate;///$order['currency_value'];
			$discount =  1-($order['order_discount'])/($order['clear_total_price']+$order['order_discount']);
			$order_content = ordGetOrderContent($orderID);
			foreach($order_content as &$order_item){
				$order_item['Price'] *=$currency_rate/$order['currency_value'];
				
				
				$order_item['ItemPrice'] *= $discount*$currency_rate/$order['currency_value'];
				$order_item['Price'] = $order_item['ItemPrice']/$order_item['Quantity'];
				if($this->NDS){
					if($this->NDS_IS_INCLUDED_IN_PRICE){
						$order_item['Nds_ItemPrice'] =  (100/($this->NDS+100))*$order_item['ItemPrice'];
						$order_item['Price'] = (100/($this->NDS+100))*$order_item['Price'];
					}else{
						$order_item['Nds_ItemPrice'] =  $order_item['ItemPrice'];
						$order_item['ItemPrice'] *= (1.00+$this->NDS/100);
							
					}
						

				}else{
					$order_item['Nds_ItemPrice'] = $order_item['ItemPrice'];
				}
				$order_item['Nds'] = $this->NDS;
				$order_item['Nds_amount'] = $order_item['ItemPrice'] - $order_item['Nds_ItemPrice'];
				$order['total_nds']+=	$order_item['Nds_amount'];
				$order['total_nds_price']+=	$order_item['Nds_ItemPrice'];
				$order['total_quantity']+=$order_item['Quantity'];
				$order['total_price'] += $order_item['ItemPrice'];
				$order['total_record']++;
			}
			$delta = 1-(($this->NDS_IS_INCLUDED_IN_PRICE?$order['total_price']:$order['total_nds_price'])-$order['clear_total_price'])/$order['clear_total_price'];
			//fix round variations
			foreach($order_content as &$order_item){
				$order_item['Nds_amount'] = round($order_item['Nds_amount']*$delta,2);
				$order_item['Nds_ItemPrice'] = round($order_item['Nds_ItemPrice']*$delta,2);
				$order_item['Price'] = round($order_item['Price']*$delta,2);
				$order_item['ItemPrice'] = round($order_item['ItemPrice']*$delta,2);
			}
			$order['total_nds'] = round($order['total_nds']*$delta,2);
			$order['total_nds_price'] = round($order['total_nds_price']*$delta,2);
			$order['total_price'] = round($order['total_price']*$delta,2);
			$order['total_record_string'] = Currency::number2string($order['total_record'],1);
			$order['total_nds_price_string'] = Currency::stringView($order['total_nds_price']);
			$order['total_price_string'] = Currency::stringView($order['total_price']);
			$order['nds'] = $this->NDS;
			$smarty = &Core::getSmarty();
			/*@var $smarty Smarty */

			$smarty->assign('order',$order);
			$smarty->assign('order_content',$order_content);
			$smarty->assign('customer',$customer);

		}
		parent::display($strict);//use consignmentnote.html
	}
}
?>