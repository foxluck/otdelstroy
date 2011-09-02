<?php

$__ur_AAApp = new UR_RO_Container( AA_APP_ID, "app_name_long", AA_APP_ID );
$__ur_AAApp = &$__ur_AAApp;
$UR_Manager->AddChild( $__ur_AAApp );

$__ur_AAScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name", AA_APP_ID );
$__ur_AAScreens = &$__ur_AAScreens;
$__ur_AAApp->AddChild($__ur_AAScreens);

	//$__ur_tmp = new UR_RO_Screen( "CP", "aa_page_title", "aa_page_title",  "aadmin.php", AA_APP_ID );
	$__ur_AAScreens->AddChild( new UR_RO_Screen( "CP", "aa_page_title", "aa_page_title",  "aadmin.php", AA_APP_ID ) );
	
	$__ur_AAApp->SetComment ("app_comment_text");
	
	//$__ur_tmp = &new UR_RO_Screen( "UNG", "ung_page_title", "ung_page_title",  "usersandgroups.php", AA_APP_ID );
	//$__ur_AAScreens->AddChild( $__ur_tmp );
	
	/*$__ur_tmp = &new UR_RO_Screen( "UNG", "ung_page_title", "ung_page_title",  "usersandgroups.php", AA_APP_ID );
	$__ur_AAScreens->AddChild( $__ur_tmp );

	//$__ur_tmp = &new UR_RO_Screen( "ARD", "rp_accessrighsdiags_title", "rp_accessrighsdiags_title",  "accessdiagrams.php", AA_APP_ID );
	//$__ur_AAScreens->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Screen( "CI", "ci_screen_long_name", "ci_screen_short_name",  "company.php", AA_APP_ID );
	$__ur_AAScreens->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Screen( "CL", "cl_screen_long_name", "cl_screen_short_name",  "currencylist.php", AA_APP_ID );
	$__ur_AAScreens->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Screen( "SYS", "sys_screen_long_name", "sys_screen_short_name",  "system.php", AA_APP_ID );
	$__ur_AAScreens->AddChild( $__ur_tmp );*/

	
	
//$__ur_AAFuncs = &new UR_RO_Container( UR_FUNCTIONS, "app_available_subpages_name" );
//$__ur_AAApp->AddChild( $__ur_AAFuncs );

	//$__ur_tmp = &new UR_RO_Bool( "UNG", "ung_page_title", AA_APP_ID );
	//$__ur_AAFuncs->AddChild( $__ur_tmp );


/*
$__ur_AAFuncs = &new UR_RO_Container( UR_FUNCTIONS, "app_available_subpages_name" );
$__ur_AAApp->AddChild( $__ur_AAFuncs );

	$__ur_tmp = &new UR_RO_Bool( "CI", "ci_screen_long_name", AA_APP_ID );
	$__ur_AAFuncs->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Bool( "CL", "cl_screen_long_name", AA_APP_ID );
	$__ur_AAFuncs->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Bool( "SYS", "sys_screen_long_name", AA_APP_ID );
	$__ur_AAFuncs->AddChild( $__ur_tmp );
	
	if ( onWebAsystServer() )
	{
		$__ur_tmp = &new UR_RO_Bool( "AI", "ai_page_title", AA_APP_ID );
		$__ur_AAFuncs->AddChild( $__ur_tmp );
	}
	
	$__ur_tmp = &new UR_RO_Bool( "SMS", "sms_page_title", AA_APP_ID );
	$__ur_AAFuncs->AddChild( $__ur_tmp );
*/

	

	//$__ur_tmp = &new UR_RO_Screen( "SMS", "sms_page_title", "sms_page_title",  "sms.php", AA_APP_ID );
	//$__ur_AAScreens->AddChild( $__ur_tmp );

	require_once WBS_DIR."kernel/cm_groupClass.php";
	require_once WBS_DIR."kernel/class.cm_folderstreedescriptor.php";

	// Global CM group class
	//

	$cm_groupClass = new cm_groupClass( $cm_TreeFoldersDescriptor );
	$cm_groupClass =&$cm_groupClass;
?>