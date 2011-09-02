<?php
$DebugMode = false;
// -------------------------INITIALIZATION-----------------------------//
define('DIR_ROOT', str_replace("\\","/",realpath(dirname(__FILE__))));

include(DIR_ROOT.'/includes/init.php');
include_once(DIR_CFG.'/connect.inc.wa.php');

include_once(DIR_FUNC.'/setting_functions.php' );

require_once(DIR_FUNC.'/product_functions.php');
require_once(DIR_FUNC.'/reg_fields_functions.php' );
require_once(DIR_FUNC.'/order_status_functions.php' );
require_once(DIR_FUNC.'/cart_functions.php');
require_once(DIR_FUNC.'/order_functions.php' );


$DB_tree = new DataBase();
db_connect(SystemSettings::get('DB_HOST'),SystemSettings::get('DB_USER'),SystemSettings::get('DB_PASS')) or die (db_error());
db_select_db(SystemSettings::get('DB_NAME')) or die (db_error());
$DB_tree->connect(SystemSettings::get('DB_HOST'), SystemSettings::get('DB_USER'), SystemSettings::get('DB_PASS'));
$DB_tree->query("SET character_set_client='".MYSQL_CHARSET."'");
$DB_tree->query("SET character_set_connection='".MYSQL_CHARSET."'");
$DB_tree->query("SET character_set_results='".MYSQL_CHARSET."'");

$DB_tree->selectDB(SystemSettings::get('DB_NAME'));
define('VAR_DBHANDLER','DBHandler');

$Register = &Register::getInstance();
$Register->set(VAR_DBHANDLER, $DB_tree);


settingDefineConstants();

$admin_mode = false;
if(isset($_SESSION['__WBS_SC_DATA'])&&isset($_SESSION['__WBS_SC_DATA']["U_ID"])){
	//TODO: check user rights for download section
	$admin_mode = true;
}

session_write_close();


$fileToDownLoad = "";
$fileToDownLoadShortName = "";
settingDefineMLConstants();
$fileToDownLoadPrefix = (CONF_SHOP_NAME?preg_replace('/[^a-z_0-9]/ui', '_', strtolower(translit(CONF_SHOP_NAME))).'_':'');
$res = 0;
$direct_mode = false;
$charset = false;

if ( !isset($_GET["getFileParam"]) ){
	sendResponce('err_forbidden',403);
}

if($debuger){
	$debuger->end(null,'init');
}
if($debuger){
	$debuger->start();
}

$getFileParam = Crypt::FileParamDeCrypt( $_GET["getFileParam"], null );

