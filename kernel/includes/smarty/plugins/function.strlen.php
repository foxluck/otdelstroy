<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     strlen
 * Purpose:  returns string length
 * -------------------------------------------------------------
 */

function smarty_function_strlen( $params, &$this )
{
    extract($params);

	return strlen($string);
}


?>
