<?php
if(!defined('WBA_SETUP_PAGE')){
	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
	redirectBrowser( PAGE_SECTION_SETUP, array() );
}
$btnIndex = getButtonIndex( array("savebtn", "cancelbtn","returnbtn"), $_POST );
$params=array();
switch( $btnIndex ) {

	case 0 :
		//$commondata = rescueElement( $commondata, "CURRENT_SERVICE_ID", 0 );
		$res = wbs_saveFrontendSettings( $commondata, $kernelStrings, $db_locStrings );
		if ( PEAR::isError( $res ) ) {
			$errorStr = $res->getMessage();
			if ( $res->getCode() == ERRCODE_INVALIDFIELD ){
				$invalidField = $res->getUserInfo();
			}
			break;
		}
		$params['msg']=base64_encode('wbs_settings_update_success');
	case 1 :
	case 2 :
		$params['section'] = 'frontend';
		redirectBrowser( PAGE_SECTION_SETUP, $params,"",false,false,true );
}

do{
	$curSettings=wbs_getFrontendSettings();
	$dbkeyList=array();
	$dbList = wbsadmin_listRegisteredSystems( $kernelStrings );
	$applications = array();
	foreach ($dbList as $DB_key=>$properties)
	{
		if(isset($properties['APPLICATIONS'])){
			if(isset($properties['APPLICATIONS']['SC'])&&$properties['APPLICATIONS']['SC'])
			$dbkeyList[]=$DB_key;
			foreach($properties['APPLICATIONS'] as $APP_ID=>$installed){
				if(!isset($applications[$APP_ID])){
					$applications[$APP_ID] = $installed?true:false;
				}else{
					$applications[$APP_ID] |=$installed;
				}
			}
			
			if(isset($properties['APPLICATIONS']['PD'])&&$properties['APPLICATIONS']['PD'])
			$dbkeyList[]=$DB_key;
			foreach($properties['APPLICATIONS'] as $APP_ID=>$installed){
				if(!isset($applications[$APP_ID])){
					$applications[$APP_ID] = $installed?true:false;
				}else{
					$applications[$APP_ID] |=$installed;
				}
			}
		}

	}
	$DB_keys = array_unique(array_keys($dbList));
	$dbkeyList = array_unique($dbkeyList);
	$commondata = array();
	$commondata['DBKEYS'] = $dbkeyList;
	$commondata['DBKEY'] = count($DB_keys)?$DB_keys[0]:'';
	$commondata['CURRENT_SERVICE_ID'] = isset($curSettings['CURRENT_SERVICE_ID'])?$curSettings['CURRENT_SERVICE_ID']:'none';
	$commondata['CURRENT_DBKEY']=$curSettings['CURRENT_DBKEY'];
	$commondata['MOD_REWRITE']=$curSettings['MOD_REWRITE'];
	$commondata['DISABLE_POWERED_BY']=$curSettings['DISABLE_POWERED_BY'];

	$commondata['SERVICES'] = array();
	$appList = listPublishedApplications( $language, true );
	foreach( array('SC'=>array('','shop/'),'PD'=>array('','photos/'),'login'=>array(translate('fes_frontend_login'),'login/'),'none'=>array(translate('fes_frontend_blank'),null))  as $key=>$val )
	{
		if(strlen($key)==2){
			if(isset($appList[$key])&&isset($applications[$key])&&$applications[$key]){
				$commondata['SERVICES'][] = array('ID' => $key,'NAME' =>$appList[$key]['APPLICATION']['NAME'][$language],'LINK'=>$val[1] );
				if($key==$commondata['CURRENT_SERVICE_ID']){
					$mainPageInfo=$appList[$key]['APPLICATION']['NAME'][$language];
				}
			}
		}else{
			$commondata['SERVICES'][] = array('ID' => $key,'NAME' =>$val[0],'LINK'=>$val[1] );
			if($key==$commondata['CURRENT_SERVICE_ID']){
				$mainPageInfo=$val[0];
			}
		}
	}
	$mod_rewrite=wbs_check_mod_rewrite();

}while(false);
$path = $_SERVER['SCRIPT_FILENAME']."/../../../../../";
while (strpos($path,'\\')!==false) {
	$path=str_replace('\\','/',$path);
}
while (strpos($path,'//')!==false) {
	$path=str_replace('//','/',$path);
}
$res = array();
$paths = explode('/',$path);
foreach ($paths as $dir){
	if($dir == '..'){
		array_pop($res);
		continue;
	}
	if($dir == '.'){
		continue;
	}
	array_push($res,$dir);
}
$path = implode('/',$res);

$install_path = str_replace(array('\\','///','//'),'/',defined('WBS_INSTALL_PATH')?'/'.WBS_INSTALL_PATH:'/'.substr($path.'/',strlen($_SERVER['DOCUMENT_ROOT'])));
$indexUrl='http://'.str_replace(array('//','\\'),'/',$_SERVER['HTTP_HOST'].$install_path);


$preproc->assign('SCinstalled',isset($appList['SC'])&&count($commondata['DBKEYS']));
$preproc->assign('PDinstalled',isset($appList['PD'])&&count($commondata['DBKEYS']));
$preproc->assign('mod_rewrite_disabled',(($mod_rewrite===-2)||($mod_rewrite===0)));

$preproc->assign( FORM_LINK, PAGE_FRONTEND_SETUP );

$preproc->assign( INVALID_FIELD, $invalidField );
$preproc->assign( PAGE_TITLE, 'fes_page_name');


if ( !$fatalError ) {
	$preproc->assign( 'commondata', prepareArrayToDisplay($commondata, null, true) );
}
$preproc->assign('mainPageInfo',$mainPageInfo);
$preproc->assign('indexUrl',$indexUrl);

$preproc->assign( "timeZones", $timeZones );
/*
 $preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
 $preproc->assign( 'pdfAdminFile', GUIDE_FILE );*/

$preproc->assign( "mainTemplate","frontend.htm" );
?>