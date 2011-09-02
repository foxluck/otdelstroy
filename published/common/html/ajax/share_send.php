<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "CT";
	$ajaxRes = array ("success" => false, "errorStr" => "no result");

	$appId = (!empty($fapp)) ? $fapp : "DD";
	pageUserAuthorization( $SCR_ID, $appId, true, true );
	$kernelStrings = $loc_str[$language];
	
	do {
		$widgetManager = getWidgetManager ();
		if (PEAR::isError ($error = $widgetManager))
			break;
		
		if (empty($wgId)) {
			$error = PEAR::raiseError("Empty WG_ID");
			break;
		}
		$wgId = base64_decode($wgId);
		
		$widgetData = $widgetManager->get($wgId);
		if (PEAR::isError($error = $widgetData))
			break;
		
		$factory = WidgetTypeFactory::getInstance ();
		
		$typeObj = $factory->getWidgetType($widgetData["WT_ID"]);	
		if (PEAR::isError($error == $typeObj))
			break;
		
		$subtypeObj = &$typeObj->subtypes[$widgetData["WST_ID"]];
		
		$sendData = prepareArrayToStore($sendData);
		$result = $subtypeObj->sendToUsers($widgetData, $sendData);
		if (PEAR::isError($error = $result))
			break;
		$resultStr = sprintf($kernelStrings["shsd_message_sended"], $result);
					
	} while (false);
	
	
	if (PEAR::isError($error)) {
		$ajaxRes["success"] = false;
		$ajaxRes["errorStr"] = $error->getMessage ();
	} else {
		$ajaxRes["success"] = true;
		$ajaxRes["resultStr"] = $resultStr;
	}	
	
	print $json->encode ($ajaxRes);
?>