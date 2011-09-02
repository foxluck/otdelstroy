<?php 

$_GET['mod'] = "contacts";
$_GET['act'] = "personal";

define('PUBLIC_AUTHORIZE', true);
$app_id = 'CM';

chdir("UG/");
include("index.php");

?>