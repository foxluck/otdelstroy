<?php

	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

	header ("Pragma: no-cache");
	header ("Cache-Control: no-cache, must-revalidate");

	$templateName = "classic";
//	$language = LANG_ENG;

	// Load application strings
	//
	$kernelStrings = $loc_str[$language];
	$fatalError = false;
	$errorStr = null;
	$systemUpdated = false;

	$btnIndex = getButtonIndex( array("updatebtn", "cancelbtn"), $_POST );

	function getUpdateLogContent()
	{
		$updateDir = sprintf( "%supdates", WBS_DIR );
		$filePath = sprintf( "%s/updatelog.txt", $updateDir );

		$fileContent = file( $filePath );
		return implode( "", $fileContent );
	}

	switch (true) {
		case true :
						$updateData = getUpdateData( $kernelStrings );
						if ( PEAR::isError($updateData) ) {
							$errorStr = $updateData->getMessage();
							$fatalError = true;

							break;
						}

						$currentVersion = getCurrentSystemVersion( $kernelStrings );
						if ( PEAR::isError($currentVersion) ) {
							$errorStr = $currentVersion->getMessage();
							$fatalError = true;

							break;
						}

						$updateFound = !is_null($updateData) && $currentVersion < $updateData['VERSION'];
						if ( !$updateFound )
							$updateData = null;
	}

	switch ( $btnIndex ) {
				case 0 : 
						$res = performUpdate( $kernelStrings );
						if ( PEAR::isError($updatelog) ) {
							$errorStr = $res->getMessage();
							$fatalError = true;

							$logContent = getUpdateLogContent();

							break;
						}

						$logContent = getUpdateLogContent();
						$systemUpdated = true;
						break;
				case 1 :
						redirectBrowser( PAGE_DB_WBSADMIN, array() );
	}

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );
	$preproc->assign( FORM_LINK, PAGE_DB_UPDATES );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preproc->assign( 'currentVersion', $currentVersion );
		$preproc->assign( 'updateData', $updateData );
		$preproc->assign( 'systemUpdated', $systemUpdated );

		if ( isset($logContent) )
			$preproc->assign( 'updatelog', $logContent );
	}

	$preproc->display( "updates.htm" );

?>