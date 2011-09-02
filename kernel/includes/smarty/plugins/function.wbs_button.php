<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wbs_button
 * Purpose:  Outputs the button markup
 * -------------------------------------------------------------
 */

$_GLOBAL_BUTTON_COUNTER = 0;

function smarty_function_wbs_button( $params, &$smarty )
{
	extract( $params );
	
	global $_GLOBAL_BUTTON_COUNTER;
	$_GLOBAL_BUTTON_COUNTER++;

	if ( !isset($name) && !isset($link) )
		$href = "javascript://";
	else {
		if (isset($ajax))
			$href = isset($link) && strlen($link) ? $link : "javascript:processAjaxButton('$name')";		
		else
			$href = isset($link) && strlen($link) ? $link : "javascript:processTextButton('$name')";			
	}

	$onClick = isset($onClick) && strlen($onClick) ? "onClick=\"$onClick\"" : null;
	$target = isset($target) ? "target=\"$target\"" : null;

	$dropDown = null;

	$UserAgent = getUserAgent();
	$isIe = $UserAgent['Agent'] == 'Internet Exploder';
	$ellipses = null;

	if ( isset($menu) ) {
		$dropDown = "<table id='MENU_B" . $_GLOBAL_BUTTON_COUNTER . "' title=''><tr><td><ul><li class=\"ToolbarMenuHeader\"><div>&nbsp;</div></li>";
		if (empty($notEllipses))
			$ellipses = "...";
		$href = "#";

		foreach ( $menu as $itemCaption=>$menuItem )
		{
			$menuItemData = explode( '||', $menuItem );
			$menuLink = $menuItemData[0];
			$itemOnClick = $menuItemData[1];
			$checked = $menuItemData[2];
			$mtarget = $menuItemData[3];

			if ( strlen($mtarget) )
				$mtarget = "target=\"$mtarget\"";

			if ($itemCaption == '-' || $menuLink == '-')
				$dropDown .= "<li class=\"Separator\" title=''><div>&nbsp;</div></li>";
			else {
				$itemClass = null;
				switch ( $checked )
				{
					case "checked" : $itemClass = "CheckedMenu"; break;
					case "unchecked" : $itemClass = "UncheckedMenu"; break;
				}

				if ( $menuLink != "" ) {
					if ( $itemOnClick != '' && $itemOnClick != null )
						$itemOnClick = "onclick=\"return $itemOnClick\"";

					$dropDown .= "<li class=\"$itemClass\"><a href=\"$menuLink\" $mtarget title=\"$itemCaption\" $itemOnClick>$itemCaption</a></li>";
				} else
					$dropDown .= "<li class=\"disabled $itemClass\"><a href=\"#\" $mtarget title=\"$itemCaption\">$itemCaption</a></li>";
			}
		}

		$dropDown .= "<li class=\"ToolbarMenuFooter\"><div>&nbsp;</div></li></ul></td></tr></table>";
	}

	if ( !$isIe )
		$result = "<a id='B{$_GLOBAL_BUTTON_COUNTER}' class=\"Button\" $target href=\"$href\" title='' $onClick>$caption$ellipses</a> $dropDown";
	else {
		if ( $dropDown === null ) 
			$result = "<a id='B{$_GLOBAL_BUTTON_COUNTER}' class=\"Button\" $target href=\"$href\" title='' $onClick >$caption$ellipses $dropDown</a>";
		else
			$result = "<a id='B{$_GLOBAL_BUTTON_COUNTER}' class=\"Button\" $target href=\"$href\" title='' $onClick onMouseOver=\"LayoutManager.ProcessShowMenu()\" onMouseOut=\"LayoutManager.ProcessHideMenu()\">$caption$ellipses $dropDown</a><span class=\"ie7\">$dropDown ";
	}
	
	$corners = $smarty->get_template_vars("corners");
	//if ($corners == "rounded") 	
		//$result .= "<script>RoundElem($('B${_GLOBAL_BUTTON_COUNTER}'), '');</script>";
		

	return $result;
}

?>