<?php
	if(!defined('WBA_SETUP_PAGE')){
		$init_required = false;
		require_once( "../../../common/html/includes/httpinit.php" );
		require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
		redirectBrowser( PAGE_SECTION_SETUP, array() );
	}
	/*$init_required = false;
	if(!defined('NOT_USE_GLOBAL_CACHE'))define('NOT_USE_GLOBAL_CACHE',true);
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

	$fatalError = false;
	$errorStr = null;
	$invalidField = null;
	$profileCreated = false;

	$errorStr = null;
	$fatalError = false;*/

	//
	// Page variables setup
	//


	$WBS_MODULES->setStrings( $kernelStrings );

	if ( !isset( $class ) )
		$currentViewModuleClass = MODULE_CLASS_SMS;
	else
		$currentViewModuleClass = $class;

	if ( isset( $id ) )
		$moduleId = base64_decode( $id );
	else
		$moduleId = NULL;

	$ret = null;

	switch( true )
	{
			case true:

					$class = &$WBS_MODULES->getClass( $currentViewModuleClass );
					if ( PEAR::isError( $class ) )
					{
						$fatalError = true;
						$errorStr = $class->getMessage();
						break;
					}
	}

	switch( true )
	{
			case true: {
					if ( $fatalError )
						break;
					$module = &$class->getModule( $moduleId );
					if ( PEAR::isError( $module ) )
					{
						$fatalError = true;
						$errorStr = $module->getMessage();
						break;
					}
					$moduleInfo = $module->getDescriptionArray();
					$moduleDescription = $moduleInfo["PARAMS"]->get( "module_description" );
					$fieldArray = $moduleInfo["PARAMS"]->getFieldsArray( );
			}
	}

	$redirParams = array( "class"=> $currentViewModuleClass);
	$btnIndex = getButtonIndex( array( "savebtn", "cancelbtn", "disablebtn" ), $_POST );

	switch ( $btnIndex )
	{
		case 0 :
			$ret = $moduleInfo["PARAMS"]->loadFromArray( $valuesArray, $kernelStrings, 1, null );
			if ( PEAR::isError( $ret ) )
			{
				$fatalError = true;
				$errorStr = $ret->getMessage();
				break;
			}

			$module->params = $moduleInfo["PARAMS"];
			$ret = modules_dumpModulesInfo();
			if ( PEAR::isError( $ret ) )
			{
				$fatalError = true;
				$errorStr = $ret->getMessage();
				break;
			}
			$redirParams['msg'] = base64_encode('wbs_settings_update_success');

		case 1 :
			redirectBrowser( PAGE_SMSMODULES, $redirParams  );
			break;

		case 2 :
			$redirParams["action"] = "uninstall";
			$redirParams["class"] = $currentViewModuleClass;
			$redirParams["id"] = base64_encode($moduleId);
			redirectBrowser( PAGE_MODULESINSTALL, $redirParams  );
			break;

	}

	switch( true )
	{
			case true: {
					if ( !isset( $edited) || ( isset( $edited)  && !$edited ) )
						$valuesArray = $moduleInfo["PARAMS"]->getValuesArray( );
			}
	}

	//
	// Page implementation
	//

//	extract(wbs_getSystemStatistics());

//	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );

/*	$preproc->assign( 'systemConfiguration', $systemConfiguration );
	$preproc->assign( 'companyInfo', $companyInfo );
	$preproc->assign( 'systemInfo', $systemInfo );
	$preproc->assign ( 'waStrings', $db_locStrings);
*/
	$preproc->assign( PAGE_TITLE, sprintf('%s &mdash; %s',translate('smscp_page_name'),
							sprintf(translate('smsm_page_title2'),$moduleDescription),strtoupper($currentViewModuleClass)) );

	$preproc->assign( FORM_LINK, PAGE_MODULESMOD );
	/*$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
*/
	

	if ( !$fatalError )
	{
		$preproc->assign( "currentViewModuleClass", $currentViewModuleClass );

		$preproc->assign( "currentClass", $currentViewModuleClass);
		$preproc->assign( "id", base64_encode( $moduleId ) );
		$preproc->assign( "moduleDescription", $moduleDescription );
		$preproc->assign( "moduleId", $moduleId );
		$preproc->assign( "fieldArray", $fieldArray );
		$preproc->assign( "valuesArray", $valuesArray );
	}

//	$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
//	$preproc->assign( 'pdfAdminFile', GUIDE_FILE );
/*	$preproc->assign ( 'waStrings', $LocalizationStrings);

	$mainMenu[PAGE_SECTION_SETUP]['link'] = '';
	
	$section = 'sms';
	$subMenu = array();
	$subMenu[] = array('title'=>'cset_page_name','link'=>($section == 'common')?'':'setup.php?section=common','description'=>'cset_page_desc','info'=>'');
	$subMenu[] = array('title'=>'fes_page_name','link'=>($section == 'frontend')?'':'setup.php?section=frontend','description'=>'fes_page_desc','info'=>'');
	if(true){
		$subMenu[] = array('title'=>'dbl_page_name','link'=>($section == 'database')?'':'setup.php?section=database','description'=>'dbl_page_desc','info'=>'');
	}else{
		$subMenu[] = array('title'=>'sqls_page_name','link'=>($section == 'server')?'':'setup.php?section=server','description'=>'sqls_page_desc','info'=>'');
		$subMenu[] = array('title'=>'dbl_page_names','link'=>($section == 'database')?'':'setup.php?section=database','description'=>'dbl_page_descs','info'=>'');
	}
	
	$subMenu[] = array('title'=>'smscp_page_name','link'=>($section == 'sms')?'':'setup.php?section=sms','description'=>'smscp_page_desc','info'=>'');
	$subMenu[] = array('title'=>'lll_page_name','link'=>($section == 'languages')?'':'setup.php?section=languages','description'=>'lll_page_desc','info'=>'');
	$subMenu[] = array('title'=>'migrate_header','link'=>($section == 'migrate')?'':'setup.php?section=migrate','description'=>'migrate_desc','info'=>'');
	
	
	$preproc->assign('mainMenu',$mainMenu);
	$preproc->assign('subMenu',$subMenu);*/

	//$preproc->assign( 'section',$section);
	$preproc->assign( "mainTemplate","modulesmod.htm" );
	//$preproc->display( "main.htm" );
?>
