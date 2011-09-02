<?php
define('ORDACTION_CANCEL', 'cancel');
define('ORDACTION_PROCESS', 'process');
define('ORDACTION_RESTORE', 'restore');
define('ORDACTION_DELIVER', 'deliver');
define('ORDACTION_REFUND', 'refund');
define('ORDACTION_CHARGE', 'charge');

define('ORDACTION_SOURCE_ADMIN', 'admin');
define('ORDACTION_SOURCE_CUSTOMER', 'customer');
define('ORDACTION_SOURCE_ROBOT', 'robot');

function ord_getOrderActionsInfo($ids = null){

	$actions = array(
		ORDACTION_PROCESS => array('name' => 'ordr_orderaction_process', 'id' => ORDACTION_PROCESS, 'status_style' => CONF_ORDSTATUS_PROCESSING),
		ORDACTION_DELIVER => array('name' => 'ordr_orderaction_deliver', 'id' => ORDACTION_DELIVER, 'status_style' => CONF_ORDSTATUS_DELIVERED),
		ORDACTION_CANCEL => array('name' => 'ordr_orderaction_cancel', 'id' => ORDACTION_CANCEL, 'confirm' => 'ordr_confirm_cancel', 'status_style' => CONF_ORDSTATUS_CANCELLED),
		ORDACTION_RESTORE => array('name' => 'ordr_orderaction_restore', 'id' => ORDACTION_RESTORE, 'status_style' => CONF_ORDSTATUS_PROCESSING),
		ORDACTION_REFUND => array('name' => 'ordr_orderaction_refund', 'id' => ORDACTION_REFUND, 'confirm' => 'ordr_confirm_refund', 'status_style' => CONF_ORDSTATUS_REFUNDED),
		ORDACTION_CHARGE => array('name' => 'ordr_orderaction_charge', 'id' => ORDACTION_CHARGE, 'status_style' => CONF_ORDSTATUS_CHARGED),
	);

	if(is_null($ids))return $actions;

	$_r = array();
	foreach ($ids as $id)$_r[$id] = $actions[$id];

	return $_r;
}

function ordGetOrdersNum($status = null)
{
	static $statusCounts;
	if(is_null($statusCounts)){
		$statusCounts = array();		
		$res = db_phquery_fetch(DBRFETCH_ROW_ALL, 'SELECT `statusID`, COUNT( * ) AS \'count\' FROM ?#ORDERS_TABLE GROUP BY `statusID`');
		foreach ($res as $row){
			$statusCounts[$row[0]] = $row[1];
		}
		$statusCounts['all'] = array_sum($statusCounts);
		
	}
	return (is_null($status)?$statusCounts['all']:(isset($statusCounts[$status])?$statusCounts[$status]:0));
	// old non cached version
    //$where = ($status != null ? ' where statusID = '.$status : '');
	//return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(*) FROM ?#ORDERS_TABLE'.$where);
}
function ordGetOrdersNumAmount($status = null)
{
	static $statusCounts;
	if(is_null($statusCounts)){
		$statusCounts = array();
		$statusCounts['all'] = array(0,0);		
		$res = db_phquery_fetch(DBRFETCH_ROW_ALL, 'SELECT `statusID`, COUNT( * ) AS \'count\', SUM(`order_amount`) AS \'amount\' FROM ?#ORDERS_TABLE GROUP BY `statusID`');
		foreach ($res as $row){
			$statusCounts[$row[0]] = array($row[1],$row[2]);;
			$statusCounts['all'][0] += $row[1];
			$statusCounts['all'][1] += $row[2];
		}
		//$statusCounts['all'] = array_sum($statusCounts);
				
	}
	return (is_null($status)?$statusCounts['all']:(isset($statusCounts[$status])?$statusCounts[$status]:array(0,0)));
	// old non cached version
    //$where = ($status != null ? ' where statusID = '.$status : '');
	//return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(*) FROM ?#ORDERS_TABLE'.$where);
}

