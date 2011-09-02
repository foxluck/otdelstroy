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

	$userID = base64_decode($U_ID);

	$btnIndex = getButtonIndex( array( "cancelbtn" ), $_POST );

	switch ( $btnIndex ) {
		case 0 : {

			redirectBrowser( PAGE_SMS, array() );
			break;
		}
	}

	switch( true ) {
			case true: {

				$userName = getUserName( $userID, true );

				if ( PEAR::isError( $userName ) )
				{
					$errorStr = $userName->getMessage();
					break;
				}

				$quoteHist = getSMSBalanceHistory( $userID );

				if ( PEAR::isError( $quoteHist ) )
				{
					$errorStr = $quoteHist->getMessage();
					break;
				}

				$balance = getSMSBalance( $userID );
				if (PEAR::isError( $balance ) )
				{
					$errorStr = $balance->getMessage();
					break;
				}

				foreach( $quoteHist as $id=>$value )
				{
					$value["SMSG_DATETIME"] = convertToDisplayDateTime($value["SMSG_DATETIME"], false, true, true );
					$quoteHist[$id] = $value;
				}

				$totalNum = count($quoteHist);
				$showPageSelector = false;
				$pages = null;
				$pageCount = 0;
				$quoteHist = addPagesSupport( $quoteHist, RECORDS_PER_PAGE, $showPageSelector, $currentPage, $pages, $pageCount );

			}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings[ $userID == '$SYSTEM' ? 'sms_credit_system_history_screen' : 'sms_credit_history_screen' ] );
	$preproc->assign( FORM_LINK, PAGE_SMS_CREDIT_HIST );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "");

	$preproc->assign( "U_ID", $U_ID );
	$preproc->assign( "userID", $userID );

	if ( !$fatalError )
	{
		$preproc->assign( "quoteHist", $quoteHist );

		$preproc->assign( "userName", $userName );
		$preproc->assign( "userId", $userID );

		$preproc->assign( "systemUser",  $userID == '$SYSTEM' ? 1 : 0 );

		$preproc->assign( "numDocuments", $totalNum );
		$preproc->assign( PAGES_SHOW, $showPageSelector );
		$preproc->assign( PAGES_PAGELIST, $pages );
		$preproc->assign( PAGES_CURRENT, $currentPage );
		$preproc->assign( PAGES_NUM, $pageCount );
		$preproc->assign( PAGES_CURRENT, $currentPage );

	}

	$preproc->display("smscrhist.htm" );
?>
