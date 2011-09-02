<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_splitterScrollableArea
 * Purpose:  Declares a splitter scrollable area
 * -------------------------------------------------------------
 */

function smarty_block_wbs_splitterScrollableArea( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{
		$ownerPanel = $smarty->_tag_stack[count($smarty->_tag_stack)-2][0];
		
		$id = $ownerPanel == "wbs_splitterLeftPanel" ? "SplitterLeftPanelContent" : "SplitterRightPanelContent";
		$contentId = $ownerPanel == "wbs_splitterLeftPanel" ? "SplitterLeftScrollableContent" : "SplitterRightScrollableContent";

		$panelStyle = null;
		if ( isset( $params['width'] ) )
		{
			$width = $params['width'].'px';
			$panelStyle = "style=\"width: $width\"";
		}
 
		$result = "<div class=\"SplitterScrollableArea\" $panelStyle id=\"$id\"><div id=\"$contentId\">\n";
		$result .= $content;
		$result .= "\n</div></div>";
	}

	return $result;
}

?>