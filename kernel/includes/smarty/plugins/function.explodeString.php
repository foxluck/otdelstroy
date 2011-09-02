<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     explodeString
 * Purpose:  explodes string into parts using PHP explode function
 * -------------------------------------------------------------
 */
function smarty_function_explodeString($params, &$smarty)
{
    extract($params);

	$parts = explode( $separator, $str );

    $smarty->assign($var, $parts);
}
?>