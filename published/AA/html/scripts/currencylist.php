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

	switch( true ) {
			case true : {
						if ( $fatalError )
							break;

						$currencyList = listCurrency();
						if ( PEAR::isError($currencyList) ){
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

							$fatalError = true;
							break;
						}

						if ( is_array($currencyList) ) 
							foreach( $currencyList as $CUR_ID=>$CUR_DATA ) {
								$CUR_DATA["EDIT_URL"] = prepareURLStr( PAGE_ADDMODCURRENCY, array( ACTION=>ACTION_EDIT, "CUR_ID"=>base64_encode($CUR_ID) ) );
								$CUR_DATA["CUR_NAME"] = prepareStrToDisplay( $CUR_DATA["CUR_NAME"] );
								$currencyList[$CUR_ID] = $CUR_DATA;
							}

			}
	}

	$btnIndex = getButtonIndex( array("addbtn"), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( PAGE_ADDMODCURRENCY, array( ACTION=>ACTION_NEW ) );

			break;
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['cl_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_CURRENCYLIST );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "currencylist.htm");

	if ( !$fatalError ) {
		$preproc->assign( "currencyList", $currencyList );
	}

	$preproc->display("currencylist.htm" );
?>