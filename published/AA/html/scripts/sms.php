<?php

	define("NOT_CACHE_MODULES", true);
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."kernel/sms.php" );

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

	$smsClass = $WBS_MODULES->getClass(MODULE_CLASS_SMS);

	$smsDisabled = $smsClass->isDisabled( );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$currencyList = array();

	$btnIndex = getButtonIndex( array( "cancelbtn", "histbtn", "backbtn", "chargebtn" ), $_POST );
	$chargeUpdated = "";
	
	switch ( $btnIndex ) {
		case 0 :

			redirectBrowser( PAGE_SMS_UA, array() );
			break;

		case 1 :

			if ( $histtype == 1 )
			{
				$toTS = time();
				$fromTS = $toTS - 864000;

				$emptyto = false;
				$emptyfrom = false;
			}
			else
			if ( $histtype == 2 )
			{
				$emptyfrom = false;
				$validfrom = true;
				$fromTS = 0;

				if ( trim( $fromdate ) != "" )
					$validfrom = validateInputDate( $fromdate, $fromTS );
				else
					$emptyfrom = true;

				$emptyto = false;
				$validto = true;
				$toTS = 0;

				if ( trim( $todate ) != "" )
					$validto = validateInputDate( $todate, $toTS );
				else
					$emptyto = true;

				if ( !$validfrom )
				{
					$invalidField = "fromdate";
					$errorStr = $kernelStrings["sms_history_date_error"];
					break;
				}

				if ( !$validto )
				{
					$invalidField = "todate";
					$errorStr = $kernelStrings["sms_history_date_error"];
					break;
				}

				if ( $fromTS > $toTS && !$emptyto )
				{
					$invalidField = "fromdate";
					$errorStr = $kernelStrings["sms_history_fromdate_error"];
					break;
				}
			}
			else
			if ( $histtype == 3 )
				$emptyto = $emptyfrom = true;

			$searchArray = array();

			if ( !$emptyto )
				$searchArray["toTS"] = $toTS + 86400;

			if ( !$emptyfrom )
				$searchArray["fromTS"] = $fromTS;

			$searchArray["emptyto"] = $emptyto;
			$searchArray["emptyfrom"] = $emptyfrom;

			$searchArray["histtype"] = $histtype;
			$searchArray["fromdate"] = $fromdate;
			$searchArray["todate"] = $todate;

			$_SESSION["searchArray"] = $searchArray;

			break;

		case 2:

			session_unregister( "searchArray" );
			break;

		case 3:

			$updated = chargeUsersSMS();

			if ( PEAR::isError( $updated ) )
			{
				$errorStr = $updated->getMessage();
				break;
			}

			$chargeUpdated = sprintf( $kernelStrings["sms_charge_updated_text"], $updated );

			if ( !isset( $currentPage ) || $currentPage=="" )
				unset( $histtype );

			break;
		default:
			chargeUsersSMS();
			
			break;



	}

	switch( true ) {

			case true: {

				if ( isset( $_GET["U_ID"] ) )
					session_unregister( "searchArray" );

				if ( isset( $histtype ) && session_is_registered( "searchArray" ) )
				{
					$searchArray = $_SESSION["searchArray"];

					$smsHist = getSMSHistory( "", $searchArray["emptyto"] ? "" : convertToSQLDate( $searchArray["toTS"] ), $searchArray["emptyfrom"] ? "" : convertToSQLDate( $searchArray["fromTS"] ) );

					if ( PEAR::isError( $smsHist ) )
					{
						$errorStr = $smsHist->getMessage();
						break;
					}

					$showHist = 1;

					$periodNum = count($smsHist);

					$showPageSelector = false;
					$pages = null;
					$pageCount = 0;

					if ( !isset( $currentPage ) || $currentPage == "" )
						$currentPage = 1;
					
					$smsHist = addPagesSupport( $smsHist, RECORDS_PER_PAGE, $showPageSelector, $currentPage, $pages, $pageCount );
					

					// Prepare SMS History
					//
					foreach( $smsHist as $id=>$value )
					{
						$value["SMSH_DATETIME"] = convertToDisplayDateTime($value["SMSH_DATETIME"]);
						$value["SMSH_APP"] = getAppName( $value["SMSH_APP"], $language );
						$value["SMSH_CHARGE"] = sprintf( "%.2f", $value["SMSH_CHARGE"] );
						$value["SMSH_STATUS"] = $kernelStrings[$sms_StatusNamesArray[$value["SMSH_STATUS"]]];

						$value["TEXT_URL"] = prepareURLStr( PAGE_SMS_TEXT, array( "smsId"=>$value["SMSH_ID"], "currentPage"=>$currentPage, "opener"=>1 ) );

						$smsHist[$id] = $value;
					}


					// Prepare pages links
					//
					foreach( $pages as $key => $value )
					{
						$params = array();
						$params[PAGES_CURRENT] = $value;

						$params["histtype"] = isset( $histtype ) ? $histtype : 1;
//						$params["fromdate"] = isset( $fromdate ) ? $fromdate : "";
//						$params["todate"] = isset( $todate ) ? $todate : "";

						$URL = prepareURLStr( PAGE_SMS_BH, $params );
						$pages[$key] = array( $value, $URL );
					}

					if ( !isset( $histtype ) )
					{
						$histtype = $searchArray["histtype"];
						$fromdate = $searchArray["fromdate"];
						$todate = $searchArray["todate"];
					}

				}

				$totalNum = getSMSHistoryCount( );
				if ( PEAR::isError( $totalNum ) )
				{
					$errorStr = $totalNum->getMessage();
					break;
				}

				$smsTotalSum = getSMSHistorySum( );

				if ( PEAR::isError( $smsTotalSum ) )
				{
					$errorStr = $smsTotalSum->getMessage();
					break;
				}

				$systemBalance = getSMSBalance( SMS_SYSTEM_USER );

				if (PEAR::isError( $systemBalance ) )
					return $systemBalance;

			}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings[ 'sms_bh_screen_long_name' ] );

	$preproc->assign( FORM_LINK, "" );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "");

	if ( !$fatalError )
	{
		if ( isset( $invalidField ) )
			$preproc->assign( "invalidField", $invalidField );

		if ( isset ( $fileReady ) )
		{
			$preproc->assign( "fileReady", $fileReady );
			$preproc->assign( "link", $link );
		}

		$preproc->assign( "totalCharge", sprintf( "%.2f", $smsTotalSum[0]+$smsTotalSum[1] ) );
		$preproc->assign( "actualCharge", sprintf( "%.2f", $smsTotalSum[1] )  );
		$preproc->assign( "reservedCharge", sprintf( "%.2f", $smsTotalSum[0] )  );

		if ( isset( $showHist ) )
		{
			$preproc->assign( "showHist", $showHist );
			$preproc->assign( "smsHist", $smsHist );

			$preproc->assign( "fromDate", $searchArray["emptyfrom"] ? "-" : convertToDisplayDate( convertToSQLDate( $searchArray["fromTS"] ) ) );
			$preproc->assign( "toDate", $searchArray["emptyto"] ? "-" : convertToDisplayDate( convertToSQLDate( $searchArray["toTS"] ) ) );
			$preproc->assign( "periodNum", $periodNum );

			$preproc->assign( PAGES_SHOW, $showPageSelector );
			$preproc->assign( PAGES_PAGELIST, $pages );
			$preproc->assign( PAGES_CURRENT, $currentPage );
			$preproc->assign( PAGES_NUM, $pageCount );
			$preproc->assign( "currentPage", $currentPage );

			$preproc->assign( "numDocuments", $periodNum );
			$preproc->assign( "numDocumentsLabel", $kernelStrings["sms_history_sent"] );
		}
		else
		{
			$preproc->assign( "fromdate", isset( $fromdate ) ? $fromdate : "" );
			$preproc->assign( "todate", isset( $todate ) ? $todate : "" );
		}
		$preproc->assign( "totalNum", $totalNum );

		$preproc->assign( "systemBalance", $systemBalance );

		$preproc->assign( "histtype", isset( $histtype ) ? $histtype : 1 );
		$preproc->assign( "fromdate", isset( $fromdate ) ? $fromdate : "" );
		$preproc->assign( "todate", isset( $todate ) ? $todate : "" );

		$preproc->assign( "chargeUpdated", $chargeUpdated );

		$preproc->assign( 'chargeComment', nl2br($kernelStrings["sms_charge_comment_text"] ) );

	}

	$preproc->assign( 'smsDisabled', $smsDisabled );

	$preproc->display("sms.htm" );
?>
