<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_checkboxStatus
 * Purpose:  outputs "checked" string if parameters are matches
 * -------------------------------------------------------------
 */

function smarty_function_wbs_checkboxStatus( $params, &$this )
{
	extract($params);

	return $val == $true_val ? "checked=\"checked\"" : null;
}

?>
