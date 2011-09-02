<?php
if(!defined('WBA_SETUP_PAGE')){
	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
	redirectBrowser( PAGE_SECTION_SETUP, array() );
}

$invalidField = null;
$profileCreated = false;
if(!isset($DB_KEY)&&!wbs_multiDbkeyEnabled()){
	$DB_KEY = wbs_getDefaultSimpleDbkey();
	$action = ACTION_EDIT;
}
if(!isset($DB_KEY)&&!wbs_multiDbkeyEnabled()){
	redirectBrowser( PAGE_SECTION_SETUP,array("msg"=>base64_encode('dbkey_not_found')));
}

if ( !isset($noServerFound) )
$noServerFound = false;

if ( !isset($sorting) )
$sorting = "";

if ( !isset( $action )||$DB_KEY ===false )
$action = ACTION_NEW;

if ( $action == ACTION_NEW )
$DB_KEY = null;

define( "DATE_DISPLAY_FORMAT", DB_DEF_DATE_FORMAT );

$btnIndex = getButtonIndex( array("savebtn", "cancelbtn", "deletebtn", "restorebtn", "removebtn", "removeprofilebtn", "smsquotebtn", "returnbtn" ), $_POST );

switch ( $btnIndex ) {
	case 7 : redirectBrowser( PAGE_DB_WBSADMIN,array());
	case 1 : redirectBrowser( PAGE_DB_DBLIST, array(SORTING_COL=>$sorting), $DB_KEY );
	case 6 :
		$prvars = base64_encode( serialize( $hostData ) );
		redirectBrowser( PAGE_DB_BALANCE, array( "prvars"=>$prvars, "DB_KEY"=>base64_encode( $DB_KEY ) ), "", false, false, true );
}

