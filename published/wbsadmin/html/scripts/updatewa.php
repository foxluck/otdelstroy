<?php

if(isset($_GET['ajax'])){
require_once('./upgrade.php');
require_once(WBS_DIR.'published/wbsadmin/wbs_auth.php');
require_once(WBS_DIR.'kernel/includes/locloader.class.php');
function translate($string)
{
	global $LocalizationStrings;
	if(isset($LocalizationStrings)&&is_array($LocalizationStrings)){
		return (isset($LocalizationStrings[$string])?$LocalizationStrings[$string]:$string);
	}
	return $string;
}
$locLoader = new LocalizationLoader ();
$dirPath = WBS_DIR.'published/wbsadmin/localization';
$LocalizationStrings = $locLoader->loadStrings( $dirPath, 'wbs');
$language = (isset($_GET['lang'])&&isset($LocalizationStrings[$_GET['lang']]))?$_GET['lang']:'eng';
$LocalizationStrings = $LocalizationStrings[$language];
$status = updateManager::getState();
$stateCode = 'REFRESH';
$progressValue = 'false';
$statusDescription = '&nbsp;';
$title = '&nbsp;';
switch($status['state']){
	case (updateManager::STATE_DOWNLOAD_RESTART):
		$stateCode = "DONWLOAD_RESTART";
		$statusDescription=translate('upd_m_status_prepare_desc');
		$progressValue = 'false';
		$title=translate('upd_m_status_prepare');
		break;
	case (updateManager::STATE_DOWNLOAD):
		$stateCode = "PROGRESS";
		$progress=unserialize($status['msg']);
		
		if($progress&&is_array($progress)){
			if(isset($progress['prepare'])){
					$statusDescription=translate('upd_m_status_prepare_desc');
					$progressValue = 'false';
					$title=translate('upd_m_status_prepare');
			}else{
				$statusDescription=sprintf(translate('upd_m_status_downoad_desc'),round($progress['downloadSize']/1048576,2),$progress['size']?round($progress['size']/1048576,2):'unknown');
				$progressValue=floor($progress['progress']);
				$progressValue = $progressValue>100?100:$progressValue;
				$title=translate('upd_m_status_download');
			}
		}
		
		break;
	case (updateManager::STATE_DOWNLOAD_COMPLETE):
		$stateCode = "WAIT";
		$title=translate('upd_m_status_prepare');
		break;
	case (updateManager::STATE_UNPACK):
	case (updateManager::STATE_UNPACK_COMPLETE):
		$statusDescription=$LocalizationStrings['upd_m_status_unpack_desc'];
		$stateCode = "WAIT";
		$title=$LocalizationStrings['upd_m_status_unpack'];
		break;
	case (updateManager::STATE_INSTALL_SCRIPTS):
		$progress=unserialize($status['msg']);
		$stateCode = "PROGRESS";
		$statusDescription=translate('upd_m_status_install_desc');
		$title=translate('upd_m_status_install');
		/*if($prevState&&($prevState!='install')){
			$stateCode = "REFRESH";
		}else*/if($progress&&is_array($progress)&&isset($progress[2])){
			$progressValue=floor($progress[2]);
			$progressValue = $progressValue>100?100:$progressValue;
		}else{
			$progressValue = 0;
		}
		break;
	
	case (updateManager::STATE_INSTALL_RESTART):
		$stateCode = "RESTART";
		$title=translate('upd_m_status_install');
		$statusDescription=translate('upd_m_status_install_desc');
		$progress = false;
		break;
	case (updateManager::STATE_UPDATE):
	case (updateManager::STATE_NONE):
		$statusDescription=translate('upd_m_status_install_desc');
		$title=translate('upd_m_status_install');
		$stateCode = "WAIT";
		$title = '&nbsp;';
		break;
	case (updateManager::STATE_INSTALL_ERROR):
		$title=$LocalizationStrings['upd_m_err_state_install'];
		$stateCode = "REFRESH";
		break;
	default:
		$stateCode = "REFRESH";
		$statusDescription = 'unknown state';
		break;
}

print sprintf('%s:%s:%s:%s',$stateCode,is_integer($progressValue)?sprintf('%d',$progressValue):$progressValue,$title,$statusDescription);
if(false&&($fp = fopen(WBS_DIR.'/temp/ajax.log',a))){
	fwrite($fp,var_export($_GET,true)."\n=========\n".
				var_export($status,true)."\n=========\n".
				"{$stateCode}:{$progressValue}:{$statusDescription}\n");
	fclose($fp);
}
exit;
	
}
$init_required = false;
require_once( "../../../common/html/includes/httpinit.php" );

