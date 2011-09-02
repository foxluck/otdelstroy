<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_thumbnail
 * Purpose:  Outputs the image thumbnail
 * -------------------------------------------------------------
 */

function smarty_function_wbs_thumbnail( $params, &$smarty )
{
	$id = isset($params['id']) ? "id=\"{$params['id']}\"" : null;

	$result = "<div class=\"Thumbnail\">\n";
	$result .= "<div>\n";
	$result .= "<div>\n";

	$anchor = null;

	if ( $params['URL'] ) {
		$url = $params['URL'];

		$target = $params['target'];
		if ( $target )
			$target = "target=\"$target\"";
		
		$onClickStr = (@$params["onClick"]) ? "onClick=\"" . $params["onClick"] . "\"" : "";

		$anchor = "<a $onClickStr href=\"$url\" $target>";
	}

	if ( isset($params['src']) ) {
		$src = $params['src'];
		$title = $params['title'];
		$result .= "<table><tr><td valign=\"middle\">\n";
		$result .= "$anchor<img $id valign=\"middle\" src=\"$src\" title=\"$title\"/>\n";
		if ( $anchor !== null )
			$result .= "</a>";
		$result .= "</td></tr></table>\n";
	} else {
		$kernelStrings = $smarty->get_template_vars('kernelStrings');
		$result .= '<span>['.$kernelStrings['app_noimage_label'].']</span>';
	}

	$result .= "</div>\n";
	$result .= "</div>\n";
	$result .= "</div>\n";

	return $result;
}

?>