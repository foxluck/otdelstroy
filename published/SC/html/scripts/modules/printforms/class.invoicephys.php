<?php
/**
 * счет на оплату для физических лиц
 *
 * @connect_module_class_name InvoicePhys
 * @package DynamicModules
 * @subpackage PrintForms
 * @_type module
 * @_sub_type invoicephys
 * @_language rus
 * @_name Квитанция
 * @_description на оплату для физических лиц через банк (РФ). Настройка полей квитанции производится в свойствах способа оплаты по квитанции (если такой установлен) в разделе администрирования «Настройки» — «Оплата». Квитанцию могут распечатать и администратор интернет-магазина, и покупатель.
 * @_no_settings -
 */
class InvoicePhys extends Forms
{
	static function _my_formatPrice($price){
		return sprintf('%0.2f',$price);
	}

	function display($strict = true)
	{
		$smarty = &Core::getSmarty();
		/*@var $smarty Smarty */

		if (!isset($_GET["orderID"])){
			die ("Заказ не найден в базе данных");
		}

		$orderID = intval($_GET["orderID"]);

		$order = ordGetOrder($orderID); //order details

		if($strict&&!$this->verifyOrderData($order)){
			unset($order);
		}



		if ($order){ //заказ найден в базе данных
			$InvoiceModule = PaymentModule::getInstance($order['payment_module_id']);
			/*@var $InvoiceModule CInvoicePhys*/
			if(!$InvoiceModule instanceof CInvoicePhys){
				die ("печатная форма не применима");
			}

			//define smarty vars
			$smarty->hassign( "billing_lastname", $order["billing_lastname"] );

			$smarty->hassign( "billing_firstname", $order["billing_firstname"] );
			$smarty->hassign( "billing_city", $order["billing_city"]);
			$smarty->hassign( "billing_state", $order['billing_state']);
			$smarty->hassign( "billing_address", $order["billing_address"] );
			$smarty->hassign( "billing_country", $order["billing_country"] );
			if ($InvoiceModule->is_installed()){
				if(($secondNameID = $InvoiceModule->_getSettingValue('CONF_PAYMENTMODULE_INVOICE_PHYS_SECOND_NAME'))){
					$regFields = GetRegFieldsValuesByOrderID($orderID);
					foreach($regFields as $regField){
						if($regField["reg_field_ID"]!=$secondNameID)continue;
						$smarty->hassign('second_name', $regField['reg_field_value']);
						break;
					}
				}

				$invoice_data = array(
				'CONF_PAYMENTMODULE_INVOICE_PHYS_COMPANYNAME' => '',
				'CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_ACCOUNT_NUMBER' => '',
				'CONF_PAYMENTMODULE_INVOICE_PHYS_INN' => '',
				'CONF_PAYMENTMODULE_INVOICE_PHYS_KPP' => '',
				'CONF_PAYMENTMODULE_INVOICE_PHYS_BANKNAME' => '',
				'CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_KOR_NUMBER' => '',
				'CONF_PAYMENTMODULE_INVOICE_PHYS_BIK' => '',
				);
				foreach ($invoice_data as $k=>$v){
					$invoice_data[$k] = $InvoiceModule->_getSettingValue($k);
				}
				$smarty->assign('invoice_data', $invoice_data);
				$smarty->assign( "invoice_description", str_replace("[orderID]", (string)$order['orderID_view'], $InvoiceModule->_getSettingValue('CONF_PAYMENTMODULE_INVOICE_PHYS_DESCRIPTION')) );
			}else{ //описание не опред
				die ("Модуль оплаты по квитанциям не установлен");
			}

			//сумма квитанции
			$row = false; 
			//$q = db_query("select order_amount_string from ".DBTABLE_PREFIX."_module_payment_invoice_phys where orderID=".$orderID);
			//$row = db_fetch_row($q);
			if ($row){ //сумма найдена в файле с описанием квитанции
				$smarty->assign( "invoice_amount", $row[0] );
			}else{ //сумма не найдена - показываем в текущей валюте
				$smarty->assign( "invoice_amount", show_price($order["order_amount"],$InvoiceModule->_getSettingValue('CONF_PAYMENTMODULE_INVOICE_PHYS_CURRENCY')) );
			}


		}
		else
		{
			die ("Заказ не найден в базе данных");
		}
		//$smarty->display("invoice_jur.tpl.html");
		parent::display($strict);
	}
}
?>