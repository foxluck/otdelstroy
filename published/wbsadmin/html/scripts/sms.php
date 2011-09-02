<?php
if(!defined('WBA_SETUP_PAGE')){
	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
	redirectBrowser( PAGE_SECTION_SETUP, array() );
}

$invalidField = null;
$profileCreated = false;

//
// Page variables setup
//

if ( !isset( $class ) ){
	$currentViewModuleClass = MODULE_CLASS_SMS;
	//$currentViewModuleClass = MODULE_CLASS_EMAIL;
}else{
	$currentViewModuleClass = $class;
}

$btnIndex = getButtonIndex( array("cancelbtn" ), $_POST );

switch ( $btnIndex ) {
	case 0 : {
		redirectBrowser( PAGE_DB_WBSADMIN, array() );
		break;
	}
}

do{
	if ( $fatalError )
	break;

	$modulesList = $WBS_MODULES->getClassModules($currentViewModuleClass);
	$mList = array();

	if ( is_array( $modulesList ) )
	{
		foreach( $modulesList as $MOD_ID=>$module )
		{
			$MOD_DATA["NAME"] = $module->descr;
			$MOD_DATA["ID"] = $module->id;

			if ( $module->isInstalled() )
			{
				$MOD_DATA["MODIFY_URL"] = prepareURLStr( PAGE_MODULESMOD, array( ACTION=>"modify", "class"=>$currentViewModuleClass, "id"=>base64_encode($MOD_ID) ) );
				$MOD_DATA["UNINSTALL_URL"] = prepareURLStr( PAGE_MODULESINSTALL, array( ACTION=>"uninstall", "class"=>$currentViewModuleClass, "id"=>base64_encode($MOD_ID) ) );
				$mList["INSTALLED"][$MOD_ID] = $MOD_DATA;
				$mList["MODULES"][$MOD_ID]["INSTALLED"] = 1;
			}
			else
			{
				$MOD_DATA["INSTALL_URL"] = prepareURLStr( PAGE_MODULESINSTALL, array( ACTION=>"install", "class"=>$currentViewModuleClass, "id"=>base64_encode($MOD_ID) ) );

				$mList["NOTINSTALLED"][$MOD_ID] = $MOD_DATA;
				$mList["MODULES"][$MOD_ID]["INSTALLED"] = 0;
			}

			$mList["MODULES"][$MOD_ID]["MODULE"] = $MOD_DATA;
		}
	}
}while(false);

//
// Page implementation
//
$preproc->assign( PAGE_TITLE, 'smscp_page_name');
$preproc->assign( FORM_LINK, PAGE_SMSMODULES );
$preproc->assign( HELP_TOPIC, "currencylist.htm");

$preproc->assign( "currentViewModuleClass", $currentViewModuleClass );

$preproc->assign( "modulesClasses", $modulesClasses);
$preproc->assign( "currentClass", $currentViewModuleClass );

if ( !$fatalError ) {
	$preproc->assign( "modulesList", $mList );
}

$preproc->assign( "mainTemplate","moduleslist.htm" );
?>
