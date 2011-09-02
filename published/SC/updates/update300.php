<?php

//fix bug with change default aux page slug - for missed this one
mysql_query("UPDATE `SC_divisions` SET `xName` = 'pgn_ap_1' WHERE `xName`='pgn_about_shoppingcart'");
mysql_query("UPDATE `SC_divisions` SET `xName` = 'pgn_ap_2' WHERE `xName`='pgn_shipping_payment'");

$lang = array();
if(mysql_ping()&&($res = mysql_query('SELECT `id`, `iso2` FROM `SC_language`'))){
	while($row = mysql_fetch_row($res)){
		$lang[$row[0]] = $row[1];
	}
	if(($res = mysql_query('SELECT `settings_value` FROM  `SC_settings` WHERE  `settings_constant_name`= \'CONF_DEFAULT_LANG\''))&&($row = mysql_fetch_row($res))){
		$def_lang = $lang[$row[0]];
	}
}



//add multilanguage news
if(mysql_ping()&&mysql_query('SELECT `title` FROM `SC_news_table` WHERE 0')){
	if($def_lang){
		$queries = array();
		$queries[] = sprintf('ALTER TABLE  `SC_news_table` CHANGE  `title`  `title_%1$s` TEXT, CHANGE  `textToPublication`  `textToPublication_%1$s` TEXT',$def_lang);
		foreach($lang as $iso2){
			if($iso2 != $def_lang){
				$queries[] = sprintf('ALTER TABLE  `SC_news_table` ADD  `title_%1$s` TEXT DEFAULT NULL AFTER  `title_%2$s`, ADD  `textToPublication_%1$s` TEXT DEFAULT NULL AFTER  `textToPublication_%2$s`',$iso2,$def_lang);
				$queries[] = sprintf('UPDATE `SC_news_table` SET  `title_%1$s` = `title_%2$s`, `textToPublication_%1$s` = `textToPublication_%2$s`',$iso2,$def_lang);
			}
		}
		foreach($queries as $query){
			if(!mysql_query($query)){
				break;
			}
		}
	}
}

//add "Social netwroks integration" and etc screens
$divisions = array(
	'social_networks'=>array(
		'count'=>0,
		'id'=>0,
		'parent_id'=>0,
		'name'=>'pgn_social_networks',
		'key'=>'social_networks',
		'parent_name'=>'pgn_modules',
		'module_config'=>48,//TODO detect it
		'priority'=>97,
),
);

$parents = array();
foreach($divisions as $division){
	$parents[] = $division['parent_name'];
}
$parents = array_unique($parents);
$parent_id = array();
$sql = "SELECT `xName`, `xID` FROM `SC_divisions` WHERE `xName` IN ('".implode("', '",$parents)."')";
if($res = mysql_query($sql)){
	while($row = mysql_fetch_row($res)){
		$parent_id[$row[0]] = (int)$row[1];
	}
}
$divisions_keys = array_keys($divisions);
$sql = "SELECT `xUnicKey`, COUNT(*) FROM `SC_divisions` WHERE `xUnicKey` IN ('".implode("', '",$divisions_keys)."') GROUP BY `xUnicKey`";
if($res = mysql_query($sql)){
	while($row = mysql_fetch_row($res)){
		$divisions[$row[0]]['count'] = $row[1];
	}
}

foreach($divisions as $key=>$division){
	if($division['count']>1){
		//TODO remove duplicates
	}elseif($division['count']==0){
		if(isset($parent_id[$division['parent_name']])&&$parent_id[$division['parent_name']]){
			$division['parent_id'] = $parent_id[$division['parent_name']];
			$insert_sql = <<<SQL
INSERT INTO `SC_divisions`
(`xName`,				`xKey`,	`xUnicKey`,	`xParentID`,				`xEnabled`, `xPriority`, `xTemplate`, `xLinkDivisionUKey`) 
VALUES 
('pgn_social_networks', '',		'{$key}',	{$division['parent_id']},	1,			{$division['priority']},			'', '')
SQL;

			if(($res = mysql_query($insert_sql))&&($division['id'] = mysql_insert_id())){
				$interface_sql = <<<SQL
INSERT INTO `SC_division_interface` 
(`xDivisionID`, `xInterface`, `xPriority`, `xInheritable`) 
VALUES 
({$division['id']}, '{$division['module_config']}_{$key}', 0, 0)
SQL;
				mysql_query($interface_sql);
			}
		}
	}
}

