<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     inarray
 * Purpose:  PHP in_array() function implementation
 * -------------------------------------------------------------
 */

function smarty_function_inarray( $params, &$smarty )
{
    extract($params);

	$result = in_array($val, $array);

	if ( !empty($var) ) {
	    $smarty->assign($var, $result);
    }
}

?>
