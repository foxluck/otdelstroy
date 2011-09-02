<?php
//updates/update279.1.php:
//CLEAN DATA FOR ALTER TABLE
mysql_query('SET NAMES UTF8');
mysql_query("set character_set_client='UTF8'");
mysql_query("set character_set_results='UTF8'");
mysql_query("set collation_connection='UTF8_general_ci'");

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

//updates/update279.2.php:

if($res = mysql_query("SELECT COUNT(*) FROM `SC_settings` WHERE `settings_constant_name`='CONF_PRDPICT_ENLARGED_SIZE'")){
	if(($row = mysql_fetch_row($res))&&($row[0]==0)){
		mysql_query("INSERT INTO `SC_settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`)
 VALUES (4, 'CONF_PRDPICT_ENLARGED_SIZE', '', 'cfg_prdpict_enlarged_size_title', 'cfg_prdpict_enlarged_size_description', 'setting_TEXT_BOX(2,', 115)");
	}
}

//updates/update279.3.php:


if(!function_exists('___update_SC_local')){
	function ___update_SC_local($local_strings, $test = false){
		$updated = false;
		$lang = array();
		if($test){
			$lang = array(1,2);
		}else{
			if($res = mysql_query('SELECT `id` FROM `SC_language`')){
				while($row = mysql_fetch_row($res)){
					$lang[] = $row[0];
				}
			}
		}
		foreach($lang as $lang_id){
			if(isset($local_strings[$lang_id])&&count($local_strings[$lang_id])){
				$delete_where = "`lang_id`={$lang_id} AND (`id` = '".implode("' OR `id`='",array_keys($local_strings[$lang_id]))."')";
				$sql_delete = "DELETE FROM `SC_local` WHERE {$delete_where}";
				if($test||mysql_query($sql_delete)){
					foreach($local_strings[$lang_id] as &$string){
						$string = str_replace('%LANG_ID%',$lang_id,$string);
					}
					$sql_insert = "INSERT INTO `SC_local` (`id`, `lang_id`, `value`, `group`, `subgroup`) VALUES ".implode($test?",\n":', ',$local_strings[$lang_id]);
					if($test){
						print $sql_insert."\n\n";
					}else{
						$updated |= mysql_query($sql_insert);
					}
				}
			}
		}
		if($updated&&class_exists('Language')){//clean cache for SC
			$language = new Language();
			if(method_exists($language,'_dropCache')){
				$language->_dropCache();
			}
		}
	}
}


$local = array(
1=>array(
'shp_method_removed'=>"('shp_method_removed', %LANG_ID%, 'Cпособ доставки удален', 'back', 'ord')",
'print_form_edit_title'=>"('print_form_edit_title', %LANG_ID%, 'Двойной клик для редактирования', 'general', 'gen')",
'ordsts_predefined_title'=>"('ordsts_predefined_title', %LANG_ID%, 'Предустановленные статусы заказов', 'back', 'ord')",
'ordsts_predefined_description_1'=>"('ordsts_predefined_description_1', %LANG_ID%, 'Отмененные заказы', 'back', 'ord')",
'ordsts_predefined_description_2'=>"('ordsts_predefined_description_2', %LANG_ID%, 'Новые заказы', 'back', 'ord')",
'ordsts_predefined_description_3'=>"('ordsts_predefined_description_3', %LANG_ID%, 'Заказы, принятые в обработку', 'back', 'ord')",
'ordsts_predefined_description_5'=>"('ordsts_predefined_description_5', %LANG_ID%, 'Успешно выполненные заказы', 'back', 'ord')",
'ordsts_predefined_description_14'=>"('ordsts_predefined_description_14', %LANG_ID%, 'Заказы, оплата по которым успешно авторизована<br />(только для заказов по кредитным картам)', 'back', 'ord')",
'ordsts_predefined_description_15'=>"('ordsts_predefined_description_15', %LANG_ID%, 'Заказы, по которым произведен возврат денег', 'back', 'ord')",
'ord_ship_to'=>"('ord_ship_to', %LANG_ID%, 'Получатель', 'general', 'gen')",
'str_logo'=>"('str_logo', %LANG_ID%, 'Адрес (URL) файла логотипа (не обязательно)', 'general', 'gen')",
'cfg_prdpict_enlarged_size_title'=>"('cfg_prdpict_enlarged_size_title',%LANG_ID%,'Уменьшать оригинальное (самое большое) загружаемое изображение продукта','back','cfg')",
'cfg_prdpict_enlarged_size_description'=>"('cfg_prdpict_enlarged_size_description',%LANG_ID%,'Введите размер в пикселях, к которому будут приведены оригиналы загружаемых изображений (рекомендуемое значение: 600) или оставьте поле пустым, чтобы не изменять оригиналы.','back','cfg')",
'sr_please_contact_seller'=>"('sr_please_contact_seller', %LANG_ID%, 'Точная стоимость доставки не расчитана', 'general', 'gen')",
'print_form_address_not_found'=>"('print_form_address_not_found', %LANG_ID%, 'Address was not found on a map. Validate address and click \"Search again\".', 'general', 'gen')",
'print_form_edit_before_print'=>"('print_form_edit_before_print', %LANG_ID%, 'Корректировка перед печатью', 'general', 'gen')",
),

2=>array(
'shp_method_removed'=>"('shp_method_removed', %LANG_ID%, 'Shipping method removed', 'back', 'ord')",
'print_form_edit_title'=>"('print_form_edit_title', %LANG_ID%, 'Double-click to modify', 'general', 'gen')",
'ordsts_predefined_title'=>"('ordsts_predefined_title', %LANG_ID%, 'Predefined order statuses', 'back', 'ord')",
'ordsts_predefined_description_1'=>"('ordsts_predefined_description_1', %LANG_ID%, 'Cancelled orders', 'back', 'ord')",
'ordsts_predefined_description_2'=>"('ordsts_predefined_description_2', %LANG_ID%, 'New orders', 'back', 'ord')",
'ordsts_predefined_description_3'=>"('ordsts_predefined_description_3', %LANG_ID%, 'Approved orders', 'back', 'ord')",
'ordsts_predefined_description_5'=>"('ordsts_predefined_description_5', %LANG_ID%, 'Successfully completed orders', 'back', 'ord')",
'ordsts_predefined_description_14'=>"('ordsts_predefined_description_14', %LANG_ID%, 'Successfully charged orders<br />(only for credit card orders)', 'back', 'ord')",
'ordsts_predefined_description_15'=>"('ordsts_predefined_description_15', %LANG_ID%, 'Refunded orders', 'back', 'ord')",
'ord_ship_to'=>"('ord_ship_to', %LANG_ID%, 'Ship to', 'general', 'gen')",
'str_logo'=>"('str_logo', %LANG_ID%, 'Logo file URL (optional)', 'general', 'gen')",
'cfg_prdpict_enlarged_size_title'=>"('cfg_prdpict_enlarged_size_title',%LANG_ID%,'Resize product source (largest) image','back','cfg')",
'cfg_prdpict_enlarged_size_description'=>"('cfg_prdpict_enlarged_size_description',%LANG_ID%,'Enter desired image size (in pixels) for source product image that you upload (recommended value: 600), or leave this field empty so source images will not be resized.','back','cfg')",
'sr_please_contact_seller'=>"('sr_please_contact_seller', %LANG_ID%, 'Call for exact quote', 'general', 'gen')",
'print_form_address_not_found'=>"('print_form_address_not_found', %LANG_ID%, 'Адрес не найден на карте. Уточните адрес и повторите поиск.', 'general', 'gen')",
'print_form_edit_before_print'=>"('print_form_edit_before_print', %LANG_ID%, 'Edit before printing', 'general', 'gen')",
),
);
___update_SC_local($local);

//updates/update279.4.php:

if($res = mysql_query('SELECT `code`, `key` FROM `SC_htmlcodes` WHERE `key`=\'ncxrvx57\' OR `key`=\'p5kgoddr\'')){
	$htmlcodes = array();
	while($row = mysql_fetch_row($res)){
		$htmlcodes[$row[1]] = str_replace('Language','{lbl_str_language}',$row[0]);
	}
	if(count($htmlcodes)){
		foreach($htmlcodes as $key=>$code){
			if ( ini_get('magic_quotes_gpc') ){
				$code = trim( stripslashes($code) );
			}else{
				$code = trim( $code );
			}
			mysql_query('UPDATE `SC_htmlcodes` SET `code`=\''.$code.'\' WHERE `key`=\''.$key.'\'');
		}
	}
}

if($res = mysql_query('SELECT `code`, `key` FROM `SC_htmlcodes` WHERE `code` LIKE \'%{lbl\_language}%\'')){
	$htmlcodes = array();
	while($row = mysql_fetch_row($res)){
		$htmlcodes[$row[1]] = str_replace('{lbl_language}','{lbl_str_language}',$row[0]);
	}
	if(count($htmlcodes)){
		foreach($htmlcodes as $key=>$code){
			if ( ini_get('magic_quotes_gpc') ){
				$code = trim( stripslashes($code) );
			}else{
				$code = trim( $code );
			}
			mysql_query('UPDATE `SC_htmlcodes` SET `code`=\''.$code.'\' WHERE `key`=\''.$key.'\'');
		}
	}
}

//updates/update279.5.php:

$sqls = array(
    "ALTER TABLE `SC_payment_types` ADD `logo` TEXT",
    "ALTER TABLE `SC_shipping_methods` ADD `logo` TEXT",
    
);
foreach ($sqls as $sql) {
    $res  = @mysql_query($sql);
    if (!$res) {
        //throw new Exception(mysql_error(), mysql_errno());
    }
}
?>