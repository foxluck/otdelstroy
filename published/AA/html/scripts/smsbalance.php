<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "CP";

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$currencyList = array();

	$btnIndex = getButtonIndex( array( "savebtn", "cancelbtn" ), $_POST );

	$users = unserialize( base64_decode( $USERS_ID ) );

	switch ( $btnIndex ) {
		case 0 :

			foreach( $credit as $key=>$value )
			{
				$value = trim($value);

				if ( $value != '' && ( !isIntStr( $value ) || intval( $value ) < 0 ) )
				{
					$errorStr = $kernelStrings["sms_err_balance_not_valid"];
					break 2;
				}

				$credit[$key] = ( $value != '' ) ? $value : null;
			}

			foreach( $credit as $key=>$value )
			{
				if ( PEAR::isError( $ret = addsetSMSBalance( "SET", $value, $currentUser, $key ) ) )
				{
					$errorStr = $kernelStrings["sms_err_balance_update"];
					break;
				}
			}

		case 1:

			redirectBrowser( PAGE_SMS_UA, array() );
			break;
	}

	switch( true ) {
			case true:

				$usersBalance = getSMSUsersBalance( $kernelStrings );
				if (PEAR::isError( $usersBalance ) )
				{
					$errorStr = $balance->getMessage();
					break;
				}

				foreach( $usersBalance as $key=>$value )
				{
					if ( !isset( $users[$key] ) )
					{
						unset( $usersBalance[$key] );
						continue;
					}

					if ( !isset( $edited ) )
					{
						if ( is_null( $value["SMS_BALANCE"] ) )
							$value["VALUE"] = '';
						else
							$value["VALUE"] = sprintf( "%.0d", $value["SMS_BALANCE"] );

						$value["ACTION"] = 0;
					}
					else
					{
						$value["VALUE"] = $credit[$key];
						$value["ACTION"] = $action[$key];
					}

					$usersBalance[$key] = $value;
				}

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['sms_update_balance_screen'] );
	$preproc->assign( FORM_LINK, PAGE_SMS_BALANCE );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "");

	$preproc->assign( "USERS_ID", $USERS_ID );

	if ( !$fatalError ) {
		if ( isset($balance) )
			$preproc->assign( 'systemBalance', $balance );

		$preproc->assign( 'usersBalance', $usersBalance );
		$preproc->assign( 'usersCount', count($usersBalance) );
	}

	$preproc->display("smsbalance.htm" );
?>
