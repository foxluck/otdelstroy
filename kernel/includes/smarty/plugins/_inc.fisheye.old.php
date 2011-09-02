<script type="text/javascript">
	var appsArray = new Array ("null",<?=$appIdsStr;?>);
</script>	
<script type="text/javascript" src='../../../common/html/cssbased/layout/topmenu/fisheye.js'></script>
	
<div style='position: absolute; bottom: 5px; padding-left: 2px; '>
<table style='display:none' id="titleTable" ><tr><td id="imageTitle" style="color: rgb(255, 255, 255); font-weight: bold; white-space: nowrap" >&nbsp;</td></tr></table>
<table style='float:left'><tr height="35" valign='bottom'><td>
	<?=$footerContent;?>
</td>
<? if ($needAddServiceLink) { ?><td valign='bottom' nowrap style='padding-bottom: 10px'><a href='<?=$addServiceLink?>'><?=$kernelStrings["app_add_remove_services"];?></a></td><? } ?>
</tr></table>
</div>
<script>
	ae ();
</script>