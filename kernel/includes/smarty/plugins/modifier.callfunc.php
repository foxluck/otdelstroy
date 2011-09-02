<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty callfunc modifier plugin
 *
 * Type:     modifier<br>
 * Name:     callfunc<br>
 * Date:     Feb 24, 2003
 * Purpose:  Handle variable by function
 * Input:    string function name
 * Example:  {$var|callfunc:"base64_encode"}
 */
function smarty_modifier_callfunc($Value, $Function){

	return call_user_func_array($Function, array($Value));
}

/* vim: set expandtab: */

?>