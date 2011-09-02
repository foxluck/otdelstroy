<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_splitterLeftPanel
 * Purpose:  Declares a splitter left panel
 * -------------------------------------------------------------
 */

function smarty_block_wbs_splitterLeftPanel( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{
		$panelStyle = null;
		if ( isset( $params['width'] ) )
		{
			$width = $params['width'].'px';
			$panelStyle = "width: $width";
		}
		if ( !empty($params['hide']))
		{
			$panelStyle .= "; display: none";
		}
		if ($panelStyle)
			$panelStyle = "style=\"$panelStyle\"";
		
		$result = "<td id='SplitterLeftPanelContainer' class=\"SplitterLeftPanelContainer\" $panelStyle>\n";
		$result .= $content;
		$result .= "\n</td>\n";
		$result .= "<td class=\"SplitterHandle\"><div>&nbsp;</div></td>\n";
	}

	return $result;
}

?>