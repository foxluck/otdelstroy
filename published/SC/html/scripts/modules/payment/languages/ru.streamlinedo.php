<?php
	define('STREAMLINEDO_TTL', 'Streamline E-Solutions');
	define('STREAMLINEDO_DSCR', 'Обработки кредитных карт через платежную систему Streamline eSolutions (Royal Bank of Scotland Group). Веб-сайт: www.streamline-esolutions.com<br />Метод интеграции Direct Order.');
	
	define('STREAMLINEDO_CFG_MERCHANTCODE_TTL', 'Merchant code');
	define('STREAMLINEDO_CFG_MERCHANTCODE_DSCR', 'Введите merchant code, предоставленный Streamline');
	
	define('STREAMLINEDO_CFG_XMLPASSWORD_TTL', 'XML password');
	define('STREAMLINEDO_CFG_XMLPASSWORD_DSCR', 'Ваш пароль Streamline');
	
	define('STREAMLINEDO_CFG_TEST_TTL', 'Тестовый режим');
	define('STREAMLINEDO_CFG_TEST_DSCR', 'Включите галочку для работы в тестовом режиме (без списания средств с кредитных карт)');
	
	define('STREAMLINEDO_CFG_TRANSCURR_TTL', 'Валюта транзакций');
	define('STREAMLINEDO_CFG_TRANSCURR_DSCR', 'Сумма заказа будет переведена в выбранную валюту по курсу Вашего магазина, после чего данные будут переданы на сервер платежной системы.');
	
	define('STREAMLINEDO_CFG_ORDERSTATUS_TTL', 'Статус заказа после удачного оформления');
	define('STREAMLINEDO_CFG_ORDERSTATUS_DSCR', 'Вы можете выбрать статус заказа, который будет присваиваться всем заказам, оплата по которым была успешно авторизована. Выберите "по умолчанию", если Вы хотите, чтобы заказы приобретали статус новых заказов, который Вы можете настроить в разделе администрирования "Настройки"');
	
	define('STREAMLINEDO_TXT_DEFAULT', 'По умолчанию');
	
	define('STREAMLINEDO_TXT_CCTYPE', 'Тип кредитной карты');
	define('STREAMLINEDO_TXT_CCNUMBER', 'Номер карты');
	define('STREAMLINEDO_TXT_CVC', 'CVC (3-х значное число на обороте карты)');
	define('STREAMLINEDO_TXT_EXPDATE', 'Истекает');
	
	define('STREAMLINEDO_TXT_AUTHDECLINED', 'Транзакция была отклонена: %s');
?>