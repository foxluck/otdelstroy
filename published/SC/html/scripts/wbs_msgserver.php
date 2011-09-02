<?php
//
// HTML client initialization script
//

define('PATH_THISSCRIPT',
str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi'
||php_sapi_name()=='cgi-fcgi' ||php_sapi_name()=='cgix')&&(isset($_SERVER['ORIG_PATH_TRANSLATED']) ?
$_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED'])?
(isset($_SERVER['ORIG_PATH_TRANSLATED']) ? $_SERVER['ORIG_PATH_TRANSLATED'] :
$_SERVER['PATH_TRANSLATED']) : (isset($_SERVER['ORIG_SCRIPT_FILENAME']) ? $_SERVER['ORIG_SCRIPT_FILENAME'] :
$_SERVER['SCRIPT_FILENAME']))));

$_SERVER['PATH_TRANSLATED'] = PATH_THISSCRIPT;

if ( !ini_get('magic_quotes_gpc') )
$_SERVER['PATH_TRANSLATED'] = addSlashes( $_SERVER['PATH_TRANSLATED'] );

$scriptPath = dirname( stripSlashes($_SERVER['PATH_TRANSLATED']) );
$WBSPath = $scriptPath."/../../../../";

define( "WBS_DIR", str_replace('\\','/',realpath($WBSPath)."/") );
require_once( WBS_DIR."kernel/wbsinit.php" );

if ( !loadWBSSettings() )
die( "Unable to load WBS settings" );
if (file_exists(WBS_DIR."kernel/classes/class.accountname.php")) {
	// Dbkey aliases mechanism
	require_once( WBS_DIR."kernel/classes/class.accountname.php");
	function getDomainName() {
		if(isset($_GET['__account_name']))return $_GET['__account_name'];
		if (preg_match('/(.*?)\.([a-z0-9\.\-]+)/ui', $_SERVER['HTTP_HOST'], $matches))
		$res=$matches[1];
		else
		$res='';
		return $res;
	}

	function getDBkey($aliasName) {
		if(file_exists(WBS_DIR."/kernel/hosting_plans.php")){
			$AccountName=new AccountName($aliasName);
			$res=strtoupper(trim($AccountName->dbkey));
			if ($res == '') $res = strtoupper($aliasName);
		}elseif(file_exists(WBS_DIR."/kernel/wbs.xml")){
			$xml= simplexml_load_file(WBS_DIR."/kernel/wbs.xml");
			$res=(string)$xml->FRONTEND['dbkey'];
		}else{
			die("not exists ".WBS_DIR."/kernel/wbs.xml");
		}
		return $res;
	}

	if(!isset($_GET['DB_KEY'])||!$_GET['DB_KEY'])$_GET['DB_KEY'] = getDBKey(isset($aliasName)?$aliasName:getDomainName());
	// ---
}else{
	function getDBkey() {
		if(file_exists(WBS_DIR."/kernel/wbs.xml")){
			$xml= simplexml_load_file(WBS_DIR."/kernel/wbs.xml");
			$res=(string)$xml->FRONTEND['dbkey'];
		}else{
			die("not exists ".WBS_DIR."/kernel/wbs.xml");
		}
		return $res;
	}

	if(!isset($_GET['DB_KEY'])||!$_GET['DB_KEY'])$_GET['DB_KEY'] = getDBKey();
}


if ( isset( $_GET["DB_KEY"] ) )
$DB_KEY = strtoupper($_GET["DB_KEY"]);

$DB_NAME = "DB".strtoupper($DB_KEY);

if ( isset($DB_KEY) )
loadDatabaseLanguageList($DB_KEY);

define ("NOT_CACHE_MODULES", true);
require_once( WBS_DIR."kernel/kernel.php" );

require_once( WBS_DIR."/published/common/html/includes/httpcommon.php" );
//Commented out - req empty file
//require_once( WBS_DIR."/published/SC/sc_dbfunctions_cmn.php" );
require_once( WBS_DIR."/published/SC/sc.php" );
ClassManager::includeClass('httpmessageserver', 'SC');
if(isset($_GET['debug'])){print "<pre>".htmlentities(var_export(ApplicationlimitExceeded('SC','eng',HOST_SC_FIRST_REST),false))."</pre>";exit;}
$msgServer = &new HttpMessageServer();

//
$session_name =$msgServer->getRequest('session.name');
//SESSION WORKAROUND BEGIN
	$session_name = ini_get('session.name');
	$session_dir = ini_get('session.save_path');
	if(preg_match('/^(tcp.+):([\d]+)$/',$session_dir,$matches)){
		$matches[2] = 11212;
		$session_dir = "{$matches[1]}:{$matches[2]}";
		ini_set('session.name',$session_name.'_SC_m');
		ini_set('session.save_path',$session_dir);
		ini_set('session.gc_maxlifetime',(1440*1));
	}else{
		$session_dir .= '/SC_session';
		if(!is_dir($session_dir)){
			@mkdir($session_dir);
		}
		if(is_dir($session_dir)){
			ini_set('session.name',$session_name.'_SC');
			ini_set('session.save_path',$session_dir);
			ini_set('session.gc_maxlifetime',(1440*1));
		}
	}

		
