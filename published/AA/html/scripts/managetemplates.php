<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//	
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;

	$SCR_ID = "CP";
	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false, true );
	if ( !$currentUser )
		pageUserAuthorization( "UC", "CM", false, true );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$cmStrings = $cm_loc_str[$language];
	$invalidField = null;

	$btnIndex = getButtonIndex( array('deleteselected', BTN_CANCEL), $_POST );

	switch ( $btnIndex ) {
		case 0 :
			if ( isset($selectedFormats) )
				foreach( $selectedFormats as $curFormat )
					deleteFileFormat( $curFormat, CM_FILEFORMATS, $kernelStrings );

			break;
		case 1 :
			redirectBrowser( $opener, array() );
	}

	switch (true)
	{
		case true:
					// Load export formats list
					//
					$sysFormats = listFileFormats( CM_FILEFORMATS, $kernelStrings );
					if ( PEAR::isError($sysFormats) ) {
						$errorMessage = $sysFormats->getMessage();
						$fatalError = true;

						break;
					}

					$customFormats = listFileFormats( CM_FILEFORMATS, $kernelStrings, $currentUser, false );
					if ( PEAR::isError($customFormats) ) {
						$errorMessage = $customFormats->getMessage();
						$fatalError = true;

						break;
					}

	}

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['tpl_page_title'] );
	$preproc->assign ("cmStrings", $cmStrings);
	$preproc->assign( FORM_LINK, PAGE_MANAGETEMPLATES );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( OPENER, $opener );

	if ( !$fatalError ) {
		$preproc->assign( 'customFormats', $customFormats );
		$preproc->assign( 'sysFormats', $sysFormats );
	}

	$preproc->display("managetemplates.htm");
?>