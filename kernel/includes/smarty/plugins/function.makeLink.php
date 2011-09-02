<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     makeLink
 * Purpose:  makes link tag if href is not null
 * -------------------------------------------------------------
 */

function smarty_function_makeLink( $params, &$this )
{
    extract($params);

	if ( !is_null($confirm) )
		$confirmTxt = sprintf( "onClick=\"return confirmLink('%s')\"", $confirm );

	if ( !is_null( $firsttext ) && !is_null( $sectext ) && is_null( $short ) )
		$confirmTxt = sprintf( "onClick=\"return twoconfirmLink('%s', '%s')\"", $firsttext, $sectext );

	if ( !is_null( $firsttext ) && !is_null( $sectext ) && !is_null( $short ) )
		$confirmTxt = sprintf( "onClick=\"return confirmLink('%s')\"", $sectext );

	if ( !is_null( $firsttext ) && !is_null( $numdoc ) && ( $numdoc==0 )   )
		$confirmTxt = sprintf( "onClick=\"return confirmLink('%s')\"", $firsttext );
	
	if ( !is_null( $onClick ) )
		$confirmTxt = sprintf( "onClick=\"%s\"", $onClick);

	if ( !is_null($href) && !is_null($fieldName) && !is_null($field) ) {
		$href = sprintf( "%s&%s=%s", $href, $field, base64_encode($fieldName) );
		return sprintf( "<a href=\"%s\" class=\"%s\" title=\"%s\" %s>%s</a>", $href, $class, $title, $confirmTxt, $text );
	}
	else if ( !is_null($href) ) 
		return sprintf( "<a href=\"%s\" class=\"%s\" title=\"%s\" %s>%s</a>", $href, $class, $title, $confirmTxt, $text );
	else
		return $text;
}


?>
