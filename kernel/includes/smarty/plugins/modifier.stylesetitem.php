<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     stylesetitem
 * Purpose:  returns path to a styleset item
 * -------------------------------------------------------------
 */
function smarty_modifier_stylesetitem($item, $template, $styleSet, $app_id = null)
{
	if ( !strlen($app_id) )
		$app_id = "common";

	return "../../../$app_id/html/$template/stylesets/$styleSet/$item";
}

?>