if(mysql_ping()&&!mysql_query('SELECT `vkontakte_type` FROM `SC_categories` WHERE 0')){
	mysql_query('ALTER TABLE  `SC_categories` ADD  `vkontakte_type` INT DEFAULT 0');
}

if(mysql_ping()&&!mysql_query('SELECT `vkontakte_update_timestamp` FROM `SC_products` WHERE 0')){
	mysql_query('ALTER TABLE  `SC_products` ADD  `vkontakte_update_timestamp` INT(11)');
}

if(mysql_ping()&&!mysql_query('SELECT `vkontakte_id` FROM `SC_customers` WHERE 0')){
	mysql_query('ALTER TABLE  `SC_customers` ADD  `vkontakte_id` INT(11)');
}

if(mysql_ping()&&(in_array('ru',$lang))&&($res = mysql_query('SELECT COUNT(*) FROM  `SC_division_interface` WHERE `xInterface` LIKE \'%vkontaktecheckout_button\''))){
	if(($row = mysql_fetch_row($res))&&(!$row[0])){
		mysql_query("INSERT INTO `SC_division_interface` (`xDivisionID`, `xInterface`, `xPriority`, `xInheritable`) VALUES
	(37, '25_vkontaktecheckout_button', 0, 0)");
	}
}


//locale

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
						print "{$sql_delete};\n\n{$sql_insert}\n\n";
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


//TODO add or update this strings into sc_data.*.sql
mysql_query("UPDATE `SC_local` SET `value`='Максимальная из скидок по группе пользователя, накопительной и по сумме заказа плюс скидка по купону (скидка по купону всегда прибавляется)' WHERE `lang_id`=1 AND `id`='cfg_calc_dsc_max'");
mysql_query("UPDATE `SC_local` SET `value`='Maximum of discounts by customer group, order amount, all orders amount, plus discount by coupon (coupon discount is always added)' WHERE `lang_id`=2 AND `id`='cfg_calc_dsc_max'");

$local = array(
1=>array(
'pgn_social_networks'				=>"('pgn_social_networks', %LANG_ID%, 'Соцсети', 'back', 'gen')",
'social_networks_page_description'	=>"('social_networks_page_description', %LANG_ID%, 'Shop-Script интегрирован с социальными сетями «Вконтакте» и «Фейсбук». Интеграция заключается в возможности разместить ваш интернет-магазин (продукты) внутри социальной сети и принимать заказы непосредственно из сети сразу в ваш магазин.', 'back', 'gen')",
'social_networks_hint_title'		=>"('social_networks_hint_title', %LANG_ID%, 'Дополнительно', 'back', 'gen')",
'social_networks_hint'				=>"('social_networks_hint', %LANG_ID%, 'Предложенные выше методы позволяют интегрировать магазин с соцсетями в направлении «соцсеть &rarr; магазин». В дополнение к такой интеграции рекомендуем «включить» интеграцию в обратном направлении «магазин → соцсеть», разместив в на страницах основной витрины магазина: 1) ссылки на страницы (группы) вашего магазина в соцсетях, 2) кнопки Like, «Мне нравится» и подобные на страницах продуктов. Это можно сделать с помощью редактора дизайна (см. ссылки на инструкции по интеграции с соцсетями выше).', 'back', 'gen')",

'prdset_vkontakte_update_date'		=>"('prdset_vkontakte_update_date', %LANG_ID%, 'Последний экспорт во «Вконтакт»', 'back', 'prd')",
'prdcat_vkontakte_change'			=>"('prdcat_vkontakte_change', %LANG_ID%, 'Экспорт во «Вконтакт»', 'back', 'prd')",
'prdcat_vkontakte_remove'			=>"('prdcat_vkontakte_remove', %LANG_ID%, 'Удалить из каталога «Вконтакта»', 'back', 'prd')",
'prdcat_social_networks_export'		=>"('prdcat_social_networks_export', %LANG_ID%, 'Вконтакте', 'back', 'prd')",
'prdcat_vkontakte_category_type'	=>"('prdcat_vkontakte_category_type', %LANG_ID%, 'Раздел каталога продуктов «Вконтакта», в который экспортировать продукты этой категории', 'back', 'prd')",
'prdimport_csv_desc2'				=>"('prdimport_csv_desc2',  %LANG_ID%, 'В закачанном файле обнаружены следующие колонки.<br>Соотнесите каждую из этих колонок с полем в базе данных.<br>В левой колонке указаны названия столбцов.', 'back', 'ine')",
'powered_by_external'				=>"('powered_by_external',%LANG_ID%,'Работает на основе <a href=\"http://www.shop-script.ru/\" style=\"font-weight: normal\" target=\"_blank\">скрипта интернет-магазина</a> <em>WebAsyst Shop-Script</em>','hidden','gen')",

//fix old strings
'prdlist_description'				=>"('prdlist_description', %LANG_ID%, 'Здесь вы можете объединять различные продукты вашего магазина в списки. <br />Списки используются для наглядного представления продуктов вашим покупателям.<br /><br />С помощью инструментов редактирования дизайна вы сможете отображать любой из списков продуктов в пользовательской части магазина.<br />Примеры списков: специальные предложения, бестселлеры, новые поступления, продукты со скидкой и т.п.', 'back', 'gen')",
'imm_delall_confirmation'			=>"('imm_delall_confirmation', %LANG_ID%, 'Вы действительно хотите удалить отмеченные изображения?', 'back', 'gen')",
'checkout_permanent_registering'	=>"('checkout_permanent_registering', %LANG_ID%, 'Я хочу зарегистрировать постоянный аккаунт в %SHOPNAME%, чтобы повторно не вводить информацию при будущих заказах', 'front', 'gen')",
'pmnt_paymtd_online_description'	=>"('pmnt_paymtd_online_description', %LANG_ID%, '<strong>Через платежную онлайн-систему</strong><br /> Прием платежей в таких платежных системах как WebMoney, Яндекс.Деньги, PayPal и другие.', 'back', 'gen')",
'checkout_transaction_result_failure'	=>"('checkout_transaction_result_failure', %LANG_ID%, 'Пожалуйста, повторите попытку позже или свяжитесь с нами для разрешения проблемы. В своем сообщении укажите номер заказа.', 'back', 'gen')",
'pmnt_calctax_for_this_payment_type'	=>"('pmnt_calctax_for_this_payment_type', %LANG_ID%, 'Раcсчитывать налог для этого способа оплаты', 'back', 'gen')",
'usr_export_userlist_to_csv'		=>"('usr_export_userlist_to_csv', %LANG_ID%, 'Экспортировать этих пользователей в CSV-файл (MS Excel, OpenOffice)', 'back', 'gen')",
'loc_lang_ltr_descr'				=>"('loc_lang_ltr_descr', %LANG_ID%, 'Направление текста: LTR (слева направо) или RTL (справа налево).', 'back', 'gen')",
'dscnt_order_amount_percent_value'	=>"('dscnt_order_amount_percent_value', %LANG_ID%, 'Действующая скидка, если стоимость заказа выше указанной левее суммы, %', 'back', 'gen')",

'prdimport_csv_desc1'				=>"('prdimport_csv_desc1', %LANG_ID%, 'В этом разделе вы можете <strong>импортировать продукты в ваш магазин из файла CSV</strong> (Comma Separated Values; файл с разделителями-запятыми). CSV файлы вы можете создать и редактировать с помощью Microsoft Excel или <a href=\"http://ru.openoffice.org/\" target=\"_blank\">OpenOffice</a>.<br /><br />Например, если вы хотите импортировать продукты в интернет-магазин из вашего прайс-листа в Excel, нужно сохранить прайс-лист в формате CSV (пункт в меню \"Сохранить как...\" - затем выберите CSV в выборе формата файла).<br />Далее, выберите сохраненный файл в следующей форме, и укажите кодировку файла (наиболее вероятно, это будет кодировка cp1251).<br /><strong>ВАЖНО: Система может производить загрузку данных только из CSV файлов только с определенной организацией (структурой) строк и столбцов.</strong> Это значит, что вам необходимо привести ваш прайс-лист к такой структуре для того, чтобы загрузить файл.<br />Посмотрите <a href=\"http://www.webasyst.ru/support/shop/manual.html#import-kataloga-tovarov-iz-csv\" target=\"_blank\">подробное описание структуры файла</a>.<br /><br />Также в этом разделе вы можете импортировать продукты из <strong>файла со списком номенклатуры, экспортированного из 1С: Предприятие</strong>.<br />Различие только в разделителе файла: в файле Excel - это точка с запятой (по умолчанию), а в списке номенклатуры 1С - табуляция.<br />Файл со списком номенклатуры можно получить в программе 1С: Предприятие в разделе \"Список номенклатуры\". Файл должен быть сохранен в формате \"Текстовый файл (с разделителями-табуляциями)\".', 'back', 'gen')",
'thm_designeditor_descr_advanced'	=>"('thm_designeditor_descr_advanced', %LANG_ID%, 'Редактирование HTML-кода страницы, которую вы видите в простом режиме редактирования дизайна(конструкторе).<br />Используйте панель справа для добавления новых компонент в ваш магазин.<br />Смотрите более подробную информацию в нашем <a href=\"http://www.webasyst.ru/support/shop/manual.html#Redaktor-dizaina\" target=\"_blank\">описании работы редактора дизайна</a> и <a href=\"http://www.shop-script.ru/demo/design-editor-tutorial.html\" target=\"_blank\">описании примера пошагового изменения дизайна</a>.', 'back', 'gen')",
'thm_designeditor_descr_simple'		=>"('thm_designeditor_descr_simple', %LANG_ID%, '<b>Перетаскивайте компоненты магазина</b>, обозначенные красной пунктирной линией, по странице с помощью мышки. Вы можете перемещать каждый компонент между контейнерами (областями, обозначенными серой пунктирной линией).<br /><b>Двойной клик по компоненту</b> для редактирования его настроек.<br />Используйте колонку справа для того, чтобы добавлять новые компоненты.<br />Смотрите более подробную информацию в нашем <a href=\"http://www.shop-script.ru/demo/design-editor-tutorial.html\" target=\"_blank\">описании примера пошагового изменения дизайна</a>.', 'back', 'gen')",
'pwgt_description'					=>"('pwgt_description', %LANG_ID%, 'Здесь вы найдете инструменты, с помощью которых сможете <strong>превратить ваш любой веб-сайт или блог в интернет-магазин</strong> &mdash; будь то веб-сайт со сложной системой управления, веб-сайт на Народ.ру, или же блог ЖЖ, Mail.Ru, Яндекс, Blogger &mdash; это не имеет значения.<br /><br />Виджет (widget) &mdash; это фрагмент HTML-кода, который вы добавляете на страницу вашего веб-сайта, а он реализуют некоторую функцию. Здесь вы можете получить HTML-код виджета, который отобразит информацию о любом продукте вашего интернет-магазина (который вы добавите здесь), или же который дает возможность заказать определенный продукт прямо на вашем веб-сайте или блоге, не покидая его контекст.<br /><br />Для внедрения виджета на ваш веб-сайт просто получите его HTML-код здесь и добавьте на страницу сайта.<br />Все заказы, которые посетители вашего веб-сайта оформят, вы увидите здесь - в администрировании магазина, а также получите уведомления о них по электронной почте.<br /><br />Смотрите наши <a href=\"http://www.webasyst.ru/support/shop/manual.html#Widgets\" target=\"_blank\">примеры использования виджетов</a>.', 'back', 'gen')",
//new
'str_printforms_logo'				=>"('str_printforms_logo', %LANG_ID%, 'Логотип для печатных форм', 'back', 'gen')",
'prdset_1c_sync'					=>"('prdset_1c_sync', %LANG_ID%, 'CommerceML-идентификатор', 'back', 'gen')",
'str_no_result'						=>"('str_no_result', %LANG_ID%, 'Отсутствует', 'back', 'gen')",
'pgn_1c'							=>"('pgn_1c', %LANG_ID%, '1C', 'back', 'gen')",
),
2=>array(
'pgn_social_networks'				=>"('pgn_social_networks', %LANG_ID%, 'Social', 'back', 'gen')",
'social_networks_page_description'	=>"('social_networks_page_description', %LANG_ID%, 'Shop-Script + Facebook integration is in the ability to embed your online storefront into Facebook as a native application, and accept orders directly from Facebook.', 'back', 'gen')",
'social_networks_hint_title'		=>"('social_networks_hint_title', %LANG_ID%, 'Like buttons', 'back', 'gen')",
'social_networks_hint'				=>"('social_networks_hint', %LANG_ID%, 'In addition to embedding your storefront into Facebook it is a good idea to 1) make a link to your Facebook page from your online storefront, 2) embed Facebook \"Like\" buttons into storefront product pages (copy-and-paste Like button code into your product using Shop-Script''s design editor).', 'back', 'gen')",

'prdset_vkontakte_update_date'		=>"('prdset_vkontakte_update_date', %LANG_ID%, 'Last export to VK catalog', 'back', 'prd')",
'prdcat_vkontakte_change'			=>"('prdcat_vkontakte_change', %LANG_ID%, 'Export to VK', 'back', 'prd')",
'prdcat_vkontakte_remove'			=>"('prdcat_vkontakte_remove', %LANG_ID%, 'Remove from VK', 'back', 'prd')",
'prdcat_social_networks_export'		=>"('prdcat_social_networks_export', %LANG_ID%, 'VK', 'back', 'prd')",
'prdcat_vkontakte_category_type'	=>"('prdcat_vkontakte_category_type', %LANG_ID%, 'VK products catalog department for this category', 'back', 'prd')",
'prdimport_csv_desc2'				=>"('prdimport_csv_desc2', %LANG_ID%, 'Your file contains following columns (see below).<br>Please associate each column with a database field.', 'back', 'ine')",
'powered_by_external'				=>"('powered_by_external',%LANG_ID%,'Powered by WebAsyst Shop-Script <a href=\"http://www.shop-script.com/\" style=\"font-weight: normal\" target=\"_blank\">shopping cart software</a>','hidden','gen')",

//fix old strings
'cfg_google_maps_api_key_descr'		=>"('cfg_google_maps_api_key_descr', %LANG_ID%, 'Input your Google Maps API key if you would like a feature that allows viewing customer address on map directly from order info page. <a href=\"http://code.google.com/apis/maps/signup.html\" target=\"_blank\">Register new API key</a> if you don''t have one.', 'back', 'gen')",

'prdimport_csv_desc1'				=>"('prdimport_csv_desc1', %LANG_ID%, 'This section allows you to <strong>import products from a CSV file</strong> (Comma Separated Values file) to your online storefront.<br /> Prepare your products catalog file in Microsoft Excel, save it as a CSV file and upload it using the following form.<br />Please note that <strong>you can only import data from CSV files of a certain columns/rows structure</strong> (you can not import data from any file with custom (ununified) structure).<br />Please refer to our <a href=\"http://www.webasyst.net/support/shop/manual.html#Bulk-import-export-operations\" target=\"_blank\">description of supported CSV file structure (organization)</a> before importing data to your online store.', 'back', 'gen')",
'thm_designeditor_descr_advanced'	=>"('thm_designeditor_descr_advanced', %LANG_ID%, 'Edit HTML code of what you see in simple editor mode.<br />Use right sidebar to embed more components to your storefront.<br />Containers are defined using this code fragments: &lt;!-- cpt_container_start --&gt; ... &lt;!-- cpt_container_end --&gt; (containers are areas that allows you to arrange store components using drag&drop in simple editor mode).<br />See our <a href=\"http://www.webasyst.net/support/shop/manual.html#Design-editor\" target=\"_blank\">design editor guidelines</a> and <a href=\"http://www.shop-script.com/demo/design-editor-tutorial.html\" target=\"_blank\">design editor tutorial</a>.', 'back', 'gen')",
'thm_designeditor_descr_simple'		=>"('thm_designeditor_descr_simple', %LANG_ID%, '<b>Drag & drop store components</b> (red dotted lined areas) to arrange your store layout. You can place components only within <b>containers</b> (areas marked with gray dotted lines).<br /><b>Double-click any component</b> to edit its settings.<br />Use right sidebar to embed more components to your storefront.<br />See our <a href=\"http://www.shop-script.com/demo/design-editor-tutorial.html\" target=\"_blank\">design editor tutorial</a>.', 'back', 'gen')",
'pwgt_description'					=>"('pwgt_description', %LANG_ID%, 'Widgets is a tool that allows you to <strong>turn any simple website or blog to online store</strong> &mdash; it could be your custom website with complex CMS, your MySpace page, Facebook, LiveJournal, Blogger, Yahoo! or any other page &mdash; this doesn''t really matter.<br /><br /> Widget is a HTML-code which you embed on your web page, and it implements some functionality. Here you can get widgets for representing whether information about products you add here or \"Add to cart\" buttons that can be used for any abstract product or service that is already described on your website. Your website visitors will be able to buy directly on your website without loosing its context.<br /><br /> All you need to do is get widget code here and then embed it to your website or blog.<br />All orders that you get using widgets are saved here, and you can always access and manage them in \"Orders\" section.<br /><br /> See our <a href=\"http://www.webasyst.net/support/shop/manual.html#Widgets\" target=\"_blank\">examples of using widgets for selling online on any website</a>.', 'back', 'gen')",
//new
'str_printforms_logo'				=>"('str_printforms_logo', %LANG_ID%, 'Print forms logo', 'back', 'gen')",
'prdset_1c_sync'					=>"('prdset_1c_sync', %LANG_ID%, 'CommerceML ID', 'back', 'gen')",
'str_no_result'						=>"('str_no_result', %LANG_ID%, 'n/a', 'back', 'gen')",
'pgn_1c'							=>"('pgn_1c', %LANG_ID%, '1С', 'back', 'gen')",
),
);

___update_SC_local($local);


//1C integration
if ( in_array("ru", $lang) ) {
	$sql = "SELECT `xID` FROM `SC_divisions` WHERE `xName`='pgn_modules' LIMIT 1";
	if($res = mysql_query($sql)){
		if($parent = mysql_fetch_array($res)){
			if($parent = $parent['xID']){

				$sql = "SELECT `xID` FROM `SC_divisions` WHERE `xName`='pgn_1c' LIMIT 1";
				$res = mysql_query($sql);
				if($data = mysql_fetch_array($res)){
					$divisionId = $data['xID'];
				}
				else {
					$sql = "INSERT IGNORE `SC_divisions` (`xName`, `xParentID`, `xEnabled`,`xPriority`) VALUES ('pgn_1c', {$parent}, 1,95)";
					$res = mysql_query($sql);
					$divisionId = mysql_insert_id();
				}

				$sql = "SELECT `ModuleID` FROM `SC_modules` WHERE `ModuleClassName` = 'ExportTo1c' LIMIT 1";
				$res = mysql_query($sql);
				if($data = mysql_fetch_array($res)){
					$moduleId = $data['ModuleID'];
				}
				else {
					$sql = "INSERT IGNORE `SC_modules` (ModuleVersion, ModuleClassName, ModuleClassFile) VALUES (1, 'ExportTo1c', '/products/exportto1c/class.exportto1c.php')";
					$res = mysql_query($sql);
					$moduleId = mysql_insert_id();
				}

				$sql = "SELECT `ModuleConfigID` FROM `SC_module_configs` WHERE `ModuleID` = {$moduleId} LIMIT 1";
				$res = mysql_query($sql);
				if($data = mysql_fetch_array($res)){
					$moduleConfigID = $data['ModuleConfigID'];
				}
				else {
					$sql = "INSERT IGNORE `SC_module_configs` (ModuleID, ConfigKey, ConfigInit, ConfigEnabled) VALUES ({$moduleId}, 'exportto1c', 1002, 1)";
					mysql_query($sql);
					$moduleConfigID = mysql_insert_id();
				}

				$sql = "SELECT * FROM `SC_division_interface` WHERE `xInterface` = '{$moduleConfigID}_export_page' LIMIT 1";
				$res = mysql_query($sql);
				if($data = mysql_fetch_array($res)){
				}
				else {
					$sql = "INSERT IGNORE `SC_division_interface` (xDivisionID, xInterface) VALUES ({$divisionId}, '{$moduleConfigID}_export_page')";
					mysql_query($sql);
				}

			}
		}
	}
	$sql = "SELECT `xID` FROM `SC_divisions` WHERE `xName`='1c_exchange' LIMIT 1";
	$res = mysql_query($sql);
	if($data = mysql_fetch_array($res)){
		$divisionId = $data['xID'];
	}
	else {
		$sql = "INSERT IGNORE `SC_divisions` (`xName`, `xUnicKey`, `xParentID`, xEnabled) VALUES ('1c_exchange', '1c_exchange', 1, 0)";
		$res = mysql_query($sql);
		$divisionId = mysql_insert_id();
	}

	$sql = "SELECT * FROM `SC_division_interface` WHERE `xInterface` = '{$moduleConfigID}_exchange_1c' LIMIT 1";
	$res = mysql_query($sql);
	if($data = mysql_fetch_array($res)){
	}
	else {
		$sql = "INSERT IGNORE `SC_division_interface` (xDivisionID, xInterface) VALUES ({$divisionId}, '{$moduleConfigID}_exchange_1c')";
		mysql_query($sql);
	}

	$res = mysql_query("SELECT * FROM `SC_settings` WHERE `settings_constant_name` = 'CONF_1C_TIME_LASTEXPORT'");
	if(!mysql_fetch_array($res)){
		mysql_query("INSERT INTO `SC_settings` ( `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES
	( 'CONF_1C_TIME_LASTEXPORT', 0 , NULL, NULL, NULL, 0)");

	}
	$name = "CONF_1C_ON";
	$value = "0";
	$res = mysql_query("SELECT * FROM `SC_settings` WHERE `settings_constant_name` = '{$name}'");
	if(!mysql_fetch_array($res)){
		mysql_query("INSERT INTO `SC_settings` ( `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES
	( '{$name}', '{$value}' , NULL, NULL, NULL, 0)");

	}


}

if(mysql_ping()&&!mysql_query('SELECT `id_1c` FROM `SC_categories` WHERE 0')){
	mysql_query("ALTER TABLE `SC_categories` ADD `id_1c` VARCHAR( 36 )");
}
if(mysql_ping()&&!mysql_query('SELECT `id_1c` FROM `SC_products` WHERE 0')){
	mysql_query("ALTER TABLE `SC_products` ADD `id_1c` VARCHAR( 74 )");
}

//ML inputs
$settings = array('CONF_HOMEPAGE_META_KEYWORDS','CONF_HOMEPAGE_META_DESCRIPTION','CONF_DEFAULT_TITLE','CONF_SHOP_NAME');
$sql = 'SELECT `settingsID`, `settings_value`, `settings_html_function` FROM `SC_settings` WHERE `settings_constant_name` IN (\''.implode("', '",$settings)."')";
if($lang && ($res = mysql_query($sql))){
	while($row = mysql_fetch_assoc($res)){
		if(preg_match('/TEXT_BOX\(/',$row['settings_html_function'])){
			$value = array();
			foreach($lang as $iso2){
				$value[$iso2] = $row['settings_value'];
			}
			$value = mysql_real_escape_string(serialize($value));
			$update_sql = "UPDATE `SC_settings` SET `settings_value`='{$value}', `settings_html_function`='setting_TEXT_BOX_ML(0,' WHERE `settingsID`={$row['settingsID']}";
			if(!mysql_query($update_sql)){
				//var_dump(mysql_error());
			}
		}
	}
}



//Printforms Logo
$name = "CONF_PRINTFORM_COMPANY_LOGO";
$value = "";
$res = mysql_query("SELECT * FROM `SC_settings` WHERE `settings_constant_name` = '{$name}'");
if(!mysql_fetch_array($res)){
	mysql_query("INSERT INTO `SC_settings` ( `settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES
( 2, '{$name}', '{$value}' , 'str_printforms_logo', '', 'setting_SINGLE_FILE(DIR_IMG,', 120)");

}



if(defined('DIR_DATA_SC')){
	$dir = DIR_DATA_SC.'/temp';
	$files = file_exists($dir)?scandir($dir):array();
	$removePattern = '/(^\.cache\.)|(^\.settings\.)/';
	$subFolders = array();
	foreach($files as $file){
		if(($file=='.')||($file=='..')){
			continue;
		}
		$fullPath = $dir.'/'.$file;
		if(is_file($fullPath)
		&&(is_null($removePattern)||preg_match($removePattern,$file))
		//&&(is_null($skippedPattern)||!preg_match($skippedPattern,$file))
		){
			@unlink($fullPath);
		}
	}
}

if(class_exists('Theme')&&in_array('cleanUpCache',get_class_methods('Theme'))){
	Theme::cleanUpCache();
}
?>