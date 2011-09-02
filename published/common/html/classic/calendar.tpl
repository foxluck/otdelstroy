<? if !$calendarPrepared ?>
	<?include file="../../../common/html/classic/calendarsetup.tpl" dateformat=$dateformat monthes=$monthNames weekdays=$weekdayNames weekstart="monday"?>
	<? assign var=calendarPrepared value=1 ?>
<? /if ?>

<input style="<?$style?>" type=image src="../../../common/html/classic/images/calendar.gif" align=absmiddle hidefocus=true
	onClick="showCalendar(document.forms[0].elements['<?$name|default:"date1"?>'], true, event); return false;">