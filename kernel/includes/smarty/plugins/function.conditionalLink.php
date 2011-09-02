<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     conditionalLink
 * Purpose:  makes link paramter if href is not null
 * -------------------------------------------------------------
 */

function smarty_function_conditionalLink( $params, &$this )
{
	extract($params);

	if ( !is_null($confirm) )
		$onClickTxt = sprintf( "onClick=\"return confirmLink('%s')\"", $confirm );
	
	if ( !empty($onClick) )
		$onClickTxt = sprintf( "onClick=\"%s\"", $onClick);

	if ( !isset( $class ) )
		$class = null;

	if ( !is_null($href) )
		if ( !isset($spanClass) )
			return sprintf( "<a href=\"%s\" class=\"%s\" %s title=\"%s\">%s</a>", $href, $class, $onClickTxt, $title, $text );
		else
			return sprintf( "<span class=\"%s\"><a href=\"%s\" class=\"%s\" %s title=\"%s\">%s</a></span>", $spanClass, $href, $class, $onClickTxt, $title, $text );
	else
		return $text;
}

?>
