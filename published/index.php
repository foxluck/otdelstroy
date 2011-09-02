<?php

	include_once("../system/init.php");
		
	// Load screens
	$screens = CurrentUser::getInstance()->getAvailableScreens();
	
	$mwScreen = isset($screens['MW']) ? $screens['MW'] : false;
	$aaScreen = isset($screens['AA']) ? $screens['AA'] : false; 
	
	// Check logo exists
	$logoFilename = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("AA", "logo.gif");
	$logoExists = file_exists($logoFilename);
	$logoTime = ($logoExists) ? filemtime($logoFilename) : null;
	
	// Load viewsettings
	$dbkeyObj = Wbs::getDbkeyObj();
	$viewsettings["showLogo"] = ($dbkeyObj->getAdvancedParam("show_company_top") == "yes") && $logoExists;
	$viewsettings["showCompanyName"] = ($dbkeyObj->getAdvancedParam("show_company_name_top") != "no");
	$viewsettings["theme"] = (string)($dbkeyObj->getAdvancedParam("theme"));
	if (!$viewsettings["theme"]) {
		$viewsettings["theme"] = ($viewsettings["showLogo"]) ? "1albino" : "darkblue";
	}
	// Get Company Name
	$companyName = (string)($dbkeyObj->getAdvancedParam("company_name"));
	if (!$companyName) {
		$companyName = (Wbs::isHosted()) ? Env::getSubdomain() : "WebAsyst";
	}
	
	$currentPage = User::getStartPage(array_keys($screens));
	
	
	if (Env::Request("app")) {
		$app = preg_replace("/[^a-z\/]/i", '', Env::Request("app"));
		$currentPage = array ("app" => htmlspecialchars($app));	
	}
	if (Env::Request("url")) {
		$currentPage = array("app" => "", "url" => Env::Request("url"));
		if (isset($_POST['LMI_PAYMENT_NO']))
		    $currentPage["url"].="?LMI_PAYMENT_NO=".$_POST['LMI_PAYMENT_NO'].
					"&LMI_SYS_TRANS_NO=".$_POST['LMI_SYS_TRANS_NO'].
					"&LMI_SYS_INVS_NO=".$_POST['LMI_SYS_INVS_NO'].
					"&LMI_SYS_TRANS_DATE=".$_POST['LMI_SYS_TRANS_DATE'].
					"&wm_mtc_id=".$_POST['wm_mtc_id'].
					"&orderID=".$_POST['orderID'];
		elseif (isset($_POST['INTERNAL_PAYMENT'])) {
	    	$currentPage["url"].="?orderID=".$_POST['orderID'];
		}
	}
	
	
	// Read account params
	$accountIsUnconfirmed = sizeof($dbkeyObj->getXmlParam("//LOGINHASH[@UNCONFIRMED=1]")) > 0;
	$needBillingAlert = $dbkeyObj->needBillingAlert();
	
	$lang = substr(User::getLang(), 0, 2);
	$view = new WbsSmarty(WBS_DIR . "published/common/templates", false, $lang);
	$view->assign ("screens", $screens);
	$view->assign ("currentPage", $currentPage);
	$view->assign ("logoTime", $logoTime);
	$view->assign ("viewsettings", $viewsettings);
	$view->assign ("companyName", $companyName);
	$view->assign ("controlPanelScreen", $aaScreen);
	$view->assign ("myAccountScreen", $mwScreen);
	$view->assign ('user_id', User::getId());
	$view->assign ('user_name', User::getName());
	$view->assign ("accountIsUnconfirmed", $accountIsUnconfirmed);
	$view->assign ("needBillingAlert", $needBillingAlert);
	$view->assign ('url', array(
		'common' => Url::get('/common/'),
		'published' => Url::get('/'),
		'templates' => Url::get('/common/templates/')
	));
		
	$view->display ("index.html");	
?> 