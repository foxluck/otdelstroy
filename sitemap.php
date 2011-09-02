<?php
if(file_exists('published/sitemap.php')){
	include('published/sitemap.php');
}else{
	header("HTTP/1.0 404 Not Found");
}
?>