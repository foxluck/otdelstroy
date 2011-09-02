<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     substring
 * Purpose:  return part of a string 
 * -------------------------------------------------------------
 */
function smarty_modifier_substring($string, $start, $length)
{
	return substr( $string, $start, $length );
}

?>