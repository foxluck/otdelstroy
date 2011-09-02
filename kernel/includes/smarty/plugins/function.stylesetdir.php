<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     stylesetdir
 * Purpose:  returns template stylesets directory
 * -------------------------------------------------------------
 */

function smarty_function_stylesetdir( $params, &$this )
{
    extract($params);

	return "../../../common/html/$template/stylesets/$styleSet/$item";
}

?>
