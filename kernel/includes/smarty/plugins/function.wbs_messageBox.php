<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_messageBox
 * Purpose:  outputs the message box
 * -------------------------------------------------------------
 */

function smarty_function_wbs_messageBox( $params, &$this )
{
	extract($params);

	$mbClass = null;

	switch ( $type ) {
		case 0 : $mbClass = "MessageBoxStop"; break;
		case 1 : $mbClass = "MessageBoxInformation"; break;
		case 2 : $mbClass = "MessageBoxExclamation"; break;
	}

	$result = "<dl class=\"MessageBox $mbClass\">\n";
	$result .= "	<dt>$message</dt>\n";
	$result .= "	<dd>$note</dd>\n";
	$result .= "</dl>\n";

	return $result;
}

?>