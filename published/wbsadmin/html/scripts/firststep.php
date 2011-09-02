<?php
//$sqlServerParams[WBS_DBCHARSET]
$init_required = false;
require_once( "../../../common/html/includes/httpinit.php" );

require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

//$templateName =	"classic";

if(!defined('WBS_INSTALL_PATH')||!strlen(WBS_INSTALL_PATH)){
	if(!isset($_SERVER['DOCUMENT_ROOT'])){
		if(isset($_SERVER['SCRIPT_FILENAME'])){
			$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
		}
	}
	if(!isset($_SERVER['DOCUMENT_ROOT'])){
		if(isset($_SERVER['PATH_TRANSLATED'])){
			$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
		}
	}
	//end for IIS
	$filePath = explode('/',str_replace('\\','/',dirname($_SERVER['SCRIPT_FILENAME'])));
	unset($filePath[count($filePath)-1]);
	unset($filePath[count($filePath)-1]);
	unset($filePath[count($filePath)-1]);
	unset($filePath[count($filePath)-1]);
	$filePath = implode('/',$filePath);
	
	
	$WBS_INSTALL_PATH = str_replace(array('\\','///','//'),'/','/'.substr($filePath.'/',strlen($_SERVER['DOCUMENT_ROOT'])));
}else{
	$WBS_INSTALL_PATH = WBS_INSTALL_PATH;
}
if(!isset($commondata['WBS_INSTALL_PATH'])){
	$commondata['WBS_INSTALL_PATH'] = $WBS_INSTALL_PATH;
}
//	$language =	LANG_ENG;

// Load	application	strings
//
$locStrings = $loc_str[$language];
$LocalizationStrings = $db_loc_str[$language];
$fatalError = false;
$invalidField = null;
$errorStr = null;
$infoStr = null;
$title = $LocalizationStrings['wbs_step4_title'];
$success = false;
$hostCreated=false;

function showStepProgress($step)
{
	global $LocalizationStrings;
	$step_=array(0,0,1,2,2,3,3,4);
	if(isset($step_[$step])){
		$step=$step_[$step];
	}else{
		$step=-1;
	}
	$names=array($LocalizationStrings['wbs_step1'],$LocalizationStrings['wbs_step2'],$LocalizationStrings['wbs_step3'],$LocalizationStrings['wbs_step4'],$LocalizationStrings['wbs_step5'],);

	$step_string='';
	foreach ($names as $stepNum=>$stepName){
		if($stepNum<$step){
			$step_string.='<li>'.$stepName.'</li>';
		}elseif ($stepNum==$step){
			$step_string.='<li class="active">'.$stepName.'</li>';
		}else{
			$step_string.='<li class="next">'.$stepName.'</li>';
		}
	}
	return '<ul class="subnavigation">'.$step_string.'</ul>';
}

if ( !isset( $action ) )
$action	= ACTION_NEW;

switch (true) {
	case (true) :
		if ( $action == ACTION_NEW && (!isset($edited) || !$edited) )
		{
				
			if($language=='rus'){
				$languages = array($language => "Russian",DEF_LANG_ID=>DEF_LANG_NAME);
			}else{
				$languages = array( DEF_LANG_ID=>DEF_LANG_NAME );
			}
			$serverData = array( WBS_HOST=>'localhost', WBS_ENCODING=>'UTF-8', WBS_LANGUAGES=>$languages,	WBS_WEBASYSTHOST=>'localhost' );
				
			if ( count( $wbs_sqlServers ) != 0 )
			foreach ( $wbs_sqlServers as $key=>$data )
			{
				$serverData = $data;
				$serverData['SERVER_NAME'] = $key;
			}
		}
}

