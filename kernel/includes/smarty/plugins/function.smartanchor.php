<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     smart anchor
 * Purpose:  returns <a> tag if $href parameter is not empty
 * -------------------------------------------------------------
 */

function smarty_function_smartanchor( $params, &$this )
{
    extract($params);

	return ( strlen( $href ) ) ? sprintf( "<a href=%s>", $href ) : null;
}

?>
