<?php

	if((isset($_SERVER["WINDIR"])
		|| isset($_SERVER["windir"]))
		||(isset($_SERVER['SERVER_SOFTWARE'])&&(strpos(strtolower($_SERVER['SERVER_SOFTWARE']),'microsoft')!==false))
	){
		define('IS_WINDOWS',true);
	}else{
		define('IS_WINDOWS',false);
	}
	if(!defined('PATH_DELIMITER')){
		define('PATH_DELIMITER', IS_WINDOWS?';':':');
	}

	define('DEFAULT_CHARSET', 'utf-8');

//	define( "WBS_DIR", str_replace('\\','/',realpath(DIR_ROOT."/../../../../")."/"));
	
		//for IIS
	if(!isset($_SERVER['DOCUMENT_ROOT'])){ if(isset($_SERVER['SCRIPT_FILENAME'])){
	$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
	}; };
	if(!isset($_SERVER['DOCUMENT_ROOT'])){ if(isset($_SERVER['PATH_TRANSLATED'])){
	$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
	}; };
	//end for IIS
	if(!defined('WBS_INSTALL_PATH')){
		if(file_exists(WBS_DIR."/kernel/wbs.xml")){
			$xml= simplexml_load_file(WBS_DIR."kernel/wbs.xml");
			$__WBS_INSTALL_PATH = (string)@$xml->DIRECTORIES->WEB_DIRECTORY['PATH'];
		}else{
			$__WBS_INSTALL_PATH = '';
		}
		define('WBS_INSTALL_PATH',strlen($__WBS_INSTALL_PATH)?$__WBS_INSTALL_PATH:str_replace(array('\\','///','//'),'/','/'.substr( __cleanpath($_SERVER['SCRIPT_FILENAME']."/../../../../../").'/',strlen(__cleanpath($_SERVER['DOCUMENT_ROOT'])))));
	}
	
		
	define('DIR_FUNC', DIR_ROOT.'/core_functions');
	define('DIR_MODULES',DIR_ROOT.'/modules');
	define('DIR_CSS',DIR_ROOT.'/css');
	define('DIR_JS',DIR_ROOT.'/js');
	define('DIR_INCLUDES', DIR_ROOT.'/includes');
	define('DIR_CFG', DIR_ROOT.'/cfg');
	define('DIR_REPOTHEMES', DIR_ROOT.'/repo_themes');
	define('DIR_CHARTS', DIR_ROOT.'/charts');
	define('DIR_TPLS', DIR_ROOT.'/templates');
	define('DIR_FORMS', DIR_TPLS.'/forms');
	define('DIR_FTPLS', DIR_ROOT.'/templates/frontend');
	define('URL_ROOT', str_replace(array('///','//'),'/',file_exists(WBS_DIR."/kernel/hosting_plans.php")?'/shop':WBS_INSTALL_PATH.'/published/SC/html/scripts'));
	define('URL_COMMON', str_replace(array('///','//'),'/',file_exists(WBS_DIR."/kernel/hosting_plans.php")?'/common':WBS_INSTALL_PATH.'/published/common'));
	
	define('URL_CSS', URL_ROOT.'/css');
	define('URL_JS', URL_ROOT.'/js');
	define('URL_3RDPARTY', URL_ROOT.'/3rdparty');
	define('URL_REPOTHEMES', URL_ROOT.'/repo_themes');
	define('URL_TEMP', URL_ROOT.'/temp');
	define('URL_DEMOPRD_IMAGES', URL_ROOT.'/demo_product_pictures');

	define('URL_CHARTS', URL_ROOT.'/charts');
//	define('URL_JSCALENDAR', URL_ROOT.'/jscalendar');

	
	include_once(DIR_FUNC.'/functions.php');

	//SESSION WORKAROUND BEGIN
	$session_name = ini_get('session.name');
	$source = isset($_POST['source'])?$_POST['source']:'';
	if(SystemSettings::is_hosted()&&!isset($_COOKIE[$session_name])&&($source!='swfupload')){
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
	}
	
	@ini_set( 'session.cookie_lifetime', 2592000 );
	session_set_cookie_params( 2592000 );
	$session_id = isset($_POST['session_id'])?$_POST['session_id']:(isset($_POST['PHPSESSID'])?$_POST['PHPSESSID']:false);
	if(isset($_POST['source'])&&($_POST['source'] == 'swfupload')&&$session_id){
		$remote_ip = null;
		if(function_exists("getallheaders")){
            $request_headers = getallheaders();
            if(isset($request_headers['X-Real-IP']))
              $remote_ip = $request_headers['X-Real-IP'];
        }
        	if(!$remote_ip){
        		$remote_ip = $_SERVER["REMOTE_ADDR"];
        }
		session_id($session_id);
		session_start();
		//TODO fix session start
		if(false&&(sc_getSessionData('swfupload_ip') != $remote_ip)){
			die('invalid session id (IP)');
		}
	}else{
		session_start();
	}
	

	define('SHCART_VIEW_FADE', 1);
	define('SHCART_VIEW_PAGE', 2);

	define('_CHECKOUT_INSTANCE_NAME', '_F0F0');
	define('_CHECKOUT_STEPMANAGER', '_STPMNG_001');
	
function __cleanpath($path){
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
return implode('/',$res);
}
?>