<?php
define('FEDEXSHIPPINGMODULE_TTL', 
	'FedEx');
define('FEDEXSHIPPINGMODULE_DSCR', 
	'FedEx Ship Manager Direct. Расчет стоимости доставки в FedEx.<br>Необходимо наличие аккаунта на www.fedex.com');
	
define('FEDEX_CNF_ACCOUNT_NUMBER_TTL', 
	'Account number');
define('FEDEX_CNF_ACCOUNT_NUMBER_DSCR', 
	'Номер учетной записи (аккаунта) в FedEx');
	
define('FEDEX_CNF_METER_NUMBER_TTL', 
	'Meter number');
define('FEDEX_CNF_METER_NUMBER_DSCR', 
	'Если у Вас нет Meter number, оставьте это поле пустым. Meter number будет сгенерирован автоматически.');
	
define('FEDEX_CNF_PACKAGING_TTL', 
	'Упаковка');
define('FEDEX_CNF_PACKAGING_DSCR', 
	'В случае, если Вы используете \'FedEx Ground\' необходимо выбрать \'Your packaging\'');
	
define('FEDEX_CNF_CARRIER_TTL', 
	'Сервис');
define('FEDEX_CNF_CARRIER_DSCR', 
	'Выберите сервис FedEx');
	
define('FEDEX_CNF_STATE_OR_PROVINCE_CODE_TTL', 
	'Штат/провинция отправителя');
define('FEDEX_CNF_STATE_OR_PROVINCE_CODE_DSCR', 
	'Обязательное поле, если страна отправления США или Канада<br />
	Введите название штата/провинции, из которой Вы отправляете заказы.');
	
define('FEDEX_CNF_POSTAL_CODE_TTL', 
	'Почтовый код (индекс, ZIP-код) отправителя');
define('FEDEX_CNF_POSTAL_CODE_DSCR', 
	'Обязательное поле, если страна отправления США или Канада<br />
	Укажите почтоый индекс места отправления заказов.');
	
define('FEDEX_CNF_COUNTRY_CODE_TTL', 
	'Страна отправитея');
define('FEDEX_CNF_COUNTRY_CODE_DSCR', 
	'Укажите страну отправления заказов.');	
	
define('FEDEX_CNF_ADDRESS_TTL', 
	'Адрес');
define('FEDEX_CNF_ADDRESS_DSCR', 
	'Введите Ваш адрес<br />
	Информация необходима для формирования Meter number');
define('FEDEX_CNF_CITY_TTL', 
	'Город');
define('FEDEX_CNF_CITY_DSCR', 
	'Информация необходима для формирования Meter number');
define('FEDEX_CNF_PHONE_NUMBER_TTL', 
	'Номер телефона');
define('FEDEX_CNF_PHONE_NUMBER_DSCR', 
	'111-222-3333<br />
	Информация необходима для формирования Meter number');
define('FEDEX_CNF_NAME_TTL', 
	'Ваше имя');
define('FEDEX_CNF_NAME_DSCR', 
	'Информация необходима для формирования Meter number');
define('FEDEX_CNF_ERROR_LOG_TTL', 
	'Включить запись ошибочных ответов сервера FedEx');
define('FEDEX_CNF_ERROR_LOG_DSCR', 
	'В случае ошибки расчета стоимости доставки, сообщение об ошибке записывается в файл temp/fedex_errors.log');

define('FEDEX_CNF_TESTMODE_TTL','Тестовый режим');
define('FEDEX_CNF_TESTMODE_DSCR','');
define('FEDEX_CNF_CURRENCY_TTL','Валюта "Доллары США"');
define('FEDEX_CNF_CURRENCY_DSCR','Стоимость доставки, расчитываемая FedEx, указывается в долларах США. Выберите валюту Вашего магазина, которая представляет собой доллары США для корректного пересчета стоимости доставки в другие валюты.');
?>