function ordGetOrders( $callBackParam, &$count_row, $navigatorParams = null )
{
	global $selected_currency_details;

	if ( $navigatorParams != null )
	{
		$offset			= $navigatorParams["offset"];
		$CountRowOnPage	= $navigatorParams["CountRowOnPage"];
		$limit_clause = $CountRowOnPage?"LIMIT {$offset}, {$CountRowOnPage}":'';
	}
	else
	{
		$offset = 0;
		$CountRowOnPage = 0;
		$limit_clause = '';
	}

	$where_clause = "";

	if ( isset($callBackParam["orderStatuses"]) )
	{
		foreach( $callBackParam["orderStatuses"] as $statusID )
		{
			if ( $where_clause == "" )
				$where_clause .= " statusID=".$statusID;
			else
				$where_clause .= " OR statusID=".$statusID;
		}

		if ( isset($callBackParam["customerID"]) )
		{
			if ( $where_clause != "" )
				$where_clause = " customerID=".$callBackParam["customerID"].
						" AND ( ".$where_clause." ) ";
			else
				$where_clause = " customerID=".$callBackParam["customerID"];
		}

		if ( $where_clause != "" )
			$where_clause = " where ".$where_clause;
		else
			$where_clause = " where statusID = -1 ";
	}
	else
	{
		if ( isset($callBackParam["customerID"]) )
			$where_clause .= " customerID = ".$callBackParam["customerID"];

		if ( isset($callBackParam["orderID"]) )
		{
			if ( $where_clause != "" )
				$where_clause .= " and orderID=".$callBackParam["orderID"];
			else
				$where_clause .= " orderID=".$callBackParam["orderID"];
		}

		if ( $where_clause != "" )
			$where_clause = " where ".$where_clause;
		else
			$where_clause = " where statusID = -1 ";
	}

	$order_by_clause = "";
	if ( isset($callBackParam["sort"]) )
	{
		$order_by_clause .= " order by ".xEscapeSQLstring($callBackParam["sort"])." ";
		if ( isset($callBackParam["direction"]) )
		{
			if ( $callBackParam["direction"] == "ASC" )
				$order_by_clause .= " ASC ";
			else
				$order_by_clause .= " DESC ";
		}
		else
			$order_by_clause .= " ASC ";
	}
	

	
	$sql_count = "select COUNT(*) from ".ORDERS_TABLE." ".$where_clause;
	$q = db_query( $sql_count);
	$row = db_fetch_row($q);
	$count_row = $row[0];
	
	$sql = "select orderID, customerID, order_time, customer_ip, shipping_type, ".
		" payment_type, customers_comment, statusID, shipping_cost, order_amount, ".
		" order_discount, currency_code, currency_value, customer_email, ".
		" customer_firstname, customer_lastname, ".
		" shipping_firstname, shipping_lastname, ".
		" shipping_country,	shipping_state, shipping_zip, shipping_city, ".
		" shipping_address, billing_firstname, billing_lastname, ".
		" billing_country, billing_state, billing_zip, billing_city, ".
		" billing_address, cc_number, cc_holdername, cc_expires, cc_cvv, shippingServiceInfo ".
		" from ".ORDERS_TABLE." ".$where_clause." ".$order_by_clause." ".$limit_clause
;

	$q = db_query( $sql);

	$res = array();
	$i = 0;
	$total_sum = 0;
	while( $row = db_fetch_row($q) )
	{
		/*if ( ($i >= $offset && $i < $offset + $CountRowOnPage) ||
				$navigatorParams == null  )
		{*/
			ordPrepareOrderInfo($row);
			$row["OrderStatus"] = ostGetOrderStatusName( $row["statusID"] );
			$total_sum += $row["order_amount"];
			$row["order_amount"] = $row["currency_code"]." ".RoundFloatValueStr($row["currency_value"]*$row["order_amount"]);

			//$q_orderContent = db_query( "select name, Price, Quantity, tax, load_counter, itemID from ".
			//	       ORDERED_CARTS_TABLE.
			//	       " where orderID=".$row["orderID"] );

			$content = array();
			/*while( $orderContentItem = db_fetch_row($q_orderContent) )
			{
				$productID = GetProductIdByItemId( $orderContentItem["itemID"] );
				$product   = GetProduct( $productID );
				if ( $product["eproduct_filename"] != null &&
				     strlen($product["eproduct_filename"]) > 0 )
				{
					if ( file_exists(DIR_PRODUCTS_FILES."/".$product["eproduct_filename"])   )
					{
							$orderContentItem["eproduct_filename"] = $product["eproduct_filename"];
							$orderContentItem["file_size"] = filesize( DIR_PRODUCTS_FILES."/".$product["eproduct_filename"] );

							if ( isset($callBackParam["customerID"]) )
							{
								$custID = $callBackParam["customerID"];
							}
							else
							{
								$custID = -1;
							}

							$orderContentItem["getFileParam"] =
								"orderID=".$row["orderID"]."&".
								"productID=".$productID."&".
								"customerID=".$custID;

							//additional security for non authorized customers
							if ($custID == -1)
							{
								$orderContentItem["getFileParam"] .= "&order_time=".base64_encode($row["order_time"]);
							}

							$orderContentItem["getFileParam"] = Crypt::FileParamCrypt(
											$orderContentItem["getFileParam"], null );
							$orderContentItem["load_counter_remainder"]		=
									$product["eproduct_download_times"] - $orderContentItem["load_counter"];

							$currentDate	= dtGetParsedDateTime( Time::dateTime() );
							$betweenDay		= _getDayBetweenDate(
									dtGetParsedDateTime( $row["order_time"] ),
									$currentDate );

							$orderContentItem["day_count_remainder"]		=
									$product["eproduct_available_days"] - $betweenDay;
							//if ( $orderContentItem["day_count_remainder"] < 0 )
							//		$orderContentItem["day_count_remainder"] = 0;

					}
				}

				$content[] = $orderContentItem;
			}*/

			$row["content"] = $content;
			$row["order_time"] = Time::standartTime( $row["order_time"] );
			$res[] = $row;
		//}

		$i++;
	}
	
	

	if ( isset($callBackParam["customerID"]) )
	{
		if ( count($res) > 0 )
		{
		        $q = db_query( "select CID from ".CUSTOMERS_TABLE.
				" where customerID=".$callBackParam["customerID"] );
			$row = db_fetch_row($q);

			if ( $row["CID"]!=null && $row["CID"]!="" )
			{
					$q = db_query( "select currency_value, currency_iso_3 from ".
						CURRENCY_TYPES_TABLE.
						" where CID=".$row["CID"] );
					$row = db_fetch_row($q);
					$res[0]["total_sum"] = $row["currency_iso_3"]." ".$row["currency_value"]*$total_sum;
			}
			else
			{
					$res[0]["total_sum"] = $selected_currency_details["currency_iso_3"].
								" ".$selected_currency_details["currency_value"]*$total_sum;
			}
		}
	}
	return $res;
}


