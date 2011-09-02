<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     base64_decode
 * Purpose:  Decodes data encoded with MIME base64

 * -------------------------------------------------------------
 */
function smarty_modifier_base64_decode($string)
{
	return base64_decode( $string );
}

?>