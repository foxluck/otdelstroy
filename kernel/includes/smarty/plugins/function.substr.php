<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     substr
 * Purpose:  return part of a string 
 * -------------------------------------------------------------
 */

function smarty_function_substr( $params, &$this )
{
    extract($params);

	return mb_substr( $string, $start,null,'UTF-8' );
}

?>