function ordGetDistributionByStatuses( $log ){
	$data = array();
	$customerID = regGetIdByLogin($log);
 	$res = db_phquery("SELECT COUNT(*), `statusID` FROM ?#ORDERS_TABLE WHERE customerID=? GROUP BY `statusID`",$customerID);
 	while($row = db_fetch_row($res)){
	 	$data[] = array( "status_name" => ostGetOrderStatusName($row[1]),'status_id'=>$row[1], "count" => $row[0] );	
 	}
	return $data;
}

function _getOrderById( $orderID )
{
	$sql = "select * FROM ".ORDERS_TABLE." where orderID=?";
	$q = db_phquery( $sql, $orderID );
	$row = db_fetch_row($q);
	ordPrepareOrderInfo($row);
	return $row;
}

function _sendOrderNotifycationToCustomer( $orderID, &$smarty_mail, $email, $login,
			 	$payment_email_comments_text, $shipping_email_comments_text, $tax )
{
	$order = _getOrderById( $orderID );
	
	$furl = MOD_REWRITE_SUPPORT?true:false; 
	$base_url = (CONF_PROTECTED_CONNECTION?'https://':'http://').CONF_SHOP_URL.(SystemSettings::is_hosted()||(SystemSettings::get('FRONTEND')=='SC')?'/':'/shop/');
	$mode = defined('CONF_STRICT_ACCESS')?constant('CONF_STRICT_ACCESS'):'lastname';
	$url = false;
	switch($mode) {
		case 'auth': {
			$url = $login?renderURL('?ukey=auth'.($furl?'&furl_enable=1':''),$base_url,false,$furl):false;
			break;
		}
		default: {
			$url = renderURL('?ukey=order_status&orderID='.$orderID.'&code='.base64_encode($order['customer_email']).($furl?'&furl_enable=1':''),$base_url,false,$furl);
			break;
		}
	}
	
	$smarty_mail->assign('order_status_url',$url?str_replace('{URL}',$url,translate('lbl_order_status_history_url')):'');
	$smarty_mail->assign( "customer_firstname", $order["customer_firstname"] );
	$smarty_mail->assign( "order", $order );
	$smarty_mail->assign( "discount", $order["order_discount"]>0?
				($order["currency_code"]." ".
				RoundFloatValueStr($order["currency_value"]*$order["order_discount"])):'' );
	$shippinginfo = $order["shipping_type"];
	if (strlen($order["shippingServiceInfo"])>0) $shippinginfo .= " (".$order["shippingServiceInfo"].")";
	$smarty_mail->assign( "shipping_type", $shippinginfo );
	$smarty_mail->assign( "shipping_type", $order["shipping_type"] );
	$smarty_mail->assign( "shipping_firstname", $order["shipping_firstname"] );
	$smarty_mail->assign( "shipping_lastname", $order["shipping_lastname"] );
	$smarty_mail->assign( "shipping_country", $order["shipping_country"] );
	$smarty_mail->assign( "shipping_state", $order["shipping_state"] );
	$smarty_mail->assign( "shipping_zip", $order["shipping_zip"] );
	$smarty_mail->assign( "shipping_city", $order["shipping_city"] );
	$smarty_mail->assign( "shipping_address", $order["shipping_address"] );
	$smarty_mail->assign( "shipping_cost",
			$order["currency_code"]." ".
				RoundFloatValueStr($order["currency_value"]*$order["shipping_cost"]) );

	$smarty_mail->assign( "payment_type", $order["payment_type"] );
	$smarty_mail->assign( "billing_firstname", $order["billing_firstname"] );
	$smarty_mail->assign( "billing_lastname", $order["billing_lastname"] );
	$smarty_mail->assign( "billing_country", $order["billing_country"] );
	$smarty_mail->assign( "billing_state", $order["billing_state"] );
	$smarty_mail->assign( "billing_zip", $order["billing_zip"] );
	$smarty_mail->assign( "billing_city", $order["billing_city"] );
	$smarty_mail->assign( "billing_address", $order["billing_address"] );
	$smarty_mail->assign( "order_amount",
		$order["currency_code"]." ".
			RoundFloatValueStr($order["currency_value"]*$order["order_amount"]) );

	$smarty_mail->assign( "payment_comments", $payment_email_comments_text );
	$smarty_mail->assign( "shipping_comments", $shipping_email_comments_text );
	$smarty_mail->assign( "order_total_tax", $order["currency_code"]." ".
								RoundFloatValueStr($order["currency_value"]*$tax) );

	//additional reg fields
	$addregfields = GetRegFieldsValuesByOrderID( $orderID );
	$smarty_mail->assign("customer_add_fields", $addregfields);

	$content = ordGetOrderContent( $orderID );
	for( $i=0; $i<count($content); $i++ )
	{
		$productID = GetProductIdByItemId( $content[$i]["itemID"] );
		if ( $productID == null || trim($productID) == "" )
			continue;

		$sql = "SELECT ".LanguagesManager::sql_prepareField('name')." AS name, product_code";
		$sql .= ", eproduct_filename, eproduct_available_days, eproduct_download_times";
		$sql .= " FROM ?#PRODUCTS_TABLE WHERE productID=?";
		$product = db_phquery_fetch(DBRFETCH_ASSOC, $sql, $productID);
		//$content[$i]["product_code"] = $product["product_code"];
		//$variants	= GetConfigurationByItemId( $content[$i]["itemID"] );
		//$options	= GetStrOptions( $variants );
		$content[$i]["Price"] = $order["currency_code"]." ".(
		RoundFloatValueStr($order["currency_value"]*$content[$i]["Price"]) );

		if ( strlen($product["eproduct_filename"])>0 && file_exists("product_files/".$product["eproduct_filename"]) )
		{
			if ($login != null)
				$customerID = regGetIdByLogin( $login );
			else
				$customerID = -1;
			$content[$i]["eproduct_filename"]		= $product["eproduct_filename"];
			$content[$i]["eproduct_available_days"] = $product["eproduct_available_days"];
			$content[$i]["eproduct_download_times"] = $product["eproduct_download_times"];
			$content[$i]["file_size"]				= filesize( DIR_PRODUCTS_FILES."/".$product["eproduct_filename"] );
			$content[$i]["file_size_str"]			= getDisplayFileSize($content[$i]["file_size"], 'B');
			$content[$i]["getFileParam"]			=
										"orderID=".$order["orderID"]."&".
										"productID=".$productID."&".
										"customerID=".$customerID;
			//additional security for non authorized customers
			if ($customerID == -1)
			{
				$content[$i]["getFileParam"] .= "&order_time=".base64_encode($order["order_time"]);
			}

			$content[$i]["getFileParam"] =
				Crypt::FileParamCrypt( $content[$i]["getFileParam"], null );
		}
	}
	/*@var $smarty_mail View */

	$smarty_mail->assign( "content", $content );
	$html = $smarty_mail->fetch( "order_notification.txt" );
	$res = ss_mail( $email, translate('ordr_id')." ".$order['orderID_view'], $html, true);
	//DEBUG: 
	//exit;
}

