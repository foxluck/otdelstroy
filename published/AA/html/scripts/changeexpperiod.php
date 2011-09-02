<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//
	
	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "CP";
	$invalidField = null;

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	switch ( $btnIndex ) {
		case 0 : 
				$res = setSessionExpireTime( $DB_KEY, $periodType, $kernelStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();
					$invalidField = $res->getUserInfo();

					break;
				}
				
		case 1 : redirectBrowser( PAGE_SYSTEM, array() );
	}

	switch( true ) {
			case true : {
						if ( $fatalError )
							break;

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

	$preproc->assign( PAGE_TITLE, $kernelStrings['sei_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_CHANGEEXPPERIOD );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( INVALID_FIELD, $invalidField );

	if ( !$fatalError ) {
		$preproc->assign( "defPeriod", $defPeriod );
		$preproc->assign( "periodType", $periodType );
	}

	$preproc->display( "changeexpperiod.htm" );
?>