require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
require_once(WBS_DIR.'/published/wbsadmin/html/scripts/upgrade.php');



$mainMenu[PAGE_WA_UPDATE]['link'] = '';


if(!function_exists('translate')){
	$LocalizationStrings = $db_loc_str[$language];
	function translate($string){
		global $LocalizationStrings;
		return isset($LocalizationStrings[$string])?$LocalizationStrings[$string]:$string;
	}
}

function getUpdatesList($language,$systemInfo)
{
	if(isset($systemInfo['applicationUpdates'])){
		$applicationUpdates=array();//$systemInfo['applicationUpdates'];
		$appData = listPublishedApplications( $language, true );
		if ( !is_array( $appData ) ) {
			$appData=null;
		}else{
			$appData = sortPublishedApplications( $appData );
		}

		foreach ($appData as $APP_ID=>$application){
			if(isset($systemInfo['applicationUpdates'][$APP_ID])&&($systemInfo['applicationUpdates'][$APP_ID])){
				$applicationUpdates[$APP_ID]=$application['APPLICATION']['LOCAL_NAME'];
			}
		}
		if(isset($systemInfo['applicationUpdates']['KERNEL'])){
			$applicationUpdates['KERNEL']=translate('upd_m_kernel_name');
		}
		return $applicationUpdates;
	}
	return null;
}
function getApplicationList($language,$applications = null)
{
	$appData = listPublishedApplications( $language, true );
	if ( !is_array( $appData ) ) {
		$appData=null;
	}else{
		$appData = sortPublishedApplications( $appData );
	}

	foreach ($appData as $APP_ID=>$application){
		if(!is_array($applications)||in_array($APP_ID,$applications)){
			if(!is_array($applicationList))
			$applicationList=array();
			$applicationList[$APP_ID]=$application['APPLICATION']['LOCAL_NAME'];
		}
	}
	if(!is_array($applications)||(!count($applications))||in_array('Kernel',$applications)||in_array('KERNEL',$applications)){
		$applicationList['KERNEL']=translate('upd_m_kernel_name');
	}
	return $applicationList;

}

$LicenseID=wbs_getInstallInformation();
$LicenseID=isset($LicenseID['LICENSE'])?$LicenseID['LICENSE']:'';
$UpdateManager=new updateManager($LicenseID);
$info=$UpdateManager->getSystemInfo();