$btnIndex =	getButtonIndex( array( "savebtn", "cancelbtn" ), $_POST );
switch( $btnIndex )
{
	case 0 : {
		//*****************************//
		//server connection settings    //
		//*****************************//
		//$hostData[DB_CREATION_NEW][]
		if(!isset($serverData['SERVER_NAME'])||!strlen($serverData['SERVER_NAME']))
		$serverData['SERVER_NAME']=isset($serverData['HOST'])?$serverData['HOST']:'Webasyst-MySQL-Server';
			
		if(isset($hostData[HOST_DB_CREATE_OPTIONS]) && $hostData[HOST_DB_CREATE_OPTIONS][HOST_CREATE_OPTION]=='new'){
			$serverData[WBS_ADMINRIGHTS]='TRUE';
			$hostData[HOST_DB_CREATE_OPTIONS][HOST_DATABASE_USER_NEW] = $hostData[HOST_DB_CREATE_OPTIONS][HOST_DATABASE_USER_EXISTING];
			$hostData[HOST_DB_CREATE_OPTIONS][HOST_PASSWORD_NEW] = $hostData[HOST_DB_CREATE_OPTIONS][HOST_PASSWORD_EXISTING];
			$serverData[WBS_ADMIN_USERNAME] = $hostData[HOST_DB_CREATE_OPTIONS][HOST_DATABASE_USER_EXISTING];
			$serverData[WBS_ADMIN_PASSWORD] = $hostData[HOST_DB_CREATE_OPTIONS][HOST_PASSWORD_EXISTING];

			$hostData[HOST_DB_CREATE_OPTIONS][HOST_DATABASE_USER] = $serverData[WBS_ADMIN_USERNAME] ;
			$hostData[HOST_DB_CREATE_OPTIONS][HOST_PASSWORD] = $serverData[WBS_ADMIN_PASSWORD];

		}else{
			$serverData[WBS_ADMINRIGHTS]='FALSE';
		}
			
		if ( isset(  $serverData[WBS_ADMINRIGHTS] ) &&  $serverData[WBS_ADMINRIGHTS] == WBS_TRUEVAL )
		$adminID = '1';
		else
		$adminID = '0';

		//$u=base64_encode($serverData[WBS_ADMIN_USERNAME]);
		//$p=base64_encode($serverData[WBS_ADMIN_PASSWORD]);

		if ( $adminID == 0 )
		$serverData[WBS_ADMIN_USERNAME] = $serverData[WBS_ADMIN_PASSWORD] = "";

		$serverData = prepareArrayToStore( $serverData );
		if($language=='rus'){
			$languages = array($language => "Russian",DEF_LANG_ID=>DEF_LANG_NAME);
		}else{
			$languages = array( DEF_LANG_ID=>DEF_LANG_NAME );
		}

		$serverData[WBS_LANGUAGES] = $languages;
		$serverData[WBS_DBCHARSET] = 'UTF8';
		$hostData['ADMINISTRATOR']['LANGUAGE'] = $language;

		$res = wbsadmin_addmodSQLServer( count( $wbs_sqlServers	) != 0 ? ACTION_EDIT : ACTION_NEW, $serverData, $locStrings, $LocalizationStrings );
		if ( PEAR::isError( $res ) ) {
			$errorStr = $res->getMessage();

			$invalidField = $res->getUserInfo();

			//$serverData[WBS_ADMIN_USERNAME] = base64_decode( $u );
			//$serverData[WBS_ADMIN_PASSWORD] = base64_decode( $p );

			break;
		}
		$hostCreated=true;
		loadWBSSettings();

		//	redirectBrowser( PAGE_DB_WBSINSTALL_STEP2, array( 'u'=>$u, 'p'=>$p,	'isadm'=>$adminID )	);
		//}
		//*****************************//
		// setup DB
		//*****************************//
		//case 2 : {
		// Load application list
		//
		$appData = listPublishedApplications( $language, true );
		if ( !is_array( $appData ) ) {
			$errorStr = $LocalizationStrings[1];
			$fatalError = true;

			break;
		}

		$appData = sortPublishedApplications( $appData );

		foreach( $appData as $APP_ID=>$cur_data ) {
			$parents = $cur_data[APP_REG_PARENTS];
			if ( false !== ($index = array_search ( AA_APP_ID, $parents ) ) ) {
				unset($parents[$index]);

				$pub = array();
				foreach ( $parents as $cur_parent )
				$pub[] = $cur_parent;

				$cur_data[APP_REG_PARENTS] = $pub;
				$appData[$APP_ID] = $cur_data;
			}
		}

		if ( !isset($app_list) )
		$app_list = null;

		$appData = html_extendApplicationData( $appData, $app_list, "Dependent on", $language );
		$appData = fixApplicationDependences( $appData );

		if ( $action == ACTION_NEW && !isset($edited) )
		{
			$hostData = array();
			$hostData[HOST_DBSETTINGS][HOST_DATE_FORMAT] = HOST_DEF_DATE_FORMAT;
			$hostData[HOST_DBSETTINGS][HOST_MAXUSERCOUNT] = HOST_DEF_MAXUSERCOUNT;
		}

		$serverNames = array_keys($wbs_sqlServers);
		foreach( $dateFormats as $dateFormat ) {
			$dateFormat_ids[] = $dateFormat;
			$dateFormat_names[] = $dateFormat;
		}

		$hostData[HOST_DBSETTINGS][HOST_SQLSERVER] = $serverNames[0];

		if ( $action == ACTION_NEW ) {
			if ( (!isset($edited) || !$edited) ) {
				$serverData = $wbs_sqlServers[$hostData[HOST_DBSETTINGS][HOST_SQLSERVER]];
				$hasAdminRights = $serverData[WBS_ADMINRIGHTS] == WBS_TRUEVAL;

				if ( !$hasAdminRights ){
					$hostData[HOST_DB_CREATE_OPTIONS][HOST_CREATE_OPTION] = "use";
				}elseif(false){//attempt to create db now
					if($dbh = @mysql_connect($serverData[WBS_HOST].($serverData[WBS_PORT]?':'.$serverData[WBS_PORT]:''),$serverData[WBS_ADMIN_USERNAME],$serverData[WBS_ADMIN_PASSWORD])){
						if(@mysql_query(sprintf('CREATE DATABASE `%s`',$hostData[HOST_DB_CREATE_OPTIONS][HOST_DATABASE_NEW]),$dbh)){
							$hostData[HOST_DB_CREATE_OPTIONS][HOST_CREATE_OPTION] = "use";
						}
						@mysql_close($dbh);
					}
				}
			}

			$prevServerName = $hostData[HOST_DBSETTINGS][HOST_SQLSERVER];

		}

		$language_names = $language_ids = $language_names_indexed = array();
		$curServerData = $wbs_sqlServers[$hostData[HOST_DBSETTINGS][HOST_SQLSERVER]];
		foreach ( $curServerData[WBS_LANGUAGES] as $lang_id => $lang_name ) {
			$language_names[] = $lang_name;
			$language_ids[] = $lang_id;
			$language_names_indexed[$lang_id] = $lang_name;
		}



		//$hostData[HOST_DB_CREATE_OPTIONS][HOST_DATABASE_USER] = base64_decode( $u );
		//$hostData[HOST_DB_CREATE_OPTIONS][HOST_PASSWORD] = base64_decode( $p );

		// Account operations log
		//
		$dom = null;
		$logRecords = listAccountLogRecords( $DB_KEY, $locStrings, $dom );
		if ( PEAR::isError($logRecords) || !count($logRecords) )
		$logRecords = null;

		if ( $logRecords ) {
			$logData = array();
			foreach( $logRecords as $index=>$logRecord ) {
				$opType = $logRecord->get_attribute(AOPR_TYPE);

				$opTypeName = $locStrings[$accountOperationNames[$opType]];

				$recordData = array( AOPR_DATETIME => convertToDisplayDateTime( $logRecord->get_attribute(AOPR_DATETIME) ),
				AOPR_TYPE => $opTypeName,
				AOPR_IP => $logRecord->get_attribute(AOPR_IP) );

				if ( $opType == aop_modify )
				$recordData['ROW_URL'] = prepareURLStr( PAGE_DB_LOGDATA, array( 'DB_KEY'=>$DB_KEY,
				'index'=>$index ) );

				$logData[] = $recordData;
			}

			$logRecords = array_reverse($logData);
		}
		//******************************//
		//
		//*******************************//
		$hostData[HOST_DBSETTINGS][HOST_DBSIZE_LIMIT] = "";
		$hostData[HOST_DBSETTINGS][HOST_EXPIRE_DATE] = "";
		$hostData[HOST_ADMINISTRATOR]['PASSWORD2'] = $hostData[HOST_ADMINISTRATOR]['PASSWORD1'] = $hostData[HOST_FIRSTLOGIN]['PASSWORD2'] = $hostData[HOST_FIRSTLOGIN]['PASSWORD1'];
		$hostData[HOST_DBSETTINGS][HOST_DATE_FORMAT] = HOST_DEF_DATE_FORMAT;
		$hostData[HOST_DBSETTINGS][HOST_MAXUSERCOUNT] = null;
		$hostData[HOST_ADMINISTRATOR][HOST_LANGUAGE] = $language;//set multi

		$dbSettingsData = prepareArrayToStore( $hostData[HOST_DBSETTINGS] );
		$accountData = prepareArrayToStore( $hostData[HOST_FIRSTLOGIN] );
		$adminData = prepareArrayToStore( $hostData[HOST_ADMINISTRATOR] );

		$dbCreateData = ($action == ACTION_NEW) ? prepareArrayToStore( $hostData[HOST_DB_CREATE_OPTIONS] ) : array();
		$invalidArr = 0;

		if ( !isset($dbSettingsData[HOST_READONLY]) )
		$dbSettingsData[HOST_READONLY] = 0;

		$appData = listPublishedApplications( $language, true );
		if ( !is_array( $appData ) ) {
			$errorStr = $LocalizationStrings[1];
			$fatalError = true;

			break;
		}

		$appData = sortPublishedApplications( $appData );

		foreach( $appData as $APP_ID=>$cur_data ) {
			$parents = $cur_data[APP_REG_PARENTS];
			if ( false !== ($index = array_search ( AA_APP_ID, $parents ) ) ) {
				unset($parents[$index]);

				$pub = array();
				foreach ( $parents as $cur_parent )
				$pub[] = $cur_parent;

				$cur_data[APP_REG_PARENTS] = $pub;
				$appData[$APP_ID] = $cur_data;
			}
		}


		$appList = array();

		foreach( $appData as $APP_ID => $APP_DATA )
		$appList[] = $APP_ID;

		if ( !strlen( $dbSettingsData[HOST_DB_KEY] ) ) {
			if ( isset($hostData[HOST_DB_CREATE_OPTIONS][HOST_CREATE_OPTION]) ) {
				if ( $hostData[HOST_DB_CREATE_OPTIONS][HOST_CREATE_OPTION] == "new" ){
					$newDBKey = substr( $dbCreateData[HOST_DATABASE_NEW], 0, DB_MAXDBKEYLEN );
				}else{
					$newDBKey = substr( $dbCreateData[HOST_DATABASE_EXISTING], 0, DB_MAXDBKEYLEN );
				}
				$newDBKey_='';
				$length=strlen($newDBKey);
				for ($i=0;$i<$length;$i++){
					$char = $newDBKey[$i];
					if(strpos(KEY_SYMBOLS,strtolower($char))===false)
					continue;
					$newDBKey_.=strtoupper($char);
				}
				$newDBKey=$newDBKey_;
			} else
			$newDBKey = null;

			$dbSettingsData[HOST_DB_KEY] = $newDBKey;
			$hostData[HOST_DBSETTINGS][HOST_DB_KEY] = $newDBKey;
		}

		$dbSettingsData[HOST_RECIPIENTSLIMIT] = null;

		if ( !isset( $hostData ) )
		$hostData = array();

		$hostData[HOST_BALANCE][MODULE_CLASS_SMS][HOST_VALUE] = "UNLIMITED";
		$hostData[HOST_MODULES][MODULE_CLASS_SMS]["ID"] = "";
		$hostData[HOST_MODULES][MODULE_CLASS_SMS]["DISABLED"] = 1;

		$dbSettingsData[HOST_MODULES] = $hostData[HOST_MODULES];
		$dbSettingsData[HOST_BALANCE] = $hostData[HOST_BALANCE];
		if(strtoupper($hostData[HOST_FIRSTLOGIN]['LOGINNAME']) == 'ADMINISTRATOR'){
			$invalidField = sprintf( "hostData[FIRSTLOGIN][LOGINNAME]" );
			$errorStr = $LocalizationStrings['wbs_reserved_login'];
			break;
		}

		$res = addModDBProfile( $action, $DB_KEY, $dbSettingsData, $accountData, $adminData, $dbCreateData, $locStrings, $invalidArr, $appList, null, null, true );

		if ( PEAR::isError( $res ) ) {

			if ( strlen( $res->getUserInfo()) ) {

				$invalidField = $res->getUserInfo();

				if ( $invalidArr == 0 ){
					if ($invalidField==HOST_DB_KEY){
						$invalidField = sprintf( "hostData[DB_CREATE_OPTIONS][%s]", ($serverData[WBS_ADMINRIGHTS]=='TRUE')?'DATABASE_NEW':'DATABASE_EXISTING' );;
					}else{
						$invalidField = sprintf( "hostData[DBSETTINGS][%s]", $invalidField );
					}
				}elseif ( $invalidArr == 1 )
				$invalidField = sprintf( "hostData[FIRSTLOGIN][%s]", $invalidField );
				elseif ( $invalidArr == 2 )
				$invalidField = sprintf( "hostData[ADMINISTRATOR][%s]", $invalidField );
				elseif ( $invalidArr == 3 )
				$invalidField = sprintf( "hostData[DB_CREATE_OPTIONS][%s]", $invalidField );
			}

			$errorStr = $res->getMessage();

			if ( $invalidField == "hostData[ADMINISTRATOR][PASSWORD1]" )
			$invalidField = "hostData[FIRSTLOGIN][PASSWORD1]";

			break;
		}

		$profileCreated = true;
		$DB_KEY = $res;
		if(PEAR::isError($res = wbs_saveFrontendSettings(array('CURRENT_SERVICE_ID'=>(in_array('SC',$appList)?'SC':'login'),'MOD_REWRITE'=>0,'CURRENT_DBKEY'=>$DB_KEY),$kernelStrings,$LocalizationStrings,true))){
			$errorStr = $res->getMessage();
			break;
		}
		wbs_saveInstallInformation(array('COMPANY'=>$hostData[FIRSTLOGIN][COMPANYNAME],'WBS_INSTALL_PATH'=>$commondata['WBS_INSTALL_PATH']));


		$complete = fopen( COMPLETE_FLAG, "w+" );
		fputs( $complete, "DO NOT REMOVE THIS FILE!" );
		fclose( $complete );

		if ( $sendmail_enabled )
		{

			$scriptName = $_SERVER['SCRIPT_NAME'];

			$scriptPath = substr( $scriptName, 0, strlen($scriptName)-strlen(basename($scriptName)) );
			$scriptPath = substr( $scriptPath, 0, strpos( $scriptPath, "/published/wbsadmin/html/scripts/" ) );

			$serverName = $_SERVER['SERVER_NAME'];

			$dbAdminURL = sprintf( "http://%s%s/installer/", $serverName, $scriptPath );
			$loginURL = sprintf( "http://%s%s/login/", $serverName, $scriptPath );

			$loginPageURL = prepareURLStr( $loginURL, array('DB_KEY'=>$DB_KEY) );

			$adminPageURL = $dbAdminURL;

			$message  = $LocalizationStrings['install_maillog_title'];
			$message .= $LocalizationStrings['install_maillog_congr'];
			$message .= $LocalizationStrings['install_maillog_wba'];
			$message .= sprintf( "%s\n", $adminPageURL  );
			$message .= "\n\n";
			$message .= $LocalizationStrings['install_maillog_wbd'];
			$message .= sprintf( "%s", $loginPageURL  );
			$message .= "\n\n";
			$message .= sprintf( $LocalizationStrings['install_maillog_dbkey'],  $DB_KEY );
			$message .= sprintf( $LocalizationStrings['install_maillog_login_as'], $hostData[HOST_FIRSTLOGIN]['FIRSTNAME'], $hostData[HOST_FIRSTLOGIN]['LASTNAME'] );
			$message .= sprintf( $LocalizationStrings['install_maillog_login'],  $hostData[HOST_FIRSTLOGIN]['LOGINNAME'] );
			$message .= sprintf( $LocalizationStrings['install_maillog_password'],  $hostData[HOST_FIRSTLOGIN]['PASSWORD1'] );
			$message .= $LocalizationStrings['install_maillog_login_as_adm']."ADMINISTRATOR\n";
			$message .= sprintf( $LocalizationStrings['install_maillog_password'],  $hostData[HOST_ADMINISTRATOR]['PASSWORD1'] );;

			mail( $hostData[HOST_FIRSTLOGIN]['EMAIL'], "WebAsyst Installation Wizard Notification", $message, "From: \"WebAsyst Installation Wizard\"<$wbs_robotemailaddress>\nContent-Type: text/plain; charset=\"utf-8\"");
		}
		$title = $LocalizationStrings['wbs_step5_title'];
		$success=true;


		break;


	}


	case 1 :
		redirectBrowser( '../../../../install.php', array(	'cancel'=>1	) );

	default:
		break;

}


