<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     in_array
 * Purpose:  Checks if a value exists in an array 
 * -------------------------------------------------------------
 */
function smarty_function_in_array($params, &$smarty)
{
    extract($params);

	$result = in_array( $value, $arr );

    $smarty->assign($assign, $result);
}

/* vim: set expandtab: */

?>