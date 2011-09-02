<?php
define('INTERKASSA_TTL','INTERKASSA');
define('INTERKASSA_DSCR','Система приема платежей INTERKASSA (<a href="http://www.interkassa.com" target="_blank">http://www.interkassa.com</a>)');

define('INTERKASSA_CFG_SHOP_ID_TTL','Идентификатор магазина');
define('INTERKASSA_CFG_SHOP_ID_DSCR','Идентификатор магазина, зарегистрированного в системе «INTERKASSA», на который был совершен платеж
<p>Пример: <em>64C18529-4B94-0B5D-7405-F2752F2B716C</em></p>');

define('INTERKASSA_CFG_SECRET_KEY_TTL','Секретный ключ');
define('INTERKASSA_CFG_SECRET_KEY_DSCR','Секретный ключ — это строка символов, добавляемая к реквизитам платежа, которые отправляются продавцу вместе с оповещением о новом платеже.
<br />Используется для повышения надежности идентификации оповещения и не должна быть известна третьим лицам');

define('INTERKASSA_CFG_DEBUGMODE_TTL','Режим отладки');
define('INTERKASSA_CFG_DEBUGMODE_DSCR','Включите для сохранения истории работы модуля в режиме автоматической обработки заказов');

define('INTERKASSA_CFG_PAYSYSTEM_ALIAS_TTL','Способ оплаты');
define('INTERKASSA_CFG_PAYSYSTEM_ALIAS_DSCR','Это поле позволяет заранее определить способ оплаты для покупателя. Для того чтобы покупатель мог сам выбрать способ оплаты, оставьте это поле <strong>пустым</strong>.');

define('INTERKASSA_CFG_SHOPCURRENCY_TTL','Валюта');
define('INTERKASSA_CFG_SHOPCURRENCY_DSCR','Валюта, в которой магазин передает сумму платежа платежному шлюзу «Интеркассы»');

define('INTERKASSA_CFG_ORDERSTATUS_TTL','Автоматическая смена статуса заказа');
define('INTERKASSA_CFG_ORDERSTATUS_DSCR','Все заказы, оплаченные на сайте IKI, будут автоматически переведены в выбранный статус (после получения подтверждения от сервера IKI).');

define('INTERKASSA_CUST_RESULTURL_TTL','Status URL');
define('INTERKASSA_CUST_RESULTURL_DSCR','Адрес отправки оповещения о платеже. <strong>Скопируйте и сохраните указанный в этом поле адрес в соответствующем поле внутри вашего аккаунта IKI.</strong>');

define('INTERKASSA_CUST_SUCCESURL_TTL','Success URL');
define('INTERKASSA_CUST_SUCCESURL_DSCR','Адрес страницы с уведомлением об успешно проведенном платеже. <strong>Скопируйте и сохраните указанный в этом поле адрес в соответствующем поле внутри вашего аккаунта IKI.</strong>');

define('INTERKASSA_CUST_FAILURE_TTL','Fail URL');
define('INTERKASSA_CUST_FAILURE_DSCR','Адрес страницы с уведомлением о неудавшемся платеже. <strong>Скопируйте и сохраните указанный в этом поле адрес в соответствующем поле внутри вашего аккаунта IKI.</strong>');

define('INTERKASSA_TXT_PROCESS', 'Оплатить заказ сейчас!');
define('INTERKASSA_TXT_CUSTOMER_CHOICE', 'На выбор покупателя');

