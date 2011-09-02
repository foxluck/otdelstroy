<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_hr
 * Purpose:  Outputs the horizontal rule
 * -------------------------------------------------------------
 */

function smarty_function_wbs_hr( $params, &$smarty )
{
	extract( $params );

	$minWidth = isset($minWidth) ? "; min-width: $minWidth" : null;

	$result = "<div class=\"hr\" style=\"width: $width $minWidth\"></div>";

	return $result;
}

?>