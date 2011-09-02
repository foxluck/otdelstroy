<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     basename
 * Purpose:  see basename() in php manual
 * -------------------------------------------------------------
 */
function smarty_modifier_basename($string)
{
    return basename($string);
}

/* vim: set expandtab: */

?>