<?php
class TaxTypesController extends ActionsController{

	function delete_state(){
		
		safeMode(true, 'zoneID=');
		taxDeleteZoneRate( $this->getData('define_zone_rates'), $this->getData('zoneID'));
		
		RedirectSQ('zoneID=');
	}
	
	function delete_zip(){
		
		safeMode(true, 'tax_zipID=');
		
		taxDeleteZipRate( $this->getData('tax_zipID') );
		
		RedirectSQ('tax_zipID=');
	}
	
	function save_zips(){
		
		safeMode(true);
		$error_percent = '';
		$data = scanArrayKeysForID($_POST, array( 'zip_template', 'zip_rate' ) );
		foreach( $data as $key => $val )
		{
			taxUpdateZipRate( $key, $val['zip_template'], 
				_verifyPerCent($val['zip_rate'], $verifyResult) );
			if ( $verifyResult == 1 ) 
					$error_percent = '&error_percent=yes';
		}
		if ( trim($_POST['new_zip_template']) != '' )
		{
			taxAddZipRate( $_GET['define_zone_rates'], 
				$_GET['countryID'], $_POST['new_zip_template'], 
				_verifyPerCent($_POST['new_zip_rate'], $verifyResult) );
			if ( $verifyResult == 1 ) 
					$error_percent = '&error_percent=yes';
		}
	
		taxSetIsByZoneAttribute( $_GET['define_zone_rates'], $_GET['countryID'], 1 );

		if($error_percent)Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'err_percent_is_out_of_0_100');

		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_update_successful');
	}
	
	function save_states(){
		
		safeMode(true);
		$error_percent = '';
		$data = scanArrayKeysForID($_POST, array('zone_rate') );
		foreach( $data as $key => $val ){
			
			taxUpdateZoneRate( $_GET['define_zone_rates'], $key, _verifyPerCent($val['zone_rate'], $verifyResult ) );
			if ( $verifyResult == 1 )$error_percent = '&error_percent=yes';
		}
		if ( isset($_POST['new_zone']) && (int)$_POST['new_zone'] != -1 ){

			taxAddZoneRate( $_GET['define_zone_rates'], $_GET['countryID'], $_POST['new_zone'], _verifyPerCent($_POST['new_rate'], $verifyResult) );
			if ( $verifyResult == 1 )$error_percent = '&error_percent=yes';
		}
	
		taxSetIsByZoneAttribute( $_GET['define_zone_rates'], $_GET['countryID'], 1 );
	
		if($error_percent)Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'err_percent_is_out_of_0_100');

		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_update_successful');
	}
	
	function delete_country(){
		
		safeMode(true, 'countryID=');
		
		taxDeleteRate( $_GET['define_rate'], $_GET['countryID']);
		
		RedirectSQ('countryID=');
	}
	
	function save_countries(){
		
		safeMode(true);
		
		$data = scanArrayKeysForID($_POST, array('isByZone', 'rate') );
		$error = false;
		foreach( $data as $key => $val ){
			if ( !isset($val['isByZone']) ) $val['isByZone']=0;
			taxUpdateRate($_GET['define_rate'],	$key, $val['isByZone'], _verifyPerCent($val['rate'], $verifyResult) );
			if($verifyResult)$error = true;
		}
	
		if ( isset($_POST['new_country']) && (int)$_POST['new_country'] != -1 ){
			
			taxAddRate( $_GET['define_rate'], $_POST['new_country'], 0, _verifyPerCent($_POST['new_rate'], $verifyResult) );
			if($verifyResult)$error = true;
		}
			
		if($error)Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'err_percent_is_out_of_0_100');

		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_update_successful');
	}
	
	function delete_class(){
		
		safeMode(true);
		taxDeleteTaxClass( $this->getData("classID"));
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'classID', 'msg_update_successful');
	}
	
	function save_classes(){

		safeMode(true);
		
		$data = scanArrayKeysForID($_POST, array( 'class_name', 'tax_based_on_address' ) );
		foreach( $data as $key => $val )taxUpdateTaxClass( $key, $val['class_name'], $val['tax_based_on_address'] );
		
		if ( trim($_POST['new_class_name']) != '' )taxAddTaxClass( trim($_POST['new_class_name']),$_POST['new_tax_based_on_address'] );
		
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_update_successful');
	}
	
	function main(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		
		if ( isset($_GET['define_rate']) )
			_showRates( $smarty );
		else if ( isset($_GET['define_zone_rates']) )
			_showZoneRates( $smarty );
		else
			_showTaxClasses( $smarty );
		
		$smarty->assign('admin_sub_dpt', 'conf_taxes.tpl.html');
	}
}

