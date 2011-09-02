<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     makeSortHeader
 * Purpose:  sorting support 
 * -------------------------------------------------------------
 */

function smarty_function_makeSortHeader( $params, &$this )
{
    extract($params);

	$nameLen = strlen( $fieldName );

	$curStatData = explode( " ", trim($curStatus) );

	$statusFieldPart = $curStatData[0];
	$statusOrderPart = $curStatData[1];

	if ( $statusFieldPart == $fieldName ) {
		if ( $statusOrderPart == "asc" ) {
			$statusOrderPart = "desc";
			$img = sprintf( "<img src=\"%s\">", $ascImg );
		} else {
			$statusOrderPart = "asc";
			$img =  sprintf( "<img src=\"%s\">", $descImg );
		}
	} else
			$statusOrderPart = "asc";
			

	return sprintf( "<nobr><a href=\"%s&sorting=%s\">%s</a> %s</nobr>", $URL, base64_encode("$fieldName $statusOrderPart"), $text, $img );
}


?>
