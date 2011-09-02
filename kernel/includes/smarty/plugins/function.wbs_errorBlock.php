<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_errorBlock
 * Purpose:  Outputs the error block
 * -------------------------------------------------------------
 */

function smarty_function_wbs_errorBlock( $params, &$smarty )
{
	extract( $params );

	$result = "";
	$errorStr = isset($errorStr) ? $errorStr : $smarty->get_template_vars('errorStr');

	if ( !empty($errorStr) ) {
		$id = !isset($id) ? "ErrorBlock" : $id;

		$result = "<span class=\"ErrorBlock\" id=\"$id\">\n";
		$result .= "<span>\n";
		$result .= $errorStr;
		$result .= "</span>\n";
		$result .= "</span>\n";
	}
	
	$messageStr = isset($messageStr) ? $messageStr : $smarty->get_template_vars('messageStr');
	if ( !empty($messageStr) ) {
		$id = !isset($id) ? "ErrorBlock" : $id;

		$result = "<span class=\"MessageBlock\" id=\"$id\">\n";
		$result .= "<span>\n";
		$result .= $messageStr;
		$result .= "</span>\n";
		$result .= "</span>\n";
	}
	

	return $result;
}

?>