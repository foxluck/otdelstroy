<?php

// REQUEST_URI for the windows servers IIS
if(!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
    if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING'])){
        $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
    }	
}
/*
function SystemErrorHandler($errno, $errstr, $errfile, $errline)
{
	if ($errno == 8192) {
		return true;
	}
	$str = "Error {$errno}: {$errstr} File: {$errfile} on line {$errline}";
	if (defined('DEVELOPER')) {
		echo $str;
	} else {
		error_log($str);
	}
}

set_error_handler('SystemErrorHandler', E_ALL | E_WARNING);
*/

if (function_exists('set_magic_quotes_runtime')) {
	// User @ for hide warning for PHP 5.3, because set_magic_quotes_runtime is deprecated
	@set_magic_quotes_runtime(false);
}

@ini_set('register_globals', false); 

// Correct magic_quotes_gpc
if (( function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc() ) || 
 	( ini_get('magic_quotes_sybase') && strtolower(ini_get('magic_quotes_sybase')) != "off" )) {
	function stripcslashes_array($a) {
		foreach($a as $k => $v) {
			if (is_array($v)) {
				$a[$k] = stripcslashes_array($v);
			} else {
				$a[$k] = stripcslashes($v);
			}
		}	
		return $a;
	}
	$_GET = stripcslashes_array($_GET);
	$_POST = stripcslashes_array($_POST);
	$_COOKIE = stripcslashes_array($_COOKIE);
}

// Module gettext
if (!function_exists("gettext")) {
	include(dirname(__FILE__)."/gettext.php");
}

function _s($msgid)
{
	return dgettext("system", $msgid);
}

// JSON, since PHP 5.2 exists
if (!function_exists("json_encode") ) {
	include(dirname(__FILE__)."/json.php");
}

// GD2 function imagerotate
if(!function_exists("imagerotate")) {
	include(dirname(__FILE__)."/imagerotate.php");
}

if (!function_exists("filter_var")) {
	function filter_var()
	{
		return true;	
	}	
}

?>