switch (true) {
	case (true) : {
		$sys_languages = wbs_listSysLanguages();
		if ( PEAR::isError($sys_languages) ) {
			$errorStr = $sys_languages->getMessage();
			$fatalError = true;

			break;
		}

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

				$p = array();
				foreach ( $parents as $cur_parent )
				$p[] = $cur_parent;

				$cur_data[APP_REG_PARENTS] = $p;
				$appData[$APP_ID] = $cur_data;
			}
		}

		if ( !isset($app_list) )
		$app_list = null;

		$appData = html_extendApplicationData( $appData, $app_list, "Dependent on", $language );
		$appData = fixApplicationDependences( $appData );

		// Check if server exists
		//
		if ( $noServerFound ) {
			$fatalError = true;

			break;
		}

		// Load host data
		//
		if ( $action == ACTION_EDIT && (!isset($edited) || (isset($edited) && isset($deleted)) ) )
		{

			$hostData = loadHostDataFile( $DB_KEY, $kernelStrings );

			if ( PEAR::isError($hostData) )
			{
				if ( $hostData->getCode() != ERRCODE_SERVERNOTFOUND_ERR )
				{
					$errorStr = $hostData->getMessage();
					$fatalError = false;

					break;
				}
				else
				{
					$noServerFound = true;
					$fatalError = true;
					$noServerMessage = sprintf( $LocalizationStrings[56], $hostData->getUserInfo() );
					$recoverMessage = sprintf( $LocalizationStrings[57], $hostData->getUserInfo(), $hostData->getUserInfo() );

					break;
				}
			}

			$hostData[HOST_DBSETTINGS][HOST_SIGNUP_DATETIME] = convertToDisplayDate( $hostData[HOST_DBSETTINGS][HOST_SIGNUP_DATETIME] );
			$hostData[HOST_DBSETTINGS][HOST_EXPIRE_DATE] = convertToDisplayDate( $hostData[HOST_DBSETTINGS][HOST_EXPIRE_DATE] );

			if ( $hostData[HOST_DBSETTINGS][HOST_READONLY] )
			$hostData[HOST_DBSETTINGS][HOST_READONLY] = "1";
			else
			$hostData[HOST_DBSETTINGS][HOST_READONLY] = "0";

			$installedApplications = $hostData[HOST_APPLICATIONS];
			if ( is_array($appData) ) {
				$installedApplications = array_keys( $installedApplications );

				foreach( $appData as $APP_ID=>&$APP_DATA )
				$APP_DATA[APP_CHECKED] = in_array( $APP_ID, $installedApplications )?1:0;
			}

			$hostData[HOST_DBSETTINGS][HOST_DBNAME] = $hostData[HOST_DBSETTINGS][HOST_DBNAME];
		}

		$smsModules = array();
		$smsDefaultModule = "";
		$smsEnabled = 0;
		if(!$WBS_MODULES){
			/*@var $WBS_MODULES wbsModules*/
			$WBS_MODULES = new wbsModules();
		}
		$smsClass = $WBS_MODULES->getClass( MODULE_CLASS_SMS );

		if ( PEAR::isError( $smsClass ) )
		$errorStr = $smsClass->getMessage();
		else
		{
			$modulesList = $smsClass->getList();
			$defaultModule = $smsClass->getDefaultModule();

			if ( PEAR::isError( $defaultModule ) )
			$defaultId = "";
			else
			$defaultId = $defaultModule->id;

			$smsCount = 0;
			foreach( $modulesList as $key=>$value)
			if ( $value->isInstalled() )
			{
				$smsModules[$key] = ( $defaultId == $key )? 1 : 0;
				$smsCount++;
			}

			$smsDefaultModule = $defaultId;

			$smsEnabled = ( $smsClass->isDisabled() || ( $action == ACTION_NEW && $smsCount == 0 ) ) ? 0 : 1;
		}

		if ( $action == ACTION_NEW && !isset($edited) ) {
			$hostData = array();
			$hostData[HOST_DBSETTINGS][HOST_DATE_FORMAT] = HOST_DEF_DATE_FORMAT;
			$hostData[HOST_BALANCE][MODULE_CLASS_SMS][HOST_VALUE] = "UNLIMITED";

			$noServerFound = !count( $wbs_sqlServers );
			if ( $noServerFound ) {
				$fatalError = true;
				break;
			}
		}

		$serverNames = array_keys($wbs_sqlServers);

		foreach( $dateFormats as $dateFormat ) {
			$dateFormat_ids[] = $dateFormat;
			$dateFormat_names[] = $dateFormat;
		}

		if ( $action == ACTION_NEW ) {
			if ( (!isset($edited) || !$edited) )
			$hostData[HOST_DBSETTINGS][HOST_SQLSERVER] = $serverNames[0];

			if ( (!isset($edited) || !$edited) || $prevServerName != $hostData[HOST_DBSETTINGS][HOST_SQLSERVER] ) {
				$serverData = $wbs_sqlServers[$hostData[HOST_DBSETTINGS][HOST_SQLSERVER]];
				$hasAdminRights = $serverData[WBS_ADMINRIGHTS] == WBS_TRUEVAL;

				if ( !$hasAdminRights )
				$hostData[HOST_DB_CREATE_OPTIONS][HOST_CREATE_OPTION] = "use";
			}

			$prevServerName = $hostData[HOST_DBSETTINGS][HOST_SQLSERVER];
		}else{//allow user change db_create_option
			$serverData = $wbs_sqlServers[$hostData[HOST_DBSETTINGS][HOST_SQLSERVER]];
			$hasAdminRights = $serverData[WBS_ADMINRIGHTS] == WBS_TRUEVAL;//DB_CREATE_OPTION
			//var_dump($hostData[HOST_DBSETTINGS]);exit;//[HOST_DB_CREATE_OPTION];
		}

		$language_names = $language_ids = $language_names_indexed = array();

		$curServerData = $wbs_sqlServers[$hostData[HOST_DBSETTINGS][HOST_SQLSERVER]];

		foreach ( $curServerData[WBS_LANGUAGES] as $lang_id => $lang_name ) {

			if ( !array_key_exists($lang_id, $sys_languages) )
			continue;

			$lang_name = $sys_languages[$lang_id][WBS_LANGUAGE_NAME];

			$language_names[] = $lang_name;
			$language_ids[] = $lang_id;
			$language_names_indexed[$lang_id] = $lang_name;
		}

		// Account operations log
		//
		$dom = null;
		$logRecords = listAccountLogRecords( $DB_KEY, $kernelStrings, $dom );
		if ( PEAR::isError($logRecords) || !count($logRecords) )
		$logRecords = null;

		if ( $logRecords ) {
			$logData = array();
			foreach( $logRecords as $index=>$logRecord ) {
				$opType = $logRecord->get_attribute(AOPR_TYPE);

				$opTypeName = $kernelStrings[$accountOperationNames[$opType]];

				$recordData = array( AOPR_DATETIME => convertToDisplayDateTime( $logRecord->get_attribute(AOPR_DATETIME) ),
				AOPR_TYPE => $opTypeName,
				AOPR_IP => $logRecord->get_attribute(AOPR_IP) );

				if ( $opType == aop_modify )
				$recordData['ROW_URL'] = prepareURLStr( PAGE_DB_LOGDATA, array( 'DB_KEY'=>$DB_KEY,
				'index'=>$index, SORTING_COL=>$sorting) );

				$logData[] = $recordData;
			}

			$logRecords = array_reverse($logData);
		}
	}
}

