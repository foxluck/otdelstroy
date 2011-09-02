<?php
/*
 * @deprecated
 *  use printforms instead
 */
//квитанция на оплату
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();

//assign core Smarty variables
if (!isset($_GET["orderID"]) || !isset($_GET["order_time"]) || !isset($_GET["customer_email"]) || !isset($_GET["moduleID"]))
{
	die ("Заказ не найден в базе данных");
}

$InvoiceModule = PaymentModule::getInstance($_GET['moduleID']);
$_GET["orderID"] = (int) $_GET["orderID"];

$sql = '
	SELECT COUNT(*) FROM '.ORDERS_TABLE.' 
	WHERE orderID='.$_GET["orderID"].' AND order_time="'.base64_decode($_GET["order_time"]).'" AND customer_email="'.base64_decode($_GET["customer_email"]).'"
';
$q = db_query($sql) or die (db_error());
$row = db_fetch_row($q);

if ($row[0] == 1) //заказ найден в базе данных
{
	$order = ordGetOrder( $_GET["orderID"] );
	//define smarty vars
	$smarty->hassign( "billing_lastname", $order["billing_lastname"] );
	$smarty->hassign( "billing_firstname", $order["billing_firstname"] );
	$smarty->hassign( "billing_city", $order["billing_city"] );
	$smarty->hassign( "billing_address", $order["billing_address"] );
	if ($InvoiceModule->is_installed()){
		
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
	}
	else //описание не опред
	{
		die ("Модуль оплаты по квитанциям не установлен");
	}

	//сумма квитанции
	$q = db_query("select order_amount_string from ".DBTABLE_PREFIX."_module_payment_invoice_phys where orderID=".$_GET["orderID"]);
	$row = db_fetch_row($q);
	if ($row) //сумма найдена в файле с описанием квитанции
	{
		$smarty->assign( "invoice_amount", $row[0] );
	}
	else //сумма не найдена - показываем в текущей валюте
	{
		$smarty->assign( "invoice_amount", show_price($order["order_amount"]) );
	}


}
else
{
	die ("Заказ не найден в базе данных");
}

//show Smarty output
$smarty->display("invoice_phys.tpl.html");
die;
?>