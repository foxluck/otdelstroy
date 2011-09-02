<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_label
 * Purpose:  Outputs the form label
 * -------------------------------------------------------------
 */

function smarty_function_wbs_label( $params, &$smarty )
{
	extract( $params );

	$labelClass = isset($fieldName) && $fieldName == $smarty->get_template_vars('invalidField') ? "class=\"ErrorLabel\"" : null;

	if ( !isset($skipColon) || !$skipColon )
	{
		$len = strlen($text);
		if ( $len > 0 )
		{
			$lastChar = $text{$len-1};
			if ( $lastChar != ":" )
				$text .= ":";
		}
	}

	$result = "<label for=\"$for\" $labelClass>$text</label>";

	return $result;
}

?>