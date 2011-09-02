<?php 

if (!isset($_GET['mod'])) {
	$_GET['mod'] = "users";
}

$app_id = "MW";

chdir("../UG/");

include("index.php");

?>