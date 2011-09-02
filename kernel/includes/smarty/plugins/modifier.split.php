<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     split
 * Purpose:  split a string by string
 * -------------------------------------------------------------
 */
function smarty_modifier_split($string, $separator)
{
	return explode( $separator, $string );
}

?>