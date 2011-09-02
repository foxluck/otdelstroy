<?php
$smarty = &Core::getSmarty();
	
$UsersObj = &ModulesFabric::getModuleObjByKey('Users');

$UsersObj->_initUserInfo();

$order_statuses = ostGetOrderStatues();

$data = scanArrayKeysForID($_GET, array('set_order_status') );
$changeStatusIsPressed = (count($data)!=0);

$gridEntry = new Grid();

$gridEntry->query_select_rows = 'SELECT * FROM ?#ORDERS_TABLE t1 LEFT JOIN ?#ORDER_STATUSES_TABLE t2 ON t1.statusID=t2.statusID WHERE customerID='.intval($_GET['userID']);
$gridEntry->query_total_rows_num = 'SELECT COUNT(*) FROM ?#ORDERS_TABLE WHERE customerID='.intval($_GET['userID']);

$gridEntry->registerHeader(translate("ordr_id"), 'orderID', true, 'desc');
$gridEntry->registerHeader(translate("ordr_order_time"), 'order_time', false, 'desc');
$gridEntry->registerHeader(translate("payment"), 'payment_type');
$gridEntry->registerHeader(translate("shipping"), 'shipping_type');
$gridEntry->registerHeader(translate("ordr_order_total"), 'order_amount');
$gridEntry->registerHeader(translate("str_status"));

$gridEntry->show_rows_num_select = false;

$gridEntry->setRowHandler('
	LanguagesManager::ml_fillFields(ORDER_STATUSES_TABLE, $row);
	ost_renderStyle($row);
	$row["order_amount"] = $row["currency_code"]." ".RoundFloatValueStr($row["currency_value"]*$row["order_amount"]);
	ordPrepareOrderInfo($row);
	return $row;');

$gridEntry->prepare();

$customer = new Customer();
$customer = &$customer;
$customer->loadByID(intval($_GET['userID']));

$orders_statuses = ostGetOrderStatues(true);

$orders_totals = array();
foreach($orders_statuses as $os_info)
{
    $_t = $customer->getOrdersSum($os_info['statusID']);
    if($_t > 0)
    {
        $os_info['total'] = number_format($_t, 2, '.', '');
        $orders_totals[] = $os_info;
    };
};

$c = currGetCurrencyByID(CONF_DEFAULT_CURRENCY);
$smarty->assign('default_currency_code', $c['currency_iso_3']);
$smarty->assign('orders_totals', $orders_totals);

$smarty->assign( 'order_statuses', $order_statuses );
$smarty->assign( 'UserInfoFile', $this->getTemplatePath('backend/user_orders.html') );
?>