<?php

if($_SERVER["REQUEST_METHOD"] != 'POST')
{
    die('Hack detected. (01)');
};

define('DBMS', 'mysql');
define('DIR_ROOT', str_replace("\\","/",realpath(dirname(__FILE__))));

include_once(DIR_ROOT.'/includes/init.php');
include_once(DIR_CFG.'/connect.inc.wa.php');
include_once(DIR_FUNC.'/db_functions.php');
include_once(DIR_FUNC.'/functions.php');
include_once(DIR_FUNC.'/setting_functions.php');
//require_once(DIR_CLASSES.'/class.virtual.paymentmodule.php');
require_once(DIR_FUNC.'/module_function.php');
require_once(DIR_FUNC.'/order_functions.php');
require_once(DIR_FUNC.'/order_status_functions.php');
require_once(DIR_FUNC.'/statistic_functions.php');
require_once(DIR_FUNC.'/datetime_functions.php');

$DB_tree = new DataBase();
$DB_tree->connect(SystemSettings::get('DB_HOST'), SystemSettings::get('DB_USER'), SystemSettings::get('DB_PASS'));
$DB_tree->query("SET character_set_client='".MYSQL_CHARSET."'");
$DB_tree->query("SET character_set_connection='".MYSQL_CHARSET."'");
$DB_tree->query("SET character_set_results='".MYSQL_CHARSET."'");
$DB_tree->selectDB(SystemSettings::get('DB_NAME'));
define('VAR_DBHANDLER','DBHandler');
$Register = &Register::getInstance();
$Register->set(VAR_DBHANDLER, $DB_tree);
settingDefineConstants();

$data = array(
	'eshop_id'   => $_POST['eshopId']
   ,'order_id'   => $_POST['orderId']
   ,'srv_name'	 => $_POST['serviceName']
   ,'eshop_acc'  => $_POST['eshopAccount']
   ,'amount'     => $_POST['recipientAmount']
   ,'currency'	 => $_POST['recipientCurrency']
   ,'pay_status' => $_POST['paymentStatus']
   ,'uname'		 => $_POST['userName']
   ,'uemail'	 => $_POST['userEmail']
   ,'pay_date'	 => $_POST['paymentData']
);

ClassManager::includeClass('Order');

$orderEntry = new Order();
$orderEntry->loadByID($data['order_id']);
$pm = $orderEntry->getPaymentModule();

if(array_key_exists('secretKey', $_POST))
{
    if($pm->_getSettingValue('CONF_PAYMENT_RUPAYNEW_SECRET') != $_POST['secretKey'])
    {
        
        die('Hack detected. (02)');
    };
    
    $data['skey'] = $_POST['secretKey'];
};

if($data['eshop_id'] != $pm->_getSettingValue('CONF_PAYMENT_RUPAYNEW_ESHOPID'))
{
    die('Hack detected. (03)');
};

if(md5(implode('::', $data)) != $_POST['hash'])
{
    die('Hack detected. (04)');
};

$res = $orderEntry->exec_action(ORDACTION_PROCESS, ORDACTION_SOURCE_ADMIN);

die('End.');
?>