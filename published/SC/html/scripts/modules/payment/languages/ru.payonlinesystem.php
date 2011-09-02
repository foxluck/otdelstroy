<?php
/**
 *
 *
 */
define('POS_TTL','PayOnline System');
define('POS_DSCR','Подключение электронной торговой площадки к процессинговому центру PayOnline System (по схеме Standart)');

define('POS_CFG_MERCHANT_ID_TTL','MerchantId');
define('POS_CFG_MERCHANT_ID_DSCR','Идентификатор, полученный при активации');
define('POS_CFG_SECRET_KEY_TTL','PrivateSecurityKey');
define('POS_CFG_SECRET_KEY_DSCR','Ключ, полученный при активации.');
define('POS_CFG_TRANSACTION_CURRENCY_TTL','Валюта заказа');
define('POS_CFG_TRANSACTION_CURRENCY_DSCR','Валюта, в которой будет проводиться транзакция в процессинговом центре.');
define('POS_CFG_SHOP_CURRENCY_TTL','Валюта интернет-магазина');
define('POS_CFG_SHOP_CURRENCY_DSCR','Валюта, соответствующая валюте заказа.');
define('POS_CFG_GATEWAY_TTL','Параметры перехода');
define('POS_CFG_GATEWAY_DSCR','Переход покупателя с сайта интернет-магазина на платежную страницу PayOnline System может быть выполнен по одному из перечисленных адресов.');
define('POS_CFG_VALID_UNTIL_TTL','Срок «оплатить до»');
define('POS_CFG_VALID_UNTIL_DSCR','Срок оплаты заказа в часах. Введите <strong>0</strong>, чтобы отменить ограничение.');
define('POS_CFG_CUSTOMER_LANG_TTL','Язык страницы оплаты');
define('POS_CFG_CUSTOMER_LANG_DSCR','Выбор языка платежной страницы');
define('POS_CFG_DEBUGMODE_TTL','Отладочный режим');
define('POS_CFG_DEBUGMODE_DSCR','Включите для записи информации обо всех транзакциях в служебный файл.');
define('POS_CFG_ORDERSTATUS_TTL','Автоматическая смена статуса заказа');
define('POS_CFG_ORDERSTATUS_DSCR','Все заказы, оплаченные на сайте PayOnline System, будут автоматически переведены в выбранный статус (после получения подтверждения от сервера PayOnline System).');

define('POS_CUST_RESULT_URL_TTL','Callback Url для успешных транзакций');
define('POS_CUST_RESULT_URL_DSCR','Используйте это значение для настройки на стороне PayOnline System');
define('POS_CUST_FAIL_URL_TTL','Callback Url для отклоненных транзакций');
define('POS_CUST_FAIL_URL_DSCR','Используйте это значение для настройки на стороне PayOnline System');

define('POS_TXT_PROCESS','Оплатить заказ через PayOnline System');

define('POS_TXT_CURRUB','(RUB) российский рубль');
define('POS_TXT_CURUSD','(USD) доллар США');
define('POS_TXT_CUREUR','(EUR) евро');

define('POS_TXT_GATEWAYCARD','Форма для оплаты банковской картой');
define('POS_TXT_GATEWAYSELECT','Форма выбора платежного инструмента');
define('POS_TXT_GATEWAYQIWI','Форма оплаты через QIWI');
define('POS_TXT_GATEWAYWM','Форма оплаты через WebMoney');

define('POS_TXT_LANUSER','Автоматический выбор');
define('POS_TXT_LANEN','Английский');
define('POS_TXT_LANRU','Русский');


define('POS_TXT_IAMOUNT','Сумма');
define('POS_TXT_ITRANSCATIONID','Уникальный идентификатор транзакции или счета QIWI/WebMoney');
define('POS_TXT_ICARDHOLDER','Имя держателя карты');
define('POS_TXT_ICARDNUMBER','Номер карты');
define('POS_TXT_ICOUNTRY','Страна');
define('POS_TXT_IBINCOUNTRY','Код страны, определенный по BIN эмитента карты');
define('POS_TXT_ICITY','Город');
define('POS_TXT_IADDRESS','Адрес');
define('POS_TXT_IPHONE','Номер телефона');
define('POS_TXT_IWMTRANID','Служебный номер счета в системе учета WebMoney');
define('POS_TXT_IWMINVID','Уникальный номер счета в системе учета WebMoney');
define('POS_TXT_IWMID','WMID плательщика,');
define('POS_TXT_IWMPURSE','WM-кошелек плательщика');
define('POS_TXT_IIPADDRESS','IP-адрес');
define('POS_TXT_IIPCOUNTRY','Код страны, определенный по IP-адресу');
define('POS_TXT_ISTATUS_CHANGED','Статус заказа изменен автоматически');
define('POS_TXT_ICOMMENT_ADDED','Комментарий добавлен автоматически');
define('POS_TXT_DECLINED','Транзакция отклонена');

define('POS_TXT_ERCODE1','Возникла техническая ошибка, попробуйте повторить попытку оплаты спустя некоторое время.');
define('POS_TXT_ERCODE2','Оплата банковской картой недоступна. Попробуйте воспользоваться другим способом оплаты.');
define('POS_TXT_ERCODE3','Платеж отклонен банком-эмитентом карты. Обратитесь в банк, выясните причину отказа и повторите попытку оплаты.');

?>