switch($getFileParam) {
	case 'GetExchangeFiles': {
		if($admin_mode){
			$file_name = isset($_GET['filename'])?Crypt::FileParamDeCrypt($_GET['filename'],false):'';
			$file_name = preg_replace('/[\.]{2,}/msi','',$file_name);
			$fileToDownLoad = DIR_TEMP."/exchange/".$file_name;
			$fileToDownLoadShortName = $file_name;
		}else{
			$res = 4;
		}
		break;
	}
	case 'GetDataBaseSqlScript': {
		if($admin_mode){
			$fileToDownLoad = DIR_TEMP."/database.sql";
			$fileToDownLoadShortName = "database.sql";
		}else{
			$res = 4;
		}
		break;
	}
	case 'GetCustomerExcelSqlScript': {
		if($admin_mode){
			$fileToDownLoad = DIR_TEMP."/customers.csv";
			$fileToDownLoadShortName = $fileToDownLoadPrefix.'customers.csv';
		}else{
			$res = 4;
		}
		break;
	}
	case 'GetDataBaseSqlScript': {
		if($admin_mode){
			$fileToDownLoad = DIR_TEMP."/database.sql";
			$fileToDownLoadShortName = "database.sql";
		}else{
			$res = 4;
		}
		break;
	}
	case 'GetOrdersExcelSqlScript': {
		if($admin_mode){
			$fileToDownLoad = DIR_TEMP."/orders.csv";
			$fileToDownLoadShortName = $fileToDownLoadPrefix.'orders.csv';
		}else{
			$res = 4;
		}
		break;
	}
	case 'GetFroogleFeed': {
		$fileToDownLoad = DIR_TEMP."/froogle.txt";
		$fileToDownLoadShortName = "froogle.txt";
		break;
	}
	case 'GetYandex': {
		$direct_mode = isset($_GET['download'])?false:true;
		$charset = 'windows-1251';
		$fileToDownLoad = DIR_TEMP."/yandex.xml";
		$fileToDownLoadShortName="yandex.xml";
		break;
	}
	case 'Get1C': {
		if($admin_mode){
			$direct_mode = isset($_GET['download'])?false:true;
			
			$export_file_name = (defined('CONF_SHOP_NAME')?preg_replace('/[^a-z_0-9]/ui', '_', strtolower(translit(constant('CONF_SHOP_NAME'))) ):'export');
			$export_file_name_extension = '_'.strtolower("utf-8").'_commerceml.xml';
			
			$export_file_name = substr($export_file_name,0,(128-strlen($export_file_name_extension)));
			$export_file_name .= $export_file_name_extension;
			
			$charset = 'utf-8';
			$fileToDownLoad = DIR_TEMP."/exportto1c.xml";
			$fileToDownLoadShortName=$export_file_name;
		}else{
			$res = 4;
		}
		break;
	}
	case 'GetSubscriptionsList': {
		if($admin_mode){
			$fileToDownLoad = DIR_TEMP."/subscribers.txt";
			$fileToDownLoadShortName = "subscribers.txt";
		}else{
			$res = 4;
		}
		break;
	}
	default: {
		if ( $admin_mode && preg_match('/GetCSVCatalog=(.+)$/u', $getFileParam, $sp) ){
			$file = base64_decode($sp[1]);
			$file = preg_replace('/[\\/\\\\]/s','',$file);
			$fileToDownLoad = DIR_TEMP."/{$file}";
			$fileToDownLoadShortName = $file;
		}else{
			$customerID =0;$orderID = 0;$productID = 0;$code = '';
			init_order_details($getFileParam,$customerID,$orderID,$productID,$code);
			if($orderID&&$productID){//$customerID could be 0 for deleted customers
				$res = ordAccessToLoadFile( $orderID, $productID,$customerID,$fileToDownLoad, $fileToDownLoadShortName );
			}else{
				$res = 4;
			}
		}
		break;
	}

}

if($res == 0){
	download_file($fileToDownLoad,$direct_mode,$charset, $fileToDownLoadShortName);
}else{
	$message = "err_forbidden";
	switch($res){
		case 1: {
			$message = "prd_download_number_of_downloads_exceeded";
			break;
		}
		case 2: {
			$message = "prd_download_period_expired";
			break;
		}
		case 3: {
			$message = "err_access_to_product_downloadable_file_denied";
			break;
		}
		case 4:{
			$message = "err_forbidden";
			break;
		}

		default: {
			$message = "err_forbidden";
			break;
		}
	}
	sendResponce($message,403);
}


function init_order_details($getFileParam,&$customerID,&$orderID,&$productID,&$code)
{
	$customerID = 0;
	$orderID = 0;
	$productID = 0;
	$order_time = 0;
	$getFileParam = Crypt::FileParamDeCrypt( $_GET["getFileParam"], null );//echo $getFileParam;
	$params = explode( "&", $getFileParam );
	foreach( $params as $param )
	{
		$param_value = explode( "=", $param );

		if ( count($param_value) >= 2 )
		{
			if ( $param_value[0] == "orderID" ){
				$orderID = (int)$param_value[1];
			}else if ( $param_value[0] == "productID" ){
				$productID = (int)$param_value[1];
			}else if ( $param_value[0] == "customerID" ){
				$customerID = (int)$param_value[1];
			}else if ( $param_value[0] == "code" ){
				$code = $param_value[1];
			}
		}
	}
}

