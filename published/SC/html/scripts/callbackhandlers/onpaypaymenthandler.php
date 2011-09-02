<?php
define('DIR_ROOT', str_replace("\\","/",realpath(dirname(__FILE__).'/../')));
include_once(DIR_ROOT.'/includes/init.php');
$DB_tree = new DataBase();
$DB_tree->connect(SystemSettings::get('DB_HOST'), SystemSettings::get('DB_USER'), SystemSettings::get('DB_PASS'));
$DB_tree->selectDB(SystemSettings::get('DB_NAME'));
$xcsd = db_fetch_row(db_query("SELECT sp.`module_id` FROM ".MODULES_TABLE." sp 
INNER JOIN ".PAYMENT_TYPES_TABLE." pt USING (`module_id`) WHERE ModuleClassName='COnpay'"));
$_GET['modConfID'] = $xcsd[0];
require_once(DIR_FUNC.'/reg_fields_functions.php' );
require_once('paymenthandler.php' );
?>