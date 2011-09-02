<?php
	if ( isset($_GET['delete']) ){
		
		safeMode(true);
		znDeleteZone( $_GET['delete'] );
		RedirectSQ( 'delete=' );
	}
	
	if ( isset($_POST['save_zones']) ){
		
		safeMode(true);
	
		$countryID = $_GET['countryID'];
		// add new zone
		if ( !LanguagesManager::ml_isEmpty('zone_name', $_POST) )
			znAddZone( $_POST, $_POST['zone_code'], $countryID );
	
		// update zones list
		$data = scanArrayKeysForID($_POST, array('zone_name_\w{2}', 'zone_code' ) );
	
		foreach( $data as $key => $val ){
			
			znUpdateZone($key, $val, $val['zone_code'], $countryID );
		}
		
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', translate("msg_update_successful"));
	}
	
	//if country is not selected, select the first country from the database
	if ( !isset($_GET['countryID']) ){
		
		RedirectSQ( 'countryID='.(CONF_DEFAULT_COUNTRY?CONF_DEFAULT_COUNTRY:db_phquery_fetch(DBRFETCH_FIRST, 'SELECT countryID FROM ?#COUNTRIES_TABLE LIMIT 1')));
	}
	$countryID = max(1,intval($_GET['countryID']));
	
	$callBackParam		= null;
	$count_row			= 0;
	$navigatorParams	= null;
	$countries = cnGetCountries( $callBackParam, $count_row, $navigatorParams );
	if(!isset($countries[$countryID])){
		$country = array_shift($countries);
		RedirectSQ( 'countryID='.$country['countryID']);
	}
	
	$smarty->assign('countries', $countries);
	$zones = znGetZones($countryID);
	$smarty->assign('zones',$zones);
	$smarty->assign('zones_count',count($zones));
	
	$smarty->assign('countryID', $_GET['countryID'] );
	
	//set sub-department template
	$smarty->assign('admin_sub_dpt', 'conf_zones.tpl.html');
?>