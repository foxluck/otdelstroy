<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_toolbarItem
 * Purpose:  Outputs a toolbar item
 * -------------------------------------------------------------
 */

function smarty_block_wbs_toolbarItem( $params, $content, &$smarty, &$repeat )
{
	global $toolbarItemId;

	$toolbarItemId ++;

	$result = null;

	if ( isset($content) )
	{
		extract($params);

		$itemClass = isset($align) && strtoupper($align) == "RIGHT" ? "class=\"Right\"" : null;

		$id = 'ToolbarItem'.$toolbarItemId;
		$result .= "<li class='TBItem' id=\"$id\" $itemClass>$content</li>\n";
		//$result .= "<script type=\"text/javascript\">LayoutManager.AddToolbarItemId(\"$id\");</script>";
	}

	return $result;
}

?>