/**
 * @param int $orderID
 * @param Smarty $smarty_mail
 * @param unknown_type $tax
 */
function _sendOrderNotifycationToAdmin( $orderID, &$smarty_mail, $tax )
{
	$order = _getOrderById( $orderID );

	$smarty_mail->assign('order', $order);
	$smarty_mail->assign( "customer_firstname", $order["customer_firstname"] );
	$smarty_mail->assign( "customer_lastname", $order["customer_lastname"] );
	$smarty_mail->assign( "customer_email", $order["customer_email"] );
	$smarty_mail->assign( "customer_ip", $order["customer_ip"] );
	$smarty_mail->assign( "order_time", Time::standartTime($order["order_time"]) );
	$smarty_mail->assign( "customer_comments", $order["customers_comment"] );
	$smarty_mail->assign( "discount", $order["currency_code"]." ".
				RoundFloatValueStr($order["currency_value"]*$order["order_discount"]) );

	$shippinginfo = $order["shipping_type"];
	if (strlen($order["shippingServiceInfo"])>0) $shippinginfo .= " (".$order["shippingServiceInfo"].")";
	$smarty_mail->assign( "shipping_type", $shippinginfo );

	$smarty_mail->assign( "shipping_cost",
			$order["currency_code"]." ".
				RoundFloatValueStr($order["currency_value"]*$order["shipping_cost"]) );
	$smarty_mail->assign( "payment_type", $order["payment_type"] );

	$smarty_mail->assign( "shipping_firstname", $order["shipping_firstname"] );
	$smarty_mail->assign( "shipping_lastname", $order["shipping_lastname"] );
	$smarty_mail->assign( "shipping_country", $order["shipping_country"] );
	$smarty_mail->assign( "shipping_state", $order["shipping_state"] );
	$smarty_mail->assign( "shipping_zip", $order["shipping_zip"] );
	$smarty_mail->assign( "shipping_city", $order["shipping_city"] );
	$smarty_mail->assign( "shipping_address", $order["shipping_address"] );

	$smarty_mail->assign( "billing_firstname", $order["billing_firstname"] );
	$smarty_mail->assign( "billing_lastname", $order["billing_lastname"] );
	$smarty_mail->assign( "billing_country", $order["billing_country"] );
	$smarty_mail->assign( "billing_state", $order["billing_state"] );
	$smarty_mail->assign( "billing_zip", $order["billing_zip"] );
	$smarty_mail->assign( "billing_city", $order["billing_city"] );
	$smarty_mail->assign( "billing_address", $order["billing_address"] );

	$smarty_mail->assign( "billing_address", $order["billing_address"] );
	$smarty_mail->assign( "order_amount",
		$order["currency_code"]." ".
			RoundFloatValueStr($order["currency_value"]*$order["order_amount"]) );

	$smarty_mail->assign( "total_tax", $order["currency_code"]." ".
								RoundFloatValueStr($order["currency_value"]*$tax) );

	//additional reg fields
	$addregfields = GetRegFieldsValuesByOrderID( $orderID );
	$smarty_mail->assign("customer_add_fields", $addregfields);

	//fetch order content from the database
	$content = ordGetOrderContent( $orderID );
	for( $i=0; $i<count($content); $i++ )
	{
		$productID = GetProductIdByItemId( $content[$i]["itemID"] );
		if ( $productID == null || trim($productID) == "" )
			continue;
		$q = db_query( "select ".LanguagesManager::sql_prepareField('name')." AS name, product_code from ".PRODUCTS_TABLE." where productID=".$productID );
		$product = db_fetch_row($q);
		//$content[$i]["product_code"] = $product["product_code"];
		/*$variants	= GetConfigurationByItemId( $content[$i]["itemID"] );
		$options	= GetStrOptions( $variants );
		if ( $options != "" )
			$content[$i]["name"] = $product["name"]."(".$options.")";
		else
			$content[$i]["name"] = $product["name"];*/
		$content[$i]["Price"] = $order["currency_code"]." ".(
			RoundFloatValueStr($order["currency_value"]*$content[$i]["Price"])  );
	}

	$smarty_mail->assign( "content", $content );
	//check account settings
	if(SystemSettings::is_hosted()){	
		$session_id = session_id();
		session_write_close();
	
		$messageClient = new WbsHttpMessageClient($db_key, 'wbs_msgserver.php');
		$messageClient->putData('action', 'ALLOW_VIEW_ORDER_DETAILS');
		//$messageClient->putData('language',(LanguagesManager::getCurrentLanguage()->iso2));
		$res=$messageClient->send();
		session_id($session_id);
		session_start();
	}else{
		$res = false;
	}

	$mailTemplate='admin_order_notification.txt';
	if($res&&$messageClient->getResult('success')!==true){
		$mailTemplate='admin_order_notification_blocked.txt';
		$max=$messageClient->getResult('max');
		$smarty_mail->assign('max_orders_per_month',$messageClient->getResult('max'));
	}
	

	$html = $smarty_mail->fetch( $mailTemplate);

	$res = ss_mail( CONF_ORDERS_EMAIL, translate('ordr_id')." ".$order['orderID_view'],$html, true);

}
/**
 * Return information about order in array (orderID, customerID, order_time, customer_ip, shipping_type, payment_type, customers_comment, statusID, shipping_cost, order_discount, order_amount, currency_code, currency_value, customer_firstname, customer_lastname, customer_email, shipping_firstname, shipping_lastname, shipping_country, shipping_state, shipping_zip, shipping_city, shipping_address, billing_firstname, billing_lastname, billing_country, billing_state, billing_zip, billing_city, billing_address, cc_number, cc_holdername, cc_expires, cc_cvv, shippingServiceInfo
 *
 * @param int $orderID
 * @return array
 */
