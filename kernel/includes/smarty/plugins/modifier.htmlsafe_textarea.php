<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     htmlsafe_textarea
 * Purpose:  prepares string to displaying in HTML text area
 * -------------------------------------------------------------
 */
function smarty_modifier_htmlsafe_textarea($str, $stripSlashes )
{
	if ( ini_get('magic_quotes_gpc') )
		if ( $stripSlashes )
			$str = stripSlashes( $str );

	$str = str_replace( "&quot;", "\"", $str );
	$str = str_replace( "&apos;", "'", $str );
	$str = str_replace( "&bsl;", "\\", $str );
	$str = str_replace( ">", "&gt;", $str );
	$str = str_replace( "<", "&lt;", $str );

	return $str;
}

?>