<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_scrollableArea
 * Purpose:  Creates the scrollable area
 * -------------------------------------------------------------
 */

function smarty_block_wbs_scrollableArea( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{
		extract($params);

		$width = isset($width) && strlen($width) ? $width : "200px";
		$height = isset($height) && strlen($height) ? $height : "200px";
		$id = isset($id) && strlen($id) ? "id=\"$id\"" : null;

		$result = "<div $id class=\"ScrollableArea $class\" style=\"width: $width; height: $height\">".$content."</div>";
	}

	return $result;
}

?>