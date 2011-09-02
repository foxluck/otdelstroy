<?if $monthes != null or $weekdays != null?>
<script language="JavaScript">
<!--

  <?if $dateformat != null?>
  Calendar.DateFormat = "<?$dateformat?>";
  <?/if?>

  <?if $dateformat != null?>
  Calendar.DateFormat = "<?$dateformat?>";
  <?/if?>

  <?if $monthes != null?>
  <?foreach name=csetup from=$monthes item=month?>
  Calendar.MonthNames[<?$smarty.foreach.csetup.iteration?> - 1] = "<?$month?>";
  <?/foreach?>
  <?/if?>

  <?if $weekdays != null?>
  Calendar.ShortDayNames[0] = "<?$weekdays.6?>";
  <?foreach name=csetup from=$weekdays item=weekday?>
  Calendar.ShortDayNames[<?$smarty.foreach.csetup.iteration?>] = "<?$weekday?>";
  <?/foreach?>
  <?/if?>

  <?if $weekstart != null?>
    <?if $weekstart == "monday"?>
    Calendar.firstDayOfWeek = 1;
    <?elseif $weekstart == "sunday"?>
    Calendar.firstDayOfWeek = 7;
    <?/if?>
  <?/if?>

  <?if $calendarStrings != null?>
  <?foreach name=csetup key=key from=$calendarStrings item=item?>
  Calendar.Text["<?$key?>"] = "<?$item?>";
  <?/foreach?>
  <?/if?>

//-->
</script>
<?/if?>


<script language="JavaScript">
<!--
  <?if $calWBSLocalDate != null?>
  Calendar.wbsDate = "<?$calWBSLocalDate?>";
  <?/if?>
//-->
</script>
