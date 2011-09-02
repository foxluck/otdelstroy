<?php

include "../../../../system/init.php";

$user_id = Env::Get('uid', Env::TYPE_STRING, User::getId());
$user_info = User::getInfo($user_id);

$contact_type = new ContactType($user_info['CT_ID']);
$field = $contact_type->getPhotoField(false, true);

// @todo: Replace C_PHOTO to first image field in contact fields 
if ($field && isset($user_info[$field]) && $user_info[$field]) {
	$params = preg_replace('!^[^\?]*\?(.*)$!si', '$1', $user_info[$field]);
	$params = explode("&", $params);
	$name = $value = false;
	foreach ($params as $param) {
		list($name, $value) = explode("=", $param, 2);
		$_GET[$name] = $value;
		$_GET['size'] = 96;
	}
	include "thumb.php";
} else {
	header('Content-type: image/gif');
	readfile(WBS_DIR."published/UG/img/empty-contact".$user_info['CT_ID'].".gif");
}

?>