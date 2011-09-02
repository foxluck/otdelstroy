<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     mime_decode
 * Purpose:  decodes and outputs string using base64 functions
 * -------------------------------------------------------------
 */

function smarty_function_mime_decode( $params, &$this )
{
    extract($params);

	echo base64_decode($string);
}


?>
