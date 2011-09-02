<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	//require_once( WBS_DIR."/published/DD/dd.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "CT";
	pageUserAuthorization( $SCR_ID, $DD_APP_ID, true, true);
	
	$widgetManager = getWidgetManager ();
	
	$widgetsSubtypes = getAppWidgetsSubtypes ($fapp);
	
	$nodes = array ();
	foreach ($widgetsSubtypes as $cKey => $cSubtype) {
		$nodes[] = array("type" => $cSubtype->type->id, "subtype" => $cSubtype->id, "name"=> $cSubtype->name, "desc" => $cSubtype->desc, "onlyForFolders" => $cSubtype->onlyForFolders);
	}
	print $json->encode(array("success" => true, "data" => $nodes));	
?>