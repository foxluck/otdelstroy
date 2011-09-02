<?php
if($res = mysql_query("SELECT COUNT(*) FROM `SC_settings` WHERE `settings_constant_name`='CONF_STRICT_ACCESS'")) {
	if(($row = mysql_fetch_row($res)) && ($row[0]==0)) {
		mysql_query("INSERT INTO `SC_settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`)
 VALUES (6, 'CONF_STRICT_ACCESS', 'lastname', 'cfg_strict_access_title', 'cfg_strict_access_description', 'setting_RADIOGROUP(Customer::confOrderStatusAccess(),', 118)");
	}elseif ($row && $row[0]) {
		mysql_query("UPDATE `SC_settings` SET `settings_html_function`='setting_RADIOGROUP(Customer::confOrderStatusAccess(),' WHERE `settings_constant_name`='CONF_STRICT_ACCESS'");
	}
}



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
	'cfg_strict_access_title'=>"('cfg_strict_access_title', %LANG_ID%, 'Доступ к странице заказа для незарегистрированных покупателей', 'back', 'cfg')",
	'cfg_strict_access_description'=>"('cfg_strict_access_description', %LANG_ID%, 'Позволяет установить дополнительные меры идентификации для незарегистрированных покупателей при попытке доступа к информации о заказе и его статусе.', 'back', 'cfg')",

	'cfg_strict_access_lastname'=>"('cfg_strict_access_lastname', %LANG_ID%, 'Требовать ввести фамилию', 'back', 'cfg')",
	'cfg_strict_access_code'=>"('cfg_strict_access_code', %LANG_ID%, 'Требовать ввести одноразовый пароль, который дополнительно отправляется покупателю по электронной почте', 'back', 'cfg')",
	'cfg_strict_access_auth'=>"('cfg_strict_access_auth', %LANG_ID%, 'Запретить доступ к странице заказа для незарегистрированных покупателей (доступ есть только у покупателей с постоянным аккаунтом в вашем интернет-магазине)', 'back', 'cfg')",

	'ordr_full_info_code_description'=>"('ordr_full_info_code_description', %LANG_ID%, 'Чтобы посмотреть подробную информацию о заказе, необходимо ввести одноразовый пароль (щелкните по кнопке «Получить одноразовый пароль» — пароль будет отправлен вам по электронной почте):', 'front', 'ord')",
	'ordr_status_access_code'=>"('ordr_status_access_code', %LANG_ID%, 'Одноразовый пароль', 'front', 'ord')",
	'err_wrong_mcode'=>"('err_wrong_mcode', %LANG_ID%, 'Одноразовый пароль введен неверно!', 'front', 'ord')",
	'mcode_sended'=>"('mcode_sended', %LANG_ID%, 'Одноразовый пароль отправлен вам по адресу электронной почты, указанному в заказе.', 'front', 'ord')", 
	'send_mcode'=>"('send_mcode', %LANG_ID%, 'Получить одноразовый пароль', 'front', 'ord')",
	'mcode_resend'=>"('mcode_resend', %LANG_ID%, 'Не получили пароль?', 'front', 'ord')",
	'ordr_mcode_body'=>"('ordr_mcode_body', %LANG_ID%, 'Вы запросили одноразовый пароль для просмотра информации о вашем заказе %ORDER_ID%.', 'front', 'ord')",
	'ordr_mcode_comment'=>"('ordr_mcode_comment', %LANG_ID%, 'Пароль %MCODE% будет действителен в течение %TIME% минут.', 'front', 'ord')",
	'ord_status_use_myaccount'=>"('ord_status_use_myaccount', %LANG_ID%, 'Для просмотра статуса заказа используйте личный кабинет.', 'front', 'ord')",
	'access_code'=>"('access_code', %LANG_ID%, 'Одноразовый пароль', 'general', 'gen')",
),

2=>array(
	'cfg_strict_access_title'=>"('cfg_strict_access_title', %LANG_ID%, 'Access to order information viewing pages for non-registered customers', 'back', 'cfg')",
	'cfg_strict_access_description'=>"('cfg_strict_access_description', %LANG_ID%, 'Allows setting extra authentication options for non-registered customers to access order information and order status viewing pages.', 'back', 'cfg')",

	'cfg_strict_access_lastname'=>"('cfg_strict_access_lastname', %LANG_ID%, 'Require input of customer\'s last name', 'back', 'cfg')",
	'cfg_strict_access_code'=>"('cfg_strict_access_code', %LANG_ID%, 'Require input of a one-time password additionally sent to customer\'s email address.', 'back', 'cfg')",
	'cfg_strict_access_auth'=>"('cfg_strict_access_auth', %LANG_ID%, 'Deny access for non-registered customers to order information viewing pages (access is allowed only for registered customers)', 'back', 'cfg')",

	'ordr_full_info_code_description'=>"('ordr_full_info_code_description', %LANG_ID%, 'To view detailed order information, enter your one-time password (click on \"Get one-time password\" button to have it sent to your email address):', 'front', 'ord')",
	'ordr_status_access_code'=>"('ordr_status_access_code', %LANG_ID%, 'One-time password', 'front', 'ord')",
	'err_wrong_mcode'=>"('err_wrong_mcode', %LANG_ID%, 'Incorrect one-time password entered!', 'front', 'ord')",
	'mcode_sended'=>"('mcode_sended', %LANG_ID%, 'A one-time password has been sent to the email address specified in your order information.', 'front', 'ord')", 
	'send_mcode'=>"('send_mcode', %LANG_ID%, 'Get one-time password', 'front', 'ord')",
	'mcode_resend'=>"('mcode_resend', %LANG_ID%, '(Did not receive one-time password?)', 'front', 'ord')",
	'ordr_mcode_body'=>"('ordr_mcode_body', %LANG_ID%, 'You have requested a one-time password to view detailed information about your order %ORDER_ID%.', 'front', 'ord')",
	'ordr_mcode_comment'=>"('ordr_mcode_comment', %LANG_ID%, 'Password %MCODE% will remain valid during %TIME% minutes.', 'front', 'ord')",
	'ord_status_use_myaccount'=>"('ord_status_use_myaccount', %LANG_ID%, 'Please log in to view your order status.', 'front', 'ord')",
	'access_code'=>"('access_code', %LANG_ID%, 'One-time password', 'general', 'gen')",
),
);
___update_SC_local($local);

if(class_exists('Theme')&&in_array('cleanUpCache',get_class_methods('Theme'))){
//	Theme::cleanUpCache();
}
//check indexes - move it into separate update
$sqls = array();
$sqls[] = 'ALTER TABLE  `SC_products_opt_val_variants`	 ADD INDEX `optionID`		 ( `optionID` )';
$sqls[] = 'ALTER TABLE  `SC_orders`						 ADD INDEX `statusID`		 ( `statusID` )';
$sqls[] = 'ALTER TABLE  `SC_order_status`				 ADD INDEX `predefined`		 ( `predefined` )';
$sqls[] = 'ALTER TABLE  `SC_module_configs`				 ADD INDEX `ConfigInit`		 ( `ConfigInit` )';
$sqls[] = 'ALTER TABLE  `SC_subscribers`				 ADD INDEX `customerID`		 ( `customerID` )';
$sqls[] = 'ALTER TABLE  `SC_spmodules`					 ADD INDEX `module_type`	 ( `module_type` )';

foreach($sqls as $sql) {
	mysql_ping() && mysql_query($sql);
}
//EOF