ActionsController::exec('TaxTypesController');

function _verifyPerCent( $value, &$verifyResult )
{
	$value = (float)$value;
	$verifyResult = 0;
	if ( $value < 0 )
	{
		$value = 0;
		$verifyResult = 1;
	}
	if ( $value > 100 )
	{
		$value = 100;
		$verifyResult = 1;
	}
	return $value;
}

// *****************************************************************************
// Purpose	sets in smarty template corresponded variables to show rates by classes
// Inputs   
// Remarks	
// Returns	
function _showRates( &$smarty )
{
	$class = taxGetTaxClassById($_GET['define_rate']);
	$smarty->assign('class_name', $class['name']);
	$rates = taxGetRates($_GET['define_rate']);
	foreach( $rates as $val )
		if ( $val['countryID'] == 0 )
			$smarty->assign( 'group_exists', 1 );

	$admin_is_depended_on_zone = array();
	$count_zones = array();
	foreach( $rates as $val )
		if ( $val['countryID'] != 0 )
		{
			$str = translate("tax_rate_depends_on_region");
			$str = str_replace( '{N}', taxGetCountSetZone($_GET['define_rate'], 
					$val['countryID']),  $str );
			$str = str_replace( '{M}', taxGetCountZones($val['countryID']), 
					$str );
			$count_zones[$val['countryID']] = taxGetCountZones($val['countryID']);
			$admin_is_depended_on_zone[$val['countryID']] = $str;
		}
	$smarty->assign('admin_is_depended_on_zone', $admin_is_depended_on_zone);
	$smarty->assign('count_zones', $count_zones);

	$smarty->assign('rates', $rates );
	$smarty->assign('rate_count', count($rates) );
	$countries = taxGetCountriesByClassID_ToSetRate($_GET['define_rate']);
	$smarty->assign('countries', $countries );
	$smarty->assign('country_count', count($countries) );
	$smarty->assign('define_rate', $_GET['define_rate'] );
}


// *****************************************************************************
// Purpose	sets in smarty template corresponded variables to show zone rates and 
//				zip rates
// Inputs   
// Remarks	
// Returns	
function _showZoneRates( &$smarty )
{
	$zone_rates = taxGetZoneRates( $_GET['define_zone_rates'], 
		$_GET['countryID'] );
	foreach( $zone_rates as $val )
		if ( $val['zoneID'] == 0 )
			$smarty->assign( 'group_exists', 1 );
	$smarty->assign('zone_rates', $zone_rates );
	$smarty->assign('zone_rate_count', count($zone_rates) );

	$zip_rates = taxGetZipRates( $_GET['define_zone_rates'], $_GET['countryID'] );
	$smarty->assign('zip_rates', $zip_rates );
	$smarty->assign('rowspan', 
			count($zone_rates) +	// zone count
			2 +						// ADD row + new row
			1 +						// SAVE button
			1 +						// define taxes by zones title
			1 +						// head new table
			count($zip_rates)	+	// zip rates count
			2 +						// ADD row + new row
			2 +						// 2 prompts
			1						// SAVE button
				);
	$zones = taxGetZoneByClassIDCountryID_ToSetRate( $_GET['define_zone_rates'], 
			$_GET['countryID'] );
	$smarty->assign('zones', $zones );
	$smarty->assign('zone_count', count($zones) );
	$smarty->assign('define_zone_rates', $_GET['define_zone_rates'] );
	$tax_class = taxGetTaxClassById( $_GET['define_zone_rates'] );
	$smarty->assign('className', $tax_class['name'] );
	$country = cnGetCountryById( $_GET['countryID'] );
	$smarty->hassign('country_name', $country['country_name'] );
}


// *****************************************************************************
// Purpose	sets in smarty template corresponded variables to show classes
// Inputs   
// Remarks	
// Returns	
function _showTaxClasses( &$smarty )
{
	$smarty->assign('classes', taxGetTaxClasses() );
}
?>