//EXECUTABLE CODE
$pageTitle = translate('upd_m_page_title');
if(isset($_GET['action']))
{
	$force=isset($_GET['force']);
	$restart=isset($_GET['restart']);
	switch ($_GET['action'])
	{
		case 'reset':{
			$UpdateManager->setState(updateManager::STATE_NONE);
			$UpdateManager->getSystemInfo();
			break;
		}
		case 'download':{
			if($force){
				$UpdateManager->setState(updateManager::STATE_NONE);
				$UpdateManager->getSystemInfo();
			}
			$UpdateManager->downloadUpdate($force);
			$UpdateManager->unPack();
			
			/////////////////////

			$UpdateManager->updateSystem();
			$dbkeyList = $UpdateManager->listRegisteredSystems();
			$dbkeyList = array_keys($dbkeyList);
			$state = updateManager::getState();
			if($state['state']==updateManager::STATE_INSTALL_COMPLETE
				||$state['state']==updateManager::STATE_INSTALL_ERROR){
				wbs_resetCache($dbkeyList);
				$fp=fopen(WBS_DIR.'temp/.report','w');
				fwrite($fp,serialize($UpdateManager->getReport()));
				fclose($fp);
				}			
			//var_dump($UpdateManager->getActionType());
			break;
		}
		case 'unpack':{
			$UpdateManager->unPack();
			/////////////////////

			$UpdateManager->updateSystem();
			$dbkeyList = $UpdateManager->listRegisteredSystems();
			$dbkeyList = array_keys($dbkeyList);
			$state = updateManager::getState();
			if($state['state']==updateManager::STATE_INSTALL_COMPLETE
				||$state['state']==updateManager::STATE_INSTALL_ERROR){
				wbs_resetCache($dbkeyList);
				$fp=fopen(WBS_DIR.'temp/.report','w');
				fwrite($fp,serialize($UpdateManager->getReport()));
				fclose($fp);
			}
			break;
		}
		case 'install':{
			//$UpdateManager->setState(updateManager::STATE_INSTALL_COMPLETE);
			//break;//temporaly blocked to protect code
			$UpdateManager->updateSystem($force,$restart);
			$dbkeyList = $UpdateManager->listRegisteredSystems();
			$dbkeyList = array_keys($dbkeyList);
			$state = updateManager::getState();
			if($state['state']==updateManager::STATE_INSTALL_COMPLETE
				||$state['state']==updateManager::STATE_INSTALL_ERROR){
				wbs_resetCache($dbkeyList);
				$fp=fopen(WBS_DIR.'temp/.report','w');
				fwrite($fp,serialize($UpdateManager->getReport()));
				fclose($fp);
			}
			break;
		}
		case 'full':{
			$UpdateManager->downloadUpdate($force);
			$UpdateManager->unPack();
			/////////////////////
			//$UpdateManager->setState(updateManager::STATE_INSTALL_COMPLETE);
			//break;//temporaly blocked to protect code
			/////////////////////

			$UpdateManager->updateSystem();
			$dbkeyList = $UpdateManager->listRegisteredSystems();
			$dbkeyList = array_keys($dbkeyList);
			$state = updateManager::getState();
			if($state['state']==updateManager::STATE_INSTALL_COMPLETE
				||$state['state']==updateManager::STATE_INSTALL_ERROR){
				wbs_resetCache($dbkeyList);
				$fp=fopen(WBS_DIR.'temp/.report','w');
				fwrite($fp,serialize($UpdateManager->getReport()));
				fclose($fp);
			}
			break;
		}
		case 'details':{
			$APP_ID=(isset($_GET['app_id'])?$_GET['app_id']:null);
			$changeLog=$UpdateManager->showMetaDataUpdateDetails($APP_ID);
			$pageTitle.= ' &mdash; '.$LocalizationStrings['upd_m_meta_details'];
			$pageHeader=$LocalizationStrings['upd_m_meta_details'];
			break;
		}
		case 'changelog':{
			$pageTitle.= ' &mdash; '.$LocalizationStrings['upd_m_view_changelog'];
			$pageHeader=$LocalizationStrings['upd_m_changelog'];
			$changeLogDescription = '';
			//if(isset($_GET['APP_ID']))
			$APP_ID=(isset($_GET['app_id'])?$_GET['app_id']:null);
			$changeLog=$UpdateManager->getChangeLog($APP_ID,isset($_GET['full_change_log']));
			$applicationList = getApplicationList($language);
			if(!$APP_ID){
				$pageTitle .= ' &mdash; '.translate('upd_m_changelog_allapps');
				$changeLogDescription = translate('upd_m_changelog_allapps');
				$changeLogByVersion=array();
				foreach ($changeLog as $APP_ID=>$changes)
				foreach ($changes as $version=>$change){
					if(!isset($changeLogByVersion[$version]))
					$changeLogByVersion[$version]=array();
					$changeLogByVersion[$version][$APP_ID]=$change;
				}
				ksort($changeLogByVersion,SORT_NUMERIC);
				$versions = array_keys($changeLogByVersion);
				$changeLogDescription .= '<br>'.sprintf(translate('upd_m_changelog_version'),min($versions),max($versions));
				unset($versions);
				$changeLog=array_reverse($changeLogByVersion,true);
			}else{
				$pageTitle .= ' &mdash; '.sprintf(translate('upd_m_changelog_oneapp'),$applicationList[$APP_ID]);
				$changeLogDescription = sprintf(translate('upd_m_changelog_oneapp'),$applicationList[$APP_ID]);
			}
			if(!count($changeLog)){
				$changeLog = array('&lt;none&gt;'=>null);
			}
			//foreach ($changeLogByVersion as $version);
			break;
		}
		case 'systemconfiguration':{
			//$MDdetails=$UpdateManager->showMetaDataUpdateDetails();
			$pageTitle=' &mdash; '.$LocalizationStrings['upd_m_sys_conf'];
			$pageHeader=$LocalizationStrings['upd_m_sys_conf'];
			$requirements=new requirementsControl();
			$req=array();
			$total=$requirements->check_all($msg);

			foreach ($msg as $requirement=>$pass)
			{
				$req[$requirement]=array('pass'=>$pass,'description'=>$LocalizationStrings[('upd_m_sys_req_'.strtolower($requirement))]);
			}
			$req['ALL']=array('pass'=>$total,'description'=>('<font color="'.($total?'green':'red').'">'.$LocalizationStrings[('upd_m_sys_req_'.($total?'':'not').'satisfy')]).'</font>');
			break;
		}
		case 'phpinfo':{
			phpinfo();
			die();
		}
	}
}