function ordGetOrder( $orderID )
{
	$orderID = (int) $orderID;
	$order = _getOrderById($orderID);
	if ( $order )
	{
		//CC data
		if (CONF_BACKEND_SAFEMODE)
		{
			$order["cc_number"] = translate("msg_safemode_info_blocked");
			$order["cc_holdername"] = translate("msg_safemode_info_blocked");
			$order["cc_expires"] = translate("msg_safemode_info_blocked");
			$order["cc_cvv"] = translate("msg_safemode_info_blocked");
		}
		else
		{
			if (strlen($order["cc_number"])>0)
				$order["cc_number"] = Crypt::CCNumberDeCrypt($order["cc_number"],null);
			if (strlen($order["cc_holdername"])>0)
				$order["cc_holdername"] = Crypt::CCHoldernameDeCrypt($order["cc_holdername"],null);
			if (strlen($order["cc_expires"])>0)
				$order["cc_expires"] = Crypt::CCExpiresDeCrypt($order["cc_expires"],null);
			if (strlen($order["cc_cvv"])>0)
				$order["cc_cvv"] = Crypt::CCNumberDeCrypt($order["cc_cvv"],null);
		}

		//additional reg fields
		$addregfields = GetRegFieldsValuesByOrderID( $orderID );
		$order["reg_fields_values"] = $addregfields;


		$status = db_phquery_fetch(DBRFETCH_ASSOC, "select *,".LanguagesManager::sql_prepareField('status_name')." AS status_name from ?#ORDER_STATUSES_TABLE where statusID=?",$order["statusID"] );
		//$status = ostGetOrderStatusName($order["statusID"]);
		ost_renderStyle($status);

		if ( $order["statusID"] == ostGetCanceledStatusId() )
			$status['status_name'] = translate("ordr_status_cancelled");
			
		// clear cost ( without shipping, discount, tax )
		$q1 = db_query( "select Price, Quantity from ".ORDERED_CARTS_TABLE." where orderID=$orderID" );
		$clear_total_price = 0;
		while( $row=db_fetch_row($q1) )
			$clear_total_price += $row["Price"]*$row["Quantity"];

		$order["shipping_costToShow"]	= $order["currency_code"]." ".RoundFloatValueStr($order["currency_value"]*$order["shipping_cost"]);
		$order["order_amountToShow"] 	= $order["currency_code"]." ".RoundFloatValueStr($order["currency_value"]*$order["order_amount"]);

		$order["order_discount_value"] = $order["currency_value"]*$order["order_discount"];
		$order["order_discount_valueToShow"] = $order["currency_code"]." ".RoundFloatValueStr($order["order_discount_value"]);
		$order["order_discount_percent"] = $clear_total_price?round(100*$order["order_discount_value"]/($clear_total_price*$order["currency_value"]),1):0;

		$order["clear_total_price"] = $order["currency_value"]*$clear_total_price-$order["order_discount_value"];
 		$order["clear_total_priceToShow"] = $order["currency_code"]." ".RoundFloatValueStr($order["clear_total_price"]);
 		
		$order["order_time_mysql"] = $order["order_time"];
		$order["order_time"] = Time::standartTime( $order["order_time"] );

		$order["status_name"] = $status['status_name'];
		$order["status_style"] = $status['_style'];

	}
	return $order;
}

