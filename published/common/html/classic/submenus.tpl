<?foreach name=main key=menuname from=$menu item=menuitem?>
<div id=submenu<?$smarty.foreach.main.iteration?> class=submenu onmouseout="HideSubMenu(event, 'submenu<?$smarty.foreach.main.iteration?>')">
  <?foreach key=name from=$menuitem item=link?>
    <? assign var=target_arr value=$menu_targets.$menuname ?>
    <? assign var=target value=$target_arr.$name ?>
    <?if ($name == "-") || ($link == "-")?>
    <div class=separator></div>
    <?else?>
    <a hidefocus="true" <? if $target=="" ?>href="<?$link?>"<? else ?>href="#" onClick="openUniqueWindow( '<? $target ?>', '<? $link ?>' ); HideAll(); return false;"<? /if ?> onmousedown="SelectMenu('submenu<?$smarty.foreach.main.iteration?>')" target="mainFrame" onmouseover="window.status='';return true"><nobr><?$name?></nobr></a>
    <?/if?>
  <?/foreach?>
</div>
<?/foreach?>
