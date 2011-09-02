<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     mime_encode
 * Purpose:  encodes string using base64 functions
 * -------------------------------------------------------------
 */
function smarty_modifier_mime_encode($string)
{
	return base64_encode($string);
}

?>