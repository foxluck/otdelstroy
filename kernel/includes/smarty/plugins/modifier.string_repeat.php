<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty string_repeat modifier plugin
 *
 * Type:     modifier<br>
 * Name:     string_repeat<br>
 * Purpose:  repeat string n-times
 * @param string
 * @param int
 * @return string
 */
function smarty_modifier_string_repeat($string, $n)
{
    return str_repeat($string, $n);
}

/* vim: set expandtab: */

?>
