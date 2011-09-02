<?php
if (isset($_SESSION['log']) ) return '';
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();

// Purpose	copies data from $_POST variable to HTML page
// Inputs     		$smarty - smarty object
// Returns	nothing
function _copyDataFromPostToPage( &$smarty )
{
	$smarty->hassign('first_name', $_POST['first_name'] );
	$smarty->hassign('last_name', $_POST['last_name'] );
	$smarty->hassign('email', $_POST['email'] );
	$smarty->assign('subscribed4news', (isset($_POST['subscribed4news'])?1:0) );
/*
@features "Affiliate program"
@state begin
*/
	$smarty->hassign('affiliationLogin', $_POST['affiliationLogin'] );
/*
@features "Affiliate program"
@state end
*/
	$zones = znGetZonesById( $_POST['countryID'] );
	$smarty->hassign('zones',$zones);

	$smarty->assign('countryID', $_POST['countryID'] );
	if ( isset($_POST['state']) )
		$smarty->hassign('state', $_POST['state'] );
	if ( isset($_POST['zoneID']) )
		$smarty->assign('zoneID', $_POST['zoneID'] );
	$smarty->hassign('zip', $_POST['zip'] );
	$smarty->hassign('city', $_POST['city'] );
	$smarty->hassign('address', $_POST['address'] );

	$smarty->hassign( 'receiver_first_name', $_POST['receiver_first_name'] );
	$smarty->hassign( 'receiver_last_name', $_POST['receiver_last_name'] );

	//aux registration fields
	$additional_field_values = array();
	$data = ScanPostVariableWithId( array( 'additional_field' ) );
	foreach( $data as $key => $val )
	{
		$item = array( 'reg_field_ID' => $key, 'reg_field_name' => '', 
			'reg_field_value' => $val['additional_field'] );
		$additional_field_values[] = $item;
	}
	$smarty->hassign('additional_field_values', $additional_field_values );

	if ( CONF_ORDERING_REQUEST_BILLING_ADDRESS == '1' )
	{
		if (  isset($_POST['billing_address_check']) )
			$smarty->assign( 'billing_address_check', '1' );

		if ( !isset($_POST['billing_address_check']) )
		{
			$smarty->hassign( 'payer_first_name', $_POST['payer_first_name'] );
			$smarty->hassign( 'payer_last_name', $_POST['payer_last_name'] );
			$smarty->hassign( 'billingCountryID', $_POST['billingCountryID'] );
			if ( isset($_POST['billingState']) )
				$smarty->hassign( 'billingState', $_POST['billingState'] );
			if ( isset($_POST['billingZoneID']) )
			{
				$smarty->hassign( 'billingZoneID', $_POST['billingZoneID'] );
			}
			$smarty->hassign( 'billingZip', $_POST['billingZip'] );
			$smarty->hassign( 'billingCity', $_POST['billingCity'] );
			$smarty->hassign( 'billingAddress', $_POST['billingAddress'] );

			$billingZones = znGetZonesById( $_POST['billingCountryID'] );
			$smarty->hassign( 'billingZones', $billingZones );
		}
		else
		{
			$smarty->hassign( 'payer_first_name', $_POST['receiver_first_name'] );
			$smarty->hassign( 'payer_last_name', $_POST['receiver_last_name'] );
			$smarty->hassign( 'billingCountryID', $_POST['countryID'] );
			if ( isset($_POST['state']) )
				$smarty->hassign( 'billingState', $_POST['state'] );
			if ( isset($_POST['zoneId']) )
				$smarty->assign( 'billingZoneID', $_POST['zoneId'] );
			$smarty->hassign( 'billingZip', $_POST['zip'] );
			$smarty->hassign( 'billingCity', $_POST['city'] );
			$smarty->hassign( 'billingAddress', $_POST['address'] );
			$smarty->hassign( 'billingZones', $zones);
		}
	}
}

$isPost = isset($_POST['first_name']) && isset($_POST['last_name']);

if ( $isPost ){
	_copyDataFromPostToPage( $smarty );
}else
{
	$zones = znGetZonesById( CONF_DEFAULT_COUNTRY );
	$smarty->hassign('zones',$zones);
	$smarty->assign('billingZones',$zones);
}

if ( isset($_POST['save']) ){
/*
@features 'Affiliate program'
@state begin
*/
	$_POST['affiliationLogin'] = isset($_POST['affiliationLogin'])?$_POST['affiliationLogin']:'';
	$affiliationLogin	= $_POST['affiliationLogin'];
/*
@features 'Affiliate program'
@state end
*/
	if ( !isset($_POST['state']) )$_POST['state'] = '';
	if ( !isset($_POST['zoneID']) )$_POST['zoneID'] = 0;
	if ( !isset($_POST['billingState']) )$_POST['billingState'] = '';
	if ( !isset($_POST['billingZoneID']) )$_POST['billingZoneID'] = 0;

	$error = '';
	$error = quickOrderContactInfoVerify();

	// receiver address
	if ( $error == '' )$error = quickOrderReceiverAddressVerify();

	// payer address
	if ( CONF_ORDERING_REQUEST_BILLING_ADDRESS == '1' && $error == '' )$error = quickOrderBillingAddressVerify();

	if(CONF_ENABLE_CONFIRMATION_CODE){	
		
		require_once(DIR_CLASSES.'/class.ivalidator.php');
		$iVal = new IValidator();
		if(!$iVal->checkCode($_POST['fConfirmationCode']))$error = ERR_WRONG_CCODE;
	}
	
	if ( $error == '' ){
		
		quikOrderSetCustomerInfo();
		quickOrderSetReceiverAddress();
		if (  CONF_ORDERING_REQUEST_BILLING_ADDRESS == '1' )
			quickOrderSetBillingAddress();

		RedirectJavaScript(set_query('?ukey=order2_shipping_quick'));
	}else $smarty->assign( 'reg_error', $error );
}

// additional fields
$additional_fields = GetRegFields();
$smarty->assign('additional_fields', $additional_fields );


$callBackParam = array();
$count_row = 0;
$countries = cnGetCountries( $callBackParam, $count_row );
$smarty->hassign('countries', $countries );

$smarty->assign( 'quick_register', 1 );
$smarty->assign( 'main_content_template', 'register_quick.tpl.html' );
/*
@features "Affiliate program"
@state begin
*/
if(isset($_SESSION['refid']))$smarty->assign('SessionRefererLogin', $_SESSION['refid']);
/*
@features "Affiliate program"
@state end
*/
?>