//clear frontend settings on every modify db_settings
if($btnIndex!=0)wbs_resetFrontendSettingsCache();

switch ( $btnIndex )
{

	case 0 :
		{

			$hostData[HOST_ADMINISTRATOR]['PASSWORD2'] = $hostData[HOST_ADMINISTRATOR]['PASSWORD1'];
			$hostData[HOST_FIRSTLOGIN]['PASSWORD2'] = $hostData[HOST_FIRSTLOGIN]['PASSWORD1'];

			$dbSettingsData = prepareArrayToStore( $hostData[HOST_DBSETTINGS] );
			$accountData = prepareArrayToStore( $hostData[HOST_FIRSTLOGIN] );
			$adminData = prepareArrayToStore( $hostData[HOST_ADMINISTRATOR] );

			$dbCreateData = prepareArrayToStore(($action == ACTION_NEW) ?  $hostData[HOST_DB_CREATE_OPTIONS]:$hostData[HOST_DBSETTINGS]);
			if(!($action == ACTION_NEW)){
				$dbCreateData[HOST_DB_CREATE_OPTION] = isset($dbCreateData[HOST_CREATE_OPTION])?$dbCreateData[HOST_CREATE_OPTION]:$dbCreateData[HOST_DB_CREATE_OPTION];
				$dbCreateData[HOST_DATABASE_USER_EXISTING] = isset($dbCreateData[HOST_DATABASE_USER_EXISTING])?$dbCreateData[HOST_DATABASE_USER_EXISTING]:$dbCreateData[HOST_DBUSER];
				$dbCreateData[HOST_PASSWORD_EXISTING] = isset($dbCreateData[HOST_PASSWORD_EXISTING])?$dbCreateData[HOST_PASSWORD_EXISTING]:$dbCreateData[HOST_DBPASSWORD];
				$dbCreateData[HOST_DATABASE_EXISTING] = isset($dbCreateData[HOST_DATABASE_EXISTING])?$dbCreateData[HOST_DATABASE_EXISTING]:$dbCreateData[HOST_DBNAME];
			}
			$invalidArr = 0;

			if ( !isset($dbSettingsData[HOST_READONLY]) )
			$dbSettingsData[HOST_READONLY] = 0;

			$appList = array();
			foreach( $appData as $APP_ID => $APP_DATA )
			if ( $APP_DATA[APP_CHECKED] )
			$appList[] = $APP_ID;

			if ( $action == ACTION_NEW && !strlen( $dbSettingsData[HOST_DB_KEY] ) ) {
				if ( isset($hostData[HOST_DB_CREATE_OPTIONS][HOST_CREATE_OPTION]) ) {
					if ( $hostData[HOST_DB_CREATE_OPTIONS][HOST_CREATE_OPTION] == "new" )
					$newDBKey = substr( $dbCreateData[HOST_DATABASE_NEW], 0, DB_MAXDBKEYLEN );
					else
					$newDBKey = substr( $dbCreateData[HOST_DATABASE_EXISTING], 0, DB_MAXDBKEYLEN );
				} else
				$newDBKey = null;

				$dbSettingsData[HOST_DB_KEY] = $newDBKey;
				$hostData[HOST_DBSETTINGS][HOST_DB_KEY] = $newDBKey;
			}

			if ( $smsModule == "" && ( isset( $smsCheckbox ) && $smsCheckbox == 1 ) )
			{
				$errorStr = "To enable SMS you must select Gateway";
				$invalidField = "smsModule";
				break;
			}

			if ( $action == ACTION_NEW )
			{
				$hostData[HOST_BALANCE][MODULE_CLASS_SMS][HOST_VALUE] = "UNLIMITED";
				$hostData[HOST_MODULES][MODULE_CLASS_SMS]["ID"] = $smsModule;
				$hostData[HOST_MODULES][MODULE_CLASS_SMS]["DISABLED"] = ( isset( $smsCheckbox ) && $smsCheckbox == 1 ) ? 0 : 1;

				$dbSettingsData[HOST_MODULES] = $hostData[HOST_MODULES];
				$dbSettingsData[HOST_BALANCE] = $hostData[HOST_BALANCE];
			}
			else
			{
				$hostData[HOST_MODULES][MODULE_CLASS_SMS]["ID"] = $smsModule;
				$hostData[HOST_MODULES][MODULE_CLASS_SMS]["DISABLED"] = ( isset( $smsCheckbox ) && $smsCheckbox == 1 ) ? 0 : 1;

				$dbSettingsData[HOST_MODULES] = $hostData[HOST_MODULES];
			}

			$smsEnabled = ( isset( $smsCheckbox ) && $smsCheckbox == 1 ) ? 1 : 0;

			$res = addModDBProfile( $action, $DB_KEY, $dbSettingsData, $accountData, $adminData, $dbCreateData, $kernelStrings, $invalidArr, $appList );

			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();

				if ( strlen($res->getUserInfo()) ) {
					$invalidField = $res->getUserInfo();

					if ( $invalidArr == 0 )
					$invalidField = sprintf( "hostData[DBSETTINGS][%s]", $invalidField );
					elseif ( $invalidArr == 1 )
					$invalidField = sprintf( "hostData[FIRSTLOGIN][%s]", $invalidField );
					elseif ( $invalidArr == 2 )
					$invalidField = sprintf( "hostData[ADMINISTRATOR][%s]", $invalidField );
					elseif ( $invalidArr == 3 )
					$invalidField = sprintf( "hostData[DBSETTINGS][%s]", $invalidField );
					//$invalidField = sprintf( "hostData[DB_CREATE_OPTIONS][%s]", $invalidField );
				}

				break;
			}

			if ( $action == ACTION_NEW ) {
				$profileCreated = true;
				$DB_KEY = $res;
			}
			else
			redirectBrowser( PAGE_DB_DBLIST, array(SORTING_COL=>$sorting,'msg'=>base64_encode($LocalizationStrings['wbs_settings_update_success'])), $DB_KEY );

			break;
		}
	case 2 : {
		$res = deleteDbProfile( $DB_KEY, $kernelStrings );
		if ( PEAR::isError( $res ) ) {
			$errorStr = $res->getMessage();

			break;
		}
		$commondata = wbs_getFrontendSettings();
		if($commondata['CURRENT_DBKEY']==$DB_KEY){
			$commondata['CURRENT_DBKEY']='';
			$commondata['CURRENT_SERVICE_ID']='login';
			$res = wbs_saveFrontendSettings($commondata,$kernelStrings,null);
			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();

				break;
			}
		}

		redirectBrowser( PAGE_DB_DBLIST, array(SORTING_COL=>$sorting,'msg'=>base64_encode($LocalizationStrings['wbs_settings_update_success'])), $DB_KEY );
		break;
	}
	case 3 : {
		$res = restoreDbProfile( $DB_KEY, $kernelStrings );
		if ( PEAR::isError( $res ) ) {
			$errorStr = $res->getMessage();

			break;
		}
		$commondata = wbs_getFrontendSettings();
		if($commondata['CURRENT_DBKEY']==''){
			$commondata['CURRENT_DBKEY']=$DB_KEY;
			$commondata['CURRENT_SERVICE_ID']='login';
			$res = wbs_saveFrontendSettings($commondata,$kernelStrings,null);
			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();

				break;
			}
		}

		redirectBrowser( PAGE_DB_DBLIST, array(SORTING_COL=>$sorting,'msg'=>base64_encode($LocalizationStrings['wbs_settings_update_success'])), $DB_KEY );
		break;
	}
	case 4 : {
		$res = removeDbProfile( $DB_KEY, $kernelStrings );

		if ( PEAR::isError( $res ) ) {
			$errorStr = $res->getMessage();

			break;
		}
		$commondata = wbs_getFrontendSettings();
		if($commondata['CURRENT_DBKEY']==$DB_KEY){
			$commondata['CURRENT_DBKEY']='';
			$commondata['CURRENT_SERVICE_ID']='login';
			$res = wbs_saveFrontendSettings($commondata,$kernelStrings,null);
			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();

				break;
			}
		}

		redirectBrowser( PAGE_DB_DBLIST, array(SORTING_COL=>$sorting,'msg'=>base64_encode($LocalizationStrings['wbs_settings_update_success'])) );
		break;
	}
	case 5 : {
		$res = removeDbProfile( $DB_KEY, $kernelStrings, true );

		if ( PEAR::isError( $res ) ) {
			$errorStr = $res->getMessage();

			break;
		}

		redirectBrowser( PAGE_DB_DBLIST, array(SORTING_COL=>$sorting,'msg'=>base64_encode($LocalizationStrings['wbs_settings_update_success'])) );
		break;
	}
}

