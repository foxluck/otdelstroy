<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;

	$SCR_ID = "CP";
	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false, true );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$currencyList = array();

	$btnIndex = getButtonIndex( array( "backbtn" ), $_POST );

	switch ( $btnIndex ) {
		case 0 : {

			$params = array( "currentPage"=>$currentPage, "histtype"=>1);//, "U_ID"=>$U_ID );

			$openerUrl = PAGE_SMS_HIST;
			switch ($opener)
			{
				case 1 : $openerUrl = PAGE_SMS; break;
				case 2: $openerUrl = PAGE_SMS_HISTORY; break;
			}

			redirectBrowser( $openerUrl, $params );
			break;
		}
	}

	switch( true ) {
			case true: {

				$smsInfo = getSMSFromHistory( intval( $smsId ) );

				if ( PEAR::isError( $smsInfo ) )
				{
					$errorStr = $smsInfo->getMessage();
					$fatalError = true;
					break;
				}
				else
				if ( is_null( $smsInfo ) )
				{
					$errorStr = $kernelStrings["sms_history_id_error"];
					$fatalError = true;
					break;
				}

				$smsInfo["SMSH_DATETIME"] = convertToDisplayDateTime( $smsInfo["SMSH_DATETIME"], false, true, true  );

				$userName = getUserName( $smsInfo["SMSH_USER_ID"] );

				if ( !PEAR::isError( $userName ) )
					$smsInfo["SMSH_USER_ID"] = $userName." (".$smsInfo["SMSH_USER_ID"].")";

				$smsInfo["SMSH_APP"] = getAppName( $smsInfo["SMSH_APP"], $language );

				if ( $smsInfo["SMSH_STATUS"] != SMS_STATUS_CANCELED )
					$smsInfo["SMSH_STATUS"] = $kernelStrings[$sms_StatusNamesArray[$smsInfo["SMSH_STATUS"]]];
				else
					$smsInfo["SMSH_STATUS"] = $kernelStrings[$sms_StatusNamesArray[$smsInfo["SMSH_STATUS"]]]." (".$smsInfo["SMSH_STATUS_TEXT"].")";

				if ( !isset( $opener ) )
					$opener = 0;
			}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings[ 'sms_sd_screen_name' ] );

	$preproc->assign( FORM_LINK, PAGE_SMS_TEXT );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "");


	if ( !$fatalError ) {
		$preproc->assign( "smsInfo", $smsInfo );
	}

	$preproc->assign( "currentPage", intval( $currentPage ) );
	$preproc->assign( "opener", intval( $opener ) );

	if ( isset($U_ID) )
		$preproc->assign( "U_ID", $U_ID );

	$preproc->display("smstext.htm" );
?>
