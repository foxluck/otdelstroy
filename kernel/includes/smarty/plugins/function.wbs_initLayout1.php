<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_initlayout
 * Purpose:  Initializes the page layout
 * -------------------------------------------------------------
 */

function smarty_function_wbs_initLayout( $params, &$smarty )
{
	extract( $params );

	$lang = $smarty->get_template_vars ('language');
	$corners = $smarty->get_template_vars ('corners');
	$title = $smarty->get_template_vars( 'pageTitle' );
	$result = "<title>$title</title>";
	$needExt = ($smarty->get_template_vars("needExt") || isset($needExt));

	$charset = $smarty->get_template_vars( 'html_encoding' );

	$colorTheme = $smarty->get_template_vars( 'theme' );
	$layout = $smarty->get_template_vars( 'layout' );
	
	if (defined("LOOKANDFEEL_PREVIEW")) {
		global $_GET;
		if (!empty($_GET["theme"]))
			$colorTheme = $_GET["theme"];
		if (!empty($_GET["corners"])) {
			$corners = $_GET["corners"];
			$smarty->assign ("corners", $corners);
		}
	}

	$result .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\"/>\n";
	
	$result .= "<script src='../../../common/html/res/ext/pr-prototype.js'></script>";
	//if ($needExt) {
		// Add the ExtJs module
		//$result .= "<script src='../../../common/html/res/ext/ext-base.js'></script>";
		$result .= "<script src='../../../common/html/res/ext/pr-adapter.js'></script>";
		$result .= "<script src='../../../common/html/res/ext/pr-effects.js'></script>";
		//$result .= "<script src='../../../common/html/res/ext/pr-scriptaculous.js'></script>";
		
		$result .= "<script src='../../../common/html/res/ext/ext-all.js'></script>"; 
		if ($lang != LANG_ENG) 
			$result .= "<script src='../../../common/html/res/ext/build/locale/ext-lang-" . substr($lang, 0,2) . ".js'></script>";
	//}
	
	//$result .= "<script type=\"text/javascript\" src=\"../../../common/html/cssbased/prototype.js\"></script>\n";
	$result .= "<script type=\"text/javascript\" src=\"../../../common/html/cssbased/domready.js\"></script>\n";
	$result .= "<script type=\"text/javascript\" src=\"../../../common/html/cssbased/common.js\"></script>\n";
	$result .= "<script type=\"text/javascript\" src=\"../../../common/html/cssbased/layout.js\"></script>\n";
	$result .= "<script type=\"text/javascript\" src=\"../../../common/html/res/wbscommon.js\"></script>\n";
	$result .= "<script type=\"text/javascript\" src=\"../../../common/help/helpopen.js\"></script>\n";
	
	
	// NiftyCube JS include and Start
	if ($corners == "rounded") {
		$result .= "<script type='text/javascript' src='../../../common/html/cssbased/geometry/rounded/niftycube.js'></script>\n";
		//$result .= "<script>RegisterOnLoad (function(){Nifty('a.Button,div.MenuBlock','small transparent');});</script>";
		//$result .= "<script>RegisterOnLoad (function(){Nifty('dd.Tab','small transparent top');});</script>";
		
		$result .= "<script type='text/javascript' src='../../../common/html/cssbased/geometry/rounded/rcorners.js'></script>\n";
		$result .= "<script>RegisterOnLoad (function() {startRCorners();});</script>\n";
		$result .= "<link rel=\"stylesheet\" href=\"../../../common/html/cssbased/geometry/rounded/geometry.css\" type=\"text/css\"/>\n";
	} else {
		$result .= "<link rel=\"stylesheet\" href=\"../../../common/html/cssbased/geometry/straight/geometry.css\" type=\"text/css\"/>\n";
	}

	$result .= "<link rel=\"stylesheet\" href=\"../../../common/html/cssbased/base.css\" type=\"text/css\"/>\n";
	$result .= "<!--[if IE 7]><link rel=\"stylesheet\" href=\"../../../common/html/cssbased/base_ie7.css\" type=\"text/css\"/><![endif]-->\n";

	// Attach the theme layout stylesheet
	//
	$result .= "<link rel=\"stylesheet\" href=\"../../../common/html/cssbased/toolbar.css\" type=\"text/css\"/>\n";
	$result .= "<link rel=\"stylesheet\" href=\"../../../common/html/cssbased/layout/$layout/layout.css\" type=\"text/css\"/>\n";	
	$result .= "<!--[if IE 7]><link rel=\"stylesheet\" type=\"text/css\" href=\"../../../common/html/cssbased/layout/$layout/layout_ie7.css\" /><![endif]-->\n";
	$result .= "<link rel='stylesheet' type='text/css' href='../../../common/html/cssbased/geometry/rounded/niftyCorners.css'>\n";

	// Set the layout paramters
	//
	$layoutParams = array();
	//if ( isset($toolbar) && $toolbar )
		$layoutParams[] = 'toolbar=1';

	if ( !empty($splitter))
		$layoutParams[] = 'splitter=1';
	
	if ( isset($htmlArea) && $htmlArea ) {

		$tplParams = array();
		$tplParams['smarty_include_tpl_file'] = "../../../common/html/classic/htmlareasetup.tpl";
		$tplParams['smarty_include_vars'] = $smarty->get_template_vars();

		ob_start();
		$smarty->_smarty_include($tplParams);
		$result .= ob_get_clean();
	}

	$directAccess = (isset($_SESSION['HIDENAVIGATION']) && ($_SESSION['HIDENAVIGATION'] == 1)) || (isset($directAccess) && $directAccess);
	$fullScreen = (!empty($_COOKIE["screenMode"]) && $_COOKIE["screenMode"] == "min");
	$inplaceScreen = $smarty->get_template_vars ('inplaceScreen');

	if ( $directAccess  )
		$layoutParams[] = 'da=1';
	
	if ($fullScreen)
		$layoutParams[] = 'fullscreen=1';
	
	if ($inplaceScreen)
		$layoutParams[] = 'inplace=1';

	if ( isset($noScroll) && $noScroll )
		$layoutParams[] = 'ns=1';

	$layoutParams = implode( "&", $layoutParams );
	$result .= "<link rel=\"stylesheet\" href=\"../../../common/html/cssbased/layout/$layout/setlayout.php?$layoutParams\" type=\"text/css\"/>\n";
	$result .= "<!--[if IE 7]><link rel=\"stylesheet\" href=\"../../../common/html/cssbased/layout/$layout/setlayout_ie7.php?$layoutParams\" type=\"text/css\"/><![endif]-->\n";
	$result .= "<script type=\"text/javascript\" src=\"../../../common/html/cssbased/layout/$layout/layout.js\"></script>\n";
	
	$result .= "\n<script type='text/javascript'>\n";
	$result .= "var directAccess=" . ($directAccess ? 1 : 0) . ";\n";
	$result .= "var inplaceScreen=" . ($inplaceScreen ? 1 : 0) . ";\n";
	$result .= "</script>\n";
	
	if (!defined("LOOKANDFEEL_PREVIEW")) 
		$result .= "<script type=\"text/javascript\" src=\"../../../common/html/cssbased/init.js\"></script>\n";
	else
		$result .= "<script type=\"text/javascript\" src=\"../../../common/html/cssbased/init_light.js\"></script>\n";

	$htmlAreaIE6CSS =  isset($htmlAreaIE6CSS) ?  $htmlAreaIE6CSS : true;

	if ( isset($htmlArea) && $htmlArea && $htmlAreaIE6CSS )
		$result .= "<!--[if IE 6]><link rel=\"stylesheet\" href=\"../../../common/html/cssbased/htmlarea_ie6.css\" type=\"text/css\"/><![endif]-->\n";

	// Attach the color scheme stylesheet
	//
	$result .= "<link rel=\"stylesheet\" href=\"../../../common/html/cssbased/themes/common.css\" type=\"text/css\"/>\n";
	$result .= "<link rel=\"stylesheet\" href=\"../../../common/html/cssbased/themes/$colorTheme/colors.css\" type=\"text/css\"/>\n";
	$result .= "<!--[if IE 7]><link rel=\"stylesheet\" href=\"../../../common/html/cssbased/themes/$colorTheme/colors_ie7.css\" type=\"text/css\"/><![endif]-->\n";
	
	
	
	// Add Ext-module css
	
	if ($needExt) {
		$result .= "<link rel='stylesheet' type='text/css' href='../../../common/html/res/ext/resources/css/my-ext-all.css'>";
		$result .= "<link rel='stylesheet' type='text/css' href='../../../common/html/res/ext/resources/css/xtheme-slate.css'>";
		$result .= "<link rel='stylesheet' type='text/css' href='../../../common/html/res/ext/resources/css/menu.css'>";
		$result .= "<link rel='stylesheet' type='text/css' href='../../../common/html/res/ext/resources/css/layout.css'>";
		$result .= "<script>Ext.BLANK_IMAGE_URL = '../../../common/html/res/ext/resources/images/default/s.gif'</script>";
	}
	
	if (!empty($splitter)) 
		$result .= "<script type=\"text/javascript\" src=\"../../../common/html/cssbased/splitter.js\"></script>\n";

	return $result;
}

?>