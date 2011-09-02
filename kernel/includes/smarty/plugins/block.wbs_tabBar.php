<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_tabBar
 * Purpose:  Creates a bar with tabs
 * -------------------------------------------------------------
 */

function smarty_block_wbs_tabBar( $params, $content, &$smarty, &$repeat )
{
	extract( $params );

	$result = null;

	if ( isset($content) )
	{
		if ( isset($header) && strlen($header) )
		{
			$result = "<div class=\"TabBarHeader\" id=\"TabBarHeader\">";

			$params['smarty_include_tpl_file'] = $header;
			$params['smarty_include_vars'] = $smarty->get_template_vars();
			ob_start();
			$smarty->_smarty_include($params);
			$result .= ob_get_clean();
			$result .= "</div>";
		}

		$result .= "<div class=\"TabBar\" id=\"TabBar\">";
		$result .= "\t<div class=\"TabbedForm\">\n";
		$result .= "\t<dl class=\"TabbedFormList\">";

		foreach ( $params['tabs'] as $tabData )
		{
			$tabName = $tabData['caption'];

			$tabLink = $tabData['link'];
			$linkParts = explode( "||", $tabLink );
			$tabLink = $linkParts[0];
			$checked = $linkParts[1];

			if ( $checked == "checked" )
				$result .= "\t\t<dd class=\"Tab Active\"><a href=\"#\"><span>".$tabName."</span></a></dd>\n";
			else
				$result .= "\t\t<dd class=\"Tab\"><a href=\"$tabLink\"><span>".$tabName."</span></a></dd>\n";
		}

		$result .= "\t</dl>\n";
		$result .= "\t</div>\n";
		$result .= "</div>";

		$result .= "<div id=\"TabBarContentWrapper\"><div class=\"TabBarContent\" id=\"TabBarContent\">";
		$result .= $content;
		$result .= "</div></div>";

		if ( isset($footer) && strlen($footer) )
		{
			$result .= "<div class=\"TabBarFooter\" id=\"TabBarFooter\">";

			$params['smarty_include_tpl_file'] = $footer;
			$params['smarty_include_vars'] = $smarty->get_template_vars();
			ob_start();
			$smarty->_smarty_include($params);
			$result .= ob_get_clean();
			$result .= "</div>";
		}

		$result .= "<script type=\"text/javascript\" src=\"../../../common/html/cssbased/tabbar.js\"></script>\n";
	}

	return $result;
}

?>