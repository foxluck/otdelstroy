<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     wbs_pageLayout
 * Purpose:  Declares the page layout elements, 
 * leaving the Content div element open
 * -------------------------------------------------------------
 */


function smarty_block_wbs_pageLayout( $params, $content, &$smarty, &$repeat )
{
	if ($smarty->get_template_vars('ajaxAccess')) {
		$smarty->assign("hideLeftPanel", true);
		return $content;
	}
	$inplaceScreen = $smarty->get_template_vars("inplaceScreen");
	
	$directAccess = (isset($_SESSION['HIDENAVIGATION']) && ($_SESSION['HIDENAVIGATION'] == 1)) || (isset($directAccess) && $directAccess);
	
	
	$result = null;
	$lang = $smarty->get_template_vars('language');
	
	$menuLinks = $smarty->get_template_vars('menuLinks') ;
	//$menuLinks = array_merge(array ("UG" => array ("APP_NAME" => "Users and groups", "UI_NAME"=>"Users and groups", "PAGE" => "../../../AA/html/scripts/usersandgroups.php", "ID" => "UG")), $menuLinks);
	//$currentScreen = $smarty->get_template_vars('SCR_ID');
	$adminScreen = $smarty->get_template_vars('ADMIN_SCR_ID');
	$currentApplication = $smarty->get_template_vars('curAPP_ID');
	$currentScreen = $smarty->get_template_vars('ADMIN_SCR_ID');
	if ( $currentApplication == 'common' || ($currentApplication == "UG" && $currentScreen == "MW"))
		$currentApplication = 'MW';
	
	if ( isset($content) )
	{
		extract( $params );

		$formLink = isset($action) ? $action : $smarty->get_template_vars('formLink');
		$enctype = isset($enctype) ? $enctype : 'multipart/form-data';
		$formname = isset($formname) ? $formname : 'form';
		$logoType = $smarty->get_template_vars('logoType');
		
		foreach ( $menuLinks as $APP_ID=>$appData )
		{
			if ($APP_ID == $currentApplication) {
				$currentAppData = $appData;
				$currentAppData["mainURL"] = $appData["PAGES"][0]["PAGE"];
				if ($inplaceScreen)
					$currentAppData["mainURL"] .= "?inplaceScreen=" . $inplaceScreen;
			}
		}
		$smarty->assign("currentAppData", $currentAppData);
		
		$result = "<form action=\"$formLink\" method=\"post\" enctype=\"$enctype\" id='wbs-main-form' name=\"$formname\">\n";

		$kernelStrings = $smarty->get_template_vars('kernelStrings');
		$helpURL = $smarty->get_template_vars('helpURL');
		$layout = $smarty->get_template_vars ('layout');

		if (false) {
		
			$showLogo = $smarty->get_template_vars('showLogo');
			$showCompanyTop = $smarty->get_template_vars('showCompanyTop');
			$logoTime = $smarty->get_template_vars('logoTime');
			$trans_sid = $smarty->get_template_vars('trans_sid');
			$session_name = $smarty->get_template_vars('session_name');
			$session_id = $smarty->get_template_vars('session_id');
			$logoUrl = "";
			
			if ($showLogo)
				$logoUrl = "../../../common/html/scripts/getlogo.php?lt=" . substr(md5($logoTime),0,6);
			
			if ( $trans_sid )
				$logoUrl .= "?".$session_name."=".$session_id;

			$logoText = $smarty->get_template_vars('companyName');
			//$result .= "<div id=\"Header\">";

			if (!$showCompanyTop) {
				//$logoHeight = "";
				$logoUrl = "../../../" . $currentApplication . "/html/img/" . $currentApplication . "_long_{$lang}.gif";
				if (!file_exists($logoUrl))
					$logoUrl = "../../../" . $currentApplication . "/html/img/" . $currentApplication . "_long_eng.gif";
									
				$logoText = "| " . $currentAppData["APP_NAME"];
			}
			if ($layout == "topmenu2")
				$logoUrl = "../../../common/html/res/images/walogo.gif";
			//$result .= "<div>";
	
			/*if ($logoUrl)
				$result .= "<img $logoHeight src=\"$logoUrl\" class=\"CompanyLogo\" alt=\"\"/>";
			$result .= "<img src='../../../common/html/res/images/wa_logo.gif' id='WALogoImg' alt=\"\"/>";

			$result .= "<div id='WALogoText'> | " . $logoText . "</div></div>";
			$result .= "</div>\n";*/
			
			$result .= "<div id='Wrap'>";
			
			$result .= "<table id='Header' border=0><tr valign='middle'>";
			if ($logoUrl)
				$result .= "<td width=96><img id='CompanyLogo' src=\"$logoUrl\"/></td>";
			//if (!$showLogo)
			//	$result .= "<td><img src='../../../common/html/res/images/wa_logo.gif' id='WALogoImg' alt=\"\"/></td>";

			if ($showCompanyTop)
				$result .= "<td nowrap id='WALogoText'> " . $logoText . "</td>";
			$result .= "</tr></table>";
			
			

			$currentUserName = prepareStrToDisplay( $smarty->get_template_vars('currentUserName'), true );
			//$result .= "<div id=\"UserName\">$currentUserName</div>\n";

			$result .= "	<div id=\"LoginBlock\"><span id=LoggedAs> " . $kernelStrings["app_loggedas_label"] . " <b>$currentUserName</b></span> | \n";
			$result .= "<a href=\"javascript://\" onClick=\"openHelp('$helpURL')\">{$kernelStrings[app_helplink_text]}</a> | ";
			$result .= "<a href=\"../../../AA/html/scripts/logout.php\" target=\"_top\">{$kernelStrings[app_logoutlink_text]}</a>";
			
			
			if ( $smarty->get_template_vars('account_unconfirmed') && isset ($menuLinks["AA"]))
			{
				$link = URL_CONFIRMINFO;
				$result .= "<BR><span id='AccountUnconfirmedMessage'>" . $kernelStrings["app_accountunconfirmed_note"] . "</span> - <a href='$link' id='AccountUnconfirmedLink'>" . $kernelStrings["app_accountunconfirmed_link"] . "</a>";
				/*$result .= "<div class=\"TrialAlertBlock\">";
				$result .= "<div><span>";
				$result .= $kernelStrings['app_trialdemo_note'];
				$result .= "</span><br/>";

				$link = sprintf( URL_REGISTER, $smarty->get_template_vars('DB_KEY'), base64_encode($smarty->get_template_vars('currentUser')), base64_encode($smarty->get_template_vars('language')) );
				$result .= "<a target=\"_blank\" href=\"".$link."\">".$kernelStrings['app_trialdemo_link']."</a>";

				$result .= "</div>";
				$result .= "</div>";*/
			}
			
			$result .= "	</div>\n";

			
			if ($showCompanyTop)
				$result .= "	<div id=\"HeaderContainer\" class='WithCompanyLogo'>\n";
			else
				$result .= "	<div id=\"HeaderContainer\">\n";
			$result .= "	</div>\n";
		}	
		$result .= "	<div id=\"TContentWrapper\">\n";
		if (!$directAccess) {
			$result .= "<div class='FullScreenBlock'>";
			$result .= "<span id='FullScreenOff' style='display:none;'><b>" . $kernelStrings["app_fullscreen_label"]  .  "</b> <a style='padding-left:5px' href='javascript:void(0)' onClick='LayoutManager.SetScreenMode(\"max\");return false;'>" . $kernelStrings["app_fullscreenoff_link"] . "</a></span>\n";
			$result .= "<span id='FullScreenOn'><a href='javascript:void(0)' onClick='LayoutManager.SetScreenMode(\"min\");return false;'>" . $kernelStrings["app_fullscreen_label"] . "</a></span>\n";
			$result .= "</div>";
		}

		$pageTitle = $smarty->get_template_vars('pageTitle');
		if(isset($tabbar)){
			$params = array();
			$params['smarty_include_tpl_file'] = $tabbar;
			$params['smarty_include_vars'] = $smarty->get_template_vars();

			ob_start();
			$smarty->_smarty_include($params);
			$result .= ob_get_clean();
		}else//if ( $toolbar )
		{
			$result .= "		<div id=\"Toolbar\">\n";
			$result .= "<div id='SubToolbar'><div>$pageTitle</div></div>";
			$result .= "			<div id='ToolbarIn' style='height: 35px'><ul>\n";
			if ($toolbar) {
				$params = array();
				$params['smarty_include_tpl_file'] = $toolbar;
				$params['smarty_include_vars'] = $smarty->get_template_vars();

				ob_start();
				$smarty->_smarty_include($params);
				$result .= ob_get_clean();
			}
			

			$result .= "			</ul></div>\n";
			
			$result .= "		</div>\n";
		}
		
		$result .= "		<div id=\"ContentScroller\" ".($params["onScroll"] ? "onScroll=\"".$params["onScroll"]."\"" : "").">\n";
		
		
		if (!isset($contentClass))
			$contentClass = isset($fullWidthContent) && $fullWidthContent ? "FullWidthContent" : null;
		$result .= "			<div id=\"Content\" class=\"$contentClass\">\n";

		$result .= $content;

		$result .= "\n			</div>\n";
		$result .= "		</div>\n";
		$result .= "	</div>\n";
		
		if (false) {
			$needAddServiceLink = false;
			if (in_array("AA", array_keys($menuLinks))) {
				global $mt_hosting_plan_settings;
				global $global_screens;
				$installedApps = array_keys($global_screens);
				$allApps = array_keys($mt_hosting_plan_settings["FREE"]);
				$needAddServiceLink = sizeof(array_diff($allApps, $installedApps));	
				if (!onWebAsystServer())
					$needAddServiceLink = false;
				$addServiceLink = "../../../AA/html/scripts/change_plan.php";
			}
		
		
			
			if ($layout == "sidemenu") {
				$menuContent = _getAppListHTML ($menuLinks, $needImages, $currentApplication, $adminScreen, $layout);
				
				$result .= "	<div id=\"MainMenu\">\n";
				$result .= "<div id='MainMenuContent'>";
				$result .= "<ul>$menuContent</ul>";
				if ($needAddServiceLink) {
					$result .= "<ul><li><div class='SubMenuBlock'><a href='$addServiceLink'>" . $kernelStrings["app_add_remove_services"] . "</a></div></ul>";				
				}
				$result .= "</div>";
				$result .= "</div>";
			}
			$result .= "</div>\n";
			
			if ($layout != "sidemenu") {
				$footerContent = _getAppListHTML ($menuLinks, true, $currentApplication, $adminScreen, $layout);
				foreach (array_keys($menuLinks) as $cNo => $cId)
					$appIds[$cNo] = '"' . $cId . '"';
				$appIdsStr = join (",", $appIds);
				
				$result .= "<div id=\"FooterContainer\">\n";
				$result .= "	<div id=\"Footer\">" . "\n";
				
				ob_start();
				if ($layout == "topmenu")
					require_once("_inc.fisheye.php");
				elseif ($layout == "topmenu2")
					require_once("_inc.topmenu.php");
				$result .= ob_get_clean();
				
				$result .= "		<!--a href='http://www.webasyst.net' target='_blank'><img id='PoweredImg' align='right' src='../../../common/html/res/images/powered.gif'></a-->&nbsp;\n";
				
				$result .= "	</div>\n";
				$result .= "</div>\n";
			}

			if ( $smarty->get_template_vars('showBillingAlert') )
			{
				$result .= "<div class=\"TrialAlertBlock\">";
				$result .= "<div><span>";
				$days = getDaysBeforeSuspend();
				if ( $days >= 0 )
					$result .= sprintf($kernelStrings['app_accountexpire_message'], $days);
				else
					$result .= $kernelStrings['app_accountexpired_message'];

				$result .= "</span>";
				$result .= " <a href=\"../../../AA/html/scripts/change_plan.php?exceed=period\">".$kernelStrings['ai_extend_btn']."</a>";

				$result .= "</div>";
				$result .= "</div>";
			}
		}

		$result .= "<input name=\"edited\" type=\"hidden\" id=\"edited\" value=\"1\"/>\n";
		$result .= "<input type=\"hidden\" name=\"btndummy\"/>\n";
		
		if ($smarty->get_template_vars('inplaceScreen'))
			$result .= "<input type='hidden' id='inplaceScreen' name='inplaceScreen' value='" . $smarty->get_template_vars('inplaceScreen') . "'/>\n";

		$result .= "</form>\n";
	}
	return $result;
}

