<?php 

$_GET['mod'] = "users";
$_GET['act'] = "invite";
define('PUBLIC_AUTHORIZE', true);
$app_id = 'UG';
chdir("UG/");
include("index.php");

?>