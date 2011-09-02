<?php
header ("Cache-Control: no-cache, must-revalidate");
header('Content-Type: text/html; charset=UTF-8;');
header ("Pragma: no-cache");
header("Connection: close");  

@ini_set( 'memory_limit', '64M' );
@ini_set('max_execution_time', 3600 );

$errorStr = null;
$invalidField = null;
$fatalError = false;
$logCache = null;

define( 'TIME_START',microtime(true));
if(!defined('WBS_DIR')){
	define('WBS_DIR',realpath(dirname(__FILE__)));
}
define( 'DISTRIBUTIVE_FILENAME', WBS_DIR.'/distr/wbs.tgz' );
define( 'DISTRIBUTIVE_FILENAME_ALT', WBS_DIR.'/wbs.tgz' );

///
// AJAX handler
/////////
if ( version_compare(PHP_VERSION,'5','>=') ) {
	require_once( "domxml-php4-to-php5.php" );
	define( "PHP5", true );
} else
define( "PHP5", false );
require_once( "includes/PEAR.php" );
if(PHP5){
	require_once('includes/restartableTar.php');
}


if(isset($_GET['source'])&&($_GET['source']=='ajax')){
	
	
	//require_once('includes/restartableTar.php');
	
	//require_once('includes/restartableTar.php');
	$action = isset($_GET['action'])?$_GET['action']:null;
	switch($action){
		case 'extract':
			$chmod = isset($_GET['chmod'])?$_GET['chmod']:null;
			$distributive = file_exists(DISTRIBUTIVE_FILENAME)?DISTRIBUTIVE_FILENAME:DISTRIBUTIVE_FILENAME_ALT;
			ajaxTar::extract($distributive,WBS_DIR,$chmod);//.'/install_path2/'
			break;
		case 'getstate':
		default:
			ajaxTar::getState();
			break;
	}
	exit;
}

define( 'SYS_VERSION_FILE', 'kernel/wbs.xml' );
define( 'UPDATE_VERSION_FILE', 'update.xml' );
define( 'SYS_SETTINGS_FILE', 'kernel/wbs.xml' );
define( 'COMPLETE_FLAG', 'kernel/complete' );
define( 'HTACCESSREPLACED_FLAG', 'kernel/htareplaced' );
define( 'NEWADMIN_FLAG', 'published/wbsadmin/html/scripts/step3.php' );
define( 'UPDATE_SETTINGS_FILE', 'settings.xml' );
//define( 'GUIDE_FILE', 'help/webasystinstallguide.htm' );

define( 'ACTION_NOACTION', -1 );
define( 'ACTION_INSTALL', 0 );
define( 'ACTION_REPAIR', 1 );
define( 'ACTION_UPDATE', 2 );
define( 'ACTION_NOVERSION', 3 );

define( 'ERRCODE_NOVERINFO', 1 );

define( 'MAX_DBLIST_NUM', 10 );



define( 'SYSINFO_SUCCESS', '<img src="./i2/success.gif" alt="Ok" title="Ok">' );
define( 'SYSINFO_FAILED', '<img src="./i2/failed.gif" alt="Not ok" title="Not ok">' );


//	require( "MTTar.php" );
//	require( "includes/Tar.php" );
require( "includes/localization.php" );

$locString=getLanguage();

$displayLog = array();

$buttonNames = array(
ACTION_INSTALL=>$locString['inst'],
ACTION_REPAIR=>$locString['updt'],
ACTION_UPDATE=>$locString['upgrd'],
ACTION_NOVERSION=>$locString['err_openverfile']);


// Check required PHP extensions
//

error_reporting (E_ERROR | E_WARNING | E_PARSE);
extract( $_POST );

$currentDir = "";


//
// Function defenitions
//

function getButtonIndex( $values, $vars )
//
// Get the index of button pressed on the form
//
//	      Parameters:
//		      $values - button names (associative array)
//		      $vars - HTTP_POST_VARS or HTTP_GET_VARS (associative array)
//
//	      Returns the index of the button pressed or -1 if buttons was not pressed.
//
{
	$result = -1;

	if ( is_array( $vars ) ) {

		while ( list( $key, $val ) = each ( $vars ) )
		for ( $i = 0; $i < count( $values ); $i++ ) {
			$pos = strpos ( $key, $values[$i] );

			if ( strlen($pos) && ($pos >= 0) ) {
				$result = $i;
				break 2;
			}
		}
	}

	return $result;
}

function mk_dir( $dir )
{
	if ( !file_exists( $dir ) ){
		return @mkdir( $dir, 0755);
			
	}else{
		return (is_dir($dir));
	}
}
function cleanCacheLocalization()
{
	$dirname = realpath('./published');
	if(file_exists($dirname)&&is_dir($dirname)&&($dir = opendir($dirname))){
		while ($name = readdir($dir)){
			if($name == '.'|| $name == '..')continue;

			$cacheFile = $dirname.'/'.$name.'/localization/.cache.php';
			if (file_exists($cacheFile)){
				@unlink($cacheFile);
			}
		}
		closedir($dir);
	}
}
function cleanCacheSmarty()
{
	cleanDirectory('./kernel/includes/smarty/compiled');
}
function cleanDirectory($dirname)
{
	$dirname = realpath($dirname);
	if(file_exists($dirname)&&is_dir($dirname)&&($dir = opendir($dirname))){
		while ($name = readdir($dir)){
			if($name == '.'|| $name == '..')continue;

			$path = $dirname.'/'.$name;
			if(is_dir($path)){
				cleanDirectory($path);
			}elseif (file_exists($path)){
				@unlink($path);
			}
		}
		closedir($dir);
	}
}

function openUpdateLog()
{
	global $logHandle;

	$logHandle = @fopen( 'update.log', 'a' );

	return $logHandle;
}

function closeUpdateLog()
{
	global $logHandle;

	fclose( $logHandle );
}

function writeUpdateLog( $str = null )
{
	global $logHandle;
	global $logCache;

	$str .= "\n";
	$logCache .= $str;
	if(!$logHandle){
		openUpdateLog();
	}
	if($logHandle)
	fwrite( $logHandle, $str );
}

function addDisplayLog( $str = null )
{
	global $displayLog;

	$displayLog[] = $str;
}

function fileContent( $filePath )
{
	$content = @file( $filePath );
	$content = @implode( '', $content );
	if(@get_magic_quotes_gpc()){
		$content = stripslashes($content);
	}
	return $content;
}

