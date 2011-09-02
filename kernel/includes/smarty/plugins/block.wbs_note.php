<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_note
 * Purpose:  Outputs the note
 * -------------------------------------------------------------
 */

function smarty_block_wbs_note( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{
		extract($params);

		$useSmallFont = (isset($smallFont) && $smallFont) || !isset($smallFont);
		$noteClass = $useSmallFont ? "SmallFontNote" : null;

		$noteTextClass = null;
		if ( isset($indented) && $indented )
			$noteTextClass = "class=\"Indented\"";

		$displayMarker = (isset($displayNoteMarker) && $displayNoteMarker) || !isset($displayNoteMarker);

		if ( $smallFont  )

		$style = array();
		if ( isset($width) )
			$style[] = "width: $width";

		if ( isset($minWidth) )
			$style[] = "min-width: $minWidth";

		$style = count($style) ? "style=\"".implode(";", $style)."\"" : null;

		$result = "<dl $style class=\"Note $noteClass\">\n";

		if ( $displayMarker ) {
			$kernelStrings = $smarty->get_template_vars('kernelStrings');

			if ( !isset($noteType) || !strlen($noteType) )
				$noteType = 'note';

			$labelClass = null;
			switch ( $noteType )
			{
				case 'note' :
					$noteLabel = $kernelStrings['app_note_text'];

					break;
				case 'importantNote' :
					$noteLabel = $kernelStrings['app_impnote_label'];
					$labelClass = "class=\"ImportantNote\"";
	
					break;
				case 'tips' :
					$noteLabel = $kernelStrings['app_tips_label'];

					break;
			}

			$result .= "	<dt $labelClass>".$noteLabel.":</dt>\n";
		}

		$result .= "	<dd $noteTextClass>$content</dd>\n";
		$result .= "</dl>\n";
	}

	return $result;
}

?>