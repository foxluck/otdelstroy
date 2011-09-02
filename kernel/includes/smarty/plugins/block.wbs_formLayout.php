<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_formLayout
 * Purpose:  Declares the form layout 
 * -------------------------------------------------------------
 */

function smarty_block_wbs_formLayout( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{
		extract( $params );

		$result = "<table class=\"FormBlock\">\n";
		$result .= "	<tr class=\"FormCaption\">\n";
		$result .= "		<td><span>$caption</span></td>\n";
		$result .= "	</tr>\n";
		$result .= $content;
		$result .= "	<tr class=\"FormFooter\">\n";
		$result .= "		<td>&nbsp;</td>\n";
		$result .= "	</tr>\n";
		$result .= "</table>\n";
	}

	return $result;
}

?>