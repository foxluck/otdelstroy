<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_statusImage
 * Purpose:  outputs an image indicating some status value
 * -------------------------------------------------------------
 */

function smarty_function_wbs_statusImage( $params, &$this )
{
	extract($params);

	$alignClass = null;
	if ( isset($align) )
		switch ( $align )
		{
			case "right" : $alignClass = "AlignRight"; break;
			case "center" : $alignClass = "AlignCenter";
		}

	if ( isset($status) ) {
		if ( $status )
			return "<span class=\"CheckedImg $alignClass\"/>";
		else
			return "<span class=\"UnCheckedImg $alignClass\"/>";
	} else {
		if ( $value  == $onValue )
			return "<span class=\"CheckedImg $alignClass\"/>";
		else
			return "<span class=\"UnCheckedImg $alignClass\"/>";
	}
}

?>
