<?php
	define('HSBC_TTL', 'HSBC');
	define('HSBC_DSCR', 'Обработка кредитных карт через платежную систему банка HSBC (www.hsbc.com). CPI Integration.<br />Модуль требует наличия SSL-сертификата для доменного имени, на котором работает Ваш интернет-магазин.');
	
	define('HSBC_CFG_MODE_TTL', 'Режим работы модуля');
	define('HSBC_CFG_MODE_DSCR', '');
	define('HSBC_TXT_PMODE', 'Рабочий режим. Сумма заказа списывается со счета клиента.');
	define('HSBC_TXT_TMODE', 'Тестовый режим. С карты клиента не списываются деньги.');
	
	define('HSBC_CFG_STOREFRONTID_TTL', 'Storefront ID of your CPI service (Client Alias)');
	define('HSBC_CFG_STOREFRONTID_DSCR', 'Эту информацию отправляется Вам HSBC по электронной почте. Пример: UK12345678CUR');
	
	define('HSBC_CFG_TRANTYPE_TTL', 'Тип транзакции');
	define('HSBC_CFG_TRANTYPE_DSCR', 'Auth - сумма резервируется на счете клиента, но не переводится на Ваш счет автоматически; Sale - сумма автоматически переводится на Ваш счет. Для получения более подробной информации обратитесь в Innovative Gateway.');
	define('HSBC_TXT_TRANTYPE_AUTH', 'Auth');
	define('HSBC_TXT_TRANTYPE_CAPTURE', 'Capture');
	
	define('HSBC_CFG_USERID_TTL', 'User ID');
	define('HSBC_CFG_USERID_DSCR', 'Эту информацию отправляет Вам HSBC по электронной почте');

	define('HSBC_CFG_SHAREDSECRET_TTL', 'Shared Secret');
	define('HSBC_CFG_SHAREDSECRET_DSCR', 'Эту информацию отправляет Вам HSBC по электронной почте');

	define('HSBC_CFG_TRANSCURR_TTL', 'Валюта транзакций');
	define('HSBC_CFG_TRANSCURR_DSCR', 'Сумма заказа будет переведена в выбранную валюту по курсу Вашего магазина, после чего данные будут переданы на сервер платежной системы.<br /><b>В настоящее время HSBC принимает только значения в GBP!</b>');

	define('HSBC_CFG_CURCODE_TTL', 'Цифровой ISO-код валюты, выбранной выше');
	define('HSBC_CFG_CURCODE_DSCR', 'В настоящее время HSBC принимает только значения в GBP. Введите 826 в этом поле (это ISO-код фунтов стерлинга).');
	
	define('HSBC_SUBMIT_BTN','Перейти к оплате на защищенном сервере банка HSBC');
?>