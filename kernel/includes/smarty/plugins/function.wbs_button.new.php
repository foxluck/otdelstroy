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
	
	$c = $_GLOBAL_BUTTON_COUNTER;
		
	$result .= '<div title="" class="l-f-item-container">
    <div class="l-f-item" id="f' . $c . '">
      <div class="l-b-ll"></div>
      <div title="" class="l-b-rr"></div>
      <a ' . $target . ' href="' . $href . '" title="" ' . $onClick . 'onmouseover="MM_showHideLayers(\'drop-down' . $c . '\',\'\',\'show\')">' . $caption . $ellipses . '</a></div>';
  
  if ( isset($menu) ) {
  	$result .= '<div class="l-dropdown" id="drop-down' . $c . '" style="display:none;">
      <div title="" class="l-rel">
        <div class="l-menu-top" id="mt' . $c . '" onmouseover="MM_showHideLayers(\'drop-down' . $c . '\',\'\',\'show\')" onmouseout="MM_showHideLayers(\'drop-down' . $c . '\',\'\',\'hide\')">
          <div class="l-shd-lt-f"><img src="../../../common/html/res/images/spacer.gif" width="6" height="28" /></div>
          <div class="l-shd-rt-f"><img src="../../../common/html/res/images/spacer.gif" width="8" height="28" /></div>
          <div class="l-ftopbg">' . $caption . $ellipses . '</div>
        </div>
        <div title="" class="mwrapper" id="m' . $c . '" onmouseover="MM_showHideLayers(\'drop-down' . $c . '\',\'\',\'show\')" onmouseout="MM_showHideLayers(\'drop-down' . $c . '\',\'\',\'hide\')">
          <div class="l-shd-tt"></div>
          <div class="l-shd-rr">
            <ul class="l-dd-menu">';
  	
  	foreach ( $menu as $itemCaption=>$menuItem )
		{
			$menuItemData = explode( '||', $menuItem );
			$menuLink = $menuItemData[0];
			$itemOnClick = $menuItemData[1];
			$checked = $menuItemData[2];
			$mtarget = $menuItemData[3];
			
			$checkEl = "";
			switch ($checked) {
				case "checked": $checkEl="<div class='l-check'></div>"; break;
				case "unchecked": $checkEl="<div class='l-uncheck'></div>"; break;
			}
			
			$itemOnClickStr = ($itemOnClick) ? "return $itemOnClick" : "";
			
			if ($itemCaption == '-' || $menuLink == '-')
				$result .= '<li class="l-splitter">&nbsp;</li>';
			else
				$result .= '<li>' . $checkEl . '<a ' . $mtarget. ' href="' . $menuLink . '" id="fm' . $c . '_1" onclick="MM_showHideLayers(\'drop-down' . $c . '\',\'\',\'hide\'); ' . $itemOnClickStr . ';">' . $itemCaption . '</a></li>';
			
		}  	
		
		$result .= '</ul>
          </div>
          <div class="l-shd-bb"></div>
          <div class="l-shd-lb"><img src="../../../common/html/res/images/spacer.gif" width="6" height="9" /></div>
        </div>
      </div>
    </div>';
  }
  
  
  $result .= "</div>";
	
	
	
	
	
	
	
	
	

	/*if ( isset($menu) ) {
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
	}*/

	/*if ( !$isIe )
		$result = "<a id='B{$_GLOBAL_BUTTON_COUNTER}' class=\"Button\" $target href=\"$href\" title='' $onClick>$caption$ellipses</a> $dropDown";
	else {
		if ( $dropDown === null ) 
			$result = "<a id='B{$_GLOBAL_BUTTON_COUNTER}' class=\"Button\" $target href=\"$href\" title='' $onClick >$caption$ellipses $dropDown</a>";
		else
			$result = "<a id='B{$_GLOBAL_BUTTON_COUNTER}' class=\"Button\" $target href=\"$href\" title='' $onClick onMouseOver=\"LayoutManager.ProcessShowMenu()\" onMouseOut=\"LayoutManager.ProcessHideMenu()\">$caption$ellipses $dropDown</a><span class=\"ie7\">$dropDown ";
	}
	
	$corners = $smarty->get_template_vars("corners");
	if ($corners == "rounded") 	
		$result .= "<script>RoundElem($('B${_GLOBAL_BUTTON_COUNTER}'), '');</script>";
	*/
	
	return $result;
}
?>