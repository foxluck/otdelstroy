<?php
$_SESSION['timestamp'] = time();

if(sc_issetSessionData('SC_INSTALLED') && !sc_getSessionData('SC_INSTALLED')){
	print 'Dont work';
	die;
}elseif (!sc_issetSessionData('SC_INSTALLED')){

	$session_id = session_id();
	session_write_close();

	$messageClient = new WbsHttpMessageClient('', 'wbs_msgserver.php');
	$messageClient->putData('action', 'INIT_DB_CONNECT_DATA');
	$messageClient->putData('session_id', $session_id);
	$messageClient->send();

	session_id($session_id);
	session_start();

	if($messageClient->getResult('success')!=='true')die('Couldnt connect to DB');

	if(!defined('DIR_PUBLICDATA_SC')){

		define('DIR_PUBLICDATA_SC', sc_getSessionData('DIR_PUBLICDATA_SC'));
		define('DIR_DATA_SC', sc_getSessionData('DIR_DATA_SC'));
		define('DIR_IMG', DIR_PUBLICDATA_SC.'/images');
		define('DIR_THEMES',DIR_PUBLICDATA_SC.'/themes');
		define('DIR_PRODUCTS_PICTURES', DIR_PUBLICDATA_SC.'/products_pictures');
		define('DIR_PRODUCTS_FILES', DIR_DATA_SC.'/products_files');
		define('DIR_COMPILEDTEMPLATES', DIR_ROOT.'/../../../../kernel/includes/smarty/compiled/SC/templates_c/'.sc_getSessionData('DB_KEY'));
		define('DIR_TEMP', DIR_DATA_SC.'/temp');
		define('DIR_SURVEY', DIR_DATA_SC.'/survey');
		define('DIR_FLAGS', DIR_IMG.'/flags');
		define('URL_FLAGS', URL_IMAGES.'/flags');
	}
}

function db_getConnectData($key = null){

	$return = array(
		'DB_HOST' => sc_getSessionData('DB_HOST'),
		'DB_USER' => sc_getSessionData('DB_USER'),
		'DB_PASS' => sc_getSessionData('DB_PASS'),
		'DB_NAME' => sc_getSessionData('DB_NAME'),
	);

	if(!is_null($key) && isset($return[$key]))return $return[$key];
	else return $return;
}

define('ADMIN_LOGIN', 'admin');

// include table name file
include_once DIR_CFG.'/tables.inc.wa.php';
?>