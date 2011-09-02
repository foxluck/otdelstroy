<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     sureecho
 * Purpose:  returns string or non-breacking space if string is empty
 * -------------------------------------------------------------
 */
function smarty_modifier_sureecho($str )
{
	if ( !strlen($str) )
		return "&nbsp;";
	else
		return $str;
}

?>