<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     makeSortLink
 * Purpose:  sorting support 
 * -------------------------------------------------------------
 */

function smarty_function_makeSortLink( $params, &$this )
{
    extract($params);

	$nameLen = strlen( $fieldName );

	$curStatData = explode( " ", trim($curStatus) );

	$statusFieldPart = $curStatData[0];
	$statusOrderPart = $curStatData[1];

	if ( $statusFieldPart == $fieldName ) {
		if ( $statusOrderPart == "asc" )
			$statusOrderPart = "desc";
		else 
			$statusOrderPart = "asc";
	} else
			$statusOrderPart = "asc";
			

	return sprintf( "%s&sorting=%s", $URL, base64_encode("$fieldName $statusOrderPart") );
}


?>