/**
 * Get products from ordered shopping cart
 *
 * @param int $orderID
 * @return array:
 * 				0=>array(<br />
  					[name] => 007 - Gold Finger(black, 15)<br />
            [Price] => 17.49<br />
            [Quantity] => 2<br />
            [tax] => 45<br />
            [load_counter] => 0<br />
            [itemID] => 249<br />
            [PriceToShow] => USD 41.98<br />
            ),etc...
 */
function ordGetOrderContent( $orderID )
{
	$data = array();
	$orderID = intval($orderID);
	$orderID = $orderID?$orderID:-1;
	
	$q_order = db_phquery( 'SELECT currency_code, currency_value, customerID, order_time FROM ?#ORDERS_TABLE WHERE orderID=?',$orderID);
 	$order = db_fetch_row($q_order);
 	if(!$order){
 		return $data;
 	}
	$currency_code = $order["currency_code"];
	$currency_value = $order["currency_value"];
	unset($q_order);
	
	$q = db_phquery( 'SELECT name, Price, Quantity, tax, load_counter, itemID FROM ?#ORDERED_CARTS_TABLE WHERE orderID=?',$orderID );
	while( $row=db_fetch_row($q) )
	{
		$productID = GetProductIdByItemId( $row["itemID"] );
		$product   = GetProduct( $productID, true );
		if ( $product["eproduct_filename"] != null &&
			 $product["eproduct_filename"] != null )
		{
			if ( file_exists(DIR_PRODUCTS_FILES."/".$product["eproduct_filename"]) )
			{
					$row["eproduct_filename"]	= $product["eproduct_filename"];
					$row["file_size"]			= filesize( DIR_PRODUCTS_FILES."/".$product["eproduct_filename"] );
					$row["file_size_str"]		= getDisplayFileSize($row["file_size"], 'B');

					if ( $order["customerID"] != null )
					{
						$custID = $order["customerID"];
					}
					else
					{
						$custID = -1;
					}

					$row["getFileParam"] =
								"orderID=".$orderID."&".
								"productID=".$productID."&".
								"customerID=".$custID;

					//additional security for non authorized customers
					if ($custID == -1)
					{
						$row["getFileParam"] .= "&order_time=".base64_encode($order["order_time"]);
					}

					$row["getFileParam"] = Crypt::FileParamCrypt(
									$row["getFileParam"], null );
					$row["load_counter_remainder"]		=
							$product["eproduct_download_times"] - $row["load_counter"];

//					$currentDate	= dtGetParsedDateTime( Time::dateTime() );
//					$betweenDay		= _getDayBetweenDate(
//							dtGetParsedDateTime( $order["order_time"] ),
//							$currentDate );
					//TODO: use class instead function 
					$betweenDay = Time::getDaysInterval($order["order_time"]);
					

					$row["day_count_remainder"]		=
							$product["eproduct_available_days"] - $betweenDay;

			}
		}

		$row["PriceToShow"] =  $currency_code." ".RoundFloatValueStr($currency_value*$row["Price"]*$row["Quantity"]);
		$row["ItemPrice"] = RoundFloatValueStr($currency_value*$row["Price"]*$row["Quantity"]);
		$row["ItemBPrice"] = RoundFloatValueStr($currency_value*$row["Price"]);
		$data[] = $row;
	}
	return $data;
}

