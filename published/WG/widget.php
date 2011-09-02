<?php
	try {
		if (!isset($_GET['q'])) {
			throw new Exception("Incorrect URL");
		}
		$q = explode("-", $_GET['q']);
		if (count($q) == 1) {
			if (!isset($_SERVER['HTTP_DBKEY'])) {
				throw new Exception("Incorrect URL");
			} else {
				$_GET['code'] = $q[0];
			}
		} else {
			$_REQUEST['DB_KEY'] = $_GET['DB_KEY'] = $q[0];
			$_GET['code'] = $q[1];
		}
	} catch (Exception $e) {
		echo $e->getMessage();
		exit;
	}
	
	define('PUBLIC_AUTHORIZE', true);
	include "../../system/init.php";
	
	$code = Env::Get('code');
	$widgets_model = new WidgetsModel();
	$info = $widgets_model->getByCode($code);

	if (!$info) {
		echo "Widget not found";
		exit;
	}
	
	$class_name = $info['WT_ID'].'WidgetController';
	if (class_exists($class_name)) {
	    include(WBS_PUBLISHED_DIR.$info['WT_ID'].'/config/autoload.php');
	    User::setApp($info['WT_ID']);
		$controller = new $class_name($info['WG_ID']);
		$controller->exec();
	} else {
		echo "Unknown type of the widget";
		exit;
	}
	
?>