//FRONTEND CODE
$updateAvailable=false;
if(!isset($_GET['action'])){

	$meta= '<META HTTP-EQUIV="Refresh" CONTENT="10">';
	$inProgress=false;

	$pageHeader=$LocalizationStrings['upd_m_page_title'];
	switch ($info['state']){
		
		//step 1: approve downloading
		case updateManager::STATE_UPDATE_AVAILABLE:{
			$meta= '<META HTTP-EQUIV="Refresh" CONTENT="60">';

			$status=$LocalizationStrings['upd_m_upd_av'];
			$statusDescription=$LocalizationStrings['upd_m_newapp_info'];
			$updateAvailable =true;
			$max_execution_time = intval(ini_get('max_execution_time'));
			$allow_url_fopen = ini_get('allow_url_fopen');
			$allow_url_fopen = intval($allow_url_fopen)||(strtolower($allow_url_fopen)=='on');
			if(!$allow_url_fopen){
				$status=$LocalizationStrings['wbs_upd_furl_disabled'];
				$updateAllowed =false;
			}else{
				//$statusDescription .= '['.$LocalizationStrings['upd_m_newapp_info_alt'].']';
				$updateAllowed = true;
			}
		//	$statusDescription .= "<br><hr>[{$max_execution_time}]<hr>";

			//$button= "<INPUT type='button'  class='update' onClick='JavaScript:this.style.display=\"none\";runAction(\"download\");' value='{$LocalizationStrings['upd_m_btn_download']}'>&nbsp;";
			$button.=$updateAllowed?"<INPUT type='button'  class='update' onClick='JavaScript:runAction(\"full\");' value='{$LocalizationStrings['upd_m_btn_update']}' id='btn_install' DISABLED>":'';
			$applicationList=getUpdatesList($language,$info);
			

			break;
		}

		//step 2.1: download updates
		case updateManager::STATE_DOWNLOAD:{
			$meta= '';//'<META HTTP-EQUIV="Refresh" CONTENT="5">';

			//$reportHeader='Info';
			$progress=unserialize($info['msg']);
			//var_dump($progress);
			if(isset($progress['prepare'])){
				$status=$LocalizationStrings['upd_m_status_prepare'];
				$statusDescription=$LocalizationStrings['upd_m_status_prepare_desc'];

			}else{
				$status=$LocalizationStrings['upd_m_status_download'];
				$statusDescription=sprintf($LocalizationStrings['upd_m_status_downoad_desc'],round($progress['downloadSize']/1048576,2),round($progress['size']/1048576,2));
				$progressValue=intval($progress['progress']);

			}
			$inProgress=true;

			break;
		}

		//step 2.2: download complete
		case updateManager::STATE_DOWNLOAD_COMPLETE:{//not used status
			//$status= "Download complete";
			//$button.="<INPUT type='button'  class='update' onclick='JavaScript:runAction(\"unpack\");' value='Unpack'>";
			//break;
		}

		//step 3.1: extract updates
		case updateManager::STATE_UNPACK:{
			$status= $LocalizationStrings['upd_m_status_unpack'];
			$statusDescription=$LocalizationStrings['upd_m_status_unpack_desc'];
			$inProgress=true;
			$meta= '<META HTTP-EQUIV="Refresh" CONTENT="5">';
			$meta= '';//
			break;
		}

		//step 3.2: extract updates complete
		case updateManager::STATE_UNPACK_COMPLETE:{
			$status=$LocalizationStrings['upd_m_status_before_install'];
			$meta= '<META HTTP-EQUIV="Refresh" CONTENT="120">';
			$metadaView=true;
			$details=$UpdateManager->getUpdateDetails();
			if($details['targetDBCount'])
			{
				$reportHeader=$LocalizationStrings['upd_m_upd_details'];
				$report= '<p>'.$LocalizationStrings['upd_m_upd_db_count'].': '.$details['targetDBCount'].'</p>';
				if(count($details['truncatedDBList'])){
					//$report.="<li>";
					foreach ($details['truncatedDBList'] as $dbName)
					{
						$report.= "<p>&nbsp;&nbsp;&nbsp;{$dbName}</p>";
					}
					if($details['DBlistIsTruncated'])print "...<br>";
					//$report.="</li>";
					
				}
				/*$report.= "<br>Applications to update<br>";
				foreach ($details['APP_list'] as $APP_name)
				{
				$report.= "{$APP_name}<br>";
				}*/
			}
			$applicationList=getApplicationList($language,$details['APP_list']);
			//$report.= "<br>View metadata <a href='?action=details' target='_blank'>full update details</a><br><br>";

			//$button.= "<INPUT type='button'  class='update'  onClick='JavaScript:runAction(\"install\");' value='{$LocalizationStrings['upd_m_btn_install']}'>";
			break;
		}
		case updateManager::STATE_INSTALL_RESTART:
			$restartRequired = true;
			$meta= '';
		//step 4.1: update
		case updateManager::STATE_INSTALL_SCRIPTS:{
			$status=$LocalizationStrings['upd_m_status_install'].": {$info['downloadedVersion']}";
			$statusDescription=$LocalizationStrings['upd_m_status_install_desc'];
			$inProgress=true;
			
		/*	if(isset($restartRequired)&&$restartRequired){
				$statusDescription .= '<br>'.translate('upd_m_status_install_resume');
			}*/
			
			$progress=unserialize($info['msg']);
			if($progress&&is_array($progress)&&isset($progress[2])){
				//$statusDescription=sprintf($LocalizationStrings['upd_m_status_install_desc'],round($progress[2],1));
				$progressValue=floor($progress[2]);
				$progressValue = $progressValue>100?100:$progressValue;
			}
			
			$meta= '';//'<META HTTP-EQUIV="Refresh" CONTENT="5">';
			break;
		}
		case updateManager::STATE_UPDATE:
			$status=$LocalizationStrings['upd_m_status_install'].": {$info['downloadedVersion']}";
			$statusDescription=$LocalizationStrings['upd_m_status_install_desc'];
			$inProgress=true;
			break;

		//step 4.3 update complete
		case updateManager::STATE_INSTALL_COMPLETE:{
			$status=$LocalizationStrings['upd_m_status_install_compl'];
			$fp=fopen(WBS_DIR.'/temp/.displaylog','r');
			if($fp){
				$reportHeader=$LocalizationStrings['upd_m_status_install_compl_desc'];
				$report= nl2br(fread($fp,filesize(WBS_DIR.'/temp/.displaylog')));
				fclose($fp);
			}

			$meta='';//no refresh
			$UpdateManager->setState(updateManager::STATE_NONE ,'Update info showed');
			break;
		}

		//step 2.ERR
		case updateManager::STATE_DOWNLOAD_ERROR:{
			$status= $LocalizationStrings['upd_m_err_state_download'];
			$error=$UpdateManager->getErrorState();
			$reportHeader=$LocalizationStrings[$error['errCode']];
			$report=date('Y.m.d H:i:s',$error['time']).':: '.$error['fname'].($error['msg']?(': <span style="color:red;font-weight: bold;">'.$error['msg']).'</span>':'');
			$button.="<INPUT type='button'  class='update' onclick='JavaScript:runAction(\"full&amp;force=true\");' value='{$LocalizationStrings['upd_m_btn_retry_download']}'>";
			$button.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			$button.="<INPUT type='button'  class='update' onclick='JavaScript:runAction(\"reset\");' value='{$LocalizationStrings['btn_dismiss']}'>";
			$meta = '';
			break;
		}

		//step 3.ERR
		case updateManager::STATE_UNPACK_ERROR :{
			$status= $LocalizationStrings['upd_m_err_state_unpack'];
			$error=$UpdateManager->getErrorState();
			$reportHeader=$LocalizationStrings[$error['errCode']];
			$report=date('Y.m.d H:i:s',$error['time']).':: '.$error['fname'].($error['msg']?(': <span style="color:red;font-weight: bold;">'.$error['msg']).'</span>':'');
			$button.="<INPUT type='button'  class='update' onclick='JavaScript:runAction(\"full&amp;force=true\");' value='{$LocalizationStrings['upd_m_btn_retry_download']}'>";
			$button.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			$button.="<INPUT type='button'  class='update' onclick='JavaScript:runAction(\"reset\");' value='{$LocalizationStrings['btn_dismiss']}'>";
			$meta = '';

			break;
		}

		//step 4.ERR
		case updateManager::STATE_INSTALL_ERROR:{
			$status=$LocalizationStrings['upd_m_err_state_install'];
			$error=$UpdateManager->getErrorState();
			$reportHeader=$LocalizationStrings[$error['errCode']];
			$report=date('Y.m.d H:i:s',$error['time']).':: '.(isset($error['fname'])?($error['fname'].' '):'').'<span style="color:red;font-weight: bold;">'.$error['msg'].'</span>';
			$button.= "<INPUT type='button'  class='update' onClick='JavaScript:runAction(\"install&amp;force\");' value='{$LocalizationStrings['upd_m_btn_retry_install']}'>";
			$button.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			$button.= "<INPUT type='button'  class='update' onClick='JavaScript:runAction(\"full&amp;force\");' value='{$LocalizationStrings['upd_m_btn_retry_download_install']}'>";
			$button.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			$button.="<INPUT type='button'  class='update' onclick='JavaScript:runAction(\"reset\");' value='{$LocalizationStrings['btn_dismiss']}'>";
			$meta = '';
			
			break;
		}

		default:{
			$UpdateManager->cleanState('unknown state',$info);
			redirectBrowser( PAGE_WA_UPDATE, array() );
			//print "<h2>unknown value</h2>";
			//print "<pre>";var_dump($info);print "</pre>";
		}
		case updateManager::STATE_NONE :{
			$updateAvailable =true;
			$meta= '<META HTTP-EQUIV="Refresh" CONTENT="300">';
			
			$max_execution_time = ini_get('max_execution_time');
			$allow_url_fopen = ini_get('allow_url_fopen');
			$allow_url_fopen = intval($allow_url_fopen)||(strtolower($allow_url_fopen)=='on');
			
			if($info['webVersion']){
				$status=translate('upd_m_repair_av');
				$statusDescription = sprintf('<p>%s</p>',translate('upd_m_repair_may_be_required_if'));
				$statusDescription .= sprintf('<p><b>1.</b>&nbsp;%s</p>',translate('upd_m_repair_cur'));
				$statusDescription .= sprintf('<p><b>2.</b>&nbsp;%s</p>',translate('upd_m_newapp_info'));
				$updateAllowed =true;
			}else{
				$status=translate('upd_m_upd_err_title');
				$statusDescription=translate('upd_m_upd_err');
				$updateAllowed =false;
			}
			
			if(!$allow_url_fopen){
				$status=$LocalizationStrings['wbs_upd_furl_disabled'];
				$statusDescription = $LocalizationStrings['upd_m_upd_use_install_info'].'<br><hr>'.$statusDescription;
				$updateAllowed =false;
			}/*else{
				$status=$LocalizationStrings['upd_m_upd_use_install'];
				$statusDescription = $LocalizationStrings['upd_m_upd_use_install_info'].'<br><hr>'.$statusDescription;
				$updateAllowed =true;
			}*/
			$button.=$updateAllowed?"<INPUT type='button'  class='update' onClick='JavaScript:runAction(\"full&amp;force=\");' value='{$LocalizationStrings['upd_m_btn_update']}' id='btn_install' DISABLED>":'';
			break;
		}
	}
}


