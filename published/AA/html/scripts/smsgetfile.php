<?php

	$allow_page_caching = false;
	require_once( "../../../common/html/includes/httpinit.php" );

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "CP";

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	$kernelStrings = $loc_str[$language];

	$file = base64_decode($file);

	if ( !file_exists($file) ) {
		header("HTTP/1.0 404 Not Found");
		die();
	}

	$fileName = 'smshistory.csv';
	$fileType = 'text/plain';

	$fileSize = filesize($file);

	header('Cache-Control: no-cache, must-revalidate');
	header("Accept-Ranges: bytes");
	header("Content-Length: $fileSize");
	header('Connection: close');
	header("Content-type: $fileType");
	header("Content-Disposition: inline; filename=$fileName;");

	@readfile($file);
?>