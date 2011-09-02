<?php
	define('LPAPICC_TTL', 'LinkPoint/YourPay API');
	define('LPAPICC_DSCR', 'Обработка кредитных карт через платежную систему LinkPoint/YourPay (www.linkpoint.com). Метод интеграции LinkPoint API. Это более продвинутый и "тесный" метод интеграции с LinkPoint: информация о кредитной карте вводится на страницах Вашего интернет-магазина, что требует наличия SSL-сертификата для доменного имени, где работает Ваш интернет-магазин');
	
	define('LPAPICC_TXT_AVAILABLECREDITCARDS', 'Мы принимаем к оплате');
	define('LPAPICC_TXT_CCNUMBER', 'Номер карты:');
	define('LPAPICC_TXT_CVV', 'CVV (3-значное число на обороте карты):');
	define('LPAPICC_TXT_EXPDATE','Истекает:');
	
	define('LPAPICC_TXT_TESTGOOD','Тестовый режим (все транзакции принимаются)');
	define('LPAPICC_TXT_TESTDECLINE','Тестовый режим (все транзакции отвергаются)');
	define('LPAPICC_TXT_LIVE','Рабочий режим');
	define('LPAPICC_TXT_DEFAULT', 'По умолчанию');
	
	define('LPAPICC_MSG_UNKNOWNCCTYPE', 'Неизвестный тип кредитной карты');
	define('LPAPICC_MSG_UNAVAILABLECCTYPE', 'Мы не принимаем к оплате "%cardname%". Приносим извинения за неудобства.');
	
	define('LPAPICC_CFG_MERCHNUMBER_TTL', 'Store Number');
	define('LPAPICC_CFG_MERCHNUMBER_DSCR', 'Идентификатор аккаунта в LinkPoint/YourPay. Обычно это шести- или десяти- значное число.');
	
	define('LPAPICC_CFG_CERTPATH_TTL', 'PEM certificate');
	define('LPAPICC_CFG_CERTPATH_DSCR', 'Вы можете загрузить файл PEM сертификата в Вашем аккаунте на <a href="https://www.linkpointcentral.com" target="_blank" class="standard">LinkPoint Central</a>');
	
	define('LPAPICC_CFG_MODE_TTL', 'Режим');
	define('LPAPICC_CFG_MODE_DSCR', 'Выберите режим работы модуля');
	
	define('LPAPICC_CFG_HOST_TTL', 'URL отправки транзакции');
	define('LPAPICC_CFG_HOST_DSCR', 'Укажите адрес, по которому будет отправлена информация о заказе в LinkPoint/YourPay (Вы должны были получить этот адрес по электронной почте)');
	
	define('LPAPICC_CFG_PAYMENTACTION_TTL', 'Способ авторизации');
	define('LPAPICC_CFG_PAYMENTACTION_DSCR', 'Спосиоб авторизации оплаты по карте. Для получения более подробной информации обратитесь в службу поддержки LinkPoint/YourPay.');
	
	define('LPAPICC_CFG_CVV_TTL', 'Запрашивать CVV');
	define('LPAPICC_CFG_CVV_DSCR', 'Выберите запрашивать у покупателей ввод CVV или нет');
	
	define('LPAPICC_CFG_AVAILABLECREDITCARDS_TTL', 'Поддерживаемые типы карт');
	define('LPAPICC_CFG_AVAILABLECREDITCARDS_DSCR', 'Выберите типы кредитных карт, оплату по которым Вы принимаете');
	
	define('LPAPICC_CFG_ORDERSTATUS_TTL', 'Статус заказа после удачного оформления');
	define('LPAPICC_CFG_ORDERSTATUS_DSCR', 'Вы можете выбрать статус заказа, который будет присваиваться всем заказам, оплата по которым была успешно авторизована. Выберите "по умолчанию", если Вы хотите, чтобы заказы приобретали статус новых заказов, который Вы можете настроить в разделе администрирования "Настройки"');
	
	define('LPAPICC_CFG_CURRENCY_TTL', 'Доллары США');
	define('LPAPICC_CFG_CURRENCY_DSCR', 'Сумма заказа, передаваемая в LinkPoint, указывается в долларах США. Выберите валюту из списка, которая представляет собой доллары США - это необходимо для корректного пересчета суммы заказа в доллары. Если валюта не выбрана, сумма не будет пересчитываться');
?>