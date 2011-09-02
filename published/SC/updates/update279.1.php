<?php
$duplicate_divisions = array(
'product_out_of_stock',
'sitemap',
'formmanagment',
);

foreach($duplicate_divisions as $division){
	if($res = mysql_query("SELECT `xID` FROM `SC_divisions` WHERE `xUnicKey`='{$division}'")){
		$divisions = array();
		while($row = mysql_fetch_row($res)){
			$divisions[] = $row[0];
		}
		array_shift($divisions);
		foreach($divisions as $xID){
			if(mysql_query("DELETE FROM `SC_division_interface` WHERE `xDivisionID` = {$xID}")){
				mysql_query("DELETE FROM `SC_divisions` WHERE `xID` = {$xID}");
			}
		}
	}
}

$duplicate_settings = array(
'CONF_ENABLE_PRODUCT_SKU',
'GOOGLE_ANALYTICS_CUSTOM_SE',
'CONF_PICTRESIZE_QUALITY',
'CONF_STOREFRONT_TIME_ZONE',
'CONF_STOREFRONT_TIME_ZONE_DST',
);


foreach($duplicate_settings as $setting){
	if($res = mysql_query("SELECT `settingsID` FROM `SC_settings` WHERE `settings_constant_name`='{$setting}'")){
		$settingns = array();
		while($row = mysql_fetch_row($res)){
			$settingns[] = $row[0];
		}
		array_shift($settingns);
		foreach($settingns as $settingsID){
			mysql_query("DELETE FROM `SC_settings` WHERE `settingsID` = {$settingsID}");
		}
	}
}
?>