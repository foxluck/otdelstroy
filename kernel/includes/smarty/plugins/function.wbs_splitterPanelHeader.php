<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_splitterPanelHeader
 * Purpose:  Outputs a splitter panel header
 * -------------------------------------------------------------
 */

function smarty_function_wbs_splitterPanelHeader( $params, &$smarty )
{
	
	require_once "modifier.html_substr.php";
	
	$id = isset($params['id']) && strlen($params['id']) ? "id=\"".$params['id']."\"" : null;
	$caption = isset($params['caption']) ? $params['caption'] : null;
	$class = isset($params['active']) && $params['active'] ? "Active" : null;
	
	$addStyle = '';
	if ($params["disabled"]) $addStyle .= "display: none;";
	if ($params["height"]) $addStyle .= "height: ".$params["height"]."px;";
	
	if ($addStyle) {
	    $addStyle = "style=\"".$addStyle."\"";
	}

	$result = "<div class=\"SplitterPanelHeader $class\" $id $addStyle><div class=\"SplitterHeaderPanelDiv\">\n";

	$result .= "<table  class=\"SplitterHeaderPanelList\"><tr>\n";

	if ( isset($params['captionControls']) && $params['captionControls'] )
	{
		$wstr = ($params["id"] == "RightPanelHeader") ? "style='width: 1px'" : "";
		$result .= "<td $wstr class=\"CaptionControls\">\n";
		$includeParams = array();
		$includeParams['smarty_include_tpl_file'] = $params['captionControls'];
		$includeParams['smarty_include_vars'] = $smarty->get_template_vars();

		ob_start();
		$smarty->_smarty_include($includeParams);
		$result .= ob_get_clean();

		$result .= "</td>\n";
	}


	$result .= "<td>\n";

	if ( isset($params['captionTemplate']) && strlen($params['captionTemplate']) )
	{
		$tplParams = array();
		$tplParams['smarty_include_tpl_file'] = $params['captionTemplate'];
		$tplParams['smarty_include_vars'] = $smarty->get_template_vars();
		
		ob_start();
		$smarty->_smarty_include($tplParams);
		$result .= ob_get_clean();
	} else {
		if ( isset($params['captionLink']) )
			$result .= "<a href=\"".$params['captionLink']."\">";

		$caption = smarty_modifier_html_substr( $caption, 50, '...' );

		$result .= $caption;
		if ( isset($params['captionLink']) )
			$result .= "</a>";
			
		if ( isset($params['have_filter']) and $params['have_filter'] == true ) ;// for filter menu
			$result .= ' <span class="filter_lick_ct" style="color: #0000FF;"><u><a style="color: #0000FF;" href="javascript:View_filter()"  id="show-filter-btn" >'.$params['rl_filter_label'].'</a></u></span>';
			
	} 
	
	$result .= "</td>\n";

	if ( isset($params['headerControls']) && $params['headerControls'] )
	{
		$result .= "<td class=\"PanelControls\" align=\"right\">\n";
		$includeParams = array();
		$includeParams['smarty_include_tpl_file'] = $params['headerControls'];
		$includeParams['smarty_include_vars'] = $smarty->get_template_vars();

		ob_start();
		$smarty->_smarty_include($includeParams);
		$result .= ob_get_clean();

		$result .= "</td>\n";
	}

	$result .= "</tr></table></div></div>\n";

	return $result;
}

?>