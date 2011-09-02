<?php
define('WBA_SETUP_PAGE',true);

$init_required = false;
$section = isset($_POST['section'])?$_POST['section']:(isset($_GET['section'])?$_GET['section']:'common');
if(in_array($section,array('sms','modules','modulesmod','modulesinstall'))){
	if(!defined('NOT_USE_GLOBAL_CACHE'))define('NOT_USE_GLOBAL_CACHE',true);
}
require_once( "../../../common/html/includes/httpinit.php" );
require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

$mainMenu[PAGE_SECTION_SETUP]['link'] = '';

$fatalError = false;
$errorStr = null;
$messageStr = null;
$invalidField = null;

$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );


switch($section){
	case 'common':
		include('./commonsettings.php');
		break;
	case 'frontend':
		include('./frontend.php');
		break;
	case 'sms':
		include('./sms.php');
		break;
	case 'modulesmod':
		include('./modulesmod.php');
		break;
	case 'modulesinstall':
		include('./modulesinstall.php');
		break;
	case 'dbprofile':
		include('./dbprofile.php');
		break;
	case 'dblist':
		include('./dblist.php');
		break;
	case 'sqlservers':
		include('./sqlservers.php');
		break;
	case 'addmodserver':
		include('./addmodserver.php');
		break;
	case 'languages':
		include('./languages.php');
		break;
	case 'addmodlanguage':
		include('./addmodlanguage.php');
		break;
	case 'localization':
		include('./localization.php');
		break;
	case 'exportlanguage':
		include('./exportlanguage.php');
		break;
	case 'importexportlang':
		include('./importexportlang.php');
		break;
	case 'migrate':
		include('./migrate.php');
		break;
}
$appData = listPublishedApplications( $language, true );

$message = isset($_POST['msg'])?$_POST['msg']:(isset($_GET['msg'])?$_GET['msg']:'');
$messageTypeIsErr = isset($_POST['msgtype'])?$_POST['msgtype']:(isset($_GET['msgtype'])?$_GET['msgtype']:false);
$message_ = base64_decode($message);
if(!$message_){
	$message = base64_decode(urldecode($message));
}else{
	$message = $message_;
}
$message = translate($message);

$preproc->assign( ERROR_STR, $errorStr );
$preproc->assign( FATAL_ERROR, $fatalError );
$preproc->assign( INVALID_FIELD, $invalidField );

$message .= ($message&&$messageStr?"<br>\n":'').translate($messageStr);
if($message&&strlen($message)){
	$preproc->assign('message',$message);
	$preproc->assign('messageType',$messageTypeIsErr);
}

$preproc->assign( PAGE_TITLE,translate('main_menu_setup').' &mdash; '.translate($preproc->get_template_vars(PAGE_TITLE)));

$preproc->assign ( 'waStrings', $LocalizationStrings);

$warningLabel = '';

$subMenu = array();
$subMenu[] = array('title'=>'cset_page_name','link'=>($section == 'common')?'':'?section=common','description'=>'cset_page_desc','info'=>'','warning'=>(strlen(trim($installInfo['LICENSE']))*strlen(trim($installInfo['COMPANY']))==0));
$frontendSettings = wbs_getFrontendSettings();

$subMenu[] = array('title'=>'fes_page_name','link'=>($section == 'frontend')?'':'?section=frontend','description'=>'fes_page_desc','info'=>'','warning'=>(strlen(trim($frontendSettings['CURRENT_DBKEY']))*strlen(trim($frontendSettings['CURRENT_SERVICE_ID']))==0));

$databaseList = wbsadmin_listRegisteredSystems($kernelStrings,false);
if(wbs_multiDbkeyEnabled()){
	$subMenu[] = array('title'=>'sqls_page_names','link'=>(($section == 'sqlservers')||($section == 'addmodserver'))?'':'?section=sqlservers','description'=>'sqls_page_descs','info'=>'','warning'=>(count($wbs_sqlServers)?false:true));
	$subMenu[] = array('title'=>'dbl_page_names','link'=>(($section == 'dblist')||($section == 'dbprofile'))?'':'?section=dblist','description'=>'dbl_page_descs','info'=>'','warning'=>(count($databaseList)?false:true));
}else{
	$subMenu[] = array('title'=>'sqls_page_name','link'=>($section == 'addmodserver')?'':'?section=addmodserver','description'=>'dbl_page_desc','info'=>'','warning'=>(count($wbs_sqlServers)?false:true));
	$subMenu[] = array('title'=>'dbl_page_name','link'=>($section == 'dbprofile')?'':'?section=dbprofile','description'=>'dbl_page_desc','info'=>'','warning'=>(count($databaseList)?false:true));
}

$subMenu[] = array('title'=>'smscp_page_name','link'=>(($section == 'sms')||($section == 'modulesmod')||($section == 'modulesinstall'))?'':'?section=sms','description'=>'smscp_page_desc','info'=>'','warning'=>false);
$subMenu[] = array('title'=>'lll_page_name','link'=>(($section == 'languages')||($section == 'addmodlanguage')||($section == 'localization')||($section == 'exportlanguage')||($section == 'importexportlang'))?'':'?section=languages','description'=>'lll_page_desc','info'=>'','warning'=>false);
if(isset($appData['SC'])){
	$subMenu[] = array('title'=>'migrate_header','link'=>($section == 'migrate')?'':'?section=migrate','description'=>'migrate_desc','info'=>'','warning'=>false);
}

$preproc->assign( 'installInfo', $installInfo );
$preproc->assign('mainMenu',$mainMenu);
$preproc->assign('subMenu',$subMenu);
$preproc->display( "main.htm" );
?>