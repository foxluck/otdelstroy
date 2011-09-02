<?php
header("content-type: text/css; charset=UTF-8;");

@ini_set("magic_quotes_runtime",0);
if(function_exists('set_magic_quotes_runtime')&&!preg_match('/^5\.3/',PHP_VERSION)){
	set_magic_quotes_runtime(false);
}
define('DIR_ROOT', str_replace("\\","/",realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..')));
$WBSPath = DIR_ROOT."/../../../../";
if(!defined("WBS_DIR")){
	define( "WBS_DIR",realpath('../../../../../').DIRECTORY_SEPARATOR);
}
if(!defined('DIR_TEMP')){
	define('DIR_TEMP',realpath(WBS_DIR.DIRECTORY_SEPARATOR.'temp'));
}
$paths = explode(',',isset($_GET['css'])?$_GET['css']:'');

// Check if it supports gzip
$supportsGzip = false;
if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])){
	$encodings = explode(',', strtolower(preg_replace("/\s+/", "", $_SERVER['HTTP_ACCEPT_ENCODING'])));

	if ((in_array('gzip', $encodings) || in_array('x-gzip', $encodings) || isset($_SERVER['---------------'])) && function_exists('ob_gzhandler') && !ini_get('zlib.output_compression')) {
		$enc = in_array('x-gzip', $encodings) ? "x-gzip" : "gzip";
		$supportsGzip = true;
	}
}

// Use cached file disk cache
/*
 if ($diskCache && $supportsGzip && file_exists($cacheFile)) {
	if ($compress)
	header("Content-Encoding: " . $enc);

	echo getFileContents($cacheFile);
	die();
	}
	*/
$mtime = filemtime(__FILE__);
$content = '';
foreach ($paths as $file){
	if(preg_match('/\.css$/',$file)){
		if(strpos($file,'/shop/repo_themes')!==false){
			$file = str_replace('/shop/repo_themes','/published/SC/html/scripts/repothemes',$file);
		}elseif(strpos($file,'/shop/css')!==false){
			$file = str_replace('/shop/css','/published/SC/html/scripts/css',$file);
		}
		$file = WBS_DIR.$file;
		$content .= getFileContents($file);
		if(file_exists($file)){
			$mtime = max($mtime,filemtime($file));
		}
	}
}
// Set Modified Time to be returned by the HTTP response
$mtimestr = gmdate("D, d M Y H:i:s", $mtime) . " GMT";

// Expires offset: 21600 are the seconds in a day
$offset = 15 * 21600;
header("Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT");
header("Last-Modified: " . $mtimestr);
//header("Cache-Control: must-revalidate", false);
header("Cache-control: public; max-age={$offset}");

$compress = true;
// Generate GZIP'd content
if ($supportsGzip) {
	if ($compress) {
		header("Content-Encoding: " . $enc);
		$cacheData = gzencode($content, 9, FORCE_GZIP);
	} else
	$cacheData = $content;

	// Write gz file
	//if ($diskCache && $cacheKey != "")
	//putFileContents($cacheFile, $cacheData);
	// Stream to client
	echo $cacheData;
} else {
	// Stream uncompressed content
	echo $content;
}

function getFileContents($path) {
	$path = realpath($path);

	if (!$path || !@is_file($path))
	return "";

	if (function_exists("file_get_contents"))
	return @file_get_contents($path);

	$content = "";
	if($fp = @fopen($path, "r")){
		while (!feof($fp)){
			$content .= fgets($fp);
		}
		fclose($fp);
	}

	return $content;
}
?>