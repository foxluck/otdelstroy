<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     count
 * Purpose:  Returns the number of elements in var
 * -------------------------------------------------------------
 */
function smarty_modifier_count($var)
{
	return count($var);
}

?>