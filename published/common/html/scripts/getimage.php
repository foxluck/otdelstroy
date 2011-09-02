<?php

function get_mime($fileName)
{
	preg_match('/\.(.*?)$/', $fileName, $m);
	switch(strtolower($m[1]))
	{
		case 'jpg': case 'jpeg': case 'jpe': return 'image/jpg';
		case 'png': case 'gif': case 'bmp': case 'tiff' : return 'image/'.strtolower($m[1]);
		default: return 'image/gif';
	}
}

$fileName = base64_decode(str_replace(' ', '+', $_GET['file']));
$ctype = get_mime($fileName);

$user = str_replace('/', '', base64_decode($_GET['user']));

$path = $_SERVER['DOCUMENT_ROOT'].str_replace(
	'/common/html/scripts/getimage.php',
	'/data/' . $user . '/attachments/mm/images/'.(int)$_GET['msg'].'/'.basename($fileName),
	$_SERVER['SCRIPT_NAME']
);
$path = str_replace('/published', '', $path);
if(file_exists($path)){
	header("Content-type: $ctype");
	print file_get_contents($path);
}else{
	header("HTTP/1.0 404 Not Found;");
	header("Status: 404 Not Found;");
	echo <<<HTML
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL {$_SERVER['REQUEST_URI']} was not found on this server.</p>
<hr>
<address>{$_SERVER['SERVER_SIGNATURE']}</address>
</body></html>
HTML;
}

?>
