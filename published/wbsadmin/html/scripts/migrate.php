<?php
	if(!defined('WBA_SETUP_PAGE')){
		$init_required = false;
		require_once( "../../../common/html/includes/httpinit.php" );
		require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
		redirectBrowser( PAGE_SECTION_SETUP, array() );
	}
$report=null;
///////////////////////////
//get application list
$appData = listPublishedApplications( $language, true );
if ( !is_array( $appData ) ) {
	$appData=null;
}else{
	$appData = array_keys( $appData );

}
//check installed SC
if(in_array('SC',$appData));
//select webasyst DB_KEY

$hostsData=wbsadmin_listRegisteredSystems($kernelStrings);
$DB_KEY_list=array_keys($hostsData);

//connect to database by actual dbkey

//check actual installed SC
$dbkeyList=array();
$dbList = wbsadmin_listRegisteredSystems( $kernelStrings );
foreach ($dbList as $DB_KEY=>$properties)
{
	if(isset($properties['APPLICATIONS'])
	&&isset($properties['APPLICATIONS']['SC'])
	&&$properties['APPLICATIONS']['SC']){
		$languageList=wbs_getLanuageList($DB_KEY);
		if(!PEAR::isError($languageList)){
			$dbkeyList[$DB_KEY]=$languageList;
		}
	}
}
if(count($dbkeyList)==0){
	$fatalError = true;
	//redirectBrowser( PAGE_SECTION_SETUP, array('msg'=>base64_encode($LocalizationStrings['wbs_migrate_sc_not_installed'])));
	$errorStr= translate('wbs_migrate_sc_not_installed');
	$migrateAllowed = false;
}else{
	$migrateAllowed = true;
	$commonData=isset($_POST['commondata'])?$_POST['commondata']:null;
	if(!is_array($commonData))$commonData=array('CUSTOM'=>array('DB_HOST'=>'localhost'));
	$commonData=trimArrayData($commonData);



	$dbq_select_language = "SELECT `id`, `name`, `iso2` FROM SC_language WHERE `enabled`=1 ORDER BY `priority`";
	$language='en';

	$btnIndex = getButtonIndex( array("savebtn", "cancelbtn"), $_POST );

	switch( $btnIndex ) {
		case 0 :{
			$timeStart=microtime(true);
			$params=wbs_getExportParams($kernelStrings,$commonData);
			if(PEAR::isError($params)){

				$fatalError=true;
				$errorStr=$params->getMessage();
				if ( $params->getCode() == ERRCODE_INVALIDFIELD )
				$invalidField = $params->getUserInfo();
				break;
			}
			$DB_KEY=$params['DB_KEY'];
			$DB_SETTINGS=$params['DB_SETTINGS'];
			$SSversion=strtolower($params['VERSION']);

			$language=strtolower(substr(trim($commonData['language'][$DB_KEY]),0,2));
			if(strlen($language)==0)$language='en';

			if($commonData['AUTO']['COPY']&&($commonData['DB_TARGET']=='auto')){
				$files=wbs_copyFiles($commonData['AUTO']['PATH'],$DB_KEY);
				if(PEAR::isError($files)){
					$fatalError=true;
					$errorStr=$files->getMessage();
					break;
				}
			}
			
			if(isset($commonData['SSenableCharset'])&&$commonData['SSenableCharset']){
				$SScharset = trim($commonData['SScharset']);
				$SSdataCharset = trim($commonData['SSdataCharset']);
				if($SSdataCharset=='')$SSdataCharset = 'utf8';
				$data=wbs_getShopScriptData($kernelStrings,$language,$DB_SETTINGS,$SSversion,$SScharset,$SSdataCharset);
			}else{
				$data=wbs_getShopScriptData($kernelStrings,$language,$DB_SETTINGS,$SSversion);
			}

			if(PEAR::isError($data)){
				$fatalError=true;
				$errorStr=$data->getMessage();
				break;
			}

			$SQLstrings=$data['SQLstrings'];
			$complianceID=$data['complianceID'];
			$SQLstatusList=$data['SQLstatusList'];

			$res=wbs_insertSCdata($kernelStrings,$SQLstrings,$complianceID,$SQLstatusList,$language,$DB_KEY);
			if(PEAR::isError($res)){
				$fatalError=true;
				$errorStr=$res->getMessage();
				break;
			}

			$report=$LocalizationStrings['migrate_complete'];
			$reportContent=$DB_SETTINGS['DB_HOST'].'.'.$DB_SETTINGS['DB_NAME'].'&nbsp;&rarr;&nbsp;'.$DB_KEY;
			$reportContent.='<br>'.sprintf('%01.3f s',(microtime(true)-$timeStart));
			break;
		}
		case 1 :{
			redirectBrowser( PAGE_DB_WBSADMIN, array() );
			exit;
		}

	}
}

///////////////////////////
$commonData['DBKEYS']=$dbkeyList;
$commonData['SSdataCharsetList'] = function_exists('mb_list_encodings')?mb_list_encodings():array();
natsort($commonData['SSdataCharsetList']); 

$commonData['AUTO']['WBS_DIR']=str_replace('\\','/',realpath('../../../../'));
if(!isset($commonData['AUTO']['PATH']))$commonData['AUTO']['PATH']='/';
////////////////////////////

//if ( !$fatalError ) {
$commonData = prepareArrayToDisplay($commonData, null, true);
$preproc->assign( 'commondata', $commonData);
$preproc->assign( 'SSversion', array('','FREE','PRO','PREMIUM'));
$preproc->assign( 'report', $report);
$preproc->assign( 'reportContent', $reportContent);
//}
$preproc->assign('migrateAllowed',$migrateAllowed);
$preproc->assign( ERROR_STR, $errorStr );
$preproc->assign( FATAL_ERROR, $fatalError );
$preproc->assign( FORM_LINK, PAGE_WA_MIGRATE );
$preproc->assign( PAGE_TITLE, 'wbs_ss_migarate');
$preproc->assign( INVALID_FIELD, $invalidField );



$preproc->assign( 'pdfAdminFile', GUIDE_FILE );
$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
//$preproc->assign( 'returnLink', PAGE_DB_WBSADMIN );
$preproc->assign ( 'waStrings', $LocalizationStrings);

	$preproc->assign( "mainTemplate","migrate.htm" );

?>