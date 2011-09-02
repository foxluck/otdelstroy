<?php
	define('PAYFLOWPRO_TTL', 'PayPal Payflow Pro');
	define('PAYFLOWPRO_DSCR', 'Обработка кредитных карт через платежную систему PayPal Payflow (известную также как VeriSign). Метод Payflow Pro (https://www.paypal.com/cgi-bin/webscr?cmd=_payflow-pro-overview-outside).');
	
	define('PAYFLOWPRO_TXT_CDCURRENCY', 'Текущая валюта пользователя');
	define('PAYFLOWPRO_TXT_SALE', 'Sale (мгновенное списание денег)');
	define('PAYFLOWPRO_TXT_AUTH', 'Только авторизация');
	define('PAYFLOWPRO_TXT_CCNUMBER', 'Номер кредитной карты');
	define('PAYFLOWPRO_TXT_CVV2', 'CVV (3-значное число на обороте карты)');
	define('PAYFLOWPRO_TXT_EXPDATE', 'Истекает');
	define('PAYFLOWPRO_TXT_NORES', 'Ошибка обработки платежа - ошибка при выполнении программного обеспечения PayPal SDK');
	define('PAYFLOWPRO_TXT_ERRORPROCESSING', 'Ошибка обработки платежа.');
	define('PAYFLOWPRO_TXT_RESCODE', 'Код ответа сервера');
	define('PAYFLOWPRO_TXT_RESMSG', 'Ответ сервера');
	define('PAYFLOWPRO_TXT_DONTCHANGE', 'Не изменять');
	
	define('PAYFLOWPRO_CFG_TESTMODE_TTL', 'Тестовый режим');
	define('PAYFLOWPRO_CFG_TESTMODE_DSCR', '');
	
	define('PAYFLOWPRO_CFG_PARTNER_TTL', 'Partner');
	define('PAYFLOWPRO_CFG_PARTNER_DSCR', 'Реселлер PayPal/VeriSign, через которого вы зарегистрировались. Если вы подключлись непосредственно в PayPal или в VeriSign, введите <b>PayPal</b> или <b>VeriSign</b> соответственно.');
	
	define('PAYFLOWPRO_CFG_PWD_TTL', 'Password');
	define('PAYFLOWPRO_CFG_PWD_DSCR', 'Ваш пароль в PayPal / VeriSign');
	
	define('PAYFLOWPRO_CFG_TRANSTYPE_TTL', 'Тип авторизации');
	define('PAYFLOWPRO_CFG_TRANSTYPE_DSCR', '');
	
	define('PAYFLOWPRO_CFG_USER_TTL', 'Пользователь');
	define('PAYFLOWPRO_CFG_USER_DSCR', 'Необязательное поле. Если Вы создали профили нескольких пользователей в Вашем аккаунте PayPal/VeriSign, Вы можете явно указать пользователя, к которому будет отнесена транзакция.');
	
	define('PAYFLOWPRO_CFG_VENDOR_TTL', 'Merchant Login ID');
	define('PAYFLOWPRO_CFG_VENDOR_DSCR', 'Ваш логин в системе PayPal / VeriSign.');
	
	define('PAYFLOWPRO_CFG_TRANSCURRENCY_TTL', 'Валюта транзакции');
	define('PAYFLOWPRO_CFG_TRANSCURRENCY_DSCR', 'Вы можете выбрать валюту, в которой будет пересчитываться сумма заказа до отправки данных на сервер PayPal/VeriSign.');
	
	define('PAYFLOWPRO_CFG_SUCCESS_ORDERSTATUS_TTL', 'Статус заказа после успешной оплаты');
	define('PAYFLOWPRO_CFG_SUCCESS_ORDERSTATUS_DSCR', 'Вы можете выбрать статус заказа, который будет присваиваться всем заказам, оплата по которым была успешно авторизована. Выберите "по умолчанию", если Вы хотите, чтобы заказы приобретали статус новых заказов, который Вы можете настроить в разделе администрирования "Настройки".');
?>