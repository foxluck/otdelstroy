<?php
	if(!defined('WBA_SETUP_PAGE')){
		$init_required = false;
		require_once( "../../../common/html/includes/httpinit.php" );
		require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
		redirectBrowser( PAGE_SECTION_SETUP, array() );
	}
	/*
	$init_required = false;
	if(!defined('NOT_USE_GLOBAL_CACHE'))define('NOT_USE_GLOBAL_CACHE',true);
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
*/
	$invalidField = null;
	$profileCreated = false;

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

	$redirParams = array( "class"=> $currentViewModuleClass);

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

	$btnIndex = getButtonIndex( array( "uninstallbtn", "installbtn", "cancelbtn" ), $_POST );

	switch ( $btnIndex )
	{
		case 1 :
		case 0 :

			if ( $btnIndex == 1 )
				$ret = $class->installModule( $moduleId );
			else
				$ret = $class->uninstallModule( $moduleId );

			if ( PEAR::isError( $ret ) )
			{
				$fatalError = true;
				$errorStr = $ret->getMessage();
				break;
			}

			$ret = modules_dumpModulesInfo();

			if ( PEAR::isError( $ret ) )
			{
				$fatalError = true;
				$errorStr = $ret->getMessage();
				break;
			}

			if ( $btnIndex == 1 )
			{
				$redirParams["action"]="modify";
				$redirParams["id"]=base64_encode( $moduleId );
				redirectBrowser( PAGE_MODULESMOD, $redirParams  );
			}
			else
				redirectBrowser( PAGE_SMSMODULES, $redirParams  );

		case 2 :

			if ( $action != "install" )
			{
				$redirParams["action"]="modify";
				$redirParams["id"]=base64_encode( $moduleId );
				redirectBrowser( PAGE_MODULESMOD, $redirParams  );
			}
			else
				redirectBrowser( PAGE_SMSMODULES, $redirParams  );

			break;

	}

	switch( true )
	{
			case true: {

					if ( $fatalError )
						break;

					$module = $class->getModule( $moduleId );

					if ( PEAR::isError( $module ) )
					{
						$fatalError = true;
						$errorStr = $module->getMessage();
						break;
					}

					$moduleInfo = $module->getDescriptionArray();

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
	
	$preproc->assign( PAGE_TITLE, sprintf("%s &mdash; %s",translate('smscp_page_name'),translate('smsm_page_title')));
	
	$preproc->assign( FORM_LINK, PAGE_MODULESINSTALL );
//	$preproc->assign( HELP_TOPIC, "currencylist.htm");

	$preproc->assign( "currentViewModuleClass", $currentViewModuleClass );

	$preproc->assign( "modulesClasses", $modulesClasses);
	$preproc->assign( "currentClass", $currentViewModuleClass );

	$preproc->assign( "id", base64_encode( $moduleId ) );


	if ( !$fatalError ) {
		$preproc->assign( "moduleInfo", $moduleInfo );
		$preproc->assign( "action", $action );
	}

//	$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
//	$preproc->assign( 'pdfAdminFile', GUIDE_FILE );

	/*$preproc->assign ( 'waStrings', $LocalizationStrings);

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
	$preproc->assign( "mainTemplate","modulesinstall.htm" );
	//$preproc->display( "main.htm" )
	
?>
