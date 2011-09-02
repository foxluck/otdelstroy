<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_dualComboBox
 * Purpose:  Outputs the dual combobox
 * -------------------------------------------------------------
 */

include_once "function.wbs_iconButton.php";
include_once "function.html_options.php";

function smarty_function_wbs_dualComboBox( $params, &$smarty )
{
	extract( $params );

	$result = "<table class=\"FormLayout\">\n";
	$result .= "<tr>\n";
	$result .= "<td class=\"FormElementCaption\"><span>$leftCaption</span></td>\n";
	$result .= "<td>&nbsp;</td>\n";
	$result .= "<td class=\"FormElementCaption\"><span>$rightCaption</span></td>\n";
	$result .= "</tr>\n";

	$onClickHandler = isset($onChange) && strlen($onChange) ? $onChange : null;

	$rowClass = null;
	if ( $separated )
		$rowClass = "class=\"SeparatedRow\"";

	$result .= "<tr $rowClass>\n";
	$result .= "<td>\n";
	$result .= "<select name=\"{$leftVariableName}[]\" size=\"$rows\" style=\"width: {$width}px\" class=\"FormControl\" multiple ondblclick=\"moveComboItems('{$leftVariableName}[]', '{$rightVariableName}[]'); $onClickHandler;\">\n";
	$result .= smarty_function_html_options( array('values'=>$leftValues, 'output'=>$leftNames ), $smarty );
	$result .= "</select>\n";
	$result .= "</td>\n";
	$result .= "<td>\n";

	if ( $showUpDown )
	{
		$result .= smarty_function_wbs_iconButton( array('link'=>"javascript: changeComboItemPosition('{$leftVariableName}[]', -1)", 'iconClass'=>"Up") );
		$result .= smarty_function_wbs_iconButton( array('link'=>"javascript: changeComboItemPosition('{$leftVariableName}[]', 1)", 'iconClass'=>"Down") );
		$result .= "<br/>\n";
	}

	$result .= smarty_function_wbs_iconButton( array('link'=>"javascript: { moveComboItems('{$rightVariableName}[]', '{$leftVariableName}[]'); $onClickHandler; }", 'iconClass'=>"Left") );
	$result .= smarty_function_wbs_iconButton( array('link'=>"javascript: { moveComboItems('{$leftVariableName}[]', '{$rightVariableName}[]'); $onClickHandler; }", 'iconClass'=>"Right") );
	$result .= "</td>\n";
	$result .= "<td>\n";
	$result .= "<select name=\"{$rightVariableName}[]\" size=\"$rows\" style=\"width: {$width}px\" class=\"FormControl\" multiple onDblClick=\"moveComboItems('{$rightVariableName}[]', '{$leftVariableName}[]'); $onClickHandler;\n\">";
	$result .= smarty_function_html_options( array('values'=>$rightValues, 'output'=>$rightNames ), $smarty );
	$result .= "</select>\n";
	$result .= "</td>\n";
	$result .= "</tr>\n";
	$result .= "</table>\n";

	return $result;
}

?>