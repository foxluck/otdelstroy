<table style='float:left' id='TopAppLinksTable'><tr height="40" valign='middle'>
	
<?
	$commonAppIds = array ("MW", "AA", "UG");
	$outCommon = false;
	$needSeparator = false;
	
	for ($i = 0; $i < 2; $i++) {
		foreach ( $menuLinks as $APP_ID => $appData )
		{
			//print "<td>" . (int)in_array($APP_ID, $commonAppIds) - $outCommon . "</td>";
			if (((int)in_array($APP_ID, $commonAppIds) - $outCommon) != 0) {
				$needSeparator = true;
				continue;
			}
			$appMenuId = "menu$APP_ID";
			$appLogoUrl = "../../../" . $APP_ID . "/html/img/" . $APP_ID . "40.gif";
			$MenuLabel = $appData['APP_NAME'];
			$MenuItems = $appData['PAGES'];
			$itemClass = ($APP_ID == $currentApplication) ? "ActiveItem" : "";
			if ($APP_ID == "AA" && $adminScrId)
				$itemClass = "";
			if ($adminScrId && $APP_ID==$adminScrId)
				$itemClass = "ActiveItem";
			$url = $MenuItems[0]["PAGE"];
			if ($appData["PAGE"])
				$url = $appData["PAGE"];
			
			print 
				"<td class='$itemClass' onmousemove='this.className=\"$itemClass Highlight\"' onMouseOut='this.className=\"$itemClass\"'>" .
					"<a href='$url' id='${appNo}a'><img id='image$appNo' src='$appLogoUrl' alt='" . $MenuLabel . "'" . " border=0 height=40 width=40><div>${MenuLabel}</div></a>" .
				"</td>";
		}
		if ($needSeparator && !$outCommon)
			print "<td><img src='../../../common/html/res/images/cleardot.gif' class='Separator'></td>";
		$outCommon = 1;
	}
?>

</tr></table>