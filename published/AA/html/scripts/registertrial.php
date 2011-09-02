<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../hostagent/hostagent.php" );
	require_once( "../../../hostagent/trialregistrator.php" );

	//	
	// Authorization
	//

	$SCR_ID = null;
	$fatalError = false;
	$errorStr = null;
	
	pageUserAuthorization( $SCR_ID, $AA_APP_ID, true );

	// Page variables setup
	//
	$kernelStrings = $loc_str[$language];
	$invalidField = null;
	$done = false;

	$btnIndex = getButtonIndex( array(BTN_SAVE), $_POST );
	switch ($btnIndex)
	{
		case 0 :
				$Registrator = new TrialRegistrator( $language, $currentUser );
				$res = $Registrator->loadFromArray( prepareArrayToStore($trialdata), $kernelStrings, true, array( s_datasource=>s_form, 'kernelStrings'=>$kernelStrings ) );
				if ( PEAR::isError($res) )
				{
					$errorStr = $res->getMessage();
					$invalidField = $res->getUserInfo();
					break;
				}

				$res = $Registrator->Register( $kernelStrings );
				if ( PEAR::isError($res) )
				{
					$errorStr = $res->getMessage();
					$invalidField = $res->getUserInfo();
					break;
				}

				$done = true;
	}

	switch (true)
	{
		case true:
				if ( !$databaseInfo[HOST_DBSETTINGS][HOST_TEMPORARY] )
				{
					$fatalError = true;
					$errorStr = $kernelStrings['rt_nottemporary_message'];
					break;
				}

				if ( !isset($trialdata) )
					$trialdata = array();
	}

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID  );

	$preproc->assign( PAGE_TITLE, $kernelStrings['rt_page_title'] );

	$preproc->assign( FORM_LINK, PAGE_REGTRIAL );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preproc->assign( "DatabaseKey", $DB_KEY );
		$preproc->assign( "trialdata", $trialdata );
		$preproc->assign( "done", $done );
	}

	$preproc->display( "registertrial.htm" );
?>