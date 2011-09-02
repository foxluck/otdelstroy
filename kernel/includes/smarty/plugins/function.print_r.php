<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     print_r
 * Purpose:  print out a variable
 * -------------------------------------------------------------
 */
function smarty_function_print_r($params, &$smarty)
{
	extract($params);

	print_r( $var );
}

?>