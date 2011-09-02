<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     count
 * Purpose:  Returns the number of elements in var
 * -------------------------------------------------------------
 */
function smarty_modifier_repeat($string, $n)
{
	return str_repeat($string, $n);
}

?>