<?php
	define('TCLINK_TTL', 'TrustCommerce');
	define('TCLINK_DSCR', 'Обработка кредитных карт через через платежную систему TrustCommerce (www.trustcommerce.com)');
	
	define('TCLINK_CFG_USERID_TTL', 'Customer ID');
	define('TCLINK_CFG_USERID_DSCR', 'Идентификатор в платежной системе TrustCommerce');
	
	define('TCLINK_CFG_PWD_TTL', 'Password');
	define('TCLINK_CFG_PWD_DSCR', 'Пароль в платежной системе TrustCommerce');
	
	define('TCLINK_ACTION_PREAUTH', 'Preauth');
	define('TCLINK_ACTION_SALE', 'Sale');
	
	define('TCLINK_CFG_DEMO_TTL', 'Тестовый режим');
	define('TCLINK_CFG_DEMO_DSCR', 'Включите галочку для работы в тестовом режиме (без списания средств с кредитных карт)');
	
	define('TCLINK_CFG_ACTION_TTL', 'Тип транзакции');
	define('TCLINK_CFG_ACTION_DSCR', 'Preauth - сумма резервируется на счете клиента, но не переводится на Ваш счет автоматически; Sale - сумма автоматически переводится на Ваш счет. Для получения более подробной информации обратитесь в TrustCommerce.');
	
	define('TCLINK_CFG_ORDERSTATUS_TTL', 'Статус заказа после удачного оформления');
	define('TCLINK_CFG_ORDERSTATUS_DSCR', 'Вы можете выбрать статус заказа, который будет присваиваться всем заказам, оплата по которым была успешно авторизована. Выберите "по умолчанию", если Вы хотите, чтобы заказы приобретали статус новых заказов, который Вы можете настроить в разделе администрирования "Настройки"');
	define('TCLINK_TXT_DEFAULT', 'По умолчанию');
	
	define('TCLINK_TXT_CCNUMBER', 'Номер карты');
	define('TCLINK_TXT_CVV', 'CVV');
	define('TCLINK_TXT_EXPDATE', 'Истекает');
	
	define('TCLINK_ERROR_EXTENSION_LOADING','Не могу загрузить расширение TCLink');
	define('TCLINK_TXT_TRANSACTION_DECLINED', 'Транзакция была отклонена: ');
	define('TCLINK_TXT_BADDATA', 'Неверно форматированные данные: ');
	define('TCLINK_TXT_ERROR', 'Ошибка: ');
?>