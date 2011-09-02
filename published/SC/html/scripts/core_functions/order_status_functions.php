<?php
define('CONF_ORDSTATUS_PENDING', '2');
define('CONF_ORDSTATUS_PROCESSING', '3');
define('CONF_ORDSTATUS_CANCELLED', '1');
define('CONF_ORDSTATUS_CHARGED', '14');
define('CONF_ORDSTATUS_DELIVERED', '5');
define('CONF_ORDSTATUS_REFUNDED', '15');

function ost_getPredefinedStatusesConsts(){

	return array('CONF_ORDSTATUS_PENDING', 'CONF_ORDSTATUS_PROCESSING', 'CONF_ORDSTATUS_CANCELLED', 'CONF_ORDSTATUS_CHARGED', 'CONF_ORDSTATUS_DELIVERED', 'CONF_ORDSTATUS_REFUNDED');
}

function ost_isPredefinedStatus($statusID){

	return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT predefined FROM ?#ORDER_STATUSES_TABLE WHERE statusID=?', $statusID);
}

function ostInstallPredefinedStatus($status_key){


}

// *****************************************************************************
// Purpose	gets status id corresponded to canceled order
// Inputs
// Remarks
// Returns	nothing
function ostGetCanceledStatusId()
{
	return 1;
}

// *****************************************************************************
// Purpose	if order status is status of canceled order
// Inputs
// Remarks
// Returns	nothing
function _correctOrderStatusName( &$orderStatus ){

	if ( $orderStatus["statusID"] == ostGetCanceledStatusId() )
	$orderStatus["status_name"] = translate("ordr_status_cancelled");
}


// *****************************************************************************
// Purpose	get any status that differents from status with $statusID ID
// Inputs
//				$statusID - status ID
// Remarks
// Returns	item
//				"statusID"		- status ID
//				"status_name"	- status name
//				"sort_order"	- status order
function ostGetOtherStatus( $statusID ){

	/* @var $dbHandler DataBase */
	$dbHandler = Core::getdbHandler();

	$sql = '
		SELECT * FROM ?#ORDER_STATUSES_TABLE
		WHERE statusID<>? AND statusID<>?
	';
	$Result = $dbHandler->query($sql);
	if( $_Row = db_fetch_row($q) ){

		LanguagesManager::ml_fillFields(ORDER_STATUSES_TABLE, $_Row);
		_correctOrderStatusName( $_Row );
		return $_Row;
	}else return false;
}

// *****************************************************************************
// Purpose	get status name ID corresponded to status ID
// Inputs
//			$statusID - status ID
// Remarks
// Returns  status ID
function ostGetOrderStatusName( $statusID )
{
	static $statuses;
	if(!$statuses)$statuses = array();
	if(isset($statuses[$statusID]))return $statuses[$statusID];

	$q = db_phquery('SELECT * FROM ?#ORDER_STATUSES_TABLE');
	while($row = db_fetch_row( $q )){
		LanguagesManager::ml_fillFields(ORDER_STATUSES_TABLE, $row);
		if ($row['statusID'] == ostGetCanceledStatusId() ){
			$row["status_name"] = translate("ordr_status_cancelled");
		}
		$statuses[$row['statusID']] = $row["status_name"];
	}
	return $statuses[$statusID];
}


// *****************************************************************************
// Purpose	get status ID corresponded to comleted order
// Inputs
// Remarks
// Returns  status ID
function ostGetCompletedOrderStatus(){

	return CONF_ORDSTATUS_DELIVERED;
}