switch ($msgServer->getRequest('action')){
	case 'SEND_SMS':
	session_id($msgServer->getRequest('session_id'));
	session_start();


	do{
		$SMSsworking = false;

		if(file_exists($WBSPath.'/kernel/sms.php')){

			require_once($WBSPath.'/kernel/sms.php');
			$SMSsworking = true;
		}

		if(!isset($databaseInfo[HOST_APPLICATIONS]['SC'])){

			$msgServer->putData('send_result', 0);
			$msgServer->putData('error_message', 'SC not installed');
			break;
		}
		if(!$SMSsworking){

			$msgServer->putData('send_result', 0);
			$msgServer->putData('error_message', 'No sms module');
			break;
		}

		$verify_key = md5($msgServer->getRequest('auth_part').':'.$databaseInfo[HOST_DBSETTINGS][HOST_DBUSER].':'.$databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD]);

		if($verify_key != $msgServer->getRequest('auth_key')){
			$msgServer->putData('send_result', 0);
			$msgServer->putData('error_message', 'Wrong auth key');
			break;
		}

		$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, AA_APP_ID );
		$appStrings = loadLocalizationStrings( $localizationPath, strtolower(AA_APP_ID) );
		$kernelStrings = $appStrings['eng'];

		$sms_data = sc_getSessionData('_SMS_DATA');
		$totalRecipients = 0;

		foreach ($sms_data['TO'] as $_phone){
			/**
 * TODO: translit
 */
			$totalRecipients++;
			if ( !is_null(MAX_SMS_RECIPIENT_NUM) && $totalRecipients > MAX_SMS_RECIPIENT_NUM ){
				$res = PEAR::raiseError('Too much recipients (max '.MAX_SMS_RECIPIENT_NUM.')');
				break;
			}

			$res = sendSMS('ROBOT', $_phone, $sms_data['MESSAGE'], 'SC', $kernelStrings, isset($sms_data['FROM'])&&$sms_data['FROM']?$sms_data['FROM']:'SC');
			if ( PEAR::isError( $res ) )break;
		}
		if ( PEAR::isError( $res ) ){
			$msgServer->putData('send_result', 0);
			$msgServer->putData('error_message', $res->getMessage().($res->getUserInfo()?' ('.$res->getUserInfo().')':''));
			break;
		}
		$msgServer->putData('send_result', 1);
		break;
	}while (0);
	$msgServer->putData('success', 'true');
	session_write_close();
	break;

	break;
	case 'INIT_DB_CONNECT_DATA':{
		$msgServer->putData('success', 'false');
		session_write_close();
		break;

		session_id($msgServer->getRequest('session_id'));
		session_start();

		do{
			if(!isset($databaseInfo[HOST_APPLICATIONS]['SC'])){

				sc_setSessionData('SC_INSTALLED', false);
				break;
			}

			sc_setSessionData('SC_INSTALLED', true);
			sc_setSessionData('DB_HOST', $wbs_sqlServers[$databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER]]['HOST'].(isset($wbs_sqlServers[$databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER]]['PORT'])&&$wbs_sqlServers[$databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER]]['PORT']?':'.$wbs_sqlServers[$databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER]]['PORT']:''));
			sc_setSessionData('DB_USER', $databaseInfo[HOST_DBSETTINGS][HOST_DBUSER]);
			sc_setSessionData('DB_PASS', $databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD]);
			sc_setSessionData('DB_NAME', $databaseInfo[HOST_DBSETTINGS][HOST_DBNAME]);
			sc_setSessionData('DB_KEY', $DB_KEY);
			sc_setSessionData('DIR_PUBLICDATA_SC', WBS_DIR.'/published/publicdata/'.$DB_KEY.'/attachments/SC');
			sc_setSessionData('DIR_DATA_SC', WBS_DIR.'/data/'.$DB_KEY.'/attachments/SC');

		}while (0);
		$msgServer->putData('success', 'true');
		session_write_close();
		break;
	}
	case 'ALLOW_ADD_FILE':{
		$QuotaManager = new DiskQuotaManager();
		$av_space = $QuotaManager->GetAvailableSystemSpace($kernelStrings);
		$msgServer->putData('result', $av_space!=''?$av_space>$msgServer->getRequest('file_size'):true);
		break;
	}
	case 'ADD_DISKUSAGE_RECORD':{
		$QuotaManager = new DiskQuotaManager();

		$QuotaManager->AddDiskUsageRecord('', 'SC', $msgServer->getRequest('file_size'));
		$QuotaManager->Flush( $kernelStrings );
		break;
	}
	case 'REMOVE_DISKUSAGE_RECORD':{
		$QuotaManager = new DiskQuotaManager();

		$QuotaManager->AddDiskUsageRecord('', 'SC', -1*$msgServer->getRequest('file_size'));
		$QuotaManager->Flush( $kernelStrings );
		break;
	}
	case 'ALLOW_ADD_PRODUCT':{
		$language=$msgServer->getRequest('language');
		if(file_exists(WBS_DIR."/kernel/hosting_plans.php")){
			$res=ApplicationlimitExceeded('SC',$language,HOST_SC_FIRST_REST);
			$msgServer->putData('max',$res['info'][HOST_SC_FIRST_REST]['max']);
			$msgServer->putData('current',$res['info'][HOST_SC_FIRST_REST]['current']);
			$msgServer->putData('success',$res['succes']);
			$msgServer->putData('debug_val',$res);
			$msgServer->putData('msg',$res['msg']);
		}else{
			$msgServer->putData('success',true);
		}
		break;
	}
	case 'ALLOW_VIEW_ORDER_DETAILS':{
		$language=$msgServer->getRequest('language');
		if(file_exists(WBS_DIR."/kernel/hosting_plans.php")){
			$res=ApplicationlimitExceeded('SC',$language,HOST_SC_SECOND_REST);
			$msgServer->putData('success',$res['succes']);
			$msgServer->putData('max',$res['info'][HOST_SC_SECOND_REST]['max']);
			$msgServer->putData('current',$res['info'][HOST_SC_SECOND_REST]['current']);
			$msgServer->putData('debug_val',$res);
			$msgServer->putData('msg',$res['msg']);
			$msgServer->putData('msg_type',$res['msg_type']);
		}else{
			$msgServer->putData('success',true);
		}
		break;
	}
}
$msgServer->end();
?>