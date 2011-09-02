<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     substr
 * Purpose:  return part of a string 
 * -------------------------------------------------------------
 */
function smarty_modifier_substr($string, $start, $count = null)
{
	if ( !is_null($count) )
		return substr( $string, $start, $count );
	else
		return substr( $string, $start );
}

?>