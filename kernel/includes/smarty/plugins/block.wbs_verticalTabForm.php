<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_verticalTabForm
 * Purpose:  Outputs the form with vertical tabs
 * -------------------------------------------------------------
 */

function smarty_block_wbs_verticalTabForm( $params, $content, &$smarty, &$repeat)
{
	global $tabFormId;

	$result = null;

	if ( isset($content) )
	{
		$tabFormId++;
		$id = 'TabForm'.$tabFormId;

		$result = "<table class=\"FormBlock\"><tr><td>\n";

		$styleAttributes = array();

		if ( isset($params['width']) )
			$styleAttributes[] = "width: ".$params['width'];
		if ( isset($params['height']) )
			$styleAttributes[] = "height: ".$params['height'];

		$style = count($styleAttributes) ? "style=\"".implode(";", $styleAttributes)."\"" : null;

		$result .= "<div class=\"TabbedForm\" id=\"$id\">\n";

		$result .= "<table class=\"VerticaslTabForm\"><tr><td>\n";

		$result .= "\t<dl class=\"TabbedFormList\" id=\"{$id}Tabs\">";

		$activeTab = isset($params['activeTab']) && strlen($params['activeTab']) ? $params['activeTab'] : $params['tabs'][0]['PAGE_ID'];

		foreach ( $params['tabs'] as $tabData )
		{
			$class = $activeTab == $tabData['PAGE_ID'] ? "Active" : null;
			$pageId = $tabData['PAGE_ID'];
			$tabCaption = prepareStrToDisplay($tabData[NAME], true);
			$openScript = $tabData['ON_OPEN'] ? "onClick=\"".$tabData['ON_OPEN']."\"" : null;
			$result .= "\t\t<dd class=\"Tab $class\" id=\"{$id}{$pageId}\"><a href=\"#\" $openScript><span>".$tabCaption."</span></a></dd>\n";
		}

		$result .= "</dl></td><td><dl class=\"TabbedFormList\" id=\"{$id}Pages\">\n";

		foreach ( $params['tabs'] as $tabData )
		{
			$class = $activeTab == $tabData['PAGE_ID'] ? "Active" : null;

			$smarty->assign('PAGE_ID', $tabData['PAGE_ID']);

			if ( strlen($tabData['PATH']) )
				$filePath = $tabData['PATH']."/".$tabData['FILE'];
			else
				$filePath = $params['basePath']."/".$tabData['FILE'];

			$includeParams = array();
			$includeParams['smarty_include_tpl_file'] = $filePath;
			$includeParams['smarty_include_vars'] = $smarty->get_template_vars();

			ob_start();
			$smarty->_smarty_include($includeParams);

			$result .= "<dt $style class=\"TabPage $class\"><div>".ob_get_clean()."</div></dt>\n";
		}

		$result .= "\t</dl></td></tr></table>\n";

		$result .= "</div>\n";

		$varNameScript = null;
		if ( isset($params['name']) && strlen($params['name']) )
			$varNameScript = "var ".$params['name']." = ";

		$result .= "<script type=\"text/javascript\">$varNameScript new TabbedForm('$id', true);</script>\n";
		$result .= $content;
		$result .= "</td></tr>";
		$result .= "	<tr class=\"FormFooter\">\n";
		$result .= "		<td>&nbsp;</td>\n";
		$result .= "	</tr>\n";
		$result .= "</table>\n";
	}

	return $result;
}

?>