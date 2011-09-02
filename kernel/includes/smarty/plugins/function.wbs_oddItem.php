<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_oddItem
 * Purpose:  Outputs a class name for odd table rows
 * -------------------------------------------------------------
 */

function smarty_function_wbs_oddItem( $params, &$smarty )
{
	extract( $params );

	if ( $invert )
		$index++;

	return ($index % 2) ? "Odd" : null;
}

?>