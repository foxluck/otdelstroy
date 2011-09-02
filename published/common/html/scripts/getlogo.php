<?php

    define('PUBLIC_AUTHORIZE', true);
    
    include_once '../../../../system/init.php';
    
    $fileName = "logo.gif";
	$filePath = Wbs::getFS()->getKernelAttachmentsDir();
	$filePath .= "/".$fileName;

	if ( !file_exists($filePath) ) {
		header("HTTP/1.0 404 Not Found");
		die();
	}
	
	

	$diskFileName = $fileName;
	$fileType = "image/gif";

	$fileSize = filesize($filePath);

	//header('Cache-Control: no-cache, must-revalidate');
	if (!empty($_GET["lt"]))
		header("Cache-Control: max-age=999999, must-revalidate");
	header("Accept-Ranges: bytes");
	header("Content-Length: $fileSize");
	header('Connection: close');
	header("Content-type: $fileType"); 
	header("Content-Disposition: inline; filename=$fileName;");

	@readfile($filePath);
?>