<?php

	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

	$templateName = "classic";
//	$language = LANG_ENG;

	$kernelStrings = $loc_str[$language];
	$db_locStrings = $db_loc_str[$language];

	$fatalError = false;
	$errorStr = null;
	$invalidField = null;
	$profileCreated = false;

	define( "DATE_DISPLAY_FORMAT", DB_DEF_DATE_FORMAT );

	$btnIndex = getButtonIndex( array("cancelbtn"), $_POST );

	switch ( $btnIndex ) {
		case 0 : redirectBrowser( PAGE_DB_DBPROFILE, array(SORTING_COL=>$sorting, 'DB_KEY'=>$DB_KEY, ACTION=>ACTION_EDIT) );
	}

	switch (true) {
		case (true) : 
					$appData = listPublishedApplications( $language, true );
					if ( !is_array( $appData ) ) {
						$errorStr = $db_locStrings[1];
						$fatalError = true;

						break;
					}

					$logRecord = loadAccountLogRecord( $DB_KEY, $index, $kernelStrings );
					if ( PEAR::isError($logRecord) ) {
						$fatalError = true;
						$errorStr = $logRecord->getMessage();

						break;
					}

					if ( is_null($logRecord) ) {
						$fatalError = true;
						$errorStr = $db_locStrings[32];

						break;
					}

					if ( isset( $logRecord[AOPR_OPTION_MODIFICATION] ) )
						foreach ( $logRecord[AOPR_OPTION_MODIFICATION] as $index=>$data ) {
							if ( $data[AOPR_NAME] == HOST_EXPIRE_DATE ) {
								$data[AOPR_PREV] = convertToDisplayDate($data[AOPR_PREV]);
								$data[AOPR_NEW] = convertToDisplayDate($data[AOPR_NEW]);
							}

							$logRecord[AOPR_OPTION_MODIFICATION][$index] = $data;
						}
					
					if ( !empty( $logRecord[AOPR_APPLICATIONS_ADDED] ) && count($logRecord[AOPR_APPLICATIONS_ADDED]) )
						foreach( $logRecord[AOPR_APPLICATIONS_ADDED] as $index=>$APP_ID )
							$logRecord[AOPR_APPLICATIONS_ADDED][$index] = $appData[$APP_ID][APP_REG_APPLICATION][APP_REG_LOCAL_NAME];

					$logRecord[AOPR_APPLICATIONS_ADDED] = implode( ", ", $logRecord[AOPR_APPLICATIONS_ADDED] );

					if ( !empty( $logRecord[AOPR_APPLICATIONS_REMOVED] ) && count($logRecord[AOPR_APPLICATIONS_REMOVED]) )
						foreach( $logRecord[AOPR_APPLICATIONS_REMOVED] as $index=>$APP_ID )
							$logRecord[AOPR_APPLICATIONS_REMOVED][$index] = $appData[$APP_ID][APP_REG_APPLICATION][APP_REG_LOCAL_NAME];

					$logRecord[AOPR_APPLICATIONS_REMOVED] = implode( ", ", $logRecord[AOPR_APPLICATIONS_REMOVED] );
	}

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );
	$preproc->assign( PAGE_TITLE, sprintf( "%s - %s", $db_locStrings[6], $db_locStrings[18] ) );
	$preproc->assign( FORM_LINK, PAGE_DB_LOGDATA );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( SORTING_COL, $sorting );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( 'db_locStrings', $db_locStrings );

	if ( !$fatalError ) {
		$preproc->assign( "DB_KEY", $DB_KEY );

		$preproc->assign( "logRecord", $logRecord );
		$preproc->assign( "modificationOptionNames", $modificationOptionNames );
		$preproc->assign( "modificationClassesNames", $modificationClassesNames );		
	}

	$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
	$preproc->assign( 'returnLink', PAGE_DB_WBSADMIN );
	$preproc->assign( 'pdfAdminFile', GUIDE_FILE );


	$preproc->display( "logdata.htm" );
?>