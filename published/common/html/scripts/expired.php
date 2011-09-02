<?php
	if (!empty($_GET["ajaxAccess"]))
		die ("__EXPIRED");
	if (!empty($_GET["ajaxRequest"]))
		die ("{success: false, errorStr: 'Session is expired', sessionExpired: true, newLocation: '../../../common/html/scripts/expired.php?redirect=1'}");
	
	$init_required = false;
	$language = "eng";
	$AA_APP_ID = "AA";
	require_once( "../../../common/html/includes/httpinit.php" );
	
	if(onWebAsystServer()){
		
		print "<script>";
		print "window.top.document.location.href = '../../../login.php?expired=1'";
		print "</script>";
		exit(1);
		include 'hosted_expired.php';
	}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Session is expired</title>
<?php
	if ( isset($_GET['redirect']) ) {
?>
<script language="JavaScript" type="text/javascript">
<!--
	if (top) top.document.location.href = "expired.php";
//-->
<?php 
	} 
?>
</script>
<link href="../../../login/res/styles.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center" valign="middle"><table border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td width="7" background="../../../login/res/frames/formtop.gif"><img src="../../../login/res/frames/formlefttop.gif" width="7" height="7"></td>
          <td background="../../../login/res/frames/formtop.gif"><img src="../../../login/res/frames/formtop.gif" width="1" height="7"></td>
          <td width="17"><img src="../../../login/res/frames/formtopright.gif" width="17" height="7"></td>
        </tr>
        <tr> 
          <td width="7" background="../../../login/res/frames/formleft.gif"><img src="../../../login/res/frames/formleft.gif" width="7" height="1"></td>
          <td align="left" bgcolor="#FBF7EF" style="padding: 5px"><p class="header1">Session 
              is expired</p>
            <p>Please use <a href="../../../login.php">login</a> page to enter 
              the system</p>
            </td>
          <td width="17" background="../../../login/res/frames/formright.gif"><img src="../../../login/res/frames/formright.gif" width="17" height="1"></td>
        </tr>
        <tr> 
          <td width="7" background="../../../login/res/frames/formbottom.gif"><img src="../../../login/res/frames/formleftbottom.gif" width="7" height="16"></td>
          <td background="../../../login/res/frames/formbottom.gif"><img src="../../../login/res/frames/formbottom.gif" width="1" height="16"></td>
          <td width="17"><img src="../../../login/res/frames/formbottomright.gif" width="17" height="16"></td>
        </tr>
      </table></td>
  </tr>
</table>
</body>
