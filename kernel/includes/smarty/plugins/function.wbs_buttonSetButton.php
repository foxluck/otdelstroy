<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_buttonSetButton
 * Purpose:  Outputs the button set buttn markup
 * -------------------------------------------------------------
 */

function smarty_function_wbs_buttonSetButton( $params, &$smarty )
{
	extract( $params );
	
	global $_GLOBAL_BUTTON_COUNTER;
	$_GLOBAL_BUTTON_COUNTER++;

	if (isset($link) && strlen($link))
		$href = $link;
	else {
		$href = (isset($formname)) ? "javascript:processTextButton('$name','$formname')" : "javascript:processTextButton('$name')";
	}
	$onClick = isset($onClick) && strlen($onClick) ? "onClick=\"$onClick\"" : null;
	$itemClass = isset($align) && strtoupper($align) == "RIGHT" ? "Right" : null;
	$target = isset($target) ? "target=\"$target\"" : null;
	$id = isset($id) ? "id=\"$id\"" : null;

	$result = "<li class=\"$itemClass\" $id><a id='B{$_GLOBAL_BUTTON_COUNTER}'  $target class=\"Button\" href=\"$href\" $onClick>$caption</a></li>";
	
	$corners = $smarty->get_template_vars("corners");
	//if ($corners == "rounded") 	
		//$result .= "<script>RoundElem(\$('B${_GLOBAL_BUTTON_COUNTER}'), ''); </script>";

	return $result;
}

?>