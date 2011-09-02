<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     htmlsafe
 * Purpose:  converts all html tags to html-safe sequiences
 * -------------------------------------------------------------
 */
function smarty_modifier_htmlsafe($str, $convertToHTML, $stripSlashes )
{
	if ( ini_get('magic_quotes_gpc') )
		if ( $stripSlashes )
			$str = stripSlashes( $str );

	if ( $convertToHTML ) {
		$str = str_replace( "\"", "&quot;", $str );
//		$str = str_replace( "'", "&apos;", $str );
		$str = str_replace( "\\", "&bsl;", $str );

		$str = str_replace( "<", "&lt;", $str );
		$str = str_replace( ">", "&gt;", $str );
		return nl2br( $str );
	} else
		return nl2br( $str );
}

?>