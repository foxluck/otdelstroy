<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_iconButton
 * Purpose:  Outputs the icon button
 * -------------------------------------------------------------
 */

function smarty_function_wbs_iconButton( $params, &$smarty  = false)
{
	extract( $params );

	if ( !isset($name) && !isset($link) )
		$href = "javascript://";
	else
		$href = isset($link) && strlen($link) ? $link : "javascript:processTextButton('$name')";

	$onClick = isset($onClick) && strlen($onClick) ? "onClick=\"$onClick\"" : null;
	$hint = isset($hint) && strlen($hint) ? $hint : null;

	$result = "<a class=\"IconButton\" href=\"$href\" $onClick><span class=\"$iconClass\" title=\"$hint\">&nbsp;</span></a>";

	return $result;
}

?>