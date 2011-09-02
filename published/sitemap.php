<?php
define('WBS_DIR',realpath(dirname(__FILE__).'/../').'/');

if (!isset($_SERVER['REQUEST_URI']))
{
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
	if (isset($_SERVER['QUERY_STRING'])&&strlen($_SERVER['QUERY_STRING'])){
		$_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
	}
}

$DB_KEY = false;
if(file_exists(WBS_DIR."kernel/hosting_plans.php")){//hosted
	// Dbkey aliases mechanism
	require_once( WBS_DIR."kernel/classes/class.accountname.php");
	function getDomainName() {
		if(isset($_GET['__account_name']))return $_GET['__account_name'];
		if (preg_match('/(.*?)\.([a-z0-9\.\-]+)/ui', $_SERVER['HTTP_HOST'], $matches)){
			$res=$matches[1];
		}else{
			$res='';
		}
		return $res;
	}
	$aliasName = getDomainName();
	$AccountName=new AccountName($aliasName);
	$res=strtoupper(trim($AccountName->dbkey));
	$DB_KEY = ($res == '')?strtoupper($aliasName):$res;

}elseif(file_exists(WBS_DIR."kernel/wbs.xml")){//OS
	$xml= simplexml_load_file(WBS_DIR."/kernel/wbs.xml");
	$DB_KEY=(string)$xml->FRONTEND['dbkey'];
	$DB_KEY=isset($DB_KEY)?$DB_KEY:false;
}

if(!$DB_KEY){
	header("HTTP/1.0 404 Not Found");
	print "error read settings";
	exit;
}


$app_id = isset($_GET['app'])?$_GET['app']:'SC';
if($app_id){
	if(!preg_match('/^[a-zA-Z]{2}$/',$app_id)){
		header("HTTP/1.0 404 Not Found");
		print "invalid application id";
		exit;
	}
}else{
	header('Content-type: application/xml');
	if(isset($_SERVER['HTTP_X_REAL_HOST'])&&preg_match('/webasyst\.((net)|(ru))$/msi',$_SERVER['HTTP_HOST'])){
		$_SERVER['REQUEST_URI'] = str_replace('shop/','',$_SERVER['REQUEST_URI']);
	}
	$host = isset($_SERVER['HTTP_X_REAL_HOST'])?$_SERVER['HTTP_X_REAL_HOST']:$_SERVER['HTTP_HOST'];
	$url = 'http://'.$host.$_SERVER['REQUEST_URI'];
	$app_ids = array('SC','pd');
	$index = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
	http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd">';
	foreach($app_ids as $app_id){
		$sitemap_path = WBS_DIR.'/published/publicdata/'.$DB_KEY.'/attachments/'.$app_id.'/sitemap/index.xml';
		if(!file_exists($sitemap_path)){
			continue;
		}
		
		$index .= '
	<sitemap>
		<loc>'.$url.'?app='.$app_id.'</loc>
		<lastmod>'.date("c",filemtime($sitemap_path)).'</lastmod>
	</sitemap>';
	}
	$index .= '</sitemapindex>';
	print $index;
	exit;
}
$section = isset($_GET['section'])?$_GET['section']:'index';
if($section){
	if(!preg_match('/^[a-zA-Z_-]+$/',$app_id)){
		header("HTTP/1.0 404 Not Found");
		print "invalid application section";
		exit;
	}
}



$sitemap_path = WBS_DIR.'/published/publicdata/'.$DB_KEY.'/attachments/'.$app_id.'/sitemap/'.$section.'.xml';
if(file_exists($sitemap_path)&&is_file($sitemap_path)){
	header('Content-type: application/xml');
	readfile($sitemap_path);
	exit;
}else{
	header("HTTP/1.0 404 Not Found");
	print "file not found";
	exit;
}
?>