function download_file($fileToDownLoad,$direct_mode = false,$charset = null, $fileToDownLoadShortName){
	if(strlen($fileToDownLoad)>0 && file_exists($fileToDownLoad)){
		//hack for ie:
		if(!$direct_mode && isset($_SERVER['HTTP_USER_AGENT'])&&preg_match('/MSIE/',$_SERVER['HTTP_USER_AGENT'])){
			if(!isset($_SERVER['HTTP_REFERER'])||!$_SERVER['HTTP_REFERER']){
				if(isset($_SERVER['HTTP_HOST'])&&$_SERVER['HTTP_HOST']){
					if(strpos($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST'])===false){
						sendResponce('btn_download',200,'get_file.php?getFileParam='.$_GET["getFileParam"]);
					}
				}else{
					sendResponce('btn_download',200,'get_file.php?getFileParam='.$_GET["getFileParam"]);
				}
			}
		}
		$headers = array();
		$matches = '';
		$direct_mode &=$direct_mode&&preg_match('/\.([^\.]+)$/',$fileToDownLoad,$matches);
		if($direct_mode){
			switch($matches[1]){
				case 'xml':$headers['Content-type'] = 'text/xml;';		break;
				case 'txt':$headers['Content-type'] = 'text/plain;';	break;
				case 'log':$headers['Content-type'] = 'text/plain;';	break;
			}
			if($charset){
				if(!isset($headers['Content-type'])){
					$headers['Content-type'] ='';
				}
				$headers['Content-type'] = trim($headers['Content-type']." charset={$charset}");
			}
		}else{
			$file_name = (isset($fileToDownLoadShortName)&&strlen($fileToDownLoadShortName)?$fileToDownLoadShortName:basename($fileToDownLoad));
			if (preg_match("/msie/i",$_SERVER['HTTP_USER_AGENT'])) {
				if (preg_match("/[а-я]/ui", $file_name)) {
					$file_name = iconv("UTF-8", "Windows-1251", $file_name);
				} else {
					$file_name = rawurlencode($file_name);
				}
			}else{
				$file_name = htmlspecialchars($file_name,ENT_QUOTES,'utf-8');
			}
			$headers['Content-type'] ='application/stream-download';//'octet/stream';//
			$headers['Content-Transfer-Encoding'] ='Binary';
			$headers['Content-length'] = filesize($fileToDownLoad);
			$headers['Content-disposition'] = 'attachment; filename="'.$file_name.'";';
			$headers['Expires'] = '0';
			$headers['Cache-Control'] = 'private';
			$headers['Pragma'] = 'public';
			$headers['Connection'] = 'close';
		}
		foreach($headers as $header_name => $header){
			header("{$header_name}: {$header}");
		}

		readfile($fileToDownLoad);
	}else{
		global $orderID, $productID,$customerID;
		sendResponce('err_cant_read_file',404);
	}

}

function sendResponce($message,$code = null,$link = null)
{
	switch($code){
		case 200:{
			break;
		}
		case 403:{
			$header = "HTTP/1.0 403 Access Forbidden";
			break;
		}
		case 404:
		default:{
			$header = "HTTP/1.0 404 Not Found";
			$code = 404;
		}
	}
	$LanguageEntry = &LanguagesManager::getCurrentLanguage();
	$locals = $LanguageEntry->getLocals(array(LOCALTYPE_FRONTEND, LOCALTYPE_GENERAL, LOCALTYPE_HIDDEN), false, false);

	$Register = &Register::getInstance();
	$Register->set('CURRLANG_LOCALS', $locals);
	$Register->set('CURR_LANGUAGE', $LanguageEntry);

	$DefLanguageEntry = &ClassManager::getInstance('Language');
	$DefLanguageEntry->loadById(CONF_DEFAULT_LANG);
	$deflocals = $DefLanguageEntry->getLocals(array(LOCALTYPE_FRONTEND, LOCALTYPE_GENERAL, LOCALTYPE_HIDDEN), false, false);

	$Register->set('DEFLANG_LOCALS', $deflocals);
	$Register->set('DEF_LANGUAGE', $DefLanguageEntry);



	$message = translate($message);
	if($header){
		header($header);
	}
	if($link){
		$link = ":&nbsp;<a href=\"{$link}\" class=\"message\">{$link}</a>";
	}
	$class = ($code > 400)?'error':'message';
	print <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>{$message}</title>
	<style type="text/css">
	H1.error {color: #ff0000;background-color: #eee;}
	H1.message {color: #339933;background-color: #eee;}
	.error {color: #ff3333;font-weight:bold;background-color: #fee;}
	.message {color: #33ff33;font-weight:bold;background-color: #efe;}
	</style>
	
</head>
<body>
	<h1 class="{$class}">{$code}</h1>
	<span class="{$class}">{$message}{$link}</span>
</body>
</html>
HTML;
	exit;
}
?>