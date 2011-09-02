<?php
/*
 * LOOKS LIKE UNUSED CODE
 */
	if(!defined('NOT_USE_GLOBAL_CACHE'))define('NOT_USE_GLOBAL_CACHE',true);
	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

	$templateName = "classic";
	$language = LANG_ENG;

	$kernelStrings = $loc_str[$language];
	$db_locStrings = $db_loc_str[$language];

	$fatalError = false;
	$errorStr = null;
	$invalidField = null;

	$profileCreated = false;
	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$currencyList = array();

	/*@var $WBS_MODULES wbsModules*/
	if(isset($WBS_MODULES)&&($WBS_MODULES instanceof wbsModules)){
		$WBS_MODULES->setStrings( $kernelStrings );
	}

	$btnIndex = getButtonIndex( array( "savebtn" ), $_POST );

	switch ( $btnIndex ) {
		case 0 : {

			$ret = saveAssignedModulesInformation( $module, $kernelStrings );

			if ( PEAR::isError( $ret ) )
			{
				$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

				$fatalError = true;
				break;
			}

			break;
		}
	}

	switch( true ) {
			case true:

						$modulesSummary = getInstalledModulesSummary( $kernelStrings );

						if ( PEAR::isError( $modulesSummary ) )
						{
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

							$fatalError = true;
							break;
						}

						break;
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['mod_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_MODULES );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "currencylist.htm");

	$preproc->assign( "moduleListURL", PAGE_MODULESLIST );

	if ( !$fatalError ) {
		$preproc->assign( "modulesSummary", $modulesSummary );
	}

	$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
	$preproc->assign( 'pdfAdminFile', GUIDE_FILE );

	//$preproc->display("modules.htm" );
	//$preproc->assign( "mainTemplate","modules.htm" );
	//$preproc->display( "main.htm" );
?>
