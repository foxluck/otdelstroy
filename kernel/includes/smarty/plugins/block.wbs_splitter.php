<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_splitter
 * Purpose:  Declares a splitter control
 * -------------------------------------------------------------
 */

function smarty_block_wbs_splitter( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if ( isset($content) )
	{

		if ( isset($params['header']) && strlen($params['header']) )
		{
			$result .= "<div id=\"SplitterHeader\">\n";
			$tplParams = array();
			$tplParams['smarty_include_tpl_file'] = $params['header'];
			$tplParams['smarty_include_vars'] = $smarty->get_template_vars();

			ob_start();
			$smarty->_smarty_include($tplParams);
			$result .= ob_get_clean();

			$result .= "</div>\n";
		}

		$result .= "<table class=\"Splitter\" id=\"PageSplitter\"><tr>\n";
		$result .= $content;
		$result .= "\n</tr></table>\n";

		$app = $smarty->get_template_vars('curAPP_ID');
		$user = $smarty->get_template_vars('currentUser');

		$result .= "<script type=\"text/javascript\">document.splitterName = \"$app$user\";</script>";
	}

	return $result;
}

?>