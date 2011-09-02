<?php

if ((!isset($_GET['callback']) || !isset($_GET['code'])) && !isset($_POST['code'])) {
    die("");
} 

if (isset($_POST['code'])) {
    $code = $_POST['code'];
    @session_start();
    echo (int)($code == $_SESSION['WG_IVAL']); 
} else {
	$callback = $_GET['callback'];
	$code = $_GET['code'];
	if (isset($_GET['s']) && $_GET['s']) {
		session_id($_GET['s']);
	} 
	@session_start();
	if ($code === '0') {
		echo $callback."('".session_id()."')";	
	} else {
		echo $callback.'('.(int)($code === $_SESSION['WG_IVAL']).')';
	}
}
?>
