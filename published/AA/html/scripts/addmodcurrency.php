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
	$invalidField = null;

	switch( true ) {
			case true : {
				if ( $fatalError )
					break;

				if ( (!isset($edited) || !$edited) && $action == ACTION_EDIT ) {
					$CUR_ID = base64_decode( $CUR_ID );

					$currencyData = db_query_result( $qr_select_currency, DB_ARRAY, array( "CUR_ID"=>$CUR_ID ) );
					if ( PEAR::isError($currencyData) ) {
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

						$fatalError = true;
						break;
					}
				}
			}
	}

	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE, "deletebtn"), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( PAGE_CURRENCYLIST, array() );
		}
		case 1 : {
			$res = addmodCurrency( $action, prepareArrayToStore($currencyData), $kernelStrings );

			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();

				if ( $res->getCode() == ERRCODE_INVALIDFIELD || $res->getCode() == ERRCODE_INVALIDLENGTH )
					$invalidField = $res->getUserInfo();

				break;
			}


			redirectBrowser( PAGE_CURRENCYLIST, array() );
		}
		case 2 : {
			$res = deleteCurrency( prepareArrayToStore($currencyData), $kernelStrings, $language );

			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();
				break;
			}

			redirectBrowser( PAGE_CURRENCYLIST, array() );
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, ($action == ACTION_NEW) ? $kernelStrings['amc_pageadd_title'] : $kernelStrings['amc_pagemodify_title'] );
	$preproc->assign( ACTION, $action );
	$preproc->assign( FORM_LINK, PAGE_ADDMODCURRENCY );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( $action == ACTION_NEW )
		$preproc->assign( HELP_TOPIC, "addcurrency.htm");
	else
		$preproc->assign( HELP_TOPIC, "modifycurrency.htm");

	if ( !$fatalError ) {
		if ( isset($currencyData) )
			$preproc->assign( "currencyData", prepareArrayToDisplay($currencyData, null, isset($edited) && $edited) );
	}

	$preproc->display( "addmodcurrency.htm" );
?>