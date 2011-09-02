<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     separate
 * Purpose:  outputs array of strings separated with commas
 * -------------------------------------------------------------
 */

function smarty_function_separate( $params, &$smarty )
{
    extract($params);

	return implode( ", ", $params );
}

?>
