<?php
	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
	
	$mainMenu[PAGE_SECTION_BUY]['link'] = '';

	$fatalError = false;
	$errorStr = null;
	
	$appData = listPublishedApplications( $language, true );
	if ( !is_array( $appData ) ) {
		$appData=array();
	}else{
		$appData = sortPublishedApplications( $appData );
	}
	$applicationList = array_keys($appData);
	$applicationList = base64_encode(implode(',',$applicationList));	
	
	//list($companyInfo) = wbs_getSystemStatistics();
	$companyInfo = wbs_getInstallInformation();

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );

	$license = strlen($companyInfo['LICENSE'])?base64_encode(str_replace('-','',$companyInfo['LICENSE'])):false;
	
	/*$preproc->assign( 'systemConfiguration', $systemConfiguration );
	$preproc->assign( 'companyInfo', $companyInfo );
	$preproc->assign( 'systemInfo', $systemInfo );*/
	
	$preproc->assign( 'applicationList', $applicationList );
	$preproc->assign( 'language', $language );
	$preproc->assign( 'license', $license );
	
	$preproc->assign( PAGE_TITLE, 'main_menu_buy' );
	//$preproc->assign( FORM_LINK, PAGE_DB_WBSADMIN );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign ( 'waStrings', $LocalizationStrings);

	$preproc->assign('mainMenu',$mainMenu);
	$preproc->assign( 'installInfo', $installInfo );
	$preproc->assign('subMenu',$subMenu);
	$preproc->assign( "mainTemplate","more.htm" );
	$preproc->display( "main.htm" );
?>