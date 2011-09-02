<?php

function smarty_function_wbs_foldersTreeNodes( $params, &$smarty )
{
	extract ($params);
	
	$nodes = wbs_foldersTreeNodes_getNodes($folders, $hierarchy, 0, $avIconCls, $unavIconCls);
	
	$nodesStrings = array ();
	foreach ($nodes as $cNode) {
		$nodesStrings[] = "[" .
			"'" . $cNode["id"] . "', " . 
			"'" . $cNode["parentId"] . "', " . 
			"'" . preg_replace("%(?<!\\\\)'%", "\\'", $cNode["text"]) . "', " . 
			"'" . $cNode["iconCls"] . "', " . 
			$cNode["editable"] . "," . 
			"'" . $cNode["link"] . "'," .
			"'" . $cNode["encId"] . "'," .
			$cNode["canRename"] . "," .
			$cNode["canDrag"] . "," .
			$cNode["canDrop"] . "," .
			$cNode["specialStatus"] .
		"]";
	}
	return join (",\n ", $nodesStrings);
}

function wbs_foldersTreeNodes_getNodes ($folders, $hierarchy, $parentId, $avIconCls, $unavIconCls) {
	$nodes = array ();
	if (!$hierarchy) {
		return $nodes;
	}
	foreach ($hierarchy as $level => $data) {
		$folderData = $folders[$level];
		
		if ($folderData->DF_SPECIALSTATUS >= 2) {
			$iconCls = "system-folder";
			$editable = false;
		}
		else {
			if ($folderData->RIGHT > 1) {
				$iconCls = (!empty($avIconCls)) ? $avIconCls : "my-folder";
				$editable = true;
			} else {
				$iconCls = (!empty($unavIconCls)) ? $avIconCls : "gray-folder";
				$editable = false;
			}
		}
		
		if ($folderData->ICON_CLS)
			$iconCls = $folderData->ICON_CLS;
		
		$fid = ($folderData->ID) ?  $folderData->ID : "AVAILABLEFOLDERS";
		$canRename = ($folderData->RIGHT >= 7 && $folderData->DF_SPECIALSTATUS <=	1);
		$canDrag = ($folderData->RIGHT >= 7 && $folderData->DF_SPECIALSTATUS <=	2);
		$canDrop = ($folderData->RIGHT >= 7 && $folderData->DF_SPECIALSTATUS <=	2);
		
		if ($fid == "AVAILABLEFOLDERS") {
			$canRename = false;
			$editable = false;
		}
		
		$nodes[] = array (
			"id" => $fid, 
			"parentId" => $parentId,
			"text" => $folderData->NAME,
			"iconCls" => $iconCls,
			"editable" => $editable ? "true" : "false",
			"link" => $folderData->ROW_URL,
			"encId" => $folderData->ENC_ID,
			"canRename" => $canRename ? "true" : "false",
			"canDrag" => $canDrag ? "true" : "false",
			"canDrop" => $canDrop ? "true" : "false",
			"specialStatus" => $folderData->DF_SPECIALSTATUS ? $folderData->DF_SPECIALSTATUS : 0
		);
		
		$nodes = array_merge($nodes, wbs_foldersTreeNodes_getNodes($folders, $data, $fid, $avIconCls, $unavIconCls));
	}
	return $nodes;
}

?>