<?php
	$kernelStrings = &$loc_str[$language];
	$wgStrings = &$wg_loc_str[$language];
	$dataManager = new WidgetManager ($kernelStrings, $wgStrings);
	
	$factory = WidgetTypeFactory::getInstance ();
	
	if (!empty($_GET["code"])) {
		$widgetData = $dataManager->getByFprint ($_GET["code"]);
		
		if (!$widgetData["WT_ID"])
			return;
		
		$language = $widgetData["WG_LANG"];
		$typeObj = $factory->getWidgetType($widgetData["WT_ID"]);	
	} elseif ($mode == "preview" && $typeId && $subtypeId) {
		if (@$_GET["language"])
			$language = $_GET["language"];
		
		$typeObj = $factory->getWidgetType($typeId);	
		$widgetData["WST_ID"] = $subtypeId;
		
		$subtype = &$typeObj->subtypes[$subtypeId];
		$fieldsData = $subtype->getFieldsData ();
		foreach ($fieldsData as $cField => $cRow) {
			$widgetData["params"][$cField] = (!empty($cRow["default"])) ? $cRow["default"] : "";
		}
	}
	
	
?>