/**
 * @param array $order by ordGetOrderContent
 * @param array $order_content by ordGetOrderContent
 */
function ordCalculateOrderTax(&$order, $order_content){

	$order['tax'] = 0;
	foreach ($order_content as $_item)
	{
		$order['tax'] += ($order['currency_value']*$_item['Price']*$_item['Quantity'])/100*/*(100-$order['order_discount'])/100**/$_item['tax'];
	}

	$order['tax_toShow'] = $order["currency_code"]." ".RoundFloatValueStr($order['tax']);
}

function mycal_days_in_month( $calendar, $month, $year )
{
	$month = (int)$month;
	$year  = (int)$year;

	if ( 1 > $month || $month > 12 )
		return 0;
	if ( $month==1 || $month==3 || $month==5 || $month==7 || $month==8 || $month==10 || $month==12 )
		return 31;
	else
	{
		if ( $month==2 && $year % 4 == 0 )
			return 29;
		else if ( $month==2 && $year % 4 != 0 )
			return 28;
		else
			return 30;
	}
}

function _getCountDay( $date )
{
	$countDay = 0;
	for( $year=1900; $year<$date["year"]; $year++ )
	{
		for( $month=1; $month <= 12; $month++ )
			$countDay += mycal_days_in_month(CAL_GREGORIAN, $month, $year);
	}

	for( $month=1; $month < $date["month"]; $month++ )
		$countDay += mycal_days_in_month(CAL_GREGORIAN, $month, $date["year"]);

	$countDay += $date["day"];
	return $countDay;
}



