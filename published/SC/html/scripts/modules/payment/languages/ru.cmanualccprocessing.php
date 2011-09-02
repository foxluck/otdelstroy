<?php
define('CMANUALCCPROCESSING_TTL',
	'Ручная обработка кредитных карт');
define('CMANUALCCPROCESSING_DSCR',
	'Сбор информации о кредитной карте на вашем сайте. Информация о картах сохраняется в зашифрованном виде.');
	
define('CMANUALCCPROCESSING_CFG_REQUESTCVV_TTL',
	'Запрашивать ввод CVV');
define('CMANUALCCPROCESSING_CFG_REQUESTCVV_DSCR',
	'Включите, если вы хотите, чтобы покупатели вводили CVV (3-х значное число на обороте карты)');
	
define('CMANUALCCPROCESSING_CFG_EMAIL4CCNUM_TTL',
	'Электронный адрес, куда нужно отправлять данные о кредитной карте');
define('CMANUALCCPROCESSING_CFG_EMAIL4CCNUM_DSCR',
	'Из соображений безопасности мы не сохраняем полный номер кредитной карты в базу данных вашего магазина. Сохраняется только половина номера карты, а вторая половина отправляется вам по электронной почте по адресу, который вы можете ввести здесь.');

define('CMANUALCCPROCESSING_TXT_EMAIL_SUBJECT',
	'Заказ #%ORDER_NUM% - информация о карте');
	
define('CMANUALCCPROCESSING_TXT_PAYMENT_FORM_HTML_1',
	'Номер кредитной карты');
define('CMANUALCCPROCESSING_TXT_PAYMENT_FORM_HTML_2',
	'Владелец карты');
define('CMANUALCCPROCESSING_TXT_PAYMENT_FORM_HTML_3',
	'Срок действия');
define('CMANUALCCPROCESSING_TXT_PAYMENT_FORM_HTML_4',
	'month');
define('CMANUALCCPROCESSING_TXT_PAYMENT_FORM_HTML_5',
	'year');
	
define('CMANUALCCPROCESSING_TXT_payment_process_1',
	'Введите номер кредитной карты');
define('CMANUALCCPROCESSING_TXT_payment_process_2',
	'Введите имя владельца кредитной карты');
define('CMANUALCCPROCESSING_TXT_payment_process_3',
	'Введите CVV (3-х значное число не обороте карты)');
define('CMANUALCCPROCESSING_TXT_payment_process_4',
	'Введите месяц окончания срока действия карты');
define('CMANUALCCPROCESSING_TXT_payment_process_5',
	'Введите год окончания срока действия карты');
?>