// *****************************************************************************
// Purpose	get all order statuses
// Inputs
// Remarks
// Returns	item
//				"statusID"		- status ID
//				"status_name"	- status name
//				"sort_order"	- status order
function ostGetOrderStatues( $fullList = true, $format = 'just' )
{
	$data = array();
	if ( $fullList )
	{
		$q = db_phquery( "SELECT * FROM ?#ORDER_STATUSES_TABLE WHERE statusID=?", ostGetCanceledStatusId() );
		$row = db_fetch_assoc( $q );
		LanguagesManager::ml_fillFields(ORDER_STATUSES_TABLE, $row);

		$r = array( "statusID" => $row["statusID"],
				"status_name" => $row["status_name"], 
				"sort_order" => $row["sort_order"],
		        "color" => $row["color"]);
		_correctOrderStatusName( $r );
		$data[] = $r;
	}

	$q = db_phquery("
		SELECT *, ".LanguagesManager::sql_prepareField('status_name')." AS status_name FROM ?#ORDER_STATUSES_TABLE
		WHERE statusID!=? ORDER BY sort_order ASC, status_name ASC", ostGetCanceledStatusId() );
	while( $r = db_fetch_assoc( $q ) )
	{
		LanguagesManager::ml_fillFields(ORDER_STATUSES_TABLE, $r);
		//if($format!='html')
		//	$r["status_name"] = xHtmlSpecialChars($r["status_name"]);

		$data[] = $r;
	}

	switch ($format){
		default:
		case 'just':
			break;
		case 'html':
			$data = xHtmlSpecialChars($data);
			break;
	}
	return $data;
}

function ost_renderStyle(&$status){

	$status['_style'] = '';
	if($status['color'])$status['_style'] .= ';color: '.xHtmlSpecialChars($status['color']).'!important';
	if($status['bold'])$status['_style'] .= ';font-weight: bold!important';
	if($status['italic'])$status['_style'] .= ';font-style: italic!important';
	if($status['_style'])$status['_style'] = substr($status['_style'], 1).';';
}

function ost_getOrderStatuses($predefined = null){

	$dbres = db_phquery('SELECT * FROM ?#ORDER_STATUSES_TABLE '.(!is_null($predefined)?' WHERE `predefined`='.intval($predefined):'').' ORDER BY predefined DESC, sort_order ASC');
	$statuses = array();
	while ($row = db_fetch_assoc($dbres)){

		LanguagesManager::ml_fillFields(ORDER_STATUSES_TABLE, $row);
		ost_renderStyle($row);
		$row['description'] = translate('ordsts_predefined_description_'.$row['statusID'],false);
		$statuses[$row['statusID']] = $row;
	}
	return $statuses;
}


// *****************************************************************************
// Purpose	add order status
// Inputs
// Remarks
// Returns  status ID
function ostAddOrderStatus($name, $sort_order, $color, $bold, $italic){

	$name_inj = LanguagesManager::sql_prepareFieldInsert('status_name', $name);
	db_phquery("INSERT ?#ORDER_STATUSES_TABLE ({$name_inj['fields']}, sort_order, color, bold, italic) VALUES({$name_inj['values']},?,?,?,?)", $sort_order, $color, $bold, $italic);
	return db_insert_id();
}


// *****************************************************************************
// Purpose	update order status
// Inputs
// Remarks
// Returns  status ID
function ostUpdateOrderStatus( $statusID, $status_name, $sort_order, $color, $bold, $italic ){

	db_phquery('
		UPDATE ?#ORDER_STATUSES_TABLE SET '.LanguagesManager::sql_prepareFieldUpdate('status_name', $status_name).',sort_order=?, color=?, bold=?, italic=? 
		WHERE statusID=?', $sort_order, $color, (int)$bold, (int)$italic, $statusID);
}

// *****************************************************************************
// Purpose	delete order status
// Inputs
// Remarks
// Returns  status ID
function ostDeleteOrderStatus( $statusID )
{
	$statusID = intval($statusID);
	$q = db_query("select count(*) from ".ORDERS_TABLE." where statusID=".$statusID );
	$r = db_fetch_row( $q );
	if ( $r[0] != 0 )
	return false;
	db_query("delete from ".ORDER_STATUSES_TABLE.
		" where statusID=$statusID" );
	return true;
}





function _changeIn_stock( $orderID, $increase )
{
	if ( !CONF_CHECKSTOCK ) return;
	$q = db_query( "select itemID, Quantity from ".ORDERED_CARTS_TABLE.
			" where orderID=$orderID" );
	while( $item = db_fetch_row($q) )
	{
		$Quantity = intval($item["Quantity"]);
		$q1 = db_query( "select productID from ".SHOPPING_CART_ITEMS_TABLE.
				" where itemID=".$item["itemID"] );
		$product = db_fetch_row( $q1 );
		$product["productID"] = intval($product["productID"]);
		if ($product["productID"] && $Quantity)
		{
			if ( $increase )
			db_query( "update ".PRODUCTS_TABLE." set in_stock=in_stock + $Quantity ".
							" where productID=".$product["productID"] );
			else
			db_query( "update ".PRODUCTS_TABLE." set in_stock=in_stock - $Quantity ".
							" where productID=".$product["productID"] );
		}
	}
}


function _changeSOLD_counter( $orderID, $increase )
{
	$q = db_query( "select itemID, Quantity from ".ORDERED_CARTS_TABLE.
			" where orderID=$orderID" );
	while( $item = db_fetch_row($q) )
	{
		$Quantity = $item["Quantity"];
		$q1 = db_query( "select productID from ".SHOPPING_CART_ITEMS_TABLE.
				" where itemID=".$item["itemID"] );
		$product = db_fetch_row( $q1 );
		if ( $product["productID"] != null &&
		trim($product["productID"]) != "" )
		{
			if ( $increase )
			{
				db_query( "update ".PRODUCTS_TABLE." set items_sold=items_sold + $Quantity ".
							" where productID=".$product["productID"] );
			}
			else
			{
				db_query( "update ".PRODUCTS_TABLE." set items_sold=items_sold - $Quantity ".
							" where productID=".$product["productID"] );
			}
		}
	}
}

/**
 * Change order status
 *
 * @param int $orderID - order number
 * @param int $statusID - new status ID
 * @param string $comment
 * @param bool $notify - notify customer about status changing
 */
function ostSetOrderStatusToOrder( $orderID, $statusID, $comment = '', $notify = 1, $force = false ){
	$q1 = db_phquery("SELECT statusID FROM ?#ORDERS_TABLE WHERE orderID=?", $orderID );
	$row = db_fetch_row( $q1 );
	$pred_statusID = (int)$row["statusID"];

	
	if(!$comment){
		$comment = str_replace('%STATUS_NAME%', ostGetOrderStatusName($statusID), translate('ordr_set_custom_status_comment', false));
	}/*elseif($statusID!=$pred_statusID&&$comment){
		$comment = str_replace('%STATUS_NAME%', ostGetOrderStatusName($statusID), translate('ordr_set_custom_status_comment', false)).' '.$comment;
	}*/

	if ( $pred_statusID != $statusID ){
		db_phquery("UPDATE ?#ORDERS_TABLE SET statusID=? WHERE orderID=?", $statusID, $orderID );
		//update product 'in stock' quantity
		if ( ($pred_statusID != ostGetCanceledStatusId()) &&
		($statusID == ostGetCanceledStatusId()) ){
			_changeIn_stock( $orderID, true );

			if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
				include_once(WBS_DIR.'/kernel/classes/class.metric.php');
					
				$DB_KEY=strtoupper(SystemSettings::get('DB_KEY'));
				$U_ID = sc_getSessionData('U_ID');
					
				$metric = metric::getInstance();
				$metric->addAction($DB_KEY, $U_ID, 'SC', 'CANCELORDER', 'ACCOUNT', '');
			}
		}else if (
		($pred_statusID == ostGetCanceledStatusId()) &&
		($statusID != ostGetCanceledStatusId()) ){
			_changeIn_stock( $orderID, false );
		}

		//update sold counter
		if ( ($pred_statusID != CONF_ORDSTATUS_DELIVERED) &&
		($statusID == CONF_ORDSTATUS_DELIVERED) ){
			_changeSOLD_counter( $orderID, true );

			if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
				include_once(WBS_DIR.'/kernel/classes/class.metric.php');
					
				$DB_KEY=SystemSettings::get('DB_KEY');
				$U_ID = sc_getSessionData('U_ID');
					
				$metric = metric::getInstance();
				$metric->addAction($DB_KEY, $U_ID, 'SC', 'DELIVERORDER', 'ACCOUNT', '');
			}
		}else if (
		($pred_statusID == CONF_ORDSTATUS_DELIVERED) &&
		($statusID != CONF_ORDSTATUS_DELIVERED) ){
			_changeSOLD_counter( $orderID, false );
		}

		if ( ($pred_statusID != CONF_ORDSTATUS_PROCESSING) &&
		($statusID == CONF_ORDSTATUS_PROCESSING) ){

			if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
				include_once(WBS_DIR.'/kernel/classes/class.metric.php');
					
				$DB_KEY=SystemSettings::get('DB_KEY');
				$U_ID = sc_getSessionData('U_ID');
					
				$metric = metric::getInstance();
				$metric->addAction($DB_KEY, $U_ID, 'SC', 'PROCESSORDER', 'ACCOUNT', '');
			}
		}
		
		if(($statusID == CONF_ORDSTATUS_DELIVERED)&&CONF_AFFILIATE_PROGRAM_ENABLED){
			include_once(DIR_FUNC.'/affiliate_functions.php');
			affp_addCommissionFromOrder($orderID);
		}
	}

	if ( ($pred_statusID != $statusID)||$force ){
		stChangeOrderStatus($orderID, $statusID, $comment, $notify,$force);
	}
}

function ostGetMaxStatusPriority(){

	return db_phquery_fetch(DBRFETCH_FIRST, 'SELECT MAX(sort_order) FROM ?#ORDER_STATUSES_TABLE');
}
?>