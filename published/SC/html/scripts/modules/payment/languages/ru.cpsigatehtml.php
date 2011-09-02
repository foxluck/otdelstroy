<?php
define('CPSIGATEHTML_TTL',
	'PSiGate HTML Posting');
define('CPSIGATEHTML_DSCR',
	'Обработка кредитных карт через платежную систему PSiGate (www.psigate.com). PSiGate HTML Posting.');
	
define('CPSIGATEHTML_CFG_MERCHANTID_TTL',
	'PSiGate Merchant ID');
define('CPSIGATEHTML_CFG_MERCHANTID_DSCR',
	'Введите ваш идентификатор в системе PSiGate');
define('CPSIGATEHTML_CFG_CHARGETYPE_TTL',
	'Способ авторизации карты');
define('CPSIGATEHTML_CFG_CHARGETYPE_DSCR',
	'Выберите способ обработки кредитных карт');
define('CPSIGATEHTML_CFG_TESTMODE_TTL',
	'Тестовый режим');
define('CPSIGATEHTML_CFG_TESTMODE_DSCR',
	'');
define('CPSIGATEHTML_CFG_REQUEST_CC_INFO_TTL',
	'Запрашивать информацию о кредитной карте на страницах вашего магазина');
define('CPSIGATEHTML_CFG_REQUEST_CC_INFO_DSCR',
	'Для работы этой опции необходимо наличие SSL-сертификата для домена, где работает магазин. Если вы включите данную опцию, вам необходимо сообщить об этом в PSiGate.<br>Если опция выключена, информация о карте будет запрашиваться на сервере PSiGate');
define('CPSIGATEHTML_CFG_USD_CURRENCY_TTL',
	'Доллары США');
define('CPSIGATEHTML_CFG_USD_CURRENCY_DSCR',
	'Сумма заказа, передаваемая в PSiGate, указывается в долларах США. Выберите валюту из списка, которая представляет собой доллары США - это необходимо для корректного пересчета суммы заказа в доллары. Если валюта не выбрана, сумма не будет пересчитываться');

	
define('CPSIGATEHTML_TXT_GETCHARGETYPEOPTIONS_1',
	'Предавторизация (Preauth)');
define('CPSIGATEHTML_TXT_GETCHARGETYPEOPTIONS_2',
	'Продажа (Sale)');
	
define('CPSIGATEHTML_TXT_PAYMENT_FORM_HTML_1',
	'Номер кредитной карты:');
define('CPSIGATEHTML_TXT_PAYMENT_FORM_HTML_2',
	'Срок действия карты:');
define('CPSIGATEHTML_TXT_PAYMENT_FORM_HTML_3',
	'month');
define('CPSIGATEHTML_TXT_PAYMENT_FORM_HTML_4',
	'year');
	
define('CPSIGATEHTML_TXT_PAYMENT_PROCESS_1',
	'Введите номер кредитной карты');
define('CPSIGATEHTML_TXT_PAYMENT_PROCESS_2',
	'Введите месяц окончания срока действия карты');
define('CPSIGATEHTML_TXT_PAYMENT_PROCESS_3',
	'Введите год окончания срока действия карты');
	
define('CPSIGATEHTML_TXT_AFTER_PROCESSING_HTML_1',
	'Продолжить оплату на сервере PSiGate');
?>