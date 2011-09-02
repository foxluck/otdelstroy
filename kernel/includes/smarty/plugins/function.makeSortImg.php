<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     makeSortImg
 * Purpose:  sorting support 
 * -------------------------------------------------------------
 */

function smarty_function_makeSortImg( $params, &$this )
{
    extract($params);

	$nameLen = strlen( $fieldName );

	$curStatData = explode( " ", trim($curStatus) );

	$statusFieldPart = $curStatData[0];
	$statusOrderPart = $curStatData[1];

	if ( $statusFieldPart == $fieldName ) {
		if ( $statusOrderPart == "asc" )
			return sprintf( "<img src=\"%s\">", $ascImg );
		else 
			return sprintf( "<img src=\"%s\">", $descImg );
	} else
			return null;
}

?>
