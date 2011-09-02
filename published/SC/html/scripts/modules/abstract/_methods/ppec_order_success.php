<?php
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	include(DIR_INCLUDES.'/ppec_order_success.php');
?>