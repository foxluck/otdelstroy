<?php
include_once './cfg/connect.inc.php';
include_once './core_functions/db_functions.php';
include_once './core_functions/functions.php';
include_once './core_functions/payment_functions.php';
include_once './core_functions/setting_functions.php';

$_POST = xStripSlashesGPC($_POST);

if(isset($_POST['jsredirect'])){
?>
<html>
	<head>
		<title>EPDQ</title>
	</head>
	<body onload="document.forms[0].submit();">
		<form action="<?php print xHtmlSpecialChars($_POST['jsredirect']);?>" method="post">
<?php
	foreach ($_POST as $k=>$v){
		
			print '<input type="hidden" name="'.xHtmlSpecialChars($k).'" value="'.xHtmlSpecialChars($v).'">';
	}
?>
		</form>
	</body>
</html>
<?php
}elseif (isset($_GET['oid'])){
	
	db_connect(SystemSettings::get('DB_HOST'),SystemSettings::get('DB_USER'),SystemSettings::get('DB_PASS')) or die (db_error());
	db_select_db(SystemSettings::get('DB_NAME')) or die (db_error());
	
	settingDefineConstants();
	Redirect(getTransactionResultURL('success'));
}
?>