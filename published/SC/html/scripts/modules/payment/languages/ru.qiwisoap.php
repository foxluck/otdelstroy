<?php
/**
 *
 * QiwiSOAP
 */
define('QIWI_TTL','Кошелек QIWI');
define('QIWI_DSCR','Интеграция с платежной системой QIWI по протоколу SOAP');
define('QIWI_CFG_LOGIN_TTL','Идентификатор (логин)');
define('QIWI_CFG_LOGIN_DSCR','');
define('QIWI_CFG_PASSWORD_TTL','Пароль');
define('QIWI_CFG_PASSWORD_DSCR','');
define('QIWI_CFG_PREFIX_TTL',	'Префикс счета');
define('QIWI_CFG_PREFIX_DSCR',	'Используйте цифры и латинские буквы для ввода префикса номера счета в системе QIWI');
define('QIWI_CFG_LIFETIME_TTL','Время жизни счета');
define('QIWI_CFG_LIFETIME_DSCR','Укажите срок оплаты счета в часах');
define('QIWI_CFG_CUSTOMER_PHONE_TTL','Телефон покупателя');
define('QIWI_CFG_CUSTOMER_PHONE_DSCR','Выберите поле вашей формы регистрации, соответствующее телефонному номеру покупателя');
define('QIWI_CFG_ALARM_TTL','Уведомления');
define('QIWI_CFG_ALARM_DSCR','Параметры отправки уведомлений');
define('QIWI_CFG_CURRENCY_TTL','Рубли');
define('QIWI_CFG_CURRENCY_DSCR','Выберите соответствующую валюту для правильного пересчета стоимости заказа');
define('QIWI_CFG_SUCCESS_STATUS_TTL','Статус оплаченных');
define('QIWI_CFG_SUCCESS_STATUS_DSCR','Выберите статус для успешно оплаченного заказа');
define('QIWI_CFG_CANCEL_STATUS_TTL','Статус отмененных');
define('QIWI_CFG_CANCEL_STATUS_DSCR','Выберите статус для отмененных заказов');
define('QIWI_CFG_TESTMODE_TTL',		'Обрабатывать запросы без пароля');
define('QIWI_CFG_TESTMODE_DSCR',		'Используйте этот режим для обработки запросов, инициированных вручную из личного кабинета QIWI.');

define('QIWI_CUST_SOAP_URL_TTL','URL');
define('QIWI_CUST_SOAP_URL_DSCR','Используйте это значение для настройки взаимодействия по протоколу SOAP в личном кабинете QIWI');
define('QIWI_TXT_QIWI_ROBOT','Уведомление платежной системы QIWI');
define('QIWI_TXT_CUSTOMER_PHONE','Мобильный телефон');
define('QIWI_TXT_INVALID_CUSTOMER_PHONE','Некорректный номер мобильного телефона');
define('QIWI_TXT_INVALID_AMOUNT','Неверная сумма платежа');
define('QIWI_TXT_MANUAL','Запрос инициирован без пароля');

define('QIWI_TXT_ALARM0','не оповещать');
define('QIWI_TXT_ALARM1','уведомление SMS-сообщением');
define('QIWI_TXT_ALARM2','уведомление звонком');
?>