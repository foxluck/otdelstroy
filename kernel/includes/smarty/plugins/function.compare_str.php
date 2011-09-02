<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     compare_str
 * Purpose:  Binary safe string comparison
 * -------------------------------------------------------------
 */
function smarty_function_compare_str($params, &$smarty)
{
    extract($params);

    $value = strcmp( $arg1, $arg2 );

    $smarty->assign($assign, $value);
}

/* vim: set expandtab: */

?>