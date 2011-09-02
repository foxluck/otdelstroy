<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty strlen modifier plugin
 *
 * Type:     modifier<br>
 * Name:     strlen<br>
 * Purpose:  returns string length
 * @param string
 * @return integer
 */
function smarty_modifier_strlen($string)
{
	return strlen($string);
}

?>