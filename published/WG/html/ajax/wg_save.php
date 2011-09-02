<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	//require_once( WBS_DIR."/published/DD/dd.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "CT";
	$ajaxRes = array ("success" => false, "errorStr" => "no result");

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, true);
	
	do {
		$widgetManager = getWidgetManager ();
		if (PEAR::isError ($error = $widgetManager))
			break;
		
		if (empty($wgId)) {
			$error = PEAR::raiseError("Empty WG_ID");
			break;
		}
		$wgId = base64_decode($wgId);
		
		//if (empty($fields)) {
		//	$error = PEAR::raiseError("Empty param name");
		//	break;
		//}
		
		//$error = PEAR::raiseError("wgId");
		//	break;
		
		
		$fields = prepareArrayToStore(@$_POST["fields"]);
		foreach ($fields as $cKey => $cValue) {
			if (!is_array ($cValue))
				$fields[$cKey] = strip_tags($cValue);
		}
		$updateParams = array ();
		if ($fields["WG_DESC"])
			$updateParams["WG_DESC"] = $fields["WG_DESC"];
		if ($fields["WG_LANG"])
			$updateParams["WG_LANG"] = $fields["WG_LANG"];
		if ($updateParams) {
			$widgetManager->update ($wgId, $updateParams, false);
		}
		
		//if (empty($fields))
			//$fields = $_POST;
		
		//$fields = (isset($_POST["fields"])) ? $_POST["fields"] : $_POST;
		$res = $widgetManager->setWidgetParams($wgId, $fields, true);
		if (PEAR::isError($error = $res))
			break;
		
		$widgetData = $widgetManager->get($wgId);
		$factory = WidgetTypeFactory::getInstance ();
		$typeObj = $factory->getWidgetType($widgetData["WT_ID"]);
		if (PEAR::isError($error = $typeObj))
			break;
		$subtypeObj = $typeObj->subtypes[$widgetData["WST_ID"]];
		if (!$subtypeObj)
			break;
		$widgetEmbInfo = $typeObj->getWidgetEmbInfo ($widgetData, $widgetData["WST_ID"]);
		$ajaxRes["embCode"] = html_entity_decode($widgetEmbInfo["code"]);
		$ajaxRes["WG_DESC"] = $widgetData["WG_DESC"];
		
	} while (false);
	
	
	
	if (PEAR::isError($error)) {
		$ajaxRes["success"] = false;
		$ajaxRes["errorStr"] = $error->getMessage ();
	} else {
		$ajaxRes["success"] = true;
		$ajaxRes["wgId"] = $wgId;
	}
	
	print $json->encode ($ajaxRes);	
?>