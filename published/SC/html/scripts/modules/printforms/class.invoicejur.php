<?php
/**
 * счет на оплату для юридических лиц
 *
 * @connect_module_class_name InvoiceJur
 * @package DynamicModules
 * @subpackage PrintForms
 * @_type module
 * @_sub_type invoicejur
 * @_language rus
 * @_name Счет на оплату
 * @_description для юридических лиц через банк (РФ). Настройка полей счета производится в свойствах способа оплаты по счету (если такой установлен) в разделе администрирования «Настройки» — «Оплата». Счет могут распечатать и администратор интернет-магазина, и покупатель.
 * @_no_settings -
 */
class InvoiceJur extends Forms
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

		if ($order){//заказ найден в базе данных
			$InvoiceModule = PaymentModule::getInstance($order['payment_module_id']);
			/*@var $InvoiceModule CInvoiceJur*/
			if(!$InvoiceModule instanceof CInvoiceJur){
				die ("печатная форма не применима");
			}
			$invoice_data = array(
			'CONF_PAYMENTMODULE_INVOICE_JUR_COMPANYNAME' => '',
			'CONF_PAYMENTMODULE_INVOICE_JUR_COMPANYADDRESS' => '',
			'CONF_PAYMENTMODULE_INVOICE_JUR_COMPANYPHONE' => '',
			'CONF_PAYMENTMODULE_INVOICE_JUR_BANK_ACCOUNT_NUMBER' => '',
			'CONF_PAYMENTMODULE_INVOICE_JUR_INN' => '',
			'CONF_PAYMENTMODULE_INVOICE_JUR_KPP' => '',
			'CONF_PAYMENTMODULE_INVOICE_JUR_BANKNAME' => '',
			'CONF_PAYMENTMODULE_INVOICE_JUR_BANK_KOR_NUMBER' => '',
			'CONF_PAYMENTMODULE_INVOICE_JUR_BIK' => '',
			);
			foreach ($invoice_data as $k=>$v){
				$invoice_data[$k] = $InvoiceModule->_getSettingValue($k);
			}
			$smarty->assign('invoice_data', $invoice_data);
			//define smarty vars
			$smarty->hassign( "billing_lastname", $order["billing_lastname"] );
			$smarty->hassign( "billing_firstname", $order["billing_firstname"] );
			$smarty->hassign( "billing_zip", $order["billing_zip"] );
			$smarty->hassign( "billing_city", $order["billing_city"] );
			$smarty->hassign( "billing_address", $order["billing_address"] );
			$smarty->hassign( "orderID", $order['orderID_view'] );

			if (!$InvoiceModule->is_installed()){ //модуль не установлен
				die ("Модуль выписки счетов не установлен");
			}

			//сумма счета
			$table = DBTABLE_PREFIX.'_module_payment_invoice_jur';
			$sql = "SELECT company_name, company_inn, nds_included, nds_rate, RUR_rate FROM {$table} where orderID=? AND module_id=?";

			$q = db_phquery($sql,$orderID,$InvoiceModule->ModuleConfigID);
			$row = db_fetch_row($q);
			if ($row){ //сумма найдена в файле с описанием счета
				$smarty->hassign( "customer_companyname", $row["company_name"] );
				$smarty->hassign( "customer_inn",  $row["company_inn"] );
				$nds_rate = (float) $row["nds_rate"];
				$RUR_rate = (float) $row["RUR_rate"];
						
				$custom_currency = $InvoiceModule->_getSettingValue('CONF_PAYMENTMODULE_INVOICE_JUR_CURRENCY');
				if($custom_currency){
					$currencyEntry = new Currency();
					$currencyEntry->loadByCID($custom_currency);
				}else{
					$currencyEntry = Currency::getDefaultCurrencyInstance();
				}
						/*@var $currencyEntry Currency*/
				$RUR_rate = $currencyEntry->currency_value;
				$nds_included = !strcmp((string)$row["nds_included"],"1") ? 1 : 0;
			}else{ //информация о счет не найдена
				die ("Счет не найден в базе данных");
			}

			//заказанные товары
			$order_content = ordGetOrderContent( $orderID );
			$amount = 0;
			foreach( $order_content as $key => $val){
				$order_content[$key]["Price"] = self::_my_formatPrice ( $order_content[$key]["Price"] * $RUR_rate );
				$order_content[$key]["Price_x_Quantity"] = self::_my_formatPrice ( $val["Quantity"] * $val["Price"] * $RUR_rate );
				$amount += (float) str_replace(",",".",$order_content[$key]["Price_x_Quantity"]);
			}

			$shipping_rate = $order["shipping_cost"]*$RUR_rate;

			//		$order["discount_value"] = round((float)$order["order_discount"] * $amount)/100;
			$order["order_discount_percent"] = round((float)$order["order_discount_value"] *100 /$amount);
			$order['date_print'] = Time::standartTime($order['order_time_mysql'],false);
			$smarty->hassign( "order", $order);
			$smarty->hassign( "order_discount", $order["order_discount"] );
			$smarty->assign( "order_discount_percent", $order["order_discount_percent"] );

			$smarty->hassign( "order_discount_value", self::_my_formatPrice($order["order_discount_value"]) );

			$amount += $shipping_rate; //+стоимость доставки

			$smarty->hassign( "order_content", $order_content );
			$smarty->hassign( "order_content_items_count", count($order_content) + 1 );
			$smarty->hassign( "order_subtotal", self::_my_formatPrice($amount-$order["discount_value"]) );


			$amount -= $order["order_discount_value"];

			if ($nds_rate <= 0){ //показать НДС
				$smarty->hassign( "order_tax_amount", "нет" );
				$smarty->hassign( "order_tax_amount_string", "нет" );
			}else{
				//налог не расчитывается на стоимость доставки
				//если вы хотите, чтобы налог расчитывался и на стоимость доставки замените ниже
				// '($amount-$shipping_rate)' на '$amount'

				if (!$nds_included){ //налог включен
					$tax_amount = round ( ($amount-$shipping_rate) * $nds_rate ) / 100;
					$amount += $tax_amount;
				}else{ //прибавить налог
					$tax_amount = round ( 100 * ($amount-$shipping_rate) * $nds_rate / ($nds_rate+100) ) / 100;
				}
				$smarty->hassign( "order_tax_amount", self::_my_formatPrice($tax_amount) );
				$smarty->hassign( "order_tax_amount_string", Currency::stringView($tax_amount) );

			}

			$smarty->hassign( "order_total", self::_my_formatPrice($amount) ); //$amount
			$smarty->hassign( "order_total_string", Currency::stringView($amount) );

			//доставка
			if ($shipping_rate > 0){
				$smarty->hassign( "shipping_type", $order["shipping_type"] );
				$smarty->hassign( "shipping_rate", self::_my_formatPrice($shipping_rate) );
			}
		}else{
			die ("Заказ не найден в базе данных");
		}

		$smarty->assign("shopping_cart_url",""); //путь к файлу логотипа
		//$smarty->display("invoice_jur.tpl.html");
		parent::display($strict);
	}
}
?>