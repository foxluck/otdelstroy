<?php

	if ( !isset( $_POST["DB_KEY"] ) && !isset( $_GET["DB_KEY"] ) )
		die( "No valid DB KEY detected." );

	$get_key_from_url = true;
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

	$templateName = "classic";
//	$language = LANG_ENG;

	// Load application strings
	//
	$kernelStrings = $loc_str[$language];
	$db_locStrings = $db_loc_str[$language];

	$fatalError = false;
	$errorStr = null;

	switch( true )
	{
		case true:
	}

	$btnIndex = getButtonIndex( array("savebtn", "cancelbtn"), $_POST );

	switch( $btnIndex ) {
		case 0 :

			$quoteData["VALUE"] = ( $quoteData["VALUE"]!= "" ) ? floatval( $quoteData["VALUE"] ) : $quoteData["VALUE"];

			if ( $quoteData["VALUE"]!= "" || $quoteData["VALUE"] < 0 )
			{
				$errorStr = "Balance field's value must be greater or equal then 0.";
				break;
			}

			if ( PEAR::isError( $ret = addsetSMSBalance( $quoteData["ACTION"], $quoteData["VALUE"] ) ) )
			{
				$errorStr = $ret->getMessage();
				break;
			}

		case 1 :
			$params = array( ACTION=>ACTION_EDIT, "DB_KEY" => base64_decode( $DB_KEY ) );
			redirectBrowser( PAGE_DB_DBPROFILE, $params );
	}

	switch( !$fatalError )
	{
		case true:

			$quoteHist = getSMSBalanceHistory( '$SYSTEM' );

			if ( PEAR::isError( $quoteHist ) )
			{
				$errorStr = $quoteHist->getMessage();
				break;
			}

			$balance = getSMSBalance( '$SYSTEM' );
			if (PEAR::isError( $balance ) )
			{
				$errorStr = $balance->getMessage();
				break;
			}

			foreach( $quoteHist as $id=>$value )
			{
				$value["SMSG_DATETIME"] = convertToDisplayDateTime($value["SMSG_DATETIME"]);
				$quoteHist[$id] = $value;
			}


			if ( !isset( $edited ) )
				$quoteData = array();
	}

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );
	$preproc->assign( FORM_LINK, PAGE_DB_BALANCE );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "loginURL", getLoginURL() );
	$preproc->assign( "pageTitle", "WebAsyst Administrator - View and Modify SMS Balance" );

	$preproc->assign( "DB_KEY", $DB_KEY );

	$preproc->assign( "DB_NAME", base64_decode( $DB_KEY ) );

	if ( !$fatalError ) {
		$preproc->assign( "quoteHist", $quoteHist );
		$preproc->assign( "quoteData", $quoteData );

		$preproc->assign( "balance", $balance );
	}

	$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
	$preproc->assign( 'pdfAdminFile', GUIDE_FILE );
	$preproc->assign( 'returnLink', PAGE_DB_WBSADMIN );

	$preproc->display( "balance.htm" );
?>