$pageTitle = ($action == ACTION_NEW) ? $LocalizationStrings[4] : sprintf('%s %s',$LocalizationStrings[5],$DB_KEY);

$preproc->assign( PAGE_TITLE, sprintf('%s &mdash; %s',wbs_multiDbkeyEnabled()?$LocalizationStrings['dbl_page_names']:$LocalizationStrings['dbl_page_name'],$pageTitle));
$preproc->assign( 'pageHeader', $pageTitle  );
$preproc->assign( FORM_LINK, PAGE_DB_DBPROFILE );
$preproc->assign( ERROR_STR, $errorStr );
$preproc->assign( FATAL_ERROR, $fatalError );
$preproc->assign( SORTING_COL, $sorting );
$preproc->assign( INVALID_FIELD, $invalidField );
$preproc->assign( ACTION, $action );
$preproc->assign( "buttonCaption", ($action == ACTION_NEW) ?  $LocalizationStrings['btn_add_db'] : $LocalizationStrings['btn_save']);
$preproc->assign( "noServerFound", $noServerFound );

if ( $noServerFound ) {
	if ( $action == ACTION_EDIT ) {
		$preproc->assign( "noServerMessage", $noServerMessage );
		$preproc->assign( "recoverMessage", $recoverMessage );
	}
}

