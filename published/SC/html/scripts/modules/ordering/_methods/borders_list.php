<?php
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();

set_query('safemode=', '', true);

$page = isset($_GET['page'])?intval($_GET['page']):1;
if ($page<1)$page=1;
//orders list 
$order_statuses = ost_getOrderStatuses();
$data = scanArrayKeysForID($_GET, array('set_order_status') );
$changeStatusIsPressed = (count($data)!=0);
$selectedStatuses = Storage::getInstance('borders_list');
if(!isset($_GET['search']) && !$changeStatusIsPressed && !isset($_GET['export_to_excel'])){
	if(($selectedStatuses->getCount())){
		renderURL('order_search_type=SearchByStatusID&search=Show', '', true);	
	}else{
		renderURL('order_search_type=SearchByStatusID&search=Show'.
		'&checkbox_order_status_'.CONF_ORDSTATUS_PENDING.'=1'.
		'&checkbox_order_status_'.CONF_ORDSTATUS_PROCESSING.'=1'.
		'&checkbox_order_status_'.CONF_ORDSTATUS_CHARGED.'=1', '', true);
	}
}

if ( isset($_GET['search']) || $changeStatusIsPressed || isset($_GET['export_to_excel'])){
	 $export_to_excel = isset($_GET['export_to_excel']);
	 if($export_to_excel)unset($_GET['export_to_excel']);

	if ( isset($_GET['order_search_type'])  )
		$smarty->assign( 'order_search_type', $_GET['order_search_type'] );
	if ( isset($_GET['orderID_textbox']) )
		$smarty->assign( 'orderID', $_GET['orderID_textbox'] );
	$data = scanArrayKeysForID($_GET, array('checkbox_order_status') );
	$defaultCurrency = Currency::getDefaultCurrencyInstance();
	/* @var $defaultCurrency Currency */
	
	foreach($order_statuses as $i => $_status)
	{
	    $order_statuses[$i]['selected'] = 0;
	    list($order_statuses[$i]['orders_count'],$order_statuses[$i]['orders_amount']) = ordGetOrdersNumAmount($i);
	    
	    $order_statuses[$i]['orders_amount_display'] = $defaultCurrency->getView($order_statuses[$i]['orders_amount']) ;
	}
	
	
	$total_statuses_amount = 0;
	if(count($data)){
		foreach( $data as $key => $val )
		{
			if ( $val['checkbox_order_status'] == '1' )
			{
				foreach($order_statuses as $i=>$_status)
					if ( (int)$order_statuses[$i]['statusID'] == (int)$key ){
						$order_statuses[$i]['selected'] = 1;
						//$total_statuses_amount += $order_statuses[$i]['orders_amount'];
						break;
					}
			}
		}
		
		foreach($order_statuses as $_status){
			$selectedStatuses->setData($_status['statusID'],$_status['selected']);
		}
	}else{
		foreach($order_statuses as $i=>$_status){
			$order_statuses[$i]['selected'] = $selectedStatuses->getData($_status['statusID'],$_status['selected']);
			//$total_statuses_amount += $order_statuses[$i]['orders_amount'];
		}
	}


	$callBackParam = array();
	if ( !isset($_GET['sort']) )$_GET['sort'] = 'orderID';
	$callBackParam['sort'] = $_GET['sort'];
	if ( !isset($_GET['direction']) )$_GET['direction'] = 'DESC';
	$callBackParam['direction'] = $_GET['direction'];

	if ( $_GET['order_search_type'] == 'SearchByOrderID' ){
		$callBackParam['orderID'] = (int)preg_replace('/^'.CONF_ORDERID_PREFIX.'/u', '', $_GET['orderID_textbox']);
	}elseif ( $_GET['order_search_type'] == 'SearchByStatusID' ){
		$orderStatuses = array();			
		foreach($order_statuses as $i=>$_status){
			if ( $_status['selected']){
				$orderStatuses[] = $_status['statusID'];
				$total_statuses_amount += $_status['orders_amount'];
			}
		}
		$callBackParam['orderStatuses'] = $orderStatuses;
	}
	$orders = array();
	$count = 0;
	
	define('ORDERS_PER_PAGE',20);
	$TotalRows = $this->getOrdersNum($callBackParam);
	$TotalPages = ceil($TotalRows/ORDERS_PER_PAGE);
	$smarty->assign('Lister', getLister($page, $TotalPages));

	if(isset($_GET['show_all'])){
		$orders = ordGetOrders($callBackParam, $count, array('offset'=>0,'CountRowOnPage'=>$TotalRows));
	}else{
		$orders = ordGetOrders($callBackParam, $count, array('offset'=>($page-1)*ORDERS_PER_PAGE,'CountRowOnPage'=>(ORDERS_PER_PAGE)));
	}

	$GridHeaders = array(
		array(
			'header_name' => translate("ordr_id"),
			'ascsort' => array('getvars'=>'&sort=orderID&direction=ASC'),
			'descsort' => array('getvars'=>'&sort=orderID&direction=DESC'),
			),
		array(
			'header_name' => translate("ordr_order_time"),
			'ascsort' => array('getvars'=>'&sort=order_time&direction=ASC'),
			'descsort' => array('getvars'=>'&sort=order_time&direction=DESC'),
			),
		array(
			'header_name' => translate("ordr_customer"),
			'ascsort' => array('getvars'=>'&sort=billing_firstname&direction=ASC'),
			'descsort' => array('getvars'=>'&sort=billing_firstname&direction=DESC'),
			),
		array(
			'header_name' => translate('payment'),
			'ascsort' => array('getvars'=>'&sort=payment_type&direction=ASC'),
			'descsort' => array('getvars'=>'&sort=payment_type&direction=DESC'),
			),
		array(
			'header_name' => translate('shipping'),
			'ascsort' => array('getvars'=>'&sort=shipping_type&direction=ASC'),
			'descsort' => array('getvars'=>'&sort=shipping_type&direction=DESC'),
			),
		array(
			'header_name' => translate("ordr_order_total"),
			'ascsort' => array('getvars'=>'&sort=order_amount&direction=ASC'),
			'descsort' => array('getvars'=>'&sort=order_amount&direction=DESC'),
			),
		array(
			'header_name' => translate("str_status")
			),
		);
	for($j = 0;$j<count($GridHeaders);$j++){

		if(!isset($GridHeaders[$j]['ascsort'])||!isset($GridHeaders[$j]['descsort']))continue;
		if(isset($_GET['sort'])&&isset($_GET['direction'])&&$GridHeaders[$j]['ascsort']['getvars'] == '&sort='.$_GET['sort'].'&direction='.$_GET['direction'] )$GridHeaders[$j]['ascsort']['enabled'] = 1;
		if(isset($_GET['sort'])&&isset($_GET['direction'])&&$GridHeaders[$j]['descsort']['getvars'] == '&sort='.$_GET['sort'].'&direction='.$_GET['direction'] )$GridHeaders[$j]['descsort']['enabled'] = 1;
	}

	$db_key=$_SESSION["wbs_dbkey"];
	if(SystemSettings::is_hosted()){
		$session_id = session_id();
		session_write_close();
		
	
		$messageClient = new WbsHttpMessageClient(strtoupper($db_key), 'wbs_msgserver.php');
		$messageClient->putData('action', 'ALLOW_VIEW_ORDER_DETAILS');
		$messageClient->putData('language',(LanguagesManager::getCurrentLanguage()->iso2));
		$res=$messageClient->send();
	
		session_id($session_id);
		session_start();
	

		if($res&&$messageClient->getResult('msg')!=''){
			$msg_type=$messageClient->getResult('msg_type');
			if($msg_type=='error'){
				$smarty->assign('MessageBlock',"<div class='error_block' ><span class='error_message'>".$messageClient->getResult('msg').'</span></div>');
			}else{
				$smarty->assign('MessageBlock',"<div class='comment_block' ><span class='success_message'>".$messageClient->getResult('msg').'</span></div>');
			}
		}
	}else{
		$res = false;
	}

	if(!$res||$messageClient->getResult('success')===true){
		$smarty->assign('GridHeaders', $GridHeaders);
		$smarty->assign('TotalFound', str_replace( "{N}",	''/*$TotalRows*/,translate("msg_n_orders_found")));
		$smarty->assign('TotalCount', $TotalRows);
		$smarty->assign('total_statuses_amount',$defaultCurrency->getView($total_statuses_amount));
		$smarty->hassign( 'orders', $orders );	
		$smarty->assign( 'order_statuses', $order_statuses );
		$smarty->assign('page_enabled','1');
		if ( $export_to_excel&&!isset($_GET['search'])){
			$currencyEntry = Currency::getDefaultCurrencyInstance();
			/* @var $currencyEntry Currency*/
			$currencyISO3 = $currencyEntry->currency_iso_3;
			
			$exportData = new ExportData();
			/*@var $exportData ExportData*/
			$whereClause = '';
			if ( isset($callBackParam["orderStatuses"]) )
			{
				foreach( $callBackParam["orderStatuses"] as $statusID )
					$whereClause .= (strlen($whereClause)?' OR':'').' `orders`.`statusID`='.$statusID;
		
				if ( isset($callBackParam["customerID"]) )
					$whereClause = ' `orders`.`customerID`='.$callBackParam['customerID'].(strlen($whereClause)?' AND ( '.$whereClause.' ) ':'');
				
				$whereClause = strlen($whereClause)?' WHERE '.$whereClause:' WHERE `orders`.`statusID` = -1';
			}
			else
			{
				if ( isset($callBackParam["customerID"]) )
					$whereClause .= " `orders`.`customerID` = ".$callBackParam["customerID"];
		
				if ( isset($callBackParam["orderID"]) )
					$whereClause .= strlen($whereClause)?" AND `orders`.`orderID`=".$callBackParam["orderID"]:" `orders`.`orderID`=".$callBackParam["orderID"];
		
				$whereClause = strlen($whereClause)?" WHERE ".$whereClause:" WHERE `orders`.`statusID` = -1 ";
			}		
			$exportData->sqlWhereClause = $whereClause;
			
			$orderClause = "";			
			if ( isset($callBackParam["sort"]) )
			{
				$orderClause .= " ORDER BY `orders`.`".xEscapeSQLstring($callBackParam["sort"])."` ";
				$orderClause .= ( !isset($callBackParam["direction"])||$callBackParam["direction"] == "ASC" )?" ASC ":" DESC ";
			}			
			$exportData->sqlOrderClause = $orderClause;
			
			$exportData->charset = $_GET['charset'];
			$exportData->setHeaders(array_map('translate',
									array('ordr_id',
										'ordr_order_time',
										'ordr_order_statuses',
										'usr_custinfo_email',
										'ordr_order_total',
										'curr_iso3',
										//'str_universal_currency',										
										//
										'email_ordr_ordered_products',
										'ordr_comment',
										'str_discount',
										'ordr_customer',
										'ordr_customer_ip',
										'ordr_shipping_type',
										'ordr_shipping_address',
										'ordr_shipping_handling_cost',
										'ordr_payment_type',
										'ordr_billing_address')));
			$status_name = LanguagesManager::ml_getLangFieldName('status_name');
			$separator = "////n";
			$exportData->sqlQuery = <<<SQL
			SELECT CONCAT('?#CONF_ORDERID_PREFIX',`orders`.`orderID`) AS orderID,
									`orders`.`order_time`,
									`statuses`.`{$status_name}`,
									`orders`.`customer_email`,
									ROUND(`orders`.`currency_value`*`orders`.`order_amount`,2) AS order_amount,
									`orders`.`currency_code`,
									(SELECT 
										GROUP_CONCAT(
											CONCAT(
												`ord_cont`.`name`,
												' x ',
												`ord_cont`.`Quantity`,
												': {$currencyISO3}',
												ROUND(`ord_cont`.`Quantity`*`ord_cont`.`Price`,2)
												) ORDER BY `ord_cont`.`itemID` DESC SEPARATOR '{$separator}')
										AS `order_content` 
										FROM `?#ORDERED_CARTS_TABLE` AS `ord_cont` 
										WHERE `ord_cont`.`orderID`=`orders`.`orderID` 
										GROUP BY `ord_cont`.`orderID`
									),
									`orders`.`customers_comment`,
									`orders`.`order_discount`,
									CONCAT(`orders`.`customer_firstname`,' ',`orders`.`customer_lastname`) as `customer`,
									`orders`.`customer_ip`,
									`orders`.`shipping_type`,
									CONCAT(
										`orders`.`shipping_firstname`,
										' ',
										`orders`.`shipping_lastname`,
										'{$separator}',
										`orders`.`shipping_address`,
										'{$separator}',
										`orders`.`shipping_city`,
										' ',
										`orders`.`shipping_zip`,
										'{$separator}',
										`orders`.`shipping_state`,
										'{$separator}',
										`orders`.`shipping_country`) AS `shipping_address`,
									`orders`.`shipping_cost`,
									`orders`.`payment_type`,
									CONCAT(
										`orders`.`billing_firstname`,
										' ',
										`orders`.`billing_lastname`,
										'{$separator}',
										`orders`.`billing_address`,
										'{$separator}',
										`orders`.`billing_city`,
										' ',
										`orders`.`billing_zip`,
										'{$separator}',
										`orders`.`billing_state`,
										'{$separator}',
										`orders`.`shipping_country`) AS `billing_address` 
									FROM `?#ORDERS_TABLE` AS `orders` 
									LEFT JOIN `?#ORDER_STATUSES_TABLE` AS `statuses` 
									ON (`statuses`.`statusID` = `orders`.`statusID`)
SQL;
#--									'CONCAT('{$currencyISO3}',`orders`.`order_amount`) AS def_order_amount,
#--									#'CONCAT(`orders`.`shipping_firstname`,' ',`orders`.`shipping_lastname`) as `shipping`,
#--									#'CONCAT(`orders`.`billing_firstname`,' ',`orders`.`billing_lastname`) as `billing`,
			
			
			
			//$exportData->setRowHandler('foreach($row as $key=>&$value){$value = "[{$key}]{$value}";}');
		//	$exportData->setRowHandler('$row[\'orderID\'] = {$value = "[{$key}]{$value}";}');
			$exportData->setRowHandler('$row[\'order_time\'] = Time::standartTime($row[\'order_time\']);return $row;');
			
			
			//$exportData->fileName = DIR_TEMP.'/orders.csv';
			
			$time = microtime(true);
			db_query("SET SESSION group_concat_max_len = 1048576");
			$res = $exportData->exportDataToFile(DIR_TEMP.'/orders.csv');
			$time = microtime(true)-$time;
			
		
			if(PEAR::isError($res)){
				$smarty->assign('MessageBlock','<div class=\'error_block\' ><span class=\'error_message\'>'.translate('lbl_error').'<br></span></div>');
			}else{
				$smarty->assign('MessageBlock',"<div class='success_block' ><span class='success_message'>".str_replace('{time}',sprintf('%01.0fs %01.0fms',$time,$time*1000%1000),translate('msg_orders_exported_to_file'))
				.'<br><br><a href="get_file.php?getFileParam='.Crypt::FileParamCrypt( 'GetOrdersExcelSqlScript', null ).'">'.translate('btn_download').'</a>'.sprintf(' (%3.2f Kb)',filesize( $exportData->fileName )/1024).'</span></div>');
				$smarty->assign( 'orders_has_been_exported_succefully', 1 );
			}
		}else{
			global $file_encoding_charsets;
			$smarty->assign('charsets', $file_encoding_charsets);
			$smarty->assign('default_charset', translate('prdine_default_charset'));
		}
	}elseif ($messageClient->getResult('msg')==''){
		$smarty->assign('MessageBlock',"<div class='error_block' ><span class='error_message'>UNKNOWN ERROR:<br><pre>".var_export($messageClient,true).'</pre></span></div>');
		
	}
}




$ocrt_url = set_query('','index.php?did='.$_GET['rdid'].'&ukey=order_creater');
$olist_url = set_query();
setcookie('olist_url',base64_encode(gzdeflate($olist_url, 9)));

$smarty->assign('ocrt_url', $ocrt_url);
$smarty->assign('sub_template', $this->getTemplatePath('backend/orders_list.html'));
?>