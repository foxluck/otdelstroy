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
				
		$widgetManager->delete($wgId);
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