//ASSIGN VARIABLES TO SMARTY TEMPLATES

if(!isset($_GET['action'])
||(isset($_GET['action'])
&&(($_GET['action']=='details')||($_GET['action']=='systemconfiguration')||($_GET['action']=='changelog'))))
{
	//extract(wbs_getSystemStatistics());
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );

	//$preproc->assign( 'systemConfiguration', $systemConfiguration );
	//$preproc->assign( 'companyInfo', $companyInfo );
	//$preproc->assign( 'systemInfo', $systemInfo );

	$preproc->assign( PAGE_TITLE, strlen($pageTitle)?$pageTitle:$LocalizationStrings[6]);

	$preproc->assign( 'waStrings', $LocalizationStrings);
	$preproc->assign( 'pageHeader', $pageHeader);
	$preproc->assign( 'updateAvailable', $updateAvailable);
	$preproc->assign( 'updateAllowed', $updateAllowed);
	$preproc->assign('language',$language);
	
	$preproc->assign( 'restartRequired',isset($restartRequired)&&$restartRequired?true:false);

	$preproc->assign('currentVersion',$info['localVersion']);
	$preproc->assign('newestVersion',$info['webVersion']);
	$preproc->assign('downloadVersion',$info['downloadedVersion']);
	/*$preproc->assign('installDate',$info['installDate']);

	*/
	$preproc->assign('updateSize',round($info['updateSize']/1048576,2));

	$preproc->assign('req',$req);

	$preproc->assign('button',$button);
	$preproc->assign('meta',$meta);

	$preproc->assign('status',$status);
	$preproc->assign('statusDescription',$statusDescription);

	$preproc->assign('applicationList',$applicationList);


	$preproc->assign('changeLogDescription',$changeLogDescription);
	$preproc->assign('changeLog',$changeLog);
	$preproc->assign('inProgress',$inProgress);
	$preproc->assign('progressValue',$progressValue);

	$preproc->assign('metadaView',$metadaView);



	$preproc->assign('reportHeader',$reportHeader);
	$preproc->assign('report',$report);//.$info['time']
	/*

	$preproc->assign('details',$MDdetails);
	$preproc->assign('detailsHeader',$detailsHeader);
	$preproc->assign('details',$details);



	$preproc->assign('mainPageLink','wbsadmin.php');

	$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
	$preproc->assign( 'pdfAdminFile', GUIDE_FILE );
	$preproc->assign( 'returnLink', PAGE_DB_WBSADMIN );*/

	$preproc->assign('mainMenu',$mainMenu);
	$preproc->assign( 'installInfo', $installInfo );

	//$preproc->display( "updatewa.htm" );
	
	$preproc->assign( "mainTemplate","updatewa.htm" );
	$preproc->display( "main.htm" );
}

?>