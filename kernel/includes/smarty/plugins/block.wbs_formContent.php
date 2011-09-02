<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_formContent
 * Purpose:  Adds the form content markup
 * -------------------------------------------------------------
 */

function smarty_block_wbs_formContent( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{
		extract( $params );

		$result = "<tr class=\"FormContent\">\n";
		$result .= "	<td>\n";
		$result .= "		<div>\n";

		if ( !( isset($suppressTable) && $suppressTable ) )
			$result .= "			<table class=\"FormLayout\">\n";

		$result .= $content;

		if ( !( isset($suppressTable) && $suppressTable ) )
			$result .= "			</table>\n";

		$result .= "		</div>\n";
		$result .= "	</td>\n";
		$result .= "</tr>\n";
	}

	return $result;
}

?>