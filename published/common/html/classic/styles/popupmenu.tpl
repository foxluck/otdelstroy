<script language="JavaScript">
<!--

	<? math equation="x + y" x=$popupMenuIndex y=1000 assign=popupindex ?>

	rightAlign = false;
	<? if $popupRightAlign ?>
		rightAlign = true;
	<? /if ?>
	
	PopupMenuBar<? $popupindex ?> = new PopupWebMenu("popupmenubar", false, rightAlign);

//-->
</script>
<a class="<? $menuClass ?>" title="<? $title ?>" href="javascript://" hidefocus="true" onClick="return PopupMenuBar<? $popupindex ?>.show(this, 'submenu<? $popupindex ?>')"><nobr><? $menuContent ?><? if $menuIcon ?>...<? /if ?></nobr></a><div id=submenu<? $popupindex ?> onmouseout="PopupMenuBar<? $popupindex ?>.hide()" <? if $freeWidth ?>class=popupmenusubmenu<?else?>class=popupmenusubmenu_fixedWidth<? /if ?> <? if $popupRightAlign ?>style="text-align: right"<?/if?>><? if $showTopBar ?><div class=popupmenuemptyspace></div><? /if ?><?foreach key=name from=$popupMenuItem item=link?><? explodeString separator="||" var=linkParts str=$link ?><? assign var=link value=$linkParts[0] ?><?if ($name == "-") || ($link == "-")?><div class=popupmenuseparator></div><?else?><? if $link != "" ?><div class="popupsubmenu_link" style="width: 100%"><a hidefocus="true" onmouseover="window.status='';return true" <? if $linkParts[1] != "" && $linkParts[1] != "null" ?>onClick="return <? $linkParts[1] ?>"<? /if ?> href="<?$link?>" <? if $linkParts[3]!='' ?> target="<? $linkParts[3] ?>"<? /if ?>><nobr><? if $checkboxes ?><? if $linkParts[2] == "checked" ?><img src="<? "images/menucb_checked.gif"|stylesetitem:"classic":$styleSet ?>" style="margin-right: 8px" border="0" align="absmiddle" width=9 height=9><? elseif $linkParts[2] == "unchecked" ?><img src="<? "images/menucb_unchecked.gif"|stylesetitem:"classic":$styleSet ?>" style="margin-right: 8px" border="0" align="absmiddle" width=9 height=9><? else ?><img src="<? "images/menucb_none.gif"|stylesetitem:"classic":$styleSet ?>" style="margin-right: 8px" border="0" align="absmiddle" width=9 height=9><? /if ?><? /if ?><?$name?></nobr></a></div><? else ?><div class="popupsubmenu_link_disabled"><a hidefocus="true" onmouseover="window.status='';return true" <? if $linkParts[1] != "" && $linkParts[1] != "null" ?>onClick="return <? $linkParts[1] ?>"<? /if ?> href="javascript://"><nobr><? if $checkboxes ?><? if $linkParts[2] == "checked" ?><img src="<? "images/menucb_checked.gif"|stylesetitem:"classic":$styleSet ?>" style="margin-right: 8px" border="0" align="absmiddle" width=9 height=9><? elseif $linkParts[2] == "unchecked" ?><img src="<? "images/menucb_unchecked.gif"|stylesetitem:"classic":$styleSet ?>" style="margin-right: 8px" border="0" align="absmiddle" width=9 height=9><? else ?><img src="<? "images/menucb_none.gif"|stylesetitem:"classic":$styleSet ?>" style="margin-right: 8px" border="0" align="absmiddle" width=9 height=9><? /if ?><? /if ?><?$name?></nobr></a></div><? /if ?><?/if?><?/foreach?></div>
