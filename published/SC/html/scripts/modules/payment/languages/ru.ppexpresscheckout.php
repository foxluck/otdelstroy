<?php
	define('PPEXPRESSCHECKOUT_TTL', 'PayPal Website Payments Pro - Express Checkout');
	define('PPEXPRESSCHECKOUT_DSCR', 'Более удобная версия модули интеграции с PayPal Pro Express Checkout.');
	define('PPEXPRESSCHECKOUT_TXT_ERRORCALLER','Could not create CallerServices instance: ');
	define('PPEXPRESSCHECKOUT_TXT_TEST','Тестовый (Sandbox)');
	define('PPEXPRESSCHECKOUT_TXT_LIVE','Рабочий');
	define('PPEXPRESSCHECKOUT_TXT_DEFAULT', 'по умолчанию');
	define('PPEXPRESSCHECKOUT_TXT_ERROR_CHECKOUT', 'Обратитесь к администратору магазина для получения дополнительной информации.');
	
	define('PPEXPRESSCHECKOUT_CFG_ENABLED_TTL', 'Включить модуль');
	define('PPEXPRESSCHECKOUT_CFG_ENABLED_DSCR', 'Если настройка выключена, покупателю не будет предложено оплатить заказ через PayPal на странице корзины покупок');
	
	define('PPEXPRESSCHECKOUT_CFG_USERNAME_TTL', 'API Username');
	define('PPEXPRESSCHECKOUT_CFG_USERNAME_DSCR', 'Введите API Username, которое было сгенерировано для Вас при подписке на PayPal Payments Pro');
	
	define('PPEXPRESSCHECKOUT_CFG_PASSWORD_TTL', 'Пароль');
	define('PPEXPRESSCHECKOUT_CFG_PASSWORD_DSCR', 'Введите пароль, который Вы указывали при подписке на PayPal Payments Pro');
	
	define('PPEXPRESSCHECKOUT_CFG_CERTPATH_TTL', 'Сертификат PayPal');
	define('PPEXPRESSCHECKOUT_CFG_CERTPATH_DSCR', 'В аккаунте на PayPal скачайте файл-сертификат (API certificate) и выберите этот файл в этой форме');
	
	define('PPEXPRESSCHECKOUT_CFG_MODE_TTL', 'Режим работы');
	define('PPEXPRESSCHECKOUT_CFG_MODE_DSCR', '');
	
	define('PPEXPRESSCHECKOUT_CFG_PAYMENTACTION_TTL', 'Способ авторизации платежа');
	define('PPEXPRESSCHECKOUT_CFG_PAYMENTACTION_DSCR', 'Sale для автоматического списания полной суммы заказа со счета клиента; Authorization и Order - только авторизация карты, уточнение суммы к списанию и само списание производятся вручную в Вашем аккаунте на PayPal');
	
	define('PPEXPRESSCHECKOUT_CFG_ORDERSTATUS_TTL', 'Статус заказа после удачного оформления');
	define('PPEXPRESSCHECKOUT_CFG_ORDERSTATUS_DSCR', 'Вы можете выбрать статус заказа, который будет присваиваться всем заказам, оплата по которым была успешно авторизована. Выберите "по умолчанию", если Вы хотите, чтобы заказы приобретали статус новых заказов, который Вы можете настроить в разделе администрирования "Настройки"');
	
	define('PPEXPRESSCHECKOUT_CFG_NOSHIPPING_TTL', 'Отключить для покупателя возможность выбора адреса доставки на сайте PayPal');
	define('PPEXPRESSCHECKOUT_CFG_NOSHIPPING_DSCR', '');
	
	define('PPEXPRESSCHECKOUT_USERINFO_PREFIX', 'PayPal подтвердил следующий адрес доставки заказа:<br>');
	
	define('PPEXPRESSCHECKOUT_CFG_TRANSCURRENCY_TTL', 'Валюта транзакций');
	define('PPEXPRESSCHECKOUT_CFG_TRANSCURRENCY_DSCR', 'Вы можете выбрать валюту, в которой будет пересчитываться сумма заказа до отправки данных на сервер PayPal.');
	
	define('PPECHECKOUT_TXT_SHIPPINGINFO', 'Информация о доставке');
	define('PPECHECKOUT_TXT_SHIPPINGMETHOD', 'Способ доставки');
	define('PPECHECKOUT_TXT_SHIPPINGADDRESS', 'Адрес доставки');
	define('PPECHECKOUT_TXT_BILLINGINFO','Информация о плательщике');
	define('PPECHECKOUT_TXT_PAYERINFO', 'Плательщик');
	define('PPECHECKOUT_TXT_ORDERDETAILS', 'Заказ');
	define('PPECHECKOUT_TXT_PAYMENTMETHOD', 'Способ оплаты');
	define('PPECHECKOUT_TXT_CUSTCOMMENT', 'PayPal payer email - %s PayPal transaction # %s');
	define('PPECHECKOUT_TXT_CDCURRENCY', 'Валюта покупателя');
	
	define('PPEC_TXT_ORDER_DETAILS', 'Детали заказа');
	define('PPEC_TXT_TRANSACTION_ID', 'Номер транзакции в PayPal');
?>