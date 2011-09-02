<?php
$sqls = array();
$sqls[] = 'ALTER TABLE  `SC_products_opt_val_variants` ADD INDEX  `optionID` (  `optionID` )';
$sqls[] = 'ALTER TABLE  `SC_orders` ADD INDEX `statusID` (  `statusID` )';
$sqls[] = 'ALTER TABLE  `SC_order_status` ADD INDEX `predefined` (  `predefined` )';
$sqls[] = 'ALTER TABLE  `SC_module_configs` ADD INDEX `ConfigInit` (  `ConfigInit` )';
$sqls[] = 'ALTER TABLE  `SC_subscribers` ADD INDEX `customerID` (  `customerID` )';
$sqls[] = 'ALTER TABLE  `SC_spmodules` ADD INDEX `module_type` (  `module_type` )';

foreach($sqls as $sql){
	mysql_ping()&&mysql_query($sql);
}

if($res = mysql_query("SELECT COUNT(*) FROM `SC_settings` WHERE `settings_constant_name`='CONF_PRDSEARCH_TAGS'")){
	if(($row = mysql_fetch_row($res))&&($row[0]==0)){
		mysql_query("INSERT INTO `SC_settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`)
 VALUES (4, 'CONF_PRDSEARCH_TAGS', '', 'cfg_prdsearch_tags_title', 'cfg_prdsearch_tags_description', 'setting_CHECK_BOX(', 116)");
	}
}

if($res = mysql_query("SELECT COUNT(*) FROM `SC_settings` WHERE `settings_constant_name`='CONF_PRDENABLE_STATISTICS'")){
	if(($row = mysql_fetch_row($res))&&($row[0]==0)){
		mysql_query("INSERT INTO `SC_settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`)
 VALUES (4, 'CONF_PRDENABLE_STATISTICS', '', 'cfg_prdenable_statistics_title', 'cfg_prdenable_statistics_description', 'setting_CHECK_BOX(', 117)");
	}
}


$sql = "SELECT `xID` FROM `SC_divisions` WHERE `xName`='pgn_modules' LIMIT 1";
if($res = mysql_query($sql)){
	if($parent = mysql_fetch_array($res)){
		if($parent = $parent['xID']){

			$sql = "SELECT `xID` FROM `SC_divisions` WHERE `xName`='pgn_quickbooks' LIMIT 1";
			$res = mysql_query($sql);
			if($data = mysql_fetch_array($res)){
				$divisionId = $data['xID'];
			}
			else {
				$sql = "INSERT IGNORE `SC_divisions` (`xName`, `xParentID`, `xEnabled`,`xPriority`) VALUES ('pgn_quickbooks', {$parent}, 1,95)";
				$res = mysql_query($sql);
				$divisionId = mysql_insert_id();
			}

			$sql = "SELECT `ModuleID` FROM `SC_modules` WHERE `ModuleClassName` = 'Quickbooks' LIMIT 1";
			$res = mysql_query($sql);
			if($data = mysql_fetch_array($res)){
				$moduleId = $data['ModuleID'];
			}
			else {
				$sql = "INSERT IGNORE `SC_modules` (ModuleVersion, ModuleClassName, ModuleClassFile) VALUES (1, 'Quickbooks', '/products/quickbooks/class.quickbooks.php')";
				$res = mysql_query($sql);
				$moduleId = mysql_insert_id();
			}

			$sql = "SELECT `ModuleConfigID` FROM `SC_module_configs` WHERE `ModuleID` = {$moduleId} LIMIT 1";
			$res = mysql_query($sql);
			if($data = mysql_fetch_array($res)){
				$moduleConfigID = $data['ModuleConfigID'];
			}
			else {
				$sql = "INSERT IGNORE `SC_module_configs` (ModuleID, ConfigKey, ConfigInit, ConfigEnabled) VALUES ({$moduleId}, 'quickbooks', 1003, 1)";
				mysql_query($sql);
				$moduleConfigID = mysql_insert_id();
			}

			$sql = "SELECT * FROM `SC_division_interface` WHERE `xInterface` = '{$moduleConfigID}_quickbooks_page' LIMIT 1";
			$res = mysql_query($sql);
			if($data = mysql_fetch_array($res)){
			}
			else {
				$sql = "INSERT IGNORE `SC_division_interface` (xDivisionID, xInterface) VALUES ({$divisionId}, '{$moduleConfigID}_quickbooks_page')";
				mysql_query($sql);
			}

			
			
			
			$sql = "SELECT `xID` FROM `SC_divisions` WHERE `xName`='quickbooks' LIMIT 1";
			$res = mysql_query($sql);
			if($data = mysql_fetch_array($res)){
				$divisionId = $data['xID'];
			}
			else {
				$sql = "INSERT IGNORE `SC_divisions` (`xName`, `xUnicKey`, `xParentID`, `xEnabled`,`xPriority`) VALUES ('quickbooks', 'quickbooks', 1, 0,0)";
				$res = mysql_query($sql);
				$divisionId = mysql_insert_id();
			}
			
			$sql = "SELECT * FROM `SC_division_interface` WHERE `xInterface` = '{$moduleConfigID}_quickbooks' LIMIT 1";
			$res = mysql_query($sql);
			if($data = mysql_fetch_array($res)){
			}
			else {
				$sql = "INSERT IGNORE `SC_division_interface` (xDivisionID, xInterface) VALUES ({$divisionId}, '{$moduleConfigID}_quickbooks')";
				mysql_query($sql);
			}

		}
	}
}



$settings = array(
    "CONF_QUICKBOOKS_ON" => 0,
    "CONF_QUICKBOOKS_INIT" => 0,
    "CONF_QUICKBOOKS_TIME_LASTUPDATE" => "0000-00-00",
    "CONF_QUICKBOOKS_PASSWORD" => "",
    "CONF_QUICKBOOKS_ACCOUNT" => "",
    "CONF_QUICKBOOKS_TEMPLATE" => "",
    "CONF_QUICKBOOKS_INCOMEACCOUNT" => "",
    "CONF_QUICKBOOKS_COGSACCOUNT" => "",
    "CONF_QUICKBOOKS_ASSETACCOUNT" => "",

	"CONF_QUICKBOOKS_ACCOUNT_DISCOUNT" => "",
	"CONF_QUICKBOOKS_ARACCOUNT" => ""
);

foreach ($settings as $name => $value) {
	$res = mysql_query("SELECT * FROM `SC_settings` WHERE `settings_constant_name` = '{$name}'");
	if(!mysql_fetch_array($res)){
		mysql_query("INSERT INTO `SC_settings` ( `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES
    ( '{$name}', '{$value}' , NULL, NULL, NULL, 0)");

	}
}



if(mysql_ping()&&!mysql_query('SELECT `id_quickbooks` FROM `SC_categories` WHERE 0')){
	mysql_query("ALTER TABLE `SC_categories` ADD `id_quickbooks` VARCHAR( 25 )");
}
if(mysql_ping()&&!mysql_query('SELECT `id_quickbooks` FROM `SC_products` WHERE 0')){
	mysql_query("ALTER TABLE `SC_products` ADD `id_quickbooks` VARCHAR( 25 )");
}
if(mysql_ping()&&!mysql_query('SELECT `id_quickbooks` FROM `SC_customers` WHERE 0')){
	mysql_query("ALTER TABLE `SC_customers` ADD `id_quickbooks` VARCHAR( 25 )");
}
?>