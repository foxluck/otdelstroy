<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_buttonSet
 * Purpose:  Adds the button set markup
 * -------------------------------------------------------------
 */

function smarty_block_wbs_buttonSet( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{
		$style = null;
		if ( isset($params['width']) && strlen($params['width']) )
			$style = sprintf( "style=\"width: %s\"", $params['width'] );

		$result = "<ul class=\"ButtonSet\" $style>";
		$result .= $content;
		$result .= "</ul>";
	}

	return $result;
}

?>