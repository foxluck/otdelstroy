<?php
define('CLINKPOINT_TTL',
	'LinkPoint Connect');
define('CLINKPOINT_DSCR',
	'Обработка кредитных карт через платежную систему LinkPoint (www.linkpoint.com)');

define('CLINKPOINT_CFG_STORENAME_TTL',
	'LinkPoint ID');
define('CLINKPOINT_CFG_STORENAME_DSCR',
	'Введите ваш идентификатор в системе LinkPoint');

define('CLINKPOINT_CFG_INTEGRATION_TYPE_TTL',
	'Способ интеграции');
define('CLINKPOINT_CFG_INTEGRATION_TYPE_DSCR',
	'Наиболее безопасен способ 1. Для работы способов 2 и 3 необходимо, чтобы на сервере был установлен SSL-сертификат для вашего домена, где работает магазин');
	
define('CLINKPOINT_CFG_USD_CURRENCY_TTL',
	'Доллары США');
define('CLINKPOINT_CFG_USD_CURRENCY_DSCR',
	'Сумма заказа, передаваемая в LinkPoint, указывается в долларах США. Выберите валюту из списка, которая представляет собой доллары США - это необходимо для корректного пересчета суммы заказа в доллары. Если валюта не выбрана, сумма не будет пересчитываться');

define('CLINKPOINT_TXT_PAYMENT_FORM_HTML_1',
	'Номер кредитной карты');
define('CLINKPOINT_TXT_PAYMENT_FORM_HTML_2',
	'Имя держателя карты');
define('CLINKPOINT_TXT_PAYMENT_FORM_HTML_3',
	'Срок действия карты');
define('CLINKPOINT_TXT_PAYMENT_FORM_HTML_4',
	'month');
define('CLINKPOINT_TXT_PAYMENT_FORM_HTML_5',
	'year');
	
define('CLINKPOINT_TXT_PAYMENT_PROCESS_1',
	'Введите номер кредитной карты');
define('CLINKPOINT_TXT_PAYMENT_PROCESS_2',
	'Введите имя владельца кредитной карты');
define('CLINKPOINT_TXT_PAYMENT_PROCESS_3',
	'Введите CVV (3-значное число на обороте карты)');
define('CLINKPOINT_TXT_PAYMENT_PROCESS_4',
	'Выберите месяц окончания действия карты');
define('CLINKPOINT_TXT_PAYMENT_PROCESS_5',
	'Выберите год окончания действия карты');
	
define('CLINKPOINT_TXT_AFTER_PROCESSING_HTML_1',
	'Proceed to LinkPoint secure server to complete payment');
	
define('CLINKPOINT_TXT_1',
	'1 - информация о кредитной карте вводится на сервере LinkPoint');
define('CLINKPOINT_TXT_2',
	'2 - информация о кредитной карте вводится на вашем сайте и передается на сервер LinkPoint');
define('CLINKPOINT_TXT_3',
	'3 - информация о кредитной карте вводится на вашем сайте и сохраняется в базе данных');
?>