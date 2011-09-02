<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     switchedOutput
 * Purpose:  outputs first or second parameter, according $val and $true_val parameters coincidence
 * -------------------------------------------------------------
 */

function smarty_function_switchedOutput( $params, &$this )
{
    extract($params);

	if ( $val == $true_val )
		return $str1;
	else
		return $str2;
}

?>