$pageTitle = ($action == ACTION_NEW) ? $LocalizationStrings[8] : $LocalizationStrings[9];

extract(wbs_getSystemStatistics());

$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );
if ( $action == ACTION_EDIT || $profileCreated )
{
	$preproc->assign( "DB_KEY", $DB_KEY );
	$preproc->assign( "LOGINNAME", $hostData[HOST_FIRSTLOGIN]['LOGINNAME'] );
	$preproc->assign( "LPASSWORD", $hostData[HOST_FIRSTLOGIN]['PASSWORD1'] );

}


$preproc->assign( 'systemConfiguration', $systemConfiguration['info'] );
//$preproc->assign( 'companyInfo', $companyInfo );
//$preproc->assign( 'systemInfo', $systemInfo );

$preproc->assign( PAGE_TITLE, $LocalizationStrings['install_title'] );
$preproc->assign ( 'waStrings', $LocalizationStrings);

$preproc->assign( FORM_LINK, 'firststep.php' );
$preproc->assign( ERROR_STR, $errorStr );
$preproc->assign( FATAL_ERROR, $fatalError );
$preproc->assign( INVALID_FIELD, $invalidField );
$preproc->assign( ACTION, $action );
$preproc->assign( 'GUIDE_FILE', GUIDE_FILE );
$preproc->assign( "infoStr", $infoStr );
$preproc->assign( "step", showStepProgress($success?7:6) );
$preproc->assign( "title", $title );

