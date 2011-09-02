<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_sortColumn
 * Purpose:  Outputs the sortable column header
 * -------------------------------------------------------------
 */

function smarty_block_wbs_sortColumn( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{
		extract($params);

		$href = isset($params['URL']) ? $params['URL'] : $smarty->get_template_vars('genericLinkUnsorted');
		$nameLen = strlen($field);
		$curStatData = explode( " ", trim($status) );

		$statusFieldPart = $curStatData[0];
		$statusOrderPart = $curStatData[1];
		$class = null;

		if ( $statusFieldPart == $field ) {
			if ( $statusOrderPart == "asc" ) {
				$statusOrderPart = "desc";
				$class = "asc";
			} else {
				$statusOrderPart = "asc";
				$class = "desc";
			}
		} else
			$statusOrderPart = "asc";
			
		if (!empty($ajax))
			$result = sprintf( "<a href='javaScript:void(0)' onClick='AjaxLoader.loadPage(\"%s&sorting=%s\", {inPlace: true, fromEl: this, inPlaceMsg: true})' class=\"$class\">$content</a>", $href, base64_encode("$field $statusOrderPart") );
		else			
			$result = sprintf( "<a href=\"%s&sorting=%s\" class=\"$class\">$content</a>", $href, base64_encode("$field $statusOrderPart") );
	}

	return $result;
}

?>