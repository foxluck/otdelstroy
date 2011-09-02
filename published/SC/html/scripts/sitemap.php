<?php
if(file_exists('../../../sitemap.php')){
	chdir('../../../');
	include('sitemap.php');
}else{
	header("HTTP/1.0 404 Not Found");
}
?>