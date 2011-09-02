<?php
header("Pragma: nocache\n");
header("cache-control: no-cache, must-revalidate, no-store\n\n");
header("Expires: Mon, 01 Jan 1990 01:01:01 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s",time())."GMT");
header('Content-Type: text/html; charset=UTF-8');
header('P3P: CP="CAO PSA OUR"');
//header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
error_reporting(E_ERROR | E_PARSE);

define('DIR_CLASSES', DIR_ROOT.'/classes');
define('WBS_ROOT_PATH', realpath( DIR_ROOT."/../../../../" ));
//define('WBS_ROOT_PATH', realpath( dirname(__FILE__)."/../../../../../" ));
define('WBS_DIR', WBS_ROOT_PATH . '/');
if (!defined("SYSTEM_PATH")) {
	define("SYSTEM_PATH", WBS_DIR."system");
}
if (!defined("WBS_PUBLISHED_DIR")){define("WBS_PUBLISHED_DIR", WBS_DIR."published");}

define('__USE_OLD_UPDATE',false);

//PHP5
if(__USE_OLD_UPDATE){
	include_once(DIR_CLASSES.'/class.classmanager.php');
	function __autoload($class_name)
	{
		ClassManager::includeClass($class_name);
	}
}else{//new
	include_once(WBS_DIR."/system/autoload.php");
	Autoload::add("ActionsController", "published/SC/html/scripts/classes/class.actionscontroller.php");
	Autoload::addRule("true", "published/SC/html/scripts/classes" , 2);
}

include_once(DIR_ROOT.'/includes/constants.php');
if(SystemSettings::is_hosted()){
	include_once(DIR_INCLUDES.'/init.wa.php');
}









@ini_set('include_path', '.'.PATH_DELIMITER.WBS_DIR.'/kernel/includes/pear');
include_once(WBS_DIR.'/kernel/includes/pear/PEAR.php');
include_once(DIR_FUNC.'/placeholders_functions.php');
include_once(DIR_FUNC.'/db_functions.php' );

require_once(DIR_MODULES.'/divisions/class.divisionmodule.php');
include_once DIR_CFG.'/tables.inc.wa.php';

include_once(DIR_FUNC.'/error_functions.php');
PEAR::setErrorHandling( PEAR_ERROR_CALLBACK, 'handlePEARError' );
set_error_handler("log_error");


$Register = &Register::getInstance();

$_GET 	= xStripSlashesGPC($_GET);
$_POST 	= xStripSlashesGPC($_POST);

MagicQuotesRuntimeSetting();

define('VAR_POST','Post');
define('VAR_GET','Get');
define('VAR_FILES','Files');
define('VAR_CURRENTDIVISION','CurrDivision');
define('VAR_MESSAGE', 'Message');

$Register->set(VAR_POST, $_POST);
$Register->set(VAR_GET, $_GET);
$Register->set(VAR_FILES, $_FILES);

/**
 * @param PEAR_Error $ErrorObject
 */
function pear_handler($ErrorObject){

	return;
	$fp = fopen(DIR_LOG.'/pear.error.txt','a');
	ob_start();
	print '<pre>';
	print_r($ErrorObject);
	print '</pre>';
	fwrite($fp, "\n".date('Y-m-d H:i:s').'  '.ob_get_contents());
	ob_end_clean();
	//		fwrite($fp, "\n".date('Y-m-d H:i:s').'  '.$ErrorObject->toString());
	fclose($fp);
}
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, "pear_handler");

define('TPLID_GENERAL_LAYOUT', 'general_layout');
define('TPLID_HOMEPAGE', 'home_page');
define('TPLID_PRODUCT_INFO', 'product_info');
define('TPLID_CSS', 'css');
define('TPLID_HEAD', 'head');

define('SHOWALL_ALLOWED_RECORDS_NUM', 50);
//functions from init.wa.php

function wbs_auth(){//needed

	if(!isset($_SESSION['WBS_ACCESS_SC'])){
		Redirect('auth.php?redirect='.base64_encode(renderURL()));
	}

	return $_SESSION['WBS_ACCESS_SC'];
}

function sc_issetSessionData($key){

	return isset($_SESSION['__WBS_SC_DATA'][$key]);
}

function sc_getSessionData($key){//needed

	return isset($_SESSION['__WBS_SC_DATA'][$key])?$_SESSION['__WBS_SC_DATA'][$key]:'';
}

function sc_setSessionData($key, $val){//needed

	$_SESSION['__WBS_SC_DATA'][$key] = $val;
}

/**
 * @param Division
 * @param array - array of divisions where last element is current division
 */
function sc_checkLoggedUserAccess2Division($CurrDivision, $BreadDivs = array()){//needed

	static $accesses;

	$U_ID = sc_getSessionData('U_ID');
	$UG_IDs = sc_getSessionData('UG_IDs');
	if(!is_array($UG_IDs) || !count($UG_IDs))$UG_IDs = null;

	if(!is_array($accesses)){

		/**
		 * TODO: replace after metaupdate
		 */
		$accesses = array();
		// personal
		$dbres = db_phquery('SELECT `AR_OBJECT_ID`,`AR_VALUE` FROM U_ACCESSRIGHTS WHERE (AR_ID=?)', $U_ID);
		while($row = db_fetch_row($dbres)){
			if(strpos($row[0],'SC__')===0&&$row[1]){
				$accesses[substr($row[0],4)] = 1;
			}
		}
		// group
		if($UG_IDs != null)
		{
			//$dbres = db_phquery('SELECT `AR_OBJECT_ID`,`AR_VALUE` FROM UG_ACCESSRIGHTS WHERE AR_ID IN (?@)', $UG_IDs);
			$dbres = db_phquery('SELECT `AR_OBJECT_ID`,`AR_VALUE` FROM UG_ACCESSRIGHTS WHERE (AR_ID=?) '.(is_null($UG_IDs)?'':' OR (AR_ID IN (?@))'), $U_ID, $UG_IDs);

			while($row = db_fetch_row($dbres)){
				if(strpos($row[0],'SC__')===0&&$row[1]){
					$accesses[substr($row[0],4)] = 1;
				}
			}
		};
	}

	if(isset($accesses[$CurrDivision->getID()]))return true;
	for($k = count($BreadDivs)-1; $k>=0; $k--){
		if(isset($accesses[$BreadDivs[$k]->getID()]))return true;
	}
	print translate('forbidden_page');
	die();
}

?>