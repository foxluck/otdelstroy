<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_formButtonsPanel
 * Purpose:  Adds the form buttons panel markup
 * -------------------------------------------------------------
 */

function smarty_block_wbs_formButtonsPanel( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{
		extract( $params );

		$result = "<tr class=\"FormButtons\">\n";
		$result .= "	<td>\n";
		$result .= $content;
		$result .= "	</td>\n";
		$result .= "</tr>\n";
	}

	return $result;
}

?>