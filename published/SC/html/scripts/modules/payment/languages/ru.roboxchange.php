<?php
define('ROBOXCHANGE_TTL', 'ROBOXchange');
define('ROBOXCHANGE_DSCR', 'Интеграция с платежной системой <a href="http://www.roboxchange.com" target="_blank">ROBOXchange</a> с сервисом <a href="http://www.robokassa.ru" target="_blank">ROBOkassa</a>.');

define('ROBOXCHANGE_CFG_LANG_TTL', 'Язык интерфейса');
define('ROBOXCHANGE_CFG_LANG_DSCR', 'Выберите язык интерфейса на сервере ROBOXchange, который увидит покупатель при оплате');

define('ROBOXCHANGE_TXT_LANGRU', 'Русский');
define('ROBOXCHANGE_TXT_LANGEN', 'Английский');
define('ROBOXCHANGE_TXT_LANUSER', '(не определен)');

define('ROBOXCHANGE_CFG_MERCHANTLOGIN_TTL', 'Логин магазина в обменном пункте');
define('ROBOXCHANGE_CFG_MERCHANTLOGIN_DSCR', 'Информация о вашем аккаунте продавца в платежной системе ROBOXchange');

define('ROBOXCHANGE_CFG_MERCHANT_ID_TTL', 'Номер счета в ROBOkassa');
define('ROBOXCHANGE_CFG_MERCHANT_ID_DSCR', '');

define('ROBOXCHANGE_CFG_ROBOXCURRENCY_TTL', 'Выберите валюту обменного пункта');
define('ROBOXCHANGE_CFG_ROBOXCURRENCY_DSCR', 'Предлагаемая валюта платежа. Покупатель может изменить ее в процессе оплаты.');

//define('ROBOXCHANGE_CFG_SHOPCURRENCY_TTL', 'Выберите валюту магазина, которой соответсвует выбранная вами валюта обменного пункта');
//define('ROBOXCHANGE_CFG_SHOPCURRENCY_DSCR', 'Выберите из списка валют вашего интернет-магазина');
define('ROBOXCHANGE_CFG_SHOPCURRENCY_TTL', 'Выберите валюту магазина');
define('ROBOXCHANGE_CFG_SHOPCURRENCY_DSCR', 'Выберите из списка валют вашего интернет-магазина, которой соответствует выбранная вами в настройках аккаунта на сервере ROBOXchange "Валюта Продавца"');

define('ROBOXCHANGE_CFG_MERCHANTPASS1_TTL', 'Пароль №1');
define('ROBOXCHANGE_CFG_MERCHANTPASS1_DSCR', 'Вводится в настройках аккаунта на сервере ROBOXchange.');
define('ROBOXCHANGE_CFG_MERCHANTPASS2_TTL', 'Пароль №2');
define('ROBOXCHANGE_CFG_MERCHANTPASS2_DSCR', 'Вводится в настройках аккаунта на сервере ROBOXchange.');

define('ROBOXCHANGE_TXT_NOCURR', 'ОШИБКА: Не удалось получить список валют с сервера ROBOXchange');

define('ROBOXCHANGE_TXT_PROCESS', 'Оплатить заказ сейчас!');

define('ROBOXCHANGE_CFG_TESTMODE_TTL', 'Тестовый режим');
define('ROBOXCHANGE_CFG_TESTMODE_DSCR', '');
define('ROBOXCHANGE_CFG_ORDERSTATUS_TTL', 'Статус заказа после подтверждения оплаты');
define('ROBOXCHANGE_CFG_ORDERSTATUS_DSCR', 'Все оплаченные на сайте ROBOXchange заказы будут автоматически переведены в выбранный статус (по факту получения сообщения от сервера ROBOXchange).');


define('ROBOXCHANGE_CUST_RESULTURL_TTL', 'Result URL');
define('ROBOXCHANGE_CUST_RESULTURL_DSCR', 'Адрес отправки оповещения о платеже. <strong>Указанный в этом поле адрес скопируйте и сохраните в соответствующем поле внутри вашего аккаунта ROBOkassa.</strong>');

define('ROBOXCHANGE_CUST_SUCCESURL_TTL', 'Success URL');
define('ROBOXCHANGE_CUST_SUCCESURL_DSCR', 'Адрес страницы с уведомлением об успешно проведенном платеже. <strong>Указанный в этом поле адрес скопируйте и сохраните в соответствующем поле внутри вашего аккаунта ROBOkassa.</strong>');

define('ROBOXCHANGE_CUST_FAILURE_TTL', 'Fail URL');
define('ROBOXCHANGE_CUST_FAILURE_DSCR', 'Адрес страницы с уведомлением о неуспешном платеже. <strong>Указанный в этом поле адрес скопируйте и сохраните в соответствующем поле внутри вашего аккаунта ROBOkassa.</strong>');
?>