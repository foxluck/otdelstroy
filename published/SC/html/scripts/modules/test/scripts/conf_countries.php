<?php
if ( isset($_GET['delete']) ){
	
	safeMode(true);
	
	cnDeleteCountry( $_GET['delete'] );
	RedirectSQ( 'delete=' );
}

if ( isset($_POST['save_countries']) ){
	
	safeMode(true);

	// add new country
	if ( !LanguagesManager::ml_isEmpty('country_name', $_POST) )
		cnAddCountry($_POST,$_POST['country_iso2'], $_POST['country_iso3']);

	// update countries
	$data = scanArrayKeysForID($_POST, array('country_name_\w{2}', 'country_iso2', 'country_iso3' ) );

	// update existing pictures
	foreach( $data as $key => $val ){
		
		cnUpdateCountry($key, $val, $val['country_iso2'], $val['country_iso3'] );
	}
	
	Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', translate("msg_update_successful"));
}


$callBackParam = array('raw data'=>true);
$countries = array();

if(isset($_GET['show_all'])){
	$Register = &Register::getInstance();
	$Register->assign('show_all',1);
	renderURL('show_all=','',true);
}

$count = 0;
$navigatorHtml = GetNavigatorHtml( set_query('show_all='), 20, 'cnGetCountries', $callBackParam, $countries, $offset, $count );

$smarty->assign('countries',$countries);
$smarty->assign('navigator', $navigatorHtml );

//set sub-department template
$smarty->assign('admin_sub_dpt', 'conf_countries.tpl.html');
?>