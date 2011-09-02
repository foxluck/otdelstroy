<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_formCaption
 * Purpose:  Outputs the form caption
 * -------------------------------------------------------------
 */

function smarty_function_wbs_formCaption( $params, &$smarty )
{
	extract( $params );

	$result = "<span class=\"FormCaption\">".$params['text']."</span>";

	return $result;
}

?>