function showStepProgress($step)
{
	/*if($step === 0)return '';
	if($step==(-1))$step=5;*/
	/*global $locString;
	$names=array($locString['step1'],$locString['step2'],$locString['step3'],$locString['step4'],$locString['step5'],);
	$step_string='';
	foreach ($names as $stepNum=>$stepName){
		if($stepNum<$step){
			$step_string.=(strlen($step_string)?'&nbsp;&raquo;&nbsp;':'').'<span style="color:black">'.$stepName.'</span>';
		}elseif ($stepNum==$step){
			$step_string.=(strlen($step_string)?'&nbsp;&raquo;&nbsp;':'').'<span style="font-size:130%;font-weight:bolder;color:black">'.$stepName.'</span>';
		}else{
			$step_string.=(strlen($step_string)?'&nbsp;&raquo;&nbsp;':'').'<span style="color:grey">'.$stepName.'</span>';
		}
	}
	return $step_string;*/
	global $locString;
	$step_=array(0,0,1,2,2,3,3,4);
	if(isset($step_[$step])){
		$step=$step_[$step];
	}else{
		$step=-1;
	}
	$names=array($locString['step1'],$locString['step2'],$locString['step3'],$locString['step4'],$locString['step5'],);

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

function getSystemVersion( $filePath, $updateVersion )
{
	global $locString;
	$content = fileContent( $filePath );

	$dom = @domxml_open_mem( $content );
	if ( !$dom )
	return PEAR::raiseError( $locString['err_openverfile']);

	$xpath = @xpath_new_context($dom);
	$nodePath = ($updateVersion) ? '/METADATAUPDATE' : '/WBS';
	if ( !( $versionnode = &xpath_eval($xpath, $nodePath) ) )
	return PEAR::raiseError($locString['err_parse_ver_file']);

	if ( !count($versionnode->nodeset) )
	return PEAR::raiseError($locString['inv_ver_file'] );

	$versionnode = $versionnode->nodeset[0];
	$versionValue = $versionnode->get_attribute( 'VERSION' );

	if ( !strlen($versionValue) && !$updateVersion )
	return PEAR::raiseError( $locString['ver_inf_not_found'], ERRCODE_NOVERINFO );

	return $versionValue;
}

function setSystemVersion( $filePath, $version )
{
	global $locString;
	$content = fileContent( $filePath );

	$dom = @domxml_open_mem( $content );
	if ( !$dom )
	return PEAR::raiseError($locString['err_openverfile']);

	$xpath = @xpath_new_context($dom);
	if ( !( $versionnode = &xpath_eval($xpath, '/WBS') ) )
	return PEAR::raiseError( $locString['err_parse_ver_file']);

	if ( !count($versionnode->nodeset) )
	return PEAR::raiseError($locString['inv_ver_file']);

	$versionnode = $versionnode->nodeset[0];

	$versionnode->set_attribute( 'VERSION', $version );
	$versionnode->set_attribute( 'UPDATEDATE', date( 'Y-m-d' ) );

	$filePath = realpath( $filePath );
	return $dom->dump_file( $filePath, false, true );
}

function getActionType( &$sysVersion, &$updateVersion )
{
	$complete = true;

	if ( file_exists(NEWADMIN_FLAG) )
	$complete = file_exists( COMPLETE_FLAG );

	if ( !file_exists( SYS_SETTINGS_FILE ) || !$complete )
	return ACTION_INSTALL;

	$noSysVersion = false;

	$sysVersion = getSystemVersion( SYS_VERSION_FILE, false );
	if ( PEAR::isError($sysVersion) )
	if ( $sysVersion->getCode() != ERRCODE_NOVERINFO )
	return $sysVersion;
	else
	$noSysVersion = true;

	$updateVersion = getSystemVersion( UPDATE_VERSION_FILE, true );
	if ( PEAR::isError($updateVersion) )
	return $updateVersion;

	if ( $noSysVersion ) {
		$sysVersion = $updateVersion;
		return ACTION_NOVERSION;
	}

	if ( $sysVersion == $updateVersion )
	return ACTION_REPAIR;

	if ( $sysVersion < $updateVersion )
	return ACTION_UPDATE;

	if ( $sysVersion > $updateVersion )
	return ACTION_NOACTION;
}

function getExtractMode()
{
	global $locString;
	//$_mode = isset($_POST['mode'])?$_POST['mode']:null;
	$time = ini_get('max_execution_time');
	if($time<3600){
		if(extension_loaded('curl')){
			$mode = 1;
		}else{
			$mode = 2;
		}
	}else{
		$mode = 0;
	}
	return $mode;
	/*$res = '';
		if($mode == 0) $res = $locString['extracting_auto'];
		if($mode == 1) $res = str_replace('%X%',$time,$locString['extracting_partial']);
		if($mode == 2) $res = str_replace('%X%',$time,$locString['extracting_manual']);
		*/
	/*$res = array();
		if($mode == 0){
		$res[0] = $locString['extracting_auto'];
		}elseif($mode == 1){
		$res[1] = str_replace('%X%',$time,$locString['extracting_partial']);
		}
		$res[2] = str_replace('%X%',$time,$locString['extracting_manual']);*/
	//return $res;
}
function getAllowedExtractModes()
{
	global $locString;
	$res = array();
	$time = '';
	//$time = ini_get('max_execution_time');
	//if($time >= 3600){
	$res[0]['type'] = $locString['extracting_auto_name'];
	$res[0]['info'] = $locString['extracting_auto'];
	$res[0]['description'] = str_replace('%X%',$time,$locString['extracting_auto_description']);
	$res[0]['info'] .='<br><br><div class="progressBar" id="progressBar" style="display:none;"><span><em
	id="progressBarStripe" style="left:0px"></em></span></div>
<span class="progressValue" id="progressValue" style="display:none;">0%</span>
<script type="text/javascript" language="JavaScript">
<!--
ProgressManager.initialize(\'progressBar\', \'progressBarStripe\', \'progressValue\');
//-->
</script>
';
	//}
	/*if(extension_loaded('curl')){
	 $res[1]['type'] = $locString['extracting_partial_name'];
	 $res[1]['info'] = str_replace('%X%',$time,isset($res[0])?$locString['extracting_partial_optional']:$locString['extracting_partial']);
	 $res[1]['description'] = str_replace('%X%',$time,isset($res[0])?$locString['extracting_partial_description']:$locString['extracting_partial_required_description']);
		}*/
	$res[2]['type'] = $locString['extracting_manual_name'];
	$res[2]['info'] = str_replace('%X%',$time,$locString['extracting_manual']);
	$res[2]['description'] = str_replace('%X%',$time,(isset($res[0])||isset($res[1]))?$locString['extracting_manual_description']:$locString['extracting_no_auto']);
	return $res;
}

function installScripts( $writeLog = true )
{
	global $locString;
	global $dbAdminURL;
	global $loginURL;

	// Unpack distributive
	//
	/*
		$archivePath = file_exists(DISTRIBUTIVE_FILENAME)?DISTRIBUTIVE_FILENAME:DISTRIBUTIVE_FILENAME_ALT;
		$targetPath = pathinfo(realpath(__FILE__));
		$targetPath = $targetPath['dirname'];
		*/
	//already extracted, just test extract result;

	/*		$tar = new MTTar( realpath($archivePath), true );

	$tar->setErrorHandling( PEAR_ERROR_PRINT );
	ob_start();
	$chmod_enabled = isset($_POST['chmod_enabled'])&&is_array($_POST['chmod_enabled'])?$_POST['chmod_enabled']:array();
	$chmod = isset($_POST['chmod'])&&is_array($_POST['chmod'])?$_POST['chmod']:array();
	*/
	$mode = isset($_POST['mode'])?$_POST['mode']:null;
	
	/*		if(isset($chmod_enabled[$mode])&&$chmod_enabled[$mode]&&isset($chmod[$mode])){
	 $chmod = sscanf($chmod[$mode],'%3o');
	 $chmod = isset($chmod[0])?$chmod[0]:null;
		}else{
		$chmod = null;
		}
		*/
	if($mode != 2){
		$tar_out = '';
		$result = restartableTar::isExtractSucces($tar_out);
		/*
		$result = restartableTar::getState();
		$tar_out = $result['msg'];
		$result = ($result['state']==restartableTar::STATE_COMPLETE)?true:false;
		restartableTar::setState(restartableTar::STATE_NONE,'install complete');
*/

	}else{
		$result = true;//check real extraction
	}
	/*
		$result = $tar->extract( $targetPath,$mode,$chmod);

		$tar_out = ob_get_clean();
		*/
	cleanCacheLocalization();
	cleanCacheSmarty();


	if ( !$result )
	return PEAR::raiseError( "<p><b>{$locString['unable_to_extract']}</b></p><p> $tar_out </p>" );
		
	$dirs_list = array();
	if($mode == 2){
		$dirs_list = array('kernel','kernel/includes','kernel/includes/smarty','published','published/publicdata');
	}else{
		//$dirs_list = array('data','dblist','temp','kernel','kernel/includes','kernel/includes/smarty','kernel/includes/smarty/compiled','published','published/publicdata');
	}
	foreach($dirs_list as $dir){
		if(!mk_dir($dir)){
			return PEAR::raiseError($locString['unable_to_create_directory'].' '.$dir);
		}
	}

	$configPath = "kernel/wbs.xml";
	if ( !@file_exists($configPath) )
	copy( UPDATE_SETTINGS_FILE, $configPath );

	$htaccessPath = "published/.htaccess";
	if ( !@file_exists($htaccessPath) )
	copy( "access", $htaccessPath );
	else {
		// Replace .htaccess file
		//
		if ( !@file_exists(HTACCESSREPLACED_FLAG) ) {
			$oldHtaccessPath = "published/bak.htaccess";
			rename( $htaccessPath, $oldHtaccessPath );
			copy( "access", $htaccessPath );

			$complete = fopen( HTACCESSREPLACED_FLAG, "w+" );
			fputs( $complete, "DO NOT REMOVE THIS FILE!" );
			fclose( $complete );
		}
	}

	// Prepare wbs.xml file
	//
	$scriptName = $_SERVER['SCRIPT_NAME'];
	$scriptPath = substr( $scriptName, 0, strlen($scriptName)-strlen(basename($scriptName)) );
	$serverName = $_SERVER['SERVER_NAME'];

	// Create extra directories & public data directory
	//
	/*	mk_dir( "data" );
	mk_dir( "dblist" );
	mk_dir( "temp" );
	mk_dir( "kernel/includes/smarty/compiled" );*/

	$dirs_list = array('data',
	'dblist','temp','kernel/includes/smarty/compiled','published/publicdata');
	foreach($dirs_list as $dir){
		if(!mk_dir($dir))
		return PEAR::raiseError($locString['unable_to_create_directory'].' '.$dir);
	}
	/*mk_dir('kernel');
	 mk_dir('kernel/includes');
	 mk_dir('kernel/includes/smarty');
	 mk_dir('published');*/

		
	// Protect data, dblist and temp directories
	//
	$dirs = array('temp','data','dblist');
	foreach ($dirs as $dir){
		$file = $dir.'/.htaccess';
		if(!file_exists($file)){
			$fp = fopen($file,'w');
			if($fp){
				fwrite($fp,'Deny from all');
				fclose($fp);
			}
		}
	}

	//Remove distributive and some other files
	//
	$files = array();//DISTRIBUTIVE_FILENAME);
	foreach ($files as $file){
		if(file_exists($file)){
			@unlink($file);
		}
	}

	// Write log
	//
	if ( $writeLog ) {
		$logFilePath = 'install.log';
		$fp = @fopen( $logFilePath, "wt" );
		if ( !$fp )
		return PEAR::raiseError( sprintf($locString['unable_to_open'].": %s", basename($logFilePath) ) );

		fwrite( $fp, $locString['wb_log'] );
		fwrite( $fp, "\n\n" );
		fwrite( $fp, sprintf($locString['inst_date']." %s", date("M'd Y H:i:s") )."\n" );
		fwrite( $fp, sprintf($locString['wba_url'].": %s", $dbAdminURL )."\n" );
		fwrite( $fp, sprintf($locString['login_url'].": %s", $loginURL ) );

		@fclose( $fp );
	}

	return null;
}

function sortUpdateList( $a, $b )
{
	$aIsKernel = substr( $a, 0, 6 ) == "Kernel";
	$bIsKernel = substr( $b, 0, 6 ) == "Kernel";

	if ( $aIsKernel && !$bIsKernel ) return -1;
	if ( !$aIsKernel && $bIsKernel ) return 1;
	if ( $aIsKernel && $bIsKernel ) return 0;

	return strcmp( $a, $b );
}

function parseUpdateFile( $sysVersion )
{
	global $locString;
	$updateList = array();

	$filePath = "update.xml";
	if ( !file_exists( $filePath ) )
	return PEAR::raiseError( $locString['file_not_found']);

	$content = fileContent( $filePath );

	$dom = @domxml_open_mem( $content );
	if ( !$dom )
	return PEAR::raiseError($locString['inv_ver_file']);

	$xpath = @xpath_new_context($dom);
	$query = sprintf( '/METADATAUPDATE/UPDATE[number(@VERSION) > number(%s)]/APPUPDATE', $sysVersion );
	if ( !( $updates = &xpath_eval($xpath, $query) ) )
	return $updateList;

	if ( !is_array( $updates->nodeset ) )
	return $updateList;

	foreach( $updates->nodeset as $update ) {
		$APP_ID = $update->get_attribute( 'APP_ID' );
		$content = $update->get_attribute( 'CONTENT' );

		$updateList[$APP_ID][] = $content;
	}

	uksort( $updateList, 'sortUpdateList' );

	return $updateList;
}

function getElementByTagname( &$dom, $tagName )
{
	$elements = $dom->get_elements_by_tagname($tagName);

	if ( !count($elements) )
	return null;

	return $elements[0];
}

function getAttributeValues( &$node )
{
	$attrs = $node->attributes();

	$result = array();

	if ( !is_array( $attrs ) )
	return $result;

	for ( $i = 0; $i < count($attrs); $i++ ) {
		$attr = $attrs[$i];

		$result[$attr->name] = $attr->value;
	}

	return $result;
}

function getHostData( $host_key, &$hostInfo )
{
	global $locString;
	$hostInfo = array();

	$filePath = sprintf( "dblist/%s.xml", strtoupper($host_key) );

	$content = fileContent( $filePath );
	$dom = @domxml_open_mem( $content );
	if ( !$dom )
	return PEAR::raiseError($locString['err_op_db_profile']);

	$element = @getElementByTagname( $dom, 'DBSETTINGS' );
	if ( is_null($element) )
	return PEAR::raiseError( $locString['err_read_db_prof']);

	$hostInfo['DBSETTINGS'] = getAttributeValues( $element );

	$element = @getElementByTagname( $dom, 'FIRSTLOGIN' );
	if ( is_null($element) )
	return PEAR::raiseError($locString['err_read_db_prof']);

	$hostInfo['FIRSTLOGIN'] = getAttributeValues( $element );
	$hostInfo['DB_KEY'] = $host_key;

	$applications = @getElementByTagname( $dom, 'APPLICATIONS' );
	if ( is_null($applications) )
	return PEAR::raiseError( $locString['err_read_db_prof']);

	$appList = array();
	$applications = $applications->get_elements_by_tagname('APPLICATION');
	foreach( $applications as $application ) {
		$app_id = $application->get_attribute( 'APP_ID' );
		$appList[$app_id] = array( 'APP_ID'=>$app_id );
	}

	$hostInfo['APPLICATIONS'] = $appList;
	
	return null;
}

function listRegisteredSystems()
{
	global $locString;
	$result = array();

	$targetDir = "dblist";
	$fileExt = "xml";

	if ( !($handle = @opendir($targetDir)) )
	return PEAR::raiseError( $locString['err_op_dblist']);

	while ( false !== ($name = readdir($handle)) ) {
		if ( $name != "." && $name != ".." && $name != "" ) {
			$filename = $targetDir.'/'.$name;

			if ( is_dir($filename) )
			continue;

			$path_parts = pathinfo($filename);
			if ( $path_parts["extension"] != $fileExt )
			continue;

			$db_key = substr( $name, 0, strlen($name)-strlen($fileExt)-1 );

			$hostInfo = null;
			$res = getHostData( $db_key, $hostInfo );
			if ( !PEAR::isError($res) ) {
				$hostInfo['FILENAME'] = realpath($filename);
				$result[$db_key] = $hostInfo;
			}
		}
	}

	closedir( $handle );

	return $result;
}

function listSystemServers()
{
	global $locString;
	$filePath = "kernel/wbs.xml";

	$content = fileContent( $filePath );

	$dom = @domxml_open_mem( $content );
	if ( !$dom )
	return PEAR::raiseError($locString['err_op_sys_set'] );

	$xpath = xpath_new_context($dom);

	$result = array();

	if ( !( $sqlservers = xpath_eval($xpath, '/WBS/SQLSERVERS/SQLSERVER') ) )
	return $result;

	foreach( $sqlservers->nodeset as $sqlserver ) {
		$serverParams = getAttributeValues($sqlserver);
		$serverName = $serverParams['NAME'];

		$result[$serverName] = $serverParams;
	}

	return $result;
}

function listTargetDatabases($sysVersion, $updateVersion )
{
	$result = array();

	$updateList = parseUpdateFile( $sysVersion );
	if ( PEAR::isError($updateList) )
	return $updateList;

	if ( !count($updateList) )
	return $result;
	else
	$updateApplications = array_keys( $updateList );

	$accounts = listRegisteredSystems();
	if ( PEAR::isError($accounts) ) {
		writeUpdateLog( $accounts->getMessage() );
		return $accounts;
	}

	foreach ( $accounts as $account_key=>$account_data ) {
		$createDate = $account_data['DBSETTINGS']['CREATE_DATE'];

		$account_applications = array_keys( $account_data['APPLICATIONS'] );
		$account_applications = array_merge( array('Kernel'), $account_applications );

		$account_updateApplications = array_intersect( $updateApplications, $account_applications );

		if ( count( $account_updateApplications ) )
		$result[] = $account_key;
	}

	sort( $result );

	return $result;
}

function getDBConnectionParameters( $account_key, $account_data, $wbs_sqlServers )
{
	// Find out database connection type
	//
	$dbNewType = false;
	if ( array_key_exists( 'DB_CREATE_OPTION', $account_data['DBSETTINGS'] ) )
	$dbNewType = true;

	$useExisting = false;
	if ( !$dbNewType )
	$useExisting = strlen( $account_data['DBSETTINGS']['DB_USER'] );
	else
	$useExisting = $account_data['DBSETTINGS']['DB_CREATE_OPTION'] == 'use';

	// Load connection parameters
	//
	$accountDBName = $account_data['DBSETTINGS']['DB_NAME'];

	if ( !strlen($accountDBName) )
	$accountDBName = 'DB'.$account_key;

	$server = $account_data['DBSETTINGS']['SQLSERVER'];

	if ( !array_key_exists( $server, $wbs_sqlServers ) )
	return PEAR::raiseError(sprintf($locString['no_server_found'], $account_key) );

	$serverData = $wbs_sqlServers[$server];

	$serverHost = $serverData['HOST'];
	if ( !strlen($serverHost) )
	$serverHost = 'localhost';

	if ( strlen($serverData['PORT']) )
	$serverHost = sprintf( "%s:%s", $serverHost, $serverData['PORT'] );

	$result = array();
	$result['HOST'] = $serverHost;

	if ( !$useExisting ) {
		$result['ADMIN_USERNAME'] = $serverData['ADMIN_USERNAME'];
		$result['ADMIN_PASSWORD'] = $serverData['ADMIN_PASSWORD'];
	} else {
		$result['ADMIN_USERNAME'] = $account_data['DBSETTINGS']['DB_USER'];
		$result['ADMIN_PASSWORD'] = $account_data['DBSETTINGS']['DB_PASSWORD'];
	}
	
	$result['DBNAME'] = $accountDBName;

	return $result;
}

function updateSystem( $sysVersion, $updateVersion )
{
	global $locString;
	$res = openUpdateLog();
	if ( !$res )
	return PEAR::raiseError( $locString['err_create_log']);

	writeUpdateLog( sprintf($locString['upd_start'], date("M'd Y H:i:s") ) );
	writeUpdateLog( sprintf($locString['old_vers'], $sysVersion ) );
	writeUpdateLog( sprintf($locString['new_vers'], $updateVersion ) );
	writeUpdateLog();
	writeUpdateLog( $locString['upd_progress'] );

	addDisplayLog( sprintf( $locString['start'], date("M'd Y H:i:s") ) );
	addDisplayLog();

	// Install scripts
	//
	$res = installScripts();
	if ( PEAR::isError( $res ) ) {
		addDisplayLog($locString['upd_err']);
		addDisplayLog();
		writeUpdateLog( $res->getMessage() );
		return $res;
	}
	addDisplayLog($locString['upd_success']);
	addDisplayLog();

	writeUpdateLog( $locString['complete']);

	writeUpdateLog();
	writeUpdateLog($locString['upd_db_structure']);

	// Update metadata
	//
	$sysVersion = getSystemVersion( SYS_VERSION_FILE, false );
	if ( PEAR::isError($sysVersion) )
	return $sysVersion;


	$updateList = parseUpdateFile( $sysVersion );
	if ( PEAR::isError($updateList) )
	return $updateList;

	$mdUpdateTriggered = false;

	if ( !count($updateList) ) {
		writeUpdateLog($locString['no_upd_meta_req']);
	} else {
		$updateApplications = array_keys( $updateList );

		$wbs_sqlServers = listSystemServers();
		if ( PEAR::isError($wbs_sqlServers) ) {
			writeUpdateLog( $wbs_sqlServers->getMessage() );
			return $accounts;
		}

		$accounts = listRegisteredSystems();
		if ( PEAR::isError($accounts) ) {
			writeUpdateLog( $accounts->getMessage() );
			return $accounts;
		}

		foreach ( $accounts as $account_key=>$account_data ) {
			$dbExists = true;


			if ( !isset($account_data['DBSETTINGS']['CREATE_DATE']) || !strlen($account_data['DBSETTINGS']['CREATE_DATE']) ){
				$dbExists = false;
				writeUpdateLog('Invalid create date ['.__FILE__.':'.__LINE__.']');
			}

			$profileFileName = $account_data['FILENAME'];

			$createDate = $account_data['DBSETTINGS']['CREATE_DATE'];

			$account_applications = array_keys( $account_data['APPLICATIONS'] );
			$account_applications = array_merge( array('Kernel'), $account_applications );
			$account_updateApplications = array_intersect( $updateApplications, $account_applications );

			if ( !count( $account_updateApplications ) )
			continue;

			$dbConnectionData = getDBConnectionParameters( $account_key, $account_data, $wbs_sqlServers );
			if ( PEAR::isError($dbConnectionData) ){
				/*@var $dbConnectionData PEAR_Error*/
				$dbExists = false;
				writeUpdateLog('Invalid connect date '.$dbConnectionData->message);
			}

			if ( !$mdUpdateTriggered ) {
				addDisplayLog($locString['upd_db_structure_det']);
				addDisplayLog();
			}
			$mdUpdateTriggered = true;

			
			writeUpdateLog( sprintf( $locString['update_db'], $account_key ) );
			writeUpdateLog(var_export($dbExists,true));
			if ( $dbExists ) {
				$dbh = @mysql_connect( $dbConnectionData['HOST'], $dbConnectionData['ADMIN_USERNAME'], $dbConnectionData['ADMIN_PASSWORD'] );
				if ( !$dbh ) {
					writeUpdateLog(  __LINE__.':'.sprintf( $locString['err_connect_to_mysql'], $dbConnectionData['HOST'] ) );
					addDisplayLog( "$account_key: <font color=red>Error</font>" );
					continue;
				}

				$res = @mysql_select_db( $dbConnectionData['DBNAME'] );
				if ( !$res ) {
					writeUpdateLog( __LINE__.':'.sprintf($locString['err_select_db'], $accountDBName ) );
					addDisplayLog( "$account_key: <font color=red>{$locString['err']}</font>" );
					@mysql_close($dbh);
					continue;
				}
			

				$DB_NAME = $dbConnectionData['DBNAME'];
				$error_count = 0;
	
				foreach( $account_updateApplications as $accout_app ) {
					writeUpdateLog( sprintf($locString['upd_app'], $accout_app ) );
	
					foreach( $updateList[$accout_app] as $updateContent ) {
						$updateContent = base64_decode( $updateContent );
						
						ob_start();
						eval( $updateContent );
						$scriptResult = ob_get_clean();
	
						$lang_cache = '/data/'.$account_key.'/attachments/SC/temp/loc_cache/';
						if(file_exists($lang_cache)&&is_dir($lang_cache)){
							cleanDirectory($lang_cache);
						}
	
						$updateRes = $scriptResult;
						//if  ( !strlen($updateRes) )
						//$updateRes = @mysql_error();
	
						if ( strlen($updateRes) ) {
							$error_count++;
							writeUpdateLog( sprintf( $locString['err'].": %s", $updateRes ) );
							writeUpdateLog( sprintf( "Executed: %s", $updateContent ) );
							//continue 3;
						}
					}
				}
				@mysql_close($dbh);
				if($error_count){
					addDisplayLog( "$account_key: <font color=red>{$locString['err']}</font>" );	
				}else{
					addDisplayLog( "$account_key: <font color=green>{$locString['success']}</font>" );
					writeUpdateLog( $locString['complete'] );
				}
			}else{
				addDisplayLog( "$account_key: <font color=yellow>{$locString['skipped']}</font>" );
			}
			

			
		}
	}

	if ( $mdUpdateTriggered )
	addDisplayLog();

	addDisplayLog( "<b>{$locString['complete']}</b>: ".date("M'd Y H:i:s") );

	writeUpdateLog();
	writeUpdateLog( $locString['upd_complete'].": ".date("M'd Y H:i:s") );

	$complete = @fopen( COMPLETE_FLAG, "w+" );
	@fputs( $complete, "DO NOT REMOVE THIS FILE!" );
	@fclose( $complete );
}



function stepform( $step )
{
	global $locString;

	$scriptName = $_SERVER['SCRIPT_NAME'];
	$scriptPath = substr( $scriptName, 0, strlen($scriptName)-strlen(basename($scriptName)) );
	$serverName = $_SERVER['SERVER_NAME'];

	$dbAdminURL = sprintf( "http://%s%sinstaller", $serverName, $scriptPath );
	$loginURL = sprintf( "http://%s%slogin/", $serverName, $scriptPath );

	print '
<FORM method="post" action="./install.php">
<input type="submit" style="display:none">
<p>
<input type="hidden" value="1" name="nojs" id="nojs">
<input type="hidden" value="'.$step.'" name="step" id="step">
<input name="edited" type="hidden" id="edited" value="1">
<input type=hidden name=loginURL value="'.$loginURL.'">
<input type=hidden name=dbAdminURL value="'.$dbAdminURL.'">
<script type="text/javascript" language="JavaScript">makeHiddenURL( \'login/\', \'loginURL\' )</script>
<script type="text/javascript" language="JavaScript">makeHiddenURL( \'installer/\', \'dbAdminURL\' )</script>';

	if ( false && $step == 4 ) {
		print '<INPUT TYPE=submit name="finishbtn" VALUE="'.$locString['finish'].'"> &nbsp;';
	}

	$mode = getExtractMode();
	$modeDescriptions = getAllowedExtractModes();
	$count_modes = count($modeDescriptions);
	$modeSelect = '<div id="mode_select" style="margin-left: 20px;">';
	$chmodString = "<p style=\"padding-top: 10px; font-style: italic;\">
<label for=\"chmod_enabled_%1\$d_\">
{$locString['force_chmod']}
</label>
<input type=\"checkbox\" id=\"chmod_enabled_%1\$d_\" name=\"chmod_enabled[%1\$d]\">
<input type=\"text\" id=\"chmod_%1\$d_\" name=\"chmod[%1\$d]\" value=\"775\" size=\"4\"><br>
{$locString['force_chmod_description']}
</p>";

	if($count_modes>1){
		if(isset($modeDescriptions[0])){
			$mode_description = $modeDescriptions[0];
			$mode_description = $mode_description['type'];
			$checked = ($mode == 0)?' checked="checked"':'';
			$modeSelect .= "<br>\n<label><input type=\"radio\" name=\"mode\" value=\"0\"{$checked}> &nbsp;".
			$modeDescriptions[0]['type']."</label>\n<div class=\"comment install-option\">{$modeDescriptions[0]['description']}".
			sprintf($chmodString,0)."</div>\n";
			if(isset($modeDescriptions[1])){
				$modeSelect .= '<div class="install-option" id="optional_link">';
				$modeSelect .= sprintf($locString['extracting_more'],'href="#extract_alt" onClick="var obj=document.getElementById(\'extract_alt\');'
						.'if(obj)obj.style.display=\'block\';obj=document.getElementById(\'optional_link\');'
						.'if(obj)obj.style.display=\'none\';return false;"');
				$modeSelect .='</div>';
			}
		}
		if(isset($modeDescriptions[1])){
			$mode_description = $modeDescriptions[1];
			$mode_description = $mode_description['type'];
			$checked = ($mode == 1)?' checked':'';
			$_modeSelect = "<br><label><input type=\"radio\" name=\"mode\" value=\"1\"{$checked}> &nbsp;".
			$modeDescriptions[1]['type'].
			"</label><div class=\"comment install-option\">".
			sprintf($chmodString,1)."{$modeDescriptions[1]['description']}</div>";
			if(isset($modeDescriptions[0])){
				$modeSelect .= '<div id="extract_alt" style="display:none;"><a name="extract_alt"></a>'.$_modeSelect.'</div>';
			}else{
				$modeSelect .= $_modeSelect;
			}
		}
		if(isset($modeDescriptions[2])){
			$mode_description = $modeDescriptions[2];
			$mode_description = $mode_description['type'];
			$checked = ($mode == 2)?' checked':'';
			$modeSelect .= "<br><label><input type=\"radio\" name=\"mode\" value=\"2\"{$checked}> &nbsp;".
			"{$modeDescriptions[2]['type']}</label><div class=\"comment  install-option\">{$modeDescriptions[2]['description']}</div>";
		}


	}else{
		$modeSelect .= '<input type="hidden" name="mode" value="'.$mode.'">';
		$modeSelect .= "<p>{$modeDescriptions[2]['type']}</p>";
		$modeSelect .= "<div class=\"comment\" style=\"padding-left:10px;\">{$modeDescriptions[2]['description']}</div>";
	}
	$modeSelect .= '</div>';

	if($step==3){
		print $modeSelect;
}?>
<br>
<INPUT
	id="btn_continue" TYPE=submit name="savebtn"
	VALUE="<?php echo $locString['continue']; ?>"
	<?php print 'onclick="return onContinue(this,\''.$step.'\')"';?>>

	<?php
	if($step==3){
		if(isset($modeDescriptions[0])){
			print '
<div id="img_continue_0" class="continue-block">
	<div style="padding-top: 7px;">
';
			echo $modeDescriptions[0]['info'];
			print '
	</div>
</div>
';
		}
		//////////
		if(isset($modeDescriptions[1])){
			print '
<div id="img_continue_1" class="continue-block">
<div style="padding-top: 7px;">';
			echo '<iframe src="" id="progress_window" name="progress_window" width="400px" height="40px" frameborder
="0" scrolling="no"></IFRAME>';
			echo $modeDescriptions[1]['info'];
			print '</div></div>z2';
		}
		///////////
		if(isset($modeDescriptions[2])){
			print '
<div id="img_continue_2" style="display:none;height:auto;padding-top:0px;">';
			echo $modeDescriptions[2]['info'].'<INPUT id="btn_continue2" TYPE=submit name="savebtn" VALUE="'.$locString['continue'].'">';
			print '</div>';
		}
	}else{
		print '
<div id="img_continue" class="continue-block">
<p style="vertical-align:middle;">&nbsp;...</p>
</div>';
	}
	?>

</FORM>
	<?php
}

function check_javascript(  )
{
	if ( isset($_POST["nojs"])&&$_POST["nojs"] == 0 )
	{
		echo SYSINFO_SUCCESS;
		return 1;
	}

	echo SYSINFO_FAILED;
	return 0;
}

function check_writable(  )
{
	if ( is_writable(".") )
	{
		echo SYSINFO_SUCCESS;
		return 1;
	}

	echo SYSINFO_FAILED;
	return 0;
}

function check_extension( $name )
{
	if ( extension_loaded( $name ) )
	{
		echo SYSINFO_SUCCESS;
		return 1;
	}

	echo SYSINFO_FAILED;
	return 0;
}

function check_phpversion()
{
	//$ver = phpversion();

	//if ( ereg ("([0-9]+).([0-9]+).([0-9]+)", $ver, $regs)) {
	if ( version_compare(PHP_VERSION,'5.0.5','>='))
	{
		echo SYSINFO_SUCCESS;
		return 1;
	}
	//}

	echo SYSINFO_FAILED;
	return 0;
}

function check_safemode()
{
	$res = (ini_get('safe_mode')==1)?0:1;
	echo $res?SYSINFO_SUCCESS:SYSINFO_FAILED;
	return $res;
}


function step3()
{
	global $displayLog;
	global $locString;
	global $loginURL;
	global $dbAdminURL;

	switch( true ) {
		case true : {
			$sysVersion = null;
			$updateVersion = null;
			$actionType = getActionType( $sysVersion, $updateVersion );
			if ( PEAR::isError($actionType) ) {
				$errorStr = $actionType->getMessage();
				$fatalError = true;

				break;
			}

			$updateVersion = getSystemVersion( UPDATE_VERSION_FILE, true );
			if ( PEAR::isError($updateVersion) ) {
				$errorStr = $updateVersion->getMessage();
				$fatalError = true;

				break;
			}

			switch ( $actionType ) {
				case ACTION_REPAIR : ;
				case ACTION_NOVERSION:
				case ACTION_INSTALL : $res = installScripts(); break;
				case ACTION_UPDATE :  $res = updateSystem( $sysVersion, $updateVersion ); break;
			}

			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();
				$fatalError = true;

				break;
			}
			cleanCacheLocalization();
			cleanCacheSmarty();

			$res = setSystemVersion( SYS_VERSION_FILE, $updateVersion );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();
				$fatalError = true;

				break;
			}
		}
	}
	?>


	<?php if ( !$fatalError ) { ?>
	<?php if ( $actionType == ACTION_INSTALL ) { ?>
<p><?php echo $locString['extract_info']; ?>
<script type="text/javascript"	language="JavaScript" type="text/javascript">
<!--
setTimeout("window.location.href='published/wbsadmin/html/scripts/firststep.php';",500);
//-->
</script></p>
	<?php } elseif ( $actionType == ACTION_REPAIR ) { ?>
<H1><?php $_POST["step"] = 6; echo $locString['inst_suc_comp']; ?></H1>
<br>
<p><?php echo $locString['wba_url_lbl']; ?></p>
<p><a href="<?php echo $dbAdminURL; ?>" name="adminLink">
<script	language="JavaScript" type="text/javascript">makeLinkURL( 'installer/', 'adminLink' )</script>
<noscript><?php echo $dbAdminURL; ?></noscript>
</a></p>
<br>
<p><?php echo $locString['db_url_lbl']; ?></p>
<p><a href="<?php echo $loginURL; ?>" name="loginLink"><script language="JavaScript" type="text/javascript">makeLinkURL( 'login/', 'loginLink' )</script>
<noscript><?php echo $loginURL; ?></noscript>
</a></p>
	<?php } elseif ( $actionType == ACTION_UPDATE || $actionType == ACTION_NOVERSION ) { ?>
<h1><?php $_POST["step"] = 6; echo $locString['upd_complete']; ?></h1>
<br>
<p><?php echo $locString['wa_upgr_ver']; ?><b><?php echo $updateVersion ?></b></p>
<hr size="1">
<p>
<blockquote><?php echo implode( "<br>", $displayLog ) ?></blockquote>
<a href="update.log" target="_blank"><?php echo $locString['open_log']; ?></a>
</p>
<hr size="1">
<br>
<p><?php echo $locString['wba_url_lbl']; ?><br>
<a href="<?php echo $dbAdminURL; ?>" name="adminLink">
<script type="text/javascript" language="JavaScript">makeLinkURL( 'installer/', 'adminLink' )</script>
<noscript><?php echo $dbAdminURL; ?></noscript>
</a><br>

	<?php echo $locString['db_url_lbl']; ?><br>
<a href="<?php echo $loginURL; ?>" name="loginLink">
<script type="text/javascript" language="JavaScript">makeLinkURL( 'login/', 'loginLink' )</script>
<noscript><?php echo $loginURL; ?></noscript>
</a></p>


	<?php
}
?>


<?php if ( $actionType == ACTION_INSTALL && !$fatalError ) stepform( 4 ); }

if ( $fatalError ) {
	?>
<p><font color="red"><b><?php echo $locString['err']; ?>:</b></font></p>
<?php echo $errorStr;?>
<p><font color="red"><b><?php echo $locString['inst_aborted']; ?></b></font></p>

<?php } ?> <?php

}

function step2()
{
	global $locString;
	$flag = 1;

	$sysVersion = null;
	$updateVersion = null;
	$actionType = getActionType( $sysVersion, $updateVersion );
	if ( PEAR::isError($actionType) ) {
		$errorStr = $actionType->getMessage();
		$fatalError = true;
	}

	if ( $actionType == ACTION_UPDATE ) {
		$targetDbList = listTargetDatabases( $sysVersion, $updateVersion );
		if ( PEAR::isError($targetDbList) ) {
			$errorStr = $targetDbList->getMessage();
			$fatalError = true;
		}
		if(is_array($targetDbList)){
			$targetDBCount = count($targetDbList);
			$DBlistIsTruncated = $targetDBCount > MAX_DBLIST_NUM;
			$truncatedDBList = array_slice( $targetDbList, 0, MAX_DBLIST_NUM );
		}else{
			$targetDBCount = 0;
			$DBlistIsTruncated = array();
			$truncatedDBList = array();
		}

		if ( $DBlistIsTruncated )
		$fullDbList = sprintf( 'showdblist.php?list=%s', base64_encode( serialize($targetDbList) ) );
	}

	?>
<h1><?php echo $locString['step3_title']; ?></h1>
<br>
<div id="main_comment">
<p><?php
if ( $actionType == ACTION_INSTALL ){
	echo $locString['no_prev_ver'].'<br><br>'.$locString['extract_desc'];
}elseif ( $actionType == ACTION_REPAIR ) {
	echo $locString['note_repair'];
}elseif ( $actionType == ACTION_NOVERSION ) {
	echo $locString['note_no_vers'];
}elseif ( $actionType == ACTION_UPDATE ) { ?> <input type=hidden
	name=oldSysVersion value="<?php echo $sysVersion; ?>"><?php echo $locString['note_inst'];
	echo "<p>{$locString['curr_wa_ver']}<b>{$sysVersion}</b></p>";
	echo "<p>{$locString['install_wa_ver']}<b>{$updateVersion}</b></p>";
	if ($targetDBCount) {
		echo '<hr size="1">';
		echo "<p>{$locString['upd_db_list_label']}</p><p><ul>";
		foreach( $truncatedDBList as $DB_NAME ) {
			echo "<li><b>{$DB_NAME}</b></li>";
		}
		if ( $DBlistIsTruncated )
		echo "<li>...</li>";
		?>


</ul>
</p>
		<?php if ($DBlistIsTruncated) { ?>
<p><a href="<?php echo $fullDbList ?>" target="_blank"><?php echo $locString['upd_db_full_list_link']; ?>.</a></p>
		<?php }?>
<p><a href="showmucontent.php" target="_blank"><?php echo sprintf($locString['upd_meta_info'],''); ?></a></p>
<hr size="1">
		<?php echo "<p><b>{$locString['backup_notice']}</b></p>
		      <p>{$locString['upgrade_db_btn_desc']}</p>";
} else {
	echo "<p>{$locString['no_db_meta']}</p>";
} } elseif ( $actionType == ACTION_NOACTION ) {
	echo "
		    <p>{$locString['upd_not_actual']
		    }</p>
		    <p>{$locString['curr_wa_ver']} <b>{$sysVersion}</b></p>
		    <p>{$locString['inst_ver']}<b>{$updateVersion}</b></p>
		    <p><b>{$locString['inst_canceled']}</b></p>
		   ";
} ?><br>
</div>

<?php if ( $actionType != ACTION_NOACTION ) {    stepform( 3 ); }    ?>

<?php
}

function step1()
{
	$flag = 1;
	global $locString;
	?>
<h1><?php echo $locString['sys_req_list']; ?></h1>
<br>

<div style="background-color: #eee; padding: 20px; margin-bottom: 20px;">
<p><?php

	$sysInfo=array();

	$sysInfo['SERVER'] = $_SERVER['SERVER_SOFTWARE'];
	
	$sysInfo['PHP']='PHP '.phpversion().' <a href="install.php?info=1" target="_blank">phpinfo</a> <img src="./i2/new_window_icon.gif" alt="">';
	
	if(function_exists('mysql_get_client_info')){
		$sysInfo['MySQL']='MySQL client version — '.mysql_get_client_info();
	}
	$sysInfo['info']=implode('<br> ',$sysInfo);
	print $sysInfo['info'];
	$req_counter = 0;
	?></p>
</div>

<table border="0" cellpadding="3" cellspacing="0" width="75%">
	<tr>
		<td height="21" width="30">
		<p><b><?php print ++$req_counter;?>.</b></p>
		</td>
		<td>
		<p><?php echo $locString['php_version']; ?></p>
		</td>
		<td>
		<p><?php if ( !check_phpversion() ) $flag=0; ?></p>
		</td>
	</tr>
	
	<tr>
		<td height="21" width="30">
		<p><b><?php print ++$req_counter;?>.</b></p>
		</td>
		<td>
		<p><?php echo $locString['php_safemode']; ?></p>
		</td>
		<td>
		<p><?php if ( !check_safemode() ) $flag=0; ?></p>
		</td>
	</tr>

	<tr>
		<td>
		<p><b><?php print ++$req_counter;?>.</b></p>
		</td>
		<td>
		<p><?php echo $locString['php_ext']; ?></p>
		</td>
		<td>&nbsp;</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td>
		<p><?php echo $locString['php_ext_mysql']; ?></p>
		</td>
		<td>
		<p><?php if ( !check_extension('mysql')) $flag=0; ?></p>
		</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td>
		<p><?php echo $locString['php_ext_mb_string']; ?></p>
		</td>
		<td>
		<p><?php if ( !check_extension("mbstring") ) $flag=0; ?></p>
		</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td>
		<p><?php echo $locString['php_ext_simplexml']; ?></p>
		</td>
		<td>
		<p><?php if ( !check_extension("simplexml") ) $flag=0; ?></p>
		</td>
	</tr>

	<?php if ( !PHP5 ) { ?>
	<tr>
		<td>&nbsp;</td>
		<td>
		<p><?php echo $locString['php_ext_domxml']; ?></p>
		</td>
		<td>
		<p><?php if ( !check_extension("domxml") ) $flag=0; ?></p>
		</td>
	</tr>
	<?php }else{ ?>
	<tr>
		<td>&nbsp;</td>
		<td>
		<p><?php echo $locString['php_ext_domxml']; ?></p>
		</td>
		<td>
		<p><?php if ( !check_extension("dom") ) $flag=0; ?></p>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td>&nbsp;</td>
		<td>
		<p><?php echo $locString['php_ext_gd']; ?></p>
		</td>
		<td>
		<p><?php $gd = check_extension("gd");?></p>
		</td>
	</tr>

	<?php if ( !$gd ) { ?>
	<tr>
		<td>&nbsp;</td>
		<td><FONT color=red><?php echo $locString['php_ext_warning']; ?></font><?php echo $locString['php_ext_gd_warning']; ?></td>
		<td></td>
	</tr>
	<?php } ?>
	<tr>
		<td>&nbsp;</td>
		<td>
		<p><?php echo $locString['php_ext_zlib']; ?></p>
		</td>
		<td>
		<p><?php $zlib = check_extension("zlib");	?></p>
		</td>
	</tr>
	<?php if ( !$zlib ) { ?>
	<tr>
		<td></td>
		<td class="comment"><FONT color=red><?php echo $locString['php_ext_warning']; ?></font>
		<?php echo $locString['php_ext_zlib_warning']; ?></td>
		<td>&nbsp;</td>
	</tr>
	<?php } ?>
	<tr>
		<td>&nbsp;</td>
		<td>
		<p><?php echo $locString['php_ext_gettext']; ?></p>
		</td>
		<td>
		<p><?php $gettext = check_extension("gettext");	?></p>
		</td>
	</tr>
	<?php if ( !$gettext ) { ?>
	<tr>
		<td></td>
		<td class="comment"><FONT color=red><?php echo $locString['php_ext_warning']; ?></font>
		<?php echo $locString['php_ext_gettext_warning']; ?></td>
		<td>&nbsp;</td>
	</tr>
	<?php } ?>
	<tr>
		<td>
		<p><b><?php print ++$req_counter;?>.</b></p>
		</td>
		<td>
		<p><?php echo $locString['java_enabled']; ?></p>
		</td>
		<td>
		<p><?php if ( !check_javascript() ) $flag=0; ?></p>
		</td>
	</tr>

	<tr>
		<td valign=top>
		<p><b><?php print ++$req_counter;?>.</b></p>
		</td>
		<td>
		<p><?php echo $locString['write_rights']; ?><br>
		<small><b><?php echo $locString['note']; ?></b> <?php echo $locString['write_rights_note']; ?></small></p>
		</td>
		<td valign=top>
		<p><?php if ( !check_writable() ) $flag=0; ?></p>
		</td>
	</tr>



	<tr>
		<td colspan=3>&nbsp;</td>
	</tr>
	<?php
	if ( $flag == 1 )
	{

		echo "<tr><td colspan=\"3\"><p><font color=green><b>{$locString['sys_req_sat']}</b></font></p></td></tr></table>";
		stepform( 2 );
		return;
	}


	echo "<tr><td colspan=\"3\">";
	echo "<p><B><FONT COLOR=RED>{$locString['sys_req_not_sat']}<BR><BR> {$locString['inst_aborted_cap']}</FONT></b></p>";
	echo "</td></tr></table>";


}



function hello($restart=false)
{
	global $locString;
	global $lang;


	echo "<h1>{$locString['license_label']}</h1><br>"; ?>

	<div class="license-agreement"><?php
	if(file_exists("./{$lang}.license.txt")){
		include("./{$lang}.license.txt");
	}elseif(file_exists("./license.txt")){
		include("./license.txt");
	}else{
		print "License file is missing";
	}
	?></div>
	<br>

	<FORM method=post action="install.php">

	<P style="font-weight: bold;<?php if($restart)print "color:red;";?>">
		<input type="checkbox" name="license" value="0" id="i-accept"> <label for="i-accept"><?php echo $locString['license_accept']; ?></label></p>


	<?php
	$scriptName = $_SERVER['SCRIPT_NAME'];
	$scriptPath = substr( $scriptName, 0, strlen($scriptName)-strlen(basename($scriptName)) );
	$serverName = $_SERVER['SERVER_NAME'];

	$dbAdminURL = sprintf( "http://%s%sinstaller/", $serverName, $scriptPath );
	$loginURL = sprintf( "http://%s%slogin/", $serverName, $scriptPath );
	?> <br>
	<input type="hidden" value="1" name="nojs" id="nojs"> <input
		type="hidden" value="1" name="step" id="step"> <input name="edited"
		type="hidden" id="edited" value="1"> <input type=hidden name=loginURL
		value="<?php echo $loginURL; ?>"> <input type=hidden name=dbAdminURL
		value="<?php echo $dbAdminURL; ?>"> <script language="JavaScript" type="text/javascript">makeHiddenURL( 'login/', 'loginURL' )</script>
	<script language="JavaScript" type="text/javascript">makeHiddenURL( 'installer/', 'dbAdminURL' )</script>

	<p><INPUT TYPE=submit name="savebtn"
		VALUE="<?php echo $locString['continue']; ?>"></p>

	</FORM>
	<?php

}

function finished()
{
	global $locString;
	?>
	<BR>
	<table border="0" cellpadding="0" cellspacing="10">
		<tr>
			<td>
			<p><?php echo $locString['inst_suc_comp']; ?></p>
			
			
			<p><?php echo $locString['press_button_to_login_wa']; ?></p>
			<br>
			
			
			<FORM method=post action="install.php">
				<input type="hidden" value="1"	name="nojs" id="nojs">
				<input type="hidden" value="-3" name="step"	id="step">
				<input name="edited" type="hidden" id="edited" value="1">
				<INPUT TYPE=submit name="savebtn" VALUE="Login">
			</FORM>
			</td>
		</tr>
	</table>


	<?php
}

function finished2()
{
	global $locString;
	?>
	<br>
	<table border="0" cellpadding="0" cellspacing="10">
		<tr>
			<td>
			<p><?php echo $locString['inst_suc_comp']; ?></p>
			<p><?php echo $locString['press_button_to_login_wa']; ?></p>
			<br>
			
			
			<FORM method=post action="install.php">
				<input type="hidden" value="1"	name="nojs" id="nojs">
				<input type="hidden" value="-4" name="step"	id="step">
				<input name="edited" type="hidden" id="edited" value="1">
				<INPUT TYPE=submit name="savebtn" VALUE="Administrator">
			</FORM>
			</td>
		</tr>
	</table>


	<?php
}

function cancel()
{
	hello(true);
	print '<script type="text/javascript" type="text/javascript">
<!--
var ItemCheckBox = document.getElementById("i-accept");
if(ItemCheckBox){
ItemCheckBox.focus();
}
//-->
</script>';
	return;
	global $locString;
	?>


	<p><?php echo $locString['install_canceled']; ?></p>
	<br>

	<FORM method=post action="install.php">



	<p><INPUT TYPE=submit name="savebtn"
		VALUE="<?php echo $locString['resume']; ?>"></p>

	</FORM>


	<?php
}


if ( isset($_GET['info'])&&$_GET["info"] == 1 )
{
	phpinfo();
	die();
}

$btnIndex = getButtonIndex( array("savebtn", "cancelbtn", "finishbtn" ), $_POST );

if ( $btnIndex == 1 || (isset($_GET['cancel'])&&$_GET['cancel'] == 1) || ( isset($_POST["step"])&&$_POST["step"]==1 && !isset( $_POST["license"] ) ) )
$_POST["step"]=-1;

if ( isset($_GET['finished'])&&$_GET['finished'] == 1 )
$_POST["step"]=-2;

if ( $btnIndex == 2 )
$_POST["step"]=-21;

if ( isset($_POST["step"])&&$_POST["step"]==4 )
header( "Location: published/wbsadmin/html/scripts/firststep.php" );
else
if (isset($_POST["step"])&&$_POST["step"]==-3 )
header( "Location: login/" );
else
if ( isset($_POST["step"])&&$_POST["step"]==-4 )
header( "Location: installer/" );

?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>WebAsyst Installer &mdash; <?php echo $locString['wa_inst_wizard']; ?></title>
<link href="./install.css" rel="stylesheet" type="text/css" >
<script type="text/javascript" language="javascript" src="./install.js"></script>
</head>
<body onload="onloadprepare();">
<div class="main-navigation">
	<div style="margin: 7px;">
		<a class="installer-logo" href="install.php" name="top">
			<span style="font-size:225%;">web<i style="color: rgb(119, 204, 255);">Asyst</i> <em style="color: #777;">Installer</em></span>
		</a>
	</div>
</div>
<div class="i-wrapper">

<!-- i-wrapper -->
<div class="i-col-container">
	<div class="i-col80">
		<div style="padding-right: 20px;">
	<!-- here -->
	<!-- MAIN_CONTENT -->	
<?php 
switch( isset($_POST["step"])?$_POST["step"]:0)
	{
		case 1:
//			echo '<h1>'.$locString['check_sys_req'].'</h1>';
			break;

		case 2:
//			echo '<h1>'.$locString['search_installed'].'</h1>';
			break;

		case 3:
			$sysVersion = null;
			$updateVersion = null;
/*			if (  getActionType( $sysVersion, $updateVersion ) == ACTION_INSTALL )
			echo '<h1>'.$locString['extracting_files'].'</h1>';
			else
			echo "<h1>{$locString['extracting_files']}</h1>";
*/			break;

		case -1:
//			echo "<h1>{$locString['install_canceled']}</h1>";
			break;

		case -2:
//			echo '<h1>'.$locString['inst_suc_comp'].'</h1>';
			break;

		default:
//			echo '<h1>'.$locString['welcome'].'</h1>';
	}

	switch( isset($_POST["step"])?$_POST["step"]:0 )
	{
		case 1:
			step1( );
			break;

		case 2:
			step2( );
			break;

		case 3:
			step3( );
			break;

		case -1:
			cancel( );
			break;

		case -2:
			finished( );
			break;

		case -21:
			finished2( );
			break;


		default:
			hello();

	}
	?>
	<!-- /MAIN_CONTENT -->
		</div>
	</div>
<div class="i-col20">
	<div class="i-colorbord i-p-padd">
		<div class="steps">
			<?php print showStepProgress(isset($_POST["step"])?($_POST["step"]+1):1); ?>
		
		</div>
	</div>
</div>
</div>
<!-- /i-wrapper -->
</div>
</body>
</html>