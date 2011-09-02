<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	//require_once( WBS_DIR."/published/DD/dd.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "CT";
	pageUserAuthorization( $SCR_ID, $DD_APP_ID, true, true);
	
	if (isset ($forUser))
		$currentUser = base64_decode($forUser);
	
	$widgetManager = getWidgetManager ();
	
	$widgetsSubtypes = getAppWidgetsSubtypes ($fapp, 0);
	
	$widgets = $widgetManager->getWidgetsForSubtypes($widgetsSubtypes, "WG_DESC ASC");
		
	$nodes = array ();
	foreach ($widgets as $cKey => $cWidget) {
		if (empty($cWidget["WG_DESC"]))
			$cWidget["WG_DESC"] = "widget #" . $cWidget["WG_ID"];
		$widgets[$cKey] = $cWidget;
		$link = "../../../WG/html/scripts/viewwidget.php?fapp=$fapp&WG_ID=" . base64_encode($cWidget["WG_ID"]);
		$nodes[] = array ("text" => $cWidget["WG_DESC"], "leaf" => true, "allowDrag" => false, "allowDrop" => false, "id" => "wg-" . $cWidget["WG_ID"], "link" => $link, "type" => "wg", "editable" => false);
	}
	
	
	print $json->encode($nodes);	
?>