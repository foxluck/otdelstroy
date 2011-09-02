<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;

	pageUserAuthorization( null, $AA_APP_ID, true );
	redirectBrowser( PAGE_BLANK, array() );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];

	switch ( true ) {
		case true :
				if ( isset($edited) ) {
					if ( !isset($showTtOnStartUp) || !$showTtOnStartUp ) {
						$res = writeUserCommonSetting( $currentUser, START_PAGE, USE_BLANK, $kernelStrings );

						redirectBrowser( PAGE_BLANK, array() );
					} else
						$res = writeUserCommonSetting( $currentUser, START_PAGE, USE_TIPSANDTRICKS, $kernelStrings );
				}

				$showTtOnStartUp = 0;

				$quckStartSeen = readUserCommonSetting( $currentUser, WBS_TT_QUICKSTART_SEEN );
				if ( !strlen($quckStartSeen) || !$quckStartSeen )
					$showQuickStart = 1;
				else
					$showQuickStart = 0;

				writeUserCommonSetting( $currentUser, WBS_TT_QUICKSTART_SEEN, 1, $kernelStrings );

				if ( readUserCommonSetting( $currentUser, START_PAGE ) == USE_TIPSANDTRICKS )
					$showTtOnStartUp = 1;


				$tmp = explode('/', $_SERVER['SERVER_PROTOCOL']);
				$protocol = strtolower(array_shift($tmp));

				if ( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on' )
					$protocol = "https";
	}

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['app_pagewelcome_title'] );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( FORM_LINK, "tipsandtricks.php" );

	$preproc->assign( "protocol", $protocol );
	$preproc->assign( "showTtOnStartUp", $showTtOnStartUp );
	$preproc->assign( "showQuickStart", $showQuickStart );

	$preproc->display( "tipsandtricks.htm" );
?>