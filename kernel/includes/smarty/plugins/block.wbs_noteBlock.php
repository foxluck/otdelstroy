<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_noteBlock
 * Purpose:  Outputs the note block
 * -------------------------------------------------------------
 */

function smarty_block_wbs_noteBlock( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{
		extract($params);

		$kernelStrings = $smarty->get_template_vars('kernelStrings');

		if ( !isset($caption) || !strlen($caption ) )
			$noteText = $kernelStrings['app_note_text'];
		else
			$noteText = $caption;

		$class = isset($class) && strlen($class) ? $class : null;

		$style = null;
		if ( isset($width) )
			$style = "style=\"width: $width\"";

		$result .= "<table $style class=\"NoteBlock\">\n";
		$result .= "<thead>\n";
		$result .= "<tr>\n";
		$result .= "<th scope=\"col\">$noteText</th>\n";
		$result .= "</tr>\n";
		$result .= "</thead>\n";
		$result .= "<tbody>\n";
		$result .= "<tr>\n";
		$result .= "<td class=\"$class\">$content</td>\n";
		$result .= "</tr>\n</tbody>\n</table>\n";
	}

	return $result;
}

?>