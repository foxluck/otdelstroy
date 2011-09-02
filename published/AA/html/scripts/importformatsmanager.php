<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//
	
	$errorStr = null;
	$fatalError = false;
	$invalidField = null;

	pageUserAuthorization( null, $AA_APP_ID, true );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];

	// Form handling
	//
	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL, 'deletebtn' ), $_POST );

	switch ($btnIndex) {
				case 0 :
						if ( !isset($formatName) )
							$formatName = array();

						foreach ( $formatName as $key=>$value ) {
							if ( !strlen($value) ) {
								$invalidField = $key;
								$errorStr = $kernelStrings[ERR_REQUIREDFIELDS];
								break 2;
							}
						}

						$formatListName = base64_decode($LIST);
						
						foreach ( $formatName as $key=>$value ) {
							$res = renameFileFormat( $key, $formatListName, $value, $kernelStrings );
							if ( PEAR::isError($res) ) {
								$errorStr = $res->get_message();
								$invalidField = $key;
								break 2;
							}
						}

				case 1 : 
						$params = unserialize( base64_decode($openerParams) );

						$openerURL = base64_decode($opener);
						redirectBrowser( $openerURL, $params );
				case 2 :
						if ( !isset($selected) )
							break;

						$formatListName = base64_decode($LIST);

						foreach( $selected as $curFormat ) {
							$res = deleteFileFormat( $curFormat, $formatListName, $kernelStrings );
							if ( PEAR::isError($res) ) {
								$errorStr = $res->get_message();
								break 2;
							}
						}
	}


	// Prepare page data
	//

	switch (true) {
		case true :
					// Load format list
					//
					$formatListName = base64_decode($LIST);

					$formats = listFileFormats( $formatListName, $kernelStrings );
					if ( PEAR::isError($formats) ) {
						$errorMessage = $formats->getMessage();
						$fatalError = true;

						break;
					}

	}

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['app_importmanageformats_title'] );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FORM_LINK, PAGE_IMPORTFORMATMANAGER );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( INVALID_FIELD, $invalidField );

	if ( !$fatalError ) {
		$preproc->assign( OPENER, $opener );
		$preproc->assign( OPENER_PARAMS, $openerParams );
		$preproc->assign( FIF_LIST, $LIST );
		$preproc->assign( FIF_USER, $U_ID );

		$preproc->assign( 'formats', $formats );
		$preproc->assign( 'formatNum', count($formats) );

		if ( isset($edited) )
			$preproc->assign( 'edited', $edited );

		if ( isset($formatName) )
			$preproc->assign( 'formatName', $formatName );
	}

	$preproc->display( "importformatsmanager.htm" );
?>