<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     fileexists
 * Purpose:  see file_exists() in php manual
 * -------------------------------------------------------------
 */
function smarty_modifier_fileexists($string)
{
    return file_exists($string);
}

/* vim: set expandtab: */

?>