// *****************************************************************************
// Purpose	gets address string
// Inputs   	$date array of item
//			"day"
//			"month"
//			"year"
//		$date2 must be more later $date1
// Remarks
// Returns
function _getDayBetweenDate( $date1, $date2 )
{
	if ( $date1["year"] > $date2["year"] )
		return -1;
	if ( $date1["year"]==$date2["year"] && $date1["month"]>$date2["month"] )
		return -1;
	if ( $date1["year"]==$date2["year"] && $date1["month"]==$date2["month"] &&
		$date1["day"] > $date2["day"]  )
		return -1;
	return _getCountDay( $date2 ) - _getCountDay( $date1 );
}

function ordPrepareOrderInfo(&$order){
	if(is_array($order))
	$order['orderID_view'] = CONF_ORDERID_PREFIX.$order['orderID'];
}

// *****************************************************************************
// Purpose
// Inputs
// Remarks
// Returns
//		-1 	access denied
//		0	success, access granted and load_counter has been incremented
//		1	access granted but count downloading is exceeded eproduct_download_times in PRODUCTS_TABLE
//		2	access granted but available days are exhausted to download product
//		3	it is not downloadable product
//		4	order is not ready
function ordAccessToLoadFile( $orderID, $productID, $customerID, & $pathToProductFile, & $productFileShortName )
{
	$order 		= ordGetOrder($orderID);
	$product 	= GetProduct( $productID );
	if(!$order){
		return -1;
	}
	if($customerID !=$order['customerID']){
		return -1;
	}

	if ( strlen($product["eproduct_filename"]) == 0 || !file_exists(DIR_PRODUCTS_FILES."/".$product["eproduct_filename"]) || $product["eproduct_filename"] == null )
	{
		return 4;
	}

	if ( (int)$order["statusID"] != (int)ostGetCompletedOrderStatus() )
		return 3;

	$orderContent 	= ordGetOrderContent( $orderID );
	foreach( $orderContent as $item )
	{
		if ( GetProductIdByItemId($item["itemID"]) == $productID )
		{
			if ( $item["load_counter"] < $product["eproduct_download_times"] ||
					$product["eproduct_download_times"] == 0 )
			{
				//$date1 = dtGetParsedDateTime( $order["order_time_mysql"] ); //$order["order_time"]
				//$date2 = dtGetParsedDateTime( Time::dateTime() );

				
				//TODO: use new class
				//$countDay = _getDayBetweenDate( $date1, $date2 );
				$countDay = Time::getDaysInterval($order["order_time_mysql"]);

				if ( $countDay>=$product["eproduct_available_days"] )
					return 2;

				if ( $product["eproduct_download_times"] != 0 )
				{
					db_query( "update ".ORDERED_CARTS_TABLE.
						" set load_counter=load_counter+1 ".
						" where itemID=".$item["itemID"]." AND orderID=".$orderID );
				}
				$pathToProductFile		= DIR_PRODUCTS_FILES."/".$product["eproduct_filename"];
				$productFileShortName	= $product["eproduct_filename"];
				return 0;
			}
			else
				return 1;
		}
	}
	return -1;
}
?>
