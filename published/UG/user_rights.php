<?php
	$__ur_UGApp = new UR_RO_Container( "UG", "app_name_long", "UG" );
	$__ur_UGApp = &$__ur_UGApp;
	$UR_Manager->AddChild( $__ur_UGApp);

	$__ur_UGScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name", "UG" );
	$__ur_UGScreens = &$__ur_UGScreens;
	$__ur_UGApp->AddChild( $__ur_UGScreens );

//		$__ur_tmp = &new UR_RO_Screen( "UNG", "aa_page_title", "aa_page_title",  "usersandgroups.php", "UG" );
		$__ur_UGScreens->AddChild( new UR_RO_Screen( "UNG", "aa_page_title", "aa_page_title",  "usersandgroups.php", "UG" ) );
		
	//$__ur_UGFuncs = &new UR_RO_Container( UR_FUNCTIONS, "app_available_functions_name" );
	$__ur_UGApp->SetComment ("app_comment_text");
	//$__ur_UGApp->AddChild( $__ur_UGFuncs );

		//$__ur_tmp = &new UR_RO_Bool( APP_CANREPORTS_RIGHTS, "app_canreports_label", UG_APP_ID);
		//$__ur_UGFuncs->AddChild( $__ur_tmp );
?>