$preproc->assign( "disableChange", ( count( $wbs_sqlServers ) != 0 ) ? 1 : 0 );
$preproc->assign( "htaccessReplaced", file_exists(str_replace(array('\\','//'),'/',sprintf( "%s/published/wbsadmin/html/configs/.htaccess.user", WBS_DIR))));

$preproc->assign( 'buttonCaption', $LocalizationStrings['install_continue']);

if(@file_exists(INSTALL_GUIDE_FILE)){

	$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( INSTALL_GUIDE_FILE )/1024 ) );
	$preproc->assign( 'pdfGuideFile', INSTALL_GUIDE_FILE );
}

if ( !$fatalError )
{
	$preproc->assign( 'serverData',	prepareArrayToDisplay($serverData) );
	$preproc->assign( "hostData", $hostData );
	$preproc->assign('commondata',$commondata);

	$preproc->assign( "language_names", $language_names );
	$preproc->assign( "language_ids", $language_ids );
	$preproc->assign( "language_names_indexed", $language_names_indexed );

	$preproc->assign( "dateFormat_ids", $dateFormat_ids );
	$preproc->assign( "dateFormat_names", $dateFormat_names );

	$preproc->assign( "app_data", $appData );
	$preproc->assign( "profileCreated", $profileCreated );

	$preproc->assign( "logRecords", $logRecords );

	if ( isset($hasAdminRights) )
	$preproc->assign( "hasAdminRights", $hasAdminRights );

	if ( $profileCreated )
	{
		$preproc->assign( "loginURL", "login/" );
		$preproc->assign( "adminURL", "installer/" );
	}

	if ( $action == ACTION_NEW )
	$preproc->assign( "prevServerName", $prevServerName );

}

//$preproc->display( "firststep.htm" );
$preproc->assign( "mainTemplate","firststep.htm" );
$preproc->display( "main.htm" );
?>