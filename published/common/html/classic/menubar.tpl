<table height="100%" border="0" cellspacing="0" cellpadding="0" class="menubar">
  <tr>
    <?foreach name=main key=menuname from=$menu item=menuitem?>

    <?if ($menuname == "-")?>
    <td width="2"><div class=vseparator></div></td>
    <?else?>
    <td nowrap><a <? if $startMenuName == $menuname ?>selected=true class="menubar-selected"<? /if ?> href="#" submenu="submenu<?$smarty.foreach.main.iteration?>" target="mainFrame" hidefocus="true" onClick="window.status='';MenuBar.show(this,'submenu<?$smarty.foreach.main.iteration?>');return false" onmouseout="MenuBar.hide('submenu<?$smarty.foreach.main.iteration?>')"><?$menuname?></a></td><?/if?><?/foreach?></tr></table>