if ( $action == ACTION_EDIT || $profileCreated )
$preproc->assign( "DB_KEY", $DB_KEY );

if ( !$fatalError )
{
	$preproc->assign( "smsModules", $smsModules );
	$preproc->assign( "smsCount", $smsCount );
	$preproc->assign( "smsDefaultModule", $smsDefaultModule );
	$preproc->assign( "smsEnabled", $smsEnabled );

	$preproc->assign( "hostData", $hostData );

	$preproc->assign( "language_names", $language_names );
	$preproc->assign( "language_ids", $language_ids );
	$preproc->assign( "language_names_indexed", $language_names_indexed );

	$preproc->assign( "dateFormat_ids", $dateFormat_ids );
	$preproc->assign( "dateFormat_names", $dateFormat_names );

	$preproc->assign( "app_data", $appData );
	$preproc->assign( "profileCreated", $profileCreated );

	$preproc->assign( "serverNames", $serverNames );

	$preproc->assign( "logRecords", $logRecords );

	if ( $profileCreated )
	$preproc->assign( "loginURL", sprintf( "login.php?DB_KEY=%s", $DB_KEY ) );

	if ( $action == ACTION_NEW )
	$preproc->assign( "prevServerName", $prevServerName );

	if ( isset($hasAdminRights) )
	$preproc->assign( "hasAdminRights", $hasAdminRights );

	$preproc->assign( "firstLogin", isset( $hostData[HOST_DBSETTINGS][HOST_FIRSTLOGIN] ) ? $hostData[HOST_DBSETTINGS][HOST_FIRSTLOGIN] : 1 );

}

//$preproc->assign( 'pdfAdminFile', GUIDE_FILE );
//$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );

$preproc->assign ( 'waStrings', $LocalizationStrings);
$preproc->assign('multiDBKEY',wbs_multiDbkeyEnabled());
$preproc->assign( "mainTemplate","dbprofile.htm" );
?>