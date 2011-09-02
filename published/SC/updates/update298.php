<?php
$sql = <<<SQL
INSERT IGNORE INTO  `SC_local` (  `id` ,  `lang_id` ,  `value` ,  `group` ,  `subgroup` ) 
VALUES 
('pmnt_more_modules_available',  '1',  '<strong>ЕЩЕ МОДУЛИ</strong>: На сайте Shop-Script доступны для загрузки <a href="http://www.shop-script.ru/features/integrations.html">дополнительные модули</a> приема платежей по банковским картам.',  'back',  'gen'),
('pmnt_more_modules_available',  '2',  '<strong>MORE MODULES</strong>: Additional credit card processing modules are <a href="http://www.shop-script.com/features/integrations.html">available for download</a> on Shop-Script website.',  'back',  'gen')
SQL;
if(mysql_query($sql)){
	if(class_exists('Language')){
		if(in_array('_dropCache',get_class_methods('Language'))){
			$language = new Language();
			$language->_dropCache();
		}
	}
}
?>