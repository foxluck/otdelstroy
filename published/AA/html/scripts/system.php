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
	$fileCount = 0;
	$totalSize = 0;

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	switch ( $btnIndex ) {
		case 0 :
				$res = setSessionExpireTime( $DB_KEY, $periodType, $kernelStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();
					$invalidField = $res->getUserInfo();

					break;
				}

				$res = setDBProfileParameter( $DB_KEY, $kernelStrings, "/".HOST_DATABASE."/".HOST_DBSETTINGS, HOST_DATE_FORMAT, $dateFormat );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();
					$invalidField = $res->getUserInfo();

					break;
				}

				//redirectBrowser( PAGE_SIMPLEREPORT, array( INFO_STR=>urlencode(base64_encode($kernelStrings['sys_savechanges_message'])) ) );
				break;
		case 1 :
				redirectBrowser( PAGE_AADMIN, array ());
				//redirectBrowser( PAGE_SIMPLEREPORT, array( INFO_STR=>urlencode(base64_encode($kernelStrings['sys_nochanges_message'])) ) );
				break;
	}


	switch( true ) {
			case true : {
						if ( $fatalError )
							break;

						if ( !isset($edited) )
						{
							$dateFormat = $dateFormats[DATE_DISPLAY_FORMAT];
						}

						$dateFormat_ids = array();
						$dateFormat_names = array();

						foreach( $dateFormats as $curDateFormat ) {
							$dateFormat_ids[] = $curDateFormat;
							$dateFormat_names[] = $curDateFormat;
						}

						$defPeriod = SESSION_TIMEOUT/60;

						if ( !isset($edited) ) {
							$periodType = array();
							$db_timeout = $_SESSION[HOST_SESS_EXPIRE_PERIOD];

							if ( $db_timeout == SESSION_USE_SYSTEM_TO ) {
								$periodType['type'] = 0; 
								$periodType['period'] = null;
							} elseif ( $db_timeout == "" ) {
								$periodType['type'] = 1;
								$periodType['period'] = null;
							} else {
								$periodType['type'] = 2;
								$periodType['period'] = $db_timeout/60;
							}
						}

		}
	}


	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['sys_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_SYSTEM );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "system.htm");
	$printUrl = prepareURLStr( "../../reports/system.php", array() );
	$preproc->assign( "printURL", $printUrl );

	if ( !$fatalError ) {
		$preproc->assign( "dateFormat", $dateFormat );
		$preproc->assign( "db_timeout", $db_timeout );
		$preproc->assign( "defPeriod", $defPeriod );
		$preproc->assign( "periodType", $periodType );

		$preproc->assign( "dateFormat_ids", $dateFormat_ids );
		$preproc->assign( "dateFormat_names", $dateFormat_names );
	}

	$preproc->display( "system.htm" );
?>