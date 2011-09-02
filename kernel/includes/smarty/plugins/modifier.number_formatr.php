<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     number_format
 * Purpose:  PHP number_format analog
 * -------------------------------------------------------------
 */
function smarty_modifier_number_format($number, $decimals, $dec_point, $thousands_sep )
{
	return number_format ( $number, $decimals, $dec_point, $thousands_sep );
}

?>