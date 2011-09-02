<?php

require_once '../common/captcha/ivalidator.class.php';

if (isset($_GET['s']) && $_GET['s']) {
	session_id($_GET['s']);
}
@session_start();
$i = new IValidator();
$i->AppId = 'WG';
$i->generateImage();

?>
