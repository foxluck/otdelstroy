<?php

header('Content-Type: text/html; charset=UTF-8;');
mb_internal_encoding('UTF-8');

define ("WBS_ROOT_PATH", realpath(dirname(__FILE__). "/.."));
define ("WBS_DIR", WBS_ROOT_PATH . "/");
define ("WBS_PUBLISHED_DIR", realpath(WBS_DIR . "published" ) . DIRECTORY_SEPARATOR);
define ("SYSTEM_PATH", realpath(dirname(__FILE__)));

include_once("kernel.php");

define("WBS_SMARTY_DIR", WBS_ROOT_PATH . "/kernel/includes/smarty");

include_once("autoload.php");

Registry::set('time', microtime(true));

if (file_exists(dirname(__FILE__)."/app_mode.php")) {
   include("app_mode.php");
}

if (file_exists(dirname(__FILE__)."/config.php")) {
	include(dirname(__FILE__)."/config.php");
}

include_once("functions/__functions.php");

if (Wbs::isHosted()) {
	include_once("const.php");	
}

WebQuery::initialize();

if (defined('PUBLIC_AUTHORIZE') && PUBLIC_AUTHORIZE && !defined('GET_DBKEY_FROM_URL')) {
    define('GET_DBKEY_FROM_URL', true);
}

// If cannot load dbkey settings
try {
	if (isset($_POST["PHPSESSID"]) && $_POST["PHPSESSID"]) {
		session_id($_POST["PHPSESSID"]);
	}
	@session_start();
	
	if (!Wbs::loadCurrentDBKey()) {
		Wbs::logout();
	}
	
	if (Wbs::isHosted() && Wbs::getDbkeyObj() !== null) {                                                                                              
  		$expire_date=strtotime(Wbs::getDbkeyObj()->getSetting('EXPIRE_DATE'));                                                                         
    	if ($expire_date > 0 && $expire_date < time() && file_exists(WBS_DIR."kernel/includes/expired.htm")) {                                         
    		header("HTTP/1.0 200 OK");                                                                                                                 
      		die(file_get_contents(WBS_DIR."kernel/includes/expired.htm"));                                                                             
     	}

     	if (isset($_SERVER['HTTPS']) && Wbs::getDbkeyObj()->getSetting('PLAN') == 'FREE') {
     		if (basename($_SERVER["SCRIPT_NAME"]) != 'proceed_pay.php' && basename($_SERVER["SCRIPT_NAME"]) != 'proceed_pay_domains.php') {
     			header('Location: http://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
     			exit();
     		}
     	}
	}                                                                                                                                                  
 	
	Wbs::connectDb();
} catch (UserException $e) {
	error_log($e->getLogMessage());
	echo new HTMLExceptionDecorator($e);
	exit;
} catch (Exception $e) {
	trigger_error($e->getMessage (), E_USER_ERROR);
	exit;
}

// Check system update
$updater = new WbsUpdater("SYSTEM");
$updater->check();

// Auth User
if (defined('PUBLIC_AUTHORIZE') && PUBLIC_AUTHORIZE) {
	Wbs::publicAuthorize();
	$lang = Env::Get('lang', Env::TYPE_STRING, User::getLang());
	GetText::load($lang, SYSTEM_PATH . "/locale", 'system', false);	
} else {
	Wbs::loadCurrentUser();
}   
