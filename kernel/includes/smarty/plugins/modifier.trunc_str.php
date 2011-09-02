<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     trunc_str
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and 
 *           appending the $etc string.
 * -------------------------------------------------------------
 */
function smarty_modifier_trunc_str($string, $length = 80, $etc = '...',
                                  $break_words = false)
{
    if ($length == 0)
        return '';

	if ( strlen( $string ) > $length )
		return substr( $string, 0, $length ).$etc;
	else
		return $string;
}

/* vim: set expandtab: */

?>