<?php

$__ur_MWApp = new UR_RO_Container( MW_APP_ID, "app_name_long", MW_APP_ID );
$__ur_MWApp = &$__ur_MWApp;
$UR_Manager->AddChild( $__ur_MWApp );

$__ur_MWScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name", MW_APP_ID  );
$__ur_MWScreens = &$__ur_MWScreens;
$__ur_MWApp->AddChild( $__ur_MWScreens );

	//$__ur_tmp = &new UR_RO_Screen( "PF", "pf_screen_long_name", "pf_screen_short_name", "preferences.php", MW_APP_ID  );
	$__ur_MWScreens->AddChild( new UR_RO_Screen( "PF", "pf_screen_long_name", "pf_screen_short_name", "preferences.php", MW_APP_ID  ) );
	
	/*$__ur_tmp = &new UR_RO_Screen( "CP", "cp_screen_long_name", "cp_screen_short_name",  "changepassword.php", MW_APP_ID );
	$__ur_MWScreens->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Screen( "LF", "lf_screen_long_name", "lf_screen_short_name", "lookandfeel.php", MW_APP_ID  );
	$__ur_MWScreens->AddChild( $__ur_tmp );
*/
	
/*
$__ur_MWFuncs = &new UR_RO_Container( UR_FUNCTIONS, "app_available_functions_name", MW_APP_ID  );
$__ur_MWApp->AddChild( $__ur_MWFuncs );

	$__ur_tmp = &new UR_RO_Bool( "NC", "amu_persnameandcont_label",  MW_APP_ID );
	$__ur_MWFuncs->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Bool( "EMAIL", "amu_persswitchemail_label",  MW_APP_ID );
	$__ur_MWFuncs->AddChild( $__ur_tmp );
*/
	
$__ur_MWTabs = new UR_RO_Container( UR_FUNCTIONS, "app_available_tabs_name", MW_APP_ID  );
$__ur_MWTabs = &$__ur_MWTabs;
$__ur_MWApp->AddChild( $__ur_MWTabs );
	
	//$__ur_tmp = &new UR_RO_Bool( "TAB_CONTACT", "amu_contact_title",  MW_APP_ID );
	$__ur_MWTabs->AddChild( new UR_RO_Bool( "TAB_CONTACT", "amu_contact_title",  MW_APP_ID ) );
	
	//$__ur_tmp = &new UR_RO_Bool( "TAB_USER", "amu_user_title",  MW_APP_ID );
	$__ur_MWTabs->AddChild( new UR_RO_Bool( "TAB_USER", "amu_user_title",  MW_APP_ID ) );
	
	//$__ur_tmp = &new UR_RO_Bool( "TAB_GROUPS", "amu_groups_title",  MW_APP_ID );
	$__ur_MWTabs->AddChild( new UR_RO_Bool( "TAB_GROUPS", "amu_groups_title",  MW_APP_ID ) );
	
	//$__ur_tmp = &new UR_RO_Bool( "TAB_ACCESS", "amu_access_title",  MW_APP_ID );
	$__ur_MWTabs->AddChild( new UR_RO_Bool( "TAB_ACCESS", "amu_access_title",  MW_APP_ID ) );
	
	//$__ur_tmp = &new UR_RO_Bool( "TAB_QUOTA", "amu_quota_title",  MW_APP_ID );
	$__ur_MWTabs->AddChild( new UR_RO_Bool( "TAB_QUOTA", "amu_quota_title",  MW_APP_ID ) );
	
	//$__ur_tmp = &new UR_RO_Bool( "TAB_LOOKANDFEEL", "amu_lookandfeel_title",  MW_APP_ID );
	//$__ur_MWTabs->AddChild( $__ur_tmp );


$__ur_MWDA = new UR_RO_Container( "DA", "amu_directaccess_title", MW_APP_ID  );
$__ur_MWDA = &$__ur_MWDA;
$__ur_MWApp->AddChild( $__ur_MWDA );

	$__ur_tmp = new UR_RO_Bool( "DIRECTACCESS", "amu_diraccess_text",  MW_APP_ID );
	$__ur_tmp = &$__ur_tmp;
	$__ur_tmp->SetComment( "amu_directaccess_text" );
	$__ur_MWDA->AddChild( $__ur_tmp );
	unset($__ur_tmp);

?>
