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

	$btnIndex = getButtonIndex( array( "cancelbtn", "histbtn", "backbtn" ), $_POST );

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

			$searchArray["U_ID"] = $userID;

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

			$searchArray = $_SESSION["searchArray"];

			$userID = $searchArray["U_ID"];

			session_unregister( "searchArray" );



	}

	switch( true ) {

			case true: {

				if ( isset( $_GET["U_ID"] ) )
					session_unregister( "searchArray" );

				if ( session_is_registered( "searchArray" ) )
				{
					$searchArray = $_SESSION["searchArray"];

					$userID = $searchArray["U_ID"];

					$smsHist = getSMSHistory( $searchArray["U_ID"], $searchArray["emptyto"] ? "" : convertToSqlDateTime( $searchArray["toTS"],  ( $searchArray["histtype"]==1 ) ), $searchArray["emptyfrom"] ? "" :  convertToSqlDateTime( $searchArray["fromTS"], ( $searchArray["histtype"]==1 )   ) );

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

					if ( !isset( $currentPage ) )
						$currentPage = 1;

					$smsHist = addPagesSupport( $smsHist, RECORDS_PER_PAGE, $showPageSelector, $currentPage, $pages, $pageCount );

					// Prepare SMS History
					//
					foreach( $smsHist as $id=>$value )
					{
						$value["SMSH_DATETIME"] = convertToDisplayDateTime($value["SMSH_DATETIME"], false, true, true );
						$value["SMSH_APP"] = getAppName( $value["SMSH_APP"], $language );
						$value["SMSH_CHARGE"] = sprintf( "%.2f", $value["SMSH_CHARGE"] );
						$value["SMSH_STATUS"] = $kernelStrings[$sms_StatusNamesArray[$value["SMSH_STATUS"]]];

						$value["TEXT_URL"] = prepareURLStr( PAGE_SMS_TEXT, array( "smsId"=>$value["SMSH_ID"], "currentPage"=>$currentPage, "U_ID"=>$U_ID ) );

						$smsHist[$id] = $value;
					}

					// Prepare pages links
					//
					foreach( $pages as $key => $value )
					{
						$params = array();
						$params[PAGES_CURRENT] = $value;

						$URL = prepareURLStr( PAGE_SMS_HIST, $params );
						$pages[$key] = array( $value, $URL );
					}

					if ( !isset( $histtype ) )
					{
						$histtype = $searchArray["histtype"];
						$fromdate = $searchArray["fromdate"];
						$todate = $searchArray["todate"];
					}

				}

				$totalNum = getSMSHistoryCount( $userID );

				if ( PEAR::isError( $totalNum ) )
				{
					$errorStr = $totalNum->getMessage();
					break;
				}

				$userName = getUserName( $userID );

				if ( PEAR::isError( $userName ) )
				{
					$errorStr = $userName->getMessage();
					break;
				}
			}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings[ 'sms_sms_history_title' ] );

	$preproc->assign( FORM_LINK, PAGE_SMS_HIST );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "");

	$preproc->assign( "U_ID", $U_ID );
	$preproc->assign( "userID", $userID );

	if ( !$fatalError )
	{
		if ( isset( $invalidField ) )
			$preproc->assign( "invalidField", $invalidField );

		if ( isset ( $fileReady ) )
		{
			$preproc->assign( "fileReady", $fileReady );
			$preproc->assign( "link", $link );
		}

		$preproc->assign( "userName", $userName );
		$preproc->assign( "userId", $userID );

		$preproc->assign( "systemUser",  $userID == '$SYSTEM' ? 1 : 0 );

		if ( isset( $showHist ) )
		{
			$preproc->assign( "showHist", $showHist );
			$preproc->assign( "smsHist", $smsHist );

			$preproc->assign( "fromDate", $searchArray["emptyfrom"] ? "-" : convertToDisplayDate( convertToSQLDate( $searchArray["fromTS"] ) ) );
			$preproc->assign( "toDate", $searchArray["emptyto"] ? "-" : convertToDisplayDate( convertToSQLDate( $searchArray["toTS"] ) )  );
			$preproc->assign( "periodNum", $periodNum );

			$preproc->assign( PAGES_SHOW, $showPageSelector );
			$preproc->assign( PAGES_PAGELIST, $pages );
			$preproc->assign( PAGES_CURRENT, $currentPage );
			$preproc->assign( PAGES_NUM, $pageCount );

			$preproc->assign( "numDocuments", $periodNum );
			$preproc->assign( "numDocumentsLabel", $kernelStrings["sms_history_sent"] );

		}

		$preproc->assign( "histtype", isset( $histtype ) ? $histtype : 1 );
		$preproc->assign( "fromdate", isset( $fromdate ) ? $fromdate : "" );
		$preproc->assign( "todate", isset( $todate ) ? $todate : "" );

		$preproc->assign( "totalNum", $totalNum );
	}

	$preproc->display("smshist.htm" );
?>
