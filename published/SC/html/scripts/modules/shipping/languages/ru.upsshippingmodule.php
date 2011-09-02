<?php
define('UPSSHIPPINGMODULE_TTL',
	'UPS');
define('UPSSHIPPINGMODULE_DSCR',
	'UPS OnLine Tools. Расчет стоимости доставки в UPS.<br>Необходимо наличие аккаунта на www.ups.com');
	
define('UPSSHIPPINGMODULE_CFG_PACKAGE_TYPE_TTL',
	'Тип упаковки');
define('UPSSHIPPINGMODULE_CFG_PACKAGE_TYPE_DSCR',
	'');
define('UPSSHIPPINGMODULE_CFG_PICKUP_TYPE_TTL',
	'Pickup type');
define('UPSSHIPPINGMODULE_CFG_PICKUP_TYPE_DSCR',
	'Способ передачи посылки в UPS');
define('UPSSHIPPINGMODULE_CFG_ACCESSLICENSENUMBER_TTL',
	'XML Access Key');
define('UPSSHIPPINGMODULE_CFG_ACCESSLICENSENUMBER_DSCR',
	'Введите XML ключ доступа, предоставленный компанией UPS');
define('UPSSHIPPINGMODULE_CFG_USERID_TTL',
	'UPS User ID');
define('UPSSHIPPINGMODULE_CFG_USERID_DSCR',
	'Ваш идентификатор (имя пользователя в UPS)');
define('UPSSHIPPINGMODULE_CFG_PASSWORD_TTL',
	'UPS Password');
define('UPSSHIPPINGMODULE_CFG_PASSWORD_DSCR',
	'Пароль доступа к Вашему UPS-аккаунту');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_COUNTRY_ID_TTL',
	'Страна отправителя');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_COUNTRY_ID_DSCR',
	'Выберите страну отправителя (Вашего интернет-магазина)');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_CITY_TTL',
	'Город отправителя');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_CITY_DSCR',
	'Город, из которого будет производиться отправка заказов');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_POSTALCODE_TTL',
	'Почтовый код (индекс, ZIP-код) отправителя');
define('UPSSHIPPINGMODULE_CFG_SHIPPER_POSTALCODE_DSCR',
	'Индекс отправителя (Вашего интернет-магазина). Обязательно для заполнения');
define('UPSSHIPPINGMODULE_CFG_ENABLE_ERROR_LOG_TTL', 'Включить запись ошибочных ответов сервера UPS');
define('UPSSHIPPINGMODULE_CFG__ENABLE_ERROR_LOG_DSCR', 'В случае ошибки расчета стоимости доставки, сообщение об ошибке записывается в файл temp/ups_errors.log');

define('UPSSHIPPINGMODULE_CFG_USD_CURRENCY_TTL', 'Валюта "Доллары США"');
define('UPSSHIPPINGMODULE_CFG_USD_CURRENCY_DSCR', 'Стоимость доставки, расчитываемая UPS, указывается в долларах США. Выберите валюту Вашего магазина, которая представляет собой доллары США для корректного пересчета стоимости доставки в другие валюты.');

define('UPSSHIPPINGMODULE_CFG_CUSTOMER_CLASSIFICATION_TTL', 'Классификация покупателей Вашего магазина');
define('UPSSHIPPINGMODULE_CFG_CUSTOMER_CLASSIFICATION_DSCR', 'Укажите "по умолчанию", чтобы использовать значения UPS по умолчанию');
?>