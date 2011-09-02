<?php
$init_required = false;
require_once( "../../../common/html/includes/httpinit.php" );

require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

$mainMenu[PAGE_DB_WBSADMIN]['link'] = '';
$mainMenu[PAGE_DB_WBSADMIN]['img'] = '../classic/images/i_home_act.gif';


$fatalError = false;
$errorStr = null;

//Security settings
if(!auth_setted()){
	$passwordRequered=true;
	if(isset($_POST['setpassword'])){
		$res=auth_setPassword($user, $kernelStrings);
		if(PEAR::isError($res)){
			$errorStr=$res->getMessage();
			if ( $res->getCode() == ERRCODE_INVALIDFIELD )
			$invalidField = $res->getUserInfo();
		}else{
			$passwordRequered=false;
		}
	}
}else{
	$passwordRequered=false;
}
//updates and availiable application info
extract(wbs_getSystemStatistics());

//update section
$updateDescription ='';
$updateAvailable =false;

if(isset($systemInfo)&&is_array($systemInfo)){
	if((isset($systemInfo['webVersion'])||isset($systemInfo['downloadedVersion']))&&$systemInfo['updateAvailable']){
		$updateAvailable = true;
		$updateDescription.='<p><strong style="font-style: italic;">'.translate('upd_m_upd_gen').'&nbsp;('.translate('upd_m_upd_ver_web').'&nbsp;'.($systemInfo['webVersion']?$systemInfo['webVersion']:$systemInfo['downloadedVersion']).($systemInfo['webVersionDate']?('&nbsp;'.translate('upd_m_wa_upd_date').'&nbsp;'.$systemInfo['webVersionDate']):'').')</strong></p>';
	}elseif($systemInfo['webVersion']){
		$updateDescription.='<p>'.translate('upd_m_upd_no').'</p>';
	}elseif($systemInfo['updateAvailable']){
		$updateDescription.='<p><strong style="font-style: italic;">'.translate('upd_m_upd_gen').'&nbsp;</strong></p>';
	}

	if($systemInfo['error']){
		$error = $systemInfo['error'];
		$updateDescription=('<p style="color:red;font-weight:bold;">'.(isset($error['msg'])?$error['msg']:'').'</p><p class="comment">'.date('Y.m.d H:i:s',$error['time']).'</p>');
	}
}
//Setup section
$dbList = wbsadmin_listRegisteredSystems( $kernelStrings );
$setupDescription = '';
if(isset($companyInfo)&&is_array($companyInfo)&&!strlen($companyInfo['LICENSE'])){
	$setupDescription .= '<p style="color:red;font-weight:bold;">'.translate('not_registered').'</p>';
}
if(!is_array($wbs_sqlServers)||!count($wbs_sqlServers)){
	$setupDescription .= '<p style="color:red;font-weight:bold;">'.translate('setup_sqlserverslist_empty').'</p>';
}
if(!is_array($dbList)||!count($dbList)){
	$setupDescription .= '<p style="color:red;font-weight:bold;">'.translate('setup_dblist_empty').'</p>';
}


//Application list
$appData = listPublishedApplications( $language, true );
if ( !is_array( $appData ) ) {
	$appData=null;
}else{
	$appData = sortPublishedApplications( $appData );
}
$availableApplicationsCount=$systemInfo['applicationCount']-count($appData);
if($availableApplicationsCount>0){
	$availableApplications=sprintf(translate('upd_m_wa_more_app'),'more.php',$availableApplicationsCount);
}

//
if(isset($systemInfo['applicationUpdates']))
{
	foreach ($systemInfo['applicationUpdates'] as $APP_ID=>$updateAvailable)
	if($updateAvailable&&isset($appData[$APP_ID])){
		$appData[$APP_ID]['updateAvailable']=true;
	}

}

$availableMigration=isset($appData['SC']);
if(!$systemConfiguration['status']){
	$activeSection = 'diagnostic';
}elseif($setupDescription){
	$activeSection = 'setup';
}elseif($updateAvailable){
	$activeSection = 'update';
}elseif($availableApplicationsCount>0){
	$activeSection = 'buymore';
}


$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );

$preproc->assign( 'systemConfiguration', $systemConfiguration );
$preproc->assign( 'updateDescription', $updateDescription );
$preproc->assign( 'setupDescription', $setupDescription );
$preproc->assign( 'activeSection', $activeSection );


$preproc->assign( 'systemInfo', $systemInfo );

$preproc->assign( PAGE_TITLE, $db_locStrings[6] );
$preproc->assign( FORM_LINK, PAGE_DB_WBSADMIN );
$preproc->assign( ERROR_STR, $errorStr );
$preproc->assign( FATAL_ERROR, $fatalError );

$preproc->assign( "app_data", $appData );
$preproc->assign( "availableMigration", $availableMigration );

$message = isset($_POST['msg'])?$_POST['msg']:(isset($_GET['msg'])?$_GET['msg']:'');
$messageTypeIsErr = isset($_POST['msgtype'])?$_POST['msgtype']:(isset($_GET['msgtype'])?$_GET['msgtype']:false);
$message_ = base64_decode($message);
if(!$message_){
	$message = base64_decode(urldecode($message));
}else{
	$message = $message_;
}
if($message&&strlen($message)){
	$preproc->assign('message',$message);
	$preproc->assign('messageType',$messageTypeIsErr);
}
if ( !$fatalError ) {
	$preproc->assign( "winclientavailable", $winclientavailable );
}

$preproc->assign('mainMenu',$mainMenu);

$preproc->assign('user',$user);
$preproc->assign('passwordRequered',$passwordRequered);
$preproc->assign('invalidField',$invalidField);

$preproc->assign( 'availableApplications', $availableApplications );
$preproc->assign( 'mdbPatchReq', $multiDBKEYpatchReaq );
$preproc->assign( 'installInfo', $installInfo );

$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
$preproc->assign( 'pdfAdminFile', GUIDE_FILE );

$preproc->assign( 'returnLink', PAGE_DB_WBSADMIN );
$preproc->assign ( 'waStrings', $LocalizationStrings);

$preproc->assign('multiDBKEY',file_exists('dblist.php')&&file_exists('sqlservers.php')&&file_exists('multidbkey.php'));

$preproc->assign( "mainTemplate","wbsadmin.htm" );
$preproc->display( "main.htm" );

?>