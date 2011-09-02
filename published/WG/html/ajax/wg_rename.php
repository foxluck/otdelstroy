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
		
		$wgId = substr($folderID, 5);
		
		if (empty($wgId) || empty($newName)) {
			$error = PEAR::raiseError("Empty wgId or newName");
			break;
		}
				
		$params = prepareArrayToStore(array("WG_DESC" => $newName));
		$res = $widgetManager->update($wgId, $params, false);
		if (PEAR::isError($error = $res))
			break;
		
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