function _getAppListHTML ($menuLinks, $needImages, $currentApplication, $adminScrId, $layout) {
	$footerContent = "";
	$appNo = 1;
	$separatorOuted = false;
	$commonAppIds = array ("MW", "AA", "UG");
	
	if (!$needImages)
		$footerContent .= "<div class='MenuBlock'>";
	
	// If menuLinks doesn't contains at least one common app - separator doesn't need
	if (!array_intersect($commonAppIds, array_keys($menuLinks)))
		$separatorOuted = true;
	
	foreach ( $menuLinks as $APP_ID => $appData )
	{
		$appMenuId = "menu$APP_ID";
		
		$appLogoUrl = "../../../" . $APP_ID . "/html/img/" . $APP_ID . "35.gif";
		$MenuLabel = $appData['APP_NAME'];
		$MenuItems = $appData['PAGES'];
		
		$itemClass = ($APP_ID == $currentApplication) ? "class='ActiveItem'" : null;
		if ($APP_ID == "AA" && $adminScrId)
			$itemClass = "";
		if ($adminScrId && $APP_ID==$adminScrId)
			$itemClass = "class='ActiveItem'";
		
			
		//$pageItemClass = ($['ID'] == $currentScreen && $currentApplication == $APP_ID) ? "ActiveItem" : null;
		$url = $MenuItems[0]["PAGE"];
		if ($appData["PAGE"])
			$url = $appData["PAGE"];
		
		$needSeparator = (!in_array($APP_ID, $commonAppIds) && !$separatorOuted);
			
		if ($needImages) {
			if ($needSeparator) {
				$footerContent .= "<img src='../../../common/html/res/images/cleardot.gif' class='Separator'>&nbsp; ";
				$separatorOuted = true;
			}
			$footerContent .= "<a $itemClass href='$url' onClick='changeImage(\"$appNo\")' id='${appNo}a'><img id='image$appNo' src='$appLogoUrl' alt='" . $MenuLabel . "'" . " onmouseover='d($appNo)' onmouseout='e($appNo)' border=0 height=35 width=35></a>";
		} else {
			$separatorClass = "";
			if ($needSeparator) {
				$footerContent .= "</div><div class='MenuBlock'>";
				$separatorOuted = true;
			}
			$footerContent .= "<li><a $itemClass href='$url' id='${appNo}a'>$MenuLabel</a>";
		}
		
		$appNo++;
	}
	
	if (!$needImages)
		$footerContent .= "</div>";
	
	return $footerContent;
}

?>
