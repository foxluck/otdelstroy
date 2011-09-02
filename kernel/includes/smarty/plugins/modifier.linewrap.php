<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     wraps each text line
 * Purpose:  returns string or non-breacking space if string is empty
 * -------------------------------------------------------------
 */
function smarty_modifier_linewrap($str, $lineWrapChar="<br>", $lineWidth=80, $wrapChar="<br>")
{
	$lines = explode( $lineWrapChar, $str );

	$result = "";

	foreach ( $lines as $line ) {
		$lineParts = explode( " ", $line );

		$newLineParts = array();
		foreach ( $lineParts as $part ) {
			if ( strlen($part) > $lineWidth )
				$part = wordwrap( $part, $lineWidth, $wrapChar, true );

			$newLineParts[] = $part;
		}

		$result .= implode( " ", $newLineParts )."\n";
	}

	return $result;
}

?>