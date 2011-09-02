<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "CP";

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	$smsClass = $WBS_MODULES->getClass(MODULE_CLASS_SMS);

	$smsDisabled = $smsClass->isDisabled( );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$currencyList = array();

	$btnIndex = getButtonIndex( array( "updatebtn", "histbtn" ), $_POST );

	switch ( $btnIndex ) {
		case 0 :

			if ( !isset( $balance ) || is_null( $balance ) || !count( $balance ) )
			{
					$errorStr = $kernelStrings["sms_screen_empty_message"];
					break;
			}

			$params = array( "USERS_ID"=>base64_encode( serialize( $balance ) ) );

			redirectBrowser( PAGE_SMS_BALANCE, $params );
			break;

		case 1 :

			$params = array( "U_ID"=>base64_encode( '$SYSTEM' ) );

			redirectBrowser( PAGE_SMS_CREDIT_HIST, $params );
			break;
	}

	switch( true ) {
			case true: {

				$balance = getSMSBalance( '$SYSTEM' );
				if (PEAR::isError( $balance ) )
				{
					$errorStr = $balance->getMessage();
					break;
				}

				$usersBalance = getSMSUsersBalance( $kernelStrings );
				if (PEAR::isError( $usersBalance ) )
				{
					$errorStr = $balance->getMessage();
					break;
				}

				foreach( $usersBalance as $key=>$value )
				{
					$params = array( "USERS_ID"=>base64_encode( serialize( array( $value["U_ID"]=>1 ) ) ) );
					$value["BALANCE_URL"] = prepareURLStr( PAGE_SMS_BALANCE, $params );

					$params = array( "U_ID"=>base64_encode( $value["U_ID"] ));

					$value["SMS_UNLIM"] = is_null( $value["SMS_BALANCE"] ) ? 1 : 0;
					$value["SMS_BALANCE"] = sprintf( "%10.0d", $value["SMS_BALANCE"] );

					$value["CREDIT_URL"] = prepareURLStr( PAGE_SMS_CREDIT_HIST, $params );
					$value["HISTORY_URL"] = prepareURLStr( PAGE_SMS_HIST, $params );


					$usersBalance[$key] = $value;
				}

			}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['sms_ua_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_SMS_UA );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "");


	if ( !$fatalError ) {
		$preproc->assign( 'systemBalance', $balance );
		$preproc->assign( 'usersBalance', $usersBalance );
		$preproc->assign( 'usersCount', count($usersBalance) );

		$preproc->assign( 'noteusers', nl2br($kernelStrings["sms_sms_select_users_title"] ) );
	}

	$preproc->assign( 'smsDisabled', $smsDisabled );

	$preproc->display("sms_ua.htm" );
?>
