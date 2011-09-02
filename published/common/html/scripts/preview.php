<?php

	require_once 'tmp_functions.php';

	session_start();

	$fileName = base64_decode(str_replace(' ', '+', $_GET['file']));

	$file = getUploadedFile($fileName, 'images', '../');

	header('Content-type: ' . $file['type']);
	print $file['body'];
	
?>