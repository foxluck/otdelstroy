<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_splitterRightPanel
 * Purpose:  Declares a splitter right panel
 * -------------------------------------------------------------
 */

function smarty_block_wbs_splitterRightPanel( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{
		$result = "<td id='SplitterRightPanelContainer' class=\"SplitterRightPanelContainer\">\n";
		$result .= $content;
		$result .= "\n</td